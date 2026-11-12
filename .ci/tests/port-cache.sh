#!/usr/bin/env bash
set -euo pipefail

helper="$(cd "$(dirname "$0")/.." && pwd)/port-cache.sh"
. "$helper"
fixture="$(mktemp -d "${RUNNER_TEMP:-/tmp}/xigmanas-port-cache.XXXXXX")"
trap 'rm -rf "$fixture"' EXIT
source="$fixture/repo"
state="$fixture/state"
mkdir -p "$source/build/ports"/{alpha,beta,options,base-ports,distfiles} \
  "$source/.ci" "$source/etc" "$source/www" "$state"
export PORT_CACHE_MAKE_CONF="$fixture/make.conf"
printf 'BATCH=yes\n' > "$PORT_CACHE_MAKE_CONF"
printf 'FREEBSD_RELEASE=14.3\nFREEBSD_SRC_REV=src-one\nFREEBSD_PORTS_REV=ports-one\n' \
  > "$source/.ci/freebsd.env"
. "$source/.ci/freebsd.env"
for name in alpha beta; do
  printf 'ON\n' > "$source/build/ports/$name/pkg-state"
  printf 'PORTNAME=%s\n' "$name" > "$source/build/ports/$name/Makefile"
done
printf 'OPTIONS=one\n' > "$source/build/ports/options/Makefile"
printf 'overlay-one\n' > "$source/build/ports/base-ports/overlay"
printf 'distfile-one\n' > "$source/build/ports/distfiles/source"
printf 'shared-one\n' > "$source/build/ports/shared.mk"
printf 'make-one\n' > "$source/build/make.conf"
printf 'functions-one\n' > "$source/build/functions.inc"
printf 'XigmaNAS\n' > "$source/etc/prd.name"
printf '14.3.0.5\n' > "$source/etc/prd.version"
printf 'page-one\n' > "$source/www/index.php"
printf '/build/ports/*/work/\n' > "$source/.gitignore"
git -C "$source" init -q
git -C "$source" config user.name 'Cache Tests'
git -C "$source" config user.email 'cache-tests@example.invalid'
git -C "$source" config core.autocrlf false
commit() {
  git -C "$source" add .
  git -C "$source" commit -qm test
}
equal() { [[ "$1" == "$2" ]] || { echo "FAIL: $3" >&2; exit 1; }; }
different() { [[ "$1" != "$2" ]] || { echo "FAIL: $3" >&2; exit 1; }; }
commit
common="$(ports_common_key)"
alpha="$(port_input_key alpha "$common")"
beta="$(port_input_key beta "$common")"

printf 'PORTREVISION=1\n' >> "$source/build/ports/alpha/Makefile"
commit
equal "$(ports_common_key)" "$common" 'one recipe changed shared inputs'
different "$(port_input_key alpha "$common")" "$alpha" 'changed recipe did not invalidate alpha'
equal "$(port_input_key beta "$common")" "$beta" 'alpha invalidated beta'
echo 'PASS: recipe changes affect only their own port'

mkdir -p "$source/build/ports/alpha/files"
before="$(port_input_key alpha "$common")"
printf 'patch-one\n' > "$source/build/ports/alpha/files/patch-source"
commit
different "$(port_input_key alpha "$common")" "$before" 'patch did not invalidate alpha'
before="$(port_input_key alpha "$common")"
printf 'checksum-one\n' > "$source/build/ports/alpha/distinfo"
commit
different "$(port_input_key alpha "$common")" "$before" 'distinfo did not invalidate alpha'
equal "$(port_input_key beta "$(ports_common_key)")" "$beta" 'patch or distinfo invalidated beta'
echo 'PASS: patches and distinfo remain isolated'

printf 'page-two\n' > "$source/www/index.php"
commit
equal "$(ports_common_key)" "$common" 'web patch invalidated shared inputs'
equal "$(port_input_key beta "$common")" "$beta" 'web patch invalidated beta'
echo 'PASS: source-only commits retain compiled ports'

for path in build/make.conf build/functions.inc build/ports/shared.mk \
    build/ports/options/Makefile build/ports/base-ports/overlay \
    build/ports/distfiles/source etc/prd.name etc/prd.version; do
  before="$(ports_common_key)"
  printf 'changed\n' >> "$source/$path"
  commit
  after="$(ports_common_key)"
  different "$before" "$after" "$path did not invalidate shared inputs"
  different "$(port_input_key alpha "$before")" "$(port_input_key alpha "$after")" \
    "$path did not invalidate alpha"
  different "$(port_input_key beta "$before")" "$(port_input_key beta "$after")" \
    "$path did not invalidate beta"
done
echo 'PASS: shared configuration and dependencies invalidate all ports'

