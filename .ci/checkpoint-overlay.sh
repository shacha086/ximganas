#!/usr/bin/env bash
set -euo pipefail

operation="${1:?preflight, restore or save}"
key="${2:?cache compatibility key}"
repository="$(gh api "repos/$GITHUB_REPOSITORY" --jq .full_name)"
branch="$(gh api "repos/$repository" --jq .default_branch)"
namespace="$(printf '%s' "$GITHUB_REF_NAME" | sha256sum | cut -c1-12)"
prefix="ci-overlay-${key}-${namespace}-"
directory="${RUNNER_TEMP:?}/xigmanas-overlay-checkpoint"
metadata="$RUNNER_TEMP/xigmanas-overlay-metadata"
context="$RUNNER_TEMP/xigmanas-overlay-parent.json"
remote=/var/tmp/xigmanas-overlay
helper="$GITHUB_WORKSPACE/.ci/overlay-state.py"
mkdir -p "$directory" "$metadata"

if [[ "$operation" == preflight ]]; then
  release_id="$(gh api --method POST "repos/$repository/releases" \
    -f tag_name="${prefix}probe-$GITHUB_RUN_ID-$GITHUB_RUN_ATTEMPT" \
    -f target_commitish="$branch" -F draft=true --jq .id)"
  gh api --method DELETE "repos/$repository/releases/$release_id"
  exit 0
fi

manifest() {
  local tag="$1" destination="$metadata/$1"
  [[ "$tag" == "$prefix"* && "$tag" =~ ^[A-Za-z0-9._-]+$ ]]
  mkdir -p "$destination"
  if [[ ! -f "$destination/manifest.json" ]]; then
    gh release download "$tag" --repo "$repository" --pattern manifest.json --dir "$destination"
  fi
  jq -e --arg key "$key" --arg namespace "$namespace" --arg tag "$tag" --arg prefix "$prefix" \
    '.format == 2 and .key == $key and .namespace == $namespace and .tag == $tag and
    (.kind == "full" or .kind == "overlay") and
    (.depth >= 0 and .depth <= 8) and
    (if .kind == "full" then .parent == null and .depth == 0
    else .depth > 0 and (.parent | startswith($prefix)) end)' \
    "$destination/manifest.json" >/dev/null
}

