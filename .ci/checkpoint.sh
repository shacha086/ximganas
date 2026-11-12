#!/usr/bin/env bash
set -euo pipefail

operation="${1:?restore or save}"
key="${2:?cache compatibility key}"
repository="$(gh api "repos/$GITHUB_REPOSITORY" --jq .full_name)"
branch="$(gh api "repos/$repository" --jq .default_branch)"
directory="${RUNNER_TEMP:?}/xigmanas-checkpoint"
prefix="ci-cache-${key}-"
mkdir -p "$directory"

if [[ "$operation" == preflight ]]; then
  release_id="$(gh api --method POST "repos/$repository/releases" \
    -f tag_name="ci-cache-permission-probe-$GITHUB_RUN_ID-$GITHUB_RUN_ATTEMPT" \
    -f target_commitish="$branch" -F draft=true --jq .id)"
  gh api --method DELETE "repos/$repository/releases/$release_id"
  echo "Checkpoint creation permission verified before compilation."
  exit 0
fi

if [[ "$operation" == restore ]]; then
  release="$(gh api --paginate "repos/$repository/releases?per_page=100" \
    --jq ".[] | select(.draft == false and (.tag_name | startswith(\"$prefix\"))) |
    select(any(.assets[]; .name == \"manifest.json\")) | [.created_at,.tag_name] | @tsv" \
    | sort -r | head -n 1 | cut -f 2)"
  fallback_run=0
  if [[ -f "$directory/manifest.json" ]] &&
      jq -e --arg key "$key" '.format == 1 and .key == $key' "$directory/manifest.json" >/dev/null; then
    fallback_run="$(jq -r .run_id "$directory/manifest.json")"
  fi
  [[ "$fallback_run" =~ ^[0-9]+$ ]] || fallback_run=0
  release_run="${release#"$prefix"}"
  release_run="${release_run%-*}"
  [[ "$release_run" =~ ^[0-9]+$ ]] || release_run=0
  if (( fallback_run > release_run )); then
    restored="Actions fallback checkpoint from run $fallback_run"
  elif [[ -n "$release" ]]; then
    rm -f "$directory"/state.tar.zst.* "$directory/SHA256SUMS" "$directory/manifest.json"
    gh release download "$release" --repo "$repository" --dir "$directory"
    restored="$release"
  else
    echo "No compatible checkpoint. Bootstrapping on the Actions VM."
    exit 0
  fi
  (cd "$directory" && sha256sum -c SHA256SUMS)
  cat "$directory"/state.tar.zst.* | zstd -dc |
    ssh freebsd 'tar -xpf - -C /'
  ssh freebsd "sh '$GITHUB_WORKSPACE/.ci/restore-accounts.sh'"
  ssh freebsd 'rm -f /usr/local/xigmanas/state/dirty'
  echo "Restored $restored" >> "$GITHUB_STEP_SUMMARY"
  rm -f "$directory"/state.tar.zst.* "$directory/SHA256SUMS" "$directory/manifest.json"
  exit 0
fi

[[ "$operation" == save ]] || exit 2
if ! ssh freebsd 'test -f /usr/local/xigmanas/state/dirty'; then
  echo "No expensive state changed; retaining the existing checkpoint."
  exit 0
fi

# Stream out of the guest: do not require a second archive-sized guest disk.
ssh freebsd "sh '$GITHUB_WORKSPACE/.ci/export-state.sh'" |
  zstd -T2 -3 |
  split -b 1500M -d -a 3 - "$directory/state.tar.zst."
(cd "$directory" && sha256sum state.tar.zst.* > SHA256SUMS)
jq -n --arg key "$key" --arg sha "$BUILD_SHA" \
  --arg run "$GITHUB_RUN_ID" \
  '{format:1,key:$key,source_sha:$sha,run_id:$run}' > "$directory/manifest.json"
echo "archive_ready=true" >> "$GITHUB_OUTPUT"
tag="${prefix}${GITHUB_RUN_ID}-${GITHUB_RUN_ATTEMPT}"
gh release create "$tag" --repo "$repository" \
  --target "$branch" --draft --prerelease --latest=false \
  --title "CI cache: FreeBSD 14.3 ($GITHUB_RUN_ID)" \
  --notes "Machine-generated build checkpoint, not an installable XigmaNAS release."
for asset in "$directory"/state.tar.zst.* "$directory/SHA256SUMS" "$directory/manifest.json"; do
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
# The cache tag is only a storage anchor; manifest.json identifies the built source.
gh release edit "$tag" --target "$branch" --draft=false --repo "$repository"
echo "Saved checkpoint $tag" >> "$GITHUB_STEP_SUMMARY"

# Retain two completed generations, and only delete our own compatible caches.
gh api --paginate "repos/$repository/releases?per_page=100" \
  --jq ".[] | select(.draft == false and (.tag_name | startswith(\"$prefix\"))) |
  [.created_at,.tag_name] | @tsv" |
  sort -r | tail -n +3 | cut -f 2 |
  while IFS= read -r old; do
    gh release delete "$old" --cleanup-tag --yes --repo "$repository"
  done