before="$(ports_common_key)"
printf 'MAKE_JOBS_NUMBER=4\n' >> "$PORT_CACHE_MAKE_CONF"
different "$before" "$(ports_common_key)" 'effective make flags did not invalidate ports'
before="$(ports_common_key)"
FREEBSD_PORTS_REV=ports-two
different "$before" "$(ports_common_key)" 'ports revision did not invalidate ports'
FREEBSD_PORTS_REV=ports-one
before="$(ports_common_key)"
FREEBSD_SRC_REV=src-two
different "$before" "$(ports_common_key)" 'source revision did not invalidate ports'
FREEBSD_SRC_REV=src-one
echo 'PASS: effective build flags and pinned dependencies are fingerprinted'

for name in alpha beta; do
  mkdir -p "$source/build/ports/$name/work"
  printf 'object-%s\n' "$name" > "$source/build/ports/$name/work/object"
done
touch "$state/built-alpha" "$state/installed-alpha"
printf '%s\n' "$(printf '%s\n%s\n' "$FREEBSD_PORTS_REV" \
  "$(input_hash build/ports build/make.conf)" | cache_hash)" > "$state/ports-key"
bash "$helper" seed "$source" "$state"
common="$(ports_common_key)"
equal "$(cat "$state/port-key-alpha")" "$(port_input_key alpha "$common")" 'completed port migration'
equal "$(cat "$state/port-key-beta")" "$(port_input_key beta "$common")" 'partial port migration'
[[ -f "$state/built-alpha" && -f "$state/installed-alpha" ]]
[[ -f "$source/build/ports/beta/work/object" && ! -f "$state/built-beta" ]]
echo 'PASS: legacy migration preserves completed and partial work'

# Reject unknown legacy provenance instead of blessing possibly stale objects.
saved_state="$state"
state="$fixture/untrusted-state"
mkdir -p "$state"
printf 'wrong\n' > "$state/ports-key"
seed_legacy_port_cache
[[ ! -f "$state/ports-common-key" && ! -f "$state/port-key-alpha" ]]
state="$saved_state"
echo 'PASS: legacy mismatches cannot seed trusted fingerprints'

clean_log="$fixture/clean.log"
make() {
  equal "$3" clean 'unexpected make target in cache tests'
  equal "${4:-}" NOCLEANDEPENDS=yes 'cleaning must not delete dependency work'
  printf '%s\n' "${2##*/}" >> "$clean_log"
  [[ "${FAIL_CLEAN:-false}" != true ]] || return 1
  rm -rf "$2/work"
}
prepare_port_cache "$source/build/ports/alpha" "$common"
prepare_port_cache "$source/build/ports/beta" "$common"
[[ ! -f "$clean_log" && -f "$state/built-alpha" ]]
echo 'PASS: unchanged completed and partial ports are not cleaned'

mkdir -p "$source/build/ports/gamma"
printf 'ON\n' > "$source/build/ports/gamma/pkg-state"
printf 'PORTNAME=gamma\n' > "$source/build/ports/gamma/Makefile"
commit
equal "$(ports_common_key)" "$common" 'adding one port invalidated shared inputs'
prepare_port_cache "$source/build/ports/gamma" "$common"
[[ -f "$state/port-key-gamma" && ! -f "$state/built-gamma" && ! -f "$clean_log" ]]
echo 'PASS: new ports start cold without clearing existing work'

# Change only alpha after seeding the old source, as workflow synchronization does.
printf 'PORTREVISION=2\n' >> "$source/build/ports/alpha/Makefile"
commit
prepare_port_cache "$source/build/ports/alpha" "$(ports_common_key)"
prepare_port_cache "$source/build/ports/beta" "$(ports_common_key)"
equal "$(cat "$clean_log")" alpha 'changing alpha cleaned unrelated work'
[[ ! -f "$state/built-alpha" && ! -f "$state/installed-alpha" ]]
[[ -f "$source/build/ports/beta/work/object" ]]
echo 'PASS: checkout changes clean only the affected port'

touch "$state/built-beta" "$state/installed-beta"
rm -rf "$source/build/ports/beta/work"
prepare_port_cache "$source/build/ports/beta" "$(ports_common_key)"
[[ ! -f "$state/built-beta" && ! -f "$state/installed-beta" ]]
echo 'PASS: missing work cannot be accepted using a stale completion stamp'

# A failed clean must not stamp the new key or retain an old successful build.
mkdir -p "$source/build/ports/alpha/work"
touch "$state/built-alpha" "$state/installed-alpha"
old="$(cat "$state/port-key-alpha")"
printf 'PORTREVISION=3\n' >> "$source/build/ports/alpha/Makefile"
commit
if (set -e; FAIL_CLEAN=true; prepare_port_cache "$source/build/ports/alpha" "$(ports_common_key)"); then
  echo 'FAIL: cleaning failure was ignored' >&2
  exit 1
fi
equal "$(cat "$state/port-key-alpha")" "$old" 'failed clean stamped the new key'
[[ ! -f "$state/built-alpha" && ! -f "$state/installed-alpha" ]]
echo 'PASS: interrupted invalidation cannot mark stale objects as current'
