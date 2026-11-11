#!/usr/bin/env bash
set -euo pipefail

operation="${1:?restore or save}"
key="${2:?cache compatibility key}"
repository="$(gh api "repos/$GITHUB_REPOSITORY" --jq .full_name)"
directory="${RUNNER_TEMP:?}/xigmanas-checkpoint"
prefix="ci-cache-${key}-"
mkdir -p "$directory"

if [[ "$operation" == restore ]]; then
  release="$(gh api --paginate "repos/$repository/releases?per_page=100" \
    --jq ".[] | select(.draft == false and (.tag_name | startswith(\"$prefix\"))) |
    select(any(.assets[]; .name == \"manifest.json\")) | [.created_at,.tag_name] | @tsv" \
    | sort -r | head -n 1 | cut -f 2)"
  if [[ -z "$release" ]]; then
    echo "No compatible checkpoint. Bootstrapping on the Actions VM."
    exit 0
  fi
  gh release download "$release" --repo "$repository" --dir "$directory"
  (cd "$directory" && sha256sum -c SHA256SUMS)
  cat "$directory"/state.tar.zst.* | zstd -dc |
    ssh freebsd 'tar -xpf - -C /'
  ssh freebsd "sh '$GITHUB_WORKSPACE/.ci/restore-accounts.sh'"
  ssh freebsd 'rm -f /usr/local/xigmanas/state/dirty'
  echo "Restored $release" >> "$GITHUB_STEP_SUMMARY"
  rm -f "$directory"/state.tar.zst.*
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
tag="${prefix}${GITHUB_RUN_ID}-${GITHUB_RUN_ATTEMPT}"
gh release create "$tag" --repo "$repository" \
  --target "$BUILD_SHA" --draft --prerelease --latest=false \
  --title "CI cache: FreeBSD 14.3 ($GITHUB_RUN_ID)" \
  --notes "Machine-generated build checkpoint, not an installable XigmaNAS release."
for asset in "$directory"/state.tar.zst.* "$directory/SHA256SUMS" "$directory/manifest.json"; do
  gh release upload "$tag" "$asset" --repo "$repository"
done
gh release edit "$tag" --draft=false --repo "$repository"
echo "Saved checkpoint $tag" >> "$GITHUB_STEP_SUMMARY"

# Retain two completed generations, and only delete our own compatible caches.
gh api --paginate "repos/$repository/releases?per_page=100" \
  --jq ".[] | select(.draft == false and (.tag_name | startswith(\"$prefix\"))) |
  [.created_at,.tag_name] | @tsv" |
  sort -r | tail -n +3 | cut -f 2 |
  while IFS= read -r old; do
    gh release delete "$old" --cleanup-tag --yes --repo "$repository"
  done