publish() {
  local tag="$1" title="$2" asset attempt uploaded draft
  if draft="$(gh release view "$tag" --repo "$repository" --json isDraft --jq .isDraft 2>/dev/null)"; then
    [[ "$draft" == true ]] || { echo "Refusing to replace published layer $tag" >&2; return 1; }
  else
    gh release create "$tag" --repo "$repository" --target "$branch" \
      --draft --prerelease --latest=false --title "$title" \
      --notes "Branch-isolated build checkpoint layer; not an installable image."
  fi
  for asset in "$directory"/*; do
    [[ -f "$asset" ]]
    uploaded=false
    for attempt in 1 2 3; do
      if gh release upload "$tag" "$asset" --clobber --repo "$repository"; then
        uploaded=true
        break
      fi
      sleep "$((attempt * 10))"
    done
    [[ "$uploaded" == true ]]
  done
  gh release edit "$tag" --repo "$repository" --target "$branch" --draft=false
}

releases() {
  gh api --paginate "repos/$repository/releases?per_page=100" \
    --jq ".[] | select(.draft == false and (.tag_name | startswith(\"$prefix\"))) |
    select(any(.assets[]; .name == \"manifest.json\")) | [.created_at,.tag_name] | @tsv" |
    sort -r
}

restore_layer() {
  local tag="$1" location="$metadata/$1" kind
  manifest "$tag"
  kind="$(jq -r .kind "$location/manifest.json")"
  gh release download "$tag" --repo "$repository" --dir "$location" \
    --pattern 'state.tar.zst.*' --pattern SHA256SUMS \
    --pattern changed.json --pattern deleted.json --pattern stats.json --skip-existing
  (cd "$location" && sha256sum -c SHA256SUMS)
  if [[ "$kind" == overlay ]]; then
    rsync -a "$location/changed.json" "$location/deleted.json" "freebsd:$remote/"
    ssh freebsd "/usr/local/bin/python3.11 '$helper' prepare '$remote'"
  fi
  cat "$location"/state.tar.zst.* | zstd -dc | ssh freebsd 'tar -xpf - -C /'
  rm -f "$location"/state.tar.zst.*
}

retain_chains() {
  local listing tag parent count=0 depth
  local -A keep=()
  listing="$(releases)" || return 0
  while IFS=$'\t' read -r _ tag; do
    [[ -n "$tag" ]] || continue
    ((count += 1))
    ((count <= 2)) || break
    parent="$tag"
    for ((depth = 0; depth <= 8; depth++)); do
      [[ -n "$parent" ]] || break
      [[ -z "${keep[$parent]:-}" ]] || break
      manifest "$parent" || return 0
      keep["$parent"]=1
      parent="$(jq -r '.parent // empty' "$metadata/$parent/manifest.json")"
    done
    [[ -z "$parent" || -n "${keep[$parent]:-}" ]] || return 0
  done <<< "$listing"
  while IFS=$'\t' read -r _ tag; do
    [[ -n "$tag" ]] || continue
    if [[ -z "${keep[$tag]:-}" ]]; then
      gh release delete "$tag" --cleanup-tag --yes --repo "$repository" ||
        echo "::warning::Could not remove obsolete overlay layer $tag"
    fi
  done <<< "$listing"
}

if [[ "$operation" == restore ]]; then
  latest="$(releases | awk -F '\t' 'NR == 1 {print $2}')"
  fallback_run=0
  if [[ -f "$directory/manifest.json" ]] &&
      jq -e --arg key "$key" --arg namespace "$namespace" \
      '.format == 2 and .key == $key and .namespace == $namespace' \
      "$directory/manifest.json" >/dev/null; then
    fallback_run="$(jq -r .run_id "$directory/manifest.json")"
  fi
  [[ "$fallback_run" =~ ^[0-9]+$ ]] || fallback_run=0
  release_run=0
  if [[ -n "$latest" ]]; then
    manifest "$latest"
    release_run="$(jq -r .run_id "$metadata/$latest/manifest.json")"
  fi
  ssh freebsd "mkdir -p '$remote'"
  if [[ -n "$latest" || "$fallback_run" != 0 ]]; then
    fallback=false
    if ((fallback_run > release_run)); then
      fallback=true
      latest="$(jq -r .tag "$directory/manifest.json")"
      mkdir -p "$metadata/$latest"
      cp "$directory/manifest.json" "$metadata/$latest/manifest.json"
    fi
    parent="$latest"
    chain=()
    for ((depth = 0; depth <= 8; depth++)); do
      manifest "$parent"
      chain+=("$parent")
      kind="$(jq -r .kind "$metadata/$parent/manifest.json")"
      [[ "$kind" != full ]] || break
      parent="$(jq -r .parent "$metadata/$parent/manifest.json")"
    done
    [[ "$kind" == full ]] || { echo 'Overlay chain is incomplete.' >&2; exit 1; }
    for ((index = ${#chain[@]} - 1; index >= 0; index--)); do
      tag="${chain[$index]}"
      if [[ "$fallback" == true && "$tag" == "$latest" ]]; then
        (cd "$directory" && sha256sum -c SHA256SUMS)
        if [[ "$(jq -r .kind "$directory/manifest.json")" == overlay ]]; then
          rsync -a "$directory/changed.json" "$directory/deleted.json" "freebsd:$remote/"
          ssh freebsd "/usr/local/bin/python3.11 '$helper' prepare '$remote'"
        fi
        cat "$directory"/state.tar.zst.* | zstd -dc | ssh freebsd 'tar -xpf - -C /'
      else
        restore_layer "$tag"
      fi
    done
    if [[ "$fallback" == true ]]; then
      # A later overlay must not reference a layer that only exists in Actions cache.
      publish "$latest" "Overlay cache: recovered checkpoint ($GITHUB_REF_NAME)"
    fi
    cp "$metadata/$latest/manifest.json" "$context"
    echo "Restored overlay chain: ${#chain[@]} layers, head $latest" >> "$GITHUB_STEP_SUMMARY"
  else
    legacy_prefix="ci-cache-${key}-"
    legacy="$(gh api --paginate "repos/$repository/releases?per_page=100" \
      --jq ".[] | select(.draft == false and (.tag_name | startswith(\"$legacy_prefix\"))) |
      select(any(.assets[]; .name == \"manifest.json\")) | [.created_at,.tag_name] | @tsv" |
      sort -r | awk -F '\t' 'NR == 1 {print $2}')"
    if [[ -z "$legacy" ]]; then
      rm -f "$context"
      echo 'No compatible checkpoint. Bootstrapping on the Actions VM.' >> "$GITHUB_STEP_SUMMARY"
      exit 0
    fi
    rm -f "$directory"/*
    gh release download "$legacy" --repo "$repository" --dir "$directory"
    jq -e --arg key "$key" '.format == 1 and .key == $key' "$directory/manifest.json" >/dev/null
    (cd "$directory" && sha256sum -c SHA256SUMS)
    cat "$directory"/state.tar.zst.* | zstd -dc | ssh freebsd 'tar -xpf - -C /'
    bytes="$(find "$directory" -name 'state.tar.zst.*' -printf '%s\n' | awk '{sum += $1} END {printf "%.0f", sum}')"
    tag="${prefix}base-$GITHUB_RUN_ID-$GITHUB_RUN_ATTEMPT"
    source_sha="$(jq -r .source_sha "$directory/manifest.json")"
    jq -n --arg key "$key" --arg namespace "$namespace" --arg tag "$tag" \
      --arg sha "$source_sha" --arg run "$GITHUB_RUN_ID" --arg legacy "$legacy" \
      --argjson bytes "$bytes" \
      '{format:2,key:$key,namespace:$namespace,tag:$tag,kind:"full",parent:null,
      depth:0,source_sha:$sha,run_id:$run,imported_from:$legacy,
      base_bytes:$bytes,delta_bytes:0}' > "$directory/manifest.json"
    echo '[]' > "$directory/changed.json"
    echo '[]' > "$directory/deleted.json"
    publish "$tag" "Overlay cache: imported base ($GITHUB_REF_NAME)"
    cp "$directory/manifest.json" "$context"
    echo "Imported $legacy once as isolated base $tag ($bytes bytes)." >> "$GITHUB_STEP_SUMMARY"
  fi
  rm -f "$directory"/*
  ssh freebsd "sh '$GITHUB_WORKSPACE/.ci/restore-accounts.sh'"
  ssh freebsd 'rm -f /usr/local/xigmanas/state/dirty /usr/local/xigmanas/state/rootfs-sha \
    /usr/local/xigmanas/state/pkg-db-sha /usr/local/xigmanas/state/installed-*'
  ssh freebsd "/usr/local/bin/python3.11 '$helper' snapshot '$remote/baseline.json'"
  ssh freebsd 'if [ "$(cat /usr/local/xigmanas/state/overlay-cache-format 2>/dev/null)" != 2 ]; then
    echo 2 > /usr/local/xigmanas/state/overlay-cache-format
    touch /usr/local/xigmanas/state/dirty
  fi'
  exit 0
fi

[[ "$operation" == save ]] || exit 2
if ! ssh freebsd 'test -f /usr/local/xigmanas/state/dirty'; then
  echo 'No expensive state changed; retaining the existing overlay chain.'
  echo 'Overlay backup skipped: reusable build cache is unchanged.' >> "$GITHUB_STEP_SUMMARY"
  exit 0
fi
ssh freebsd 'if [ ! -x /usr/local/bin/python3.11 ]; then
  env ASSUME_ALWAYS_YES=yes IGNORE_OSVERSION=yes pkg install -y python311
fi'
parent=''
depth=0
base_bytes=0
previous_delta=0
kind=full
if [[ -f "$context" ]]; then
  parent="$(jq -r .tag "$context")"
  depth="$(jq -r .depth "$context")"
  base_bytes="$(jq -r .base_bytes "$context")"
  previous_delta="$(jq -r .delta_bytes "$context")"
  kind=overlay
fi
if [[ "$kind" == full ]] || ((depth >= 8 || previous_delta >= base_bytes / 2)); then
  kind=full
  ssh freebsd "/usr/local/bin/python3.11 '$helper' full '$remote'"
  depth=0
else
  ssh freebsd "/usr/local/bin/python3.11 '$helper' diff '$remote/baseline.json' '$remote'"
  ((depth += 1))
fi
rm -f "$directory"/*
rsync -a "freebsd:$remote/changed.json" "freebsd:$remote/deleted.json" \
  "freebsd:$remote/stats.json" "$directory/"
changed="$(jq -r .changed "$directory/stats.json")"
deleted="$(jq -r .deleted "$directory/stats.json")"
if [[ "$kind" == overlay ]] && ((changed == 0 && deleted == 0)); then
  echo 'No cache file changes; no overlay uploaded.' >> "$GITHUB_STEP_SUMMARY"
  exit 0
fi
ssh freebsd "tar -cpf - -C / --no-recursion --null -T '$remote/files.list'" |
  zstd -T2 -3 | split -b 1500M -d -a 3 - "$directory/state.tar.zst."
bytes="$(find "$directory" -name 'state.tar.zst.*' -printf '%s\n' | awk '{sum += $1} END {printf "%.0f", sum}')"
tag="${prefix}${kind}-$GITHUB_RUN_ID-$GITHUB_RUN_ATTEMPT"
if [[ "$kind" == full ]]; then
  base_bytes="$bytes"
  total_delta=0
  parent=''
else
  total_delta=$((previous_delta + bytes))
fi
jq -n --arg key "$key" --arg namespace "$namespace" --arg tag "$tag" \
  --arg kind "$kind" --arg parent "$parent" --arg sha "$BUILD_SHA" --arg run "$GITHUB_RUN_ID" \
  --argjson depth "$depth" --argjson base_bytes "$base_bytes" --argjson delta_bytes "$total_delta" \
  '{format:2,key:$key,namespace:$namespace,tag:$tag,kind:$kind,
  parent:(if $parent == "" then null else $parent end),depth:$depth,
  source_sha:$sha,run_id:$run,base_bytes:$base_bytes,delta_bytes:$delta_bytes}' > "$directory/manifest.json"
(cd "$directory" && sha256sum state.tar.zst.* changed.json deleted.json stats.json > SHA256SUMS)
echo 'archive_ready=true' >> "$GITHUB_OUTPUT"
publish "$tag" "Overlay cache: $kind ($GITHUB_REF_NAME, $GITHUB_RUN_ID)"
echo "Saved $kind: $bytes bytes, $changed changed paths, $deleted removals, depth $depth." >> "$GITHUB_STEP_SUMMARY"
retain_chains
