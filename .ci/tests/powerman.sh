#!/usr/bin/env bash
set -euo pipefail

source="$(cd "$(dirname "$0")/../.." && pwd)"
. "$source/.ci/freebsd.env"
. "$source/.ci/port-cache.sh"
. "$source/.ci/powerman.sh"
fixture="$(mktemp -d "${RUNNER_TEMP:-/tmp}/xigmanas-powerman.XXXXXX")"
trap 'rm -rf "$fixture"' EXIT
export PORTS_ROOT="$fixture/ports"
state="$fixture/state"
mkdir -p "$PORTS_ROOT/sysutils/powerman/work" "$PORTS_ROOT/devel/other/work" "$state"
curl --fail --location --retry 3 \
  "https://raw.githubusercontent.com/freebsd/freebsd-ports/$FREEBSD_PORTS_REV/sysutils/powerman/Makefile" \
  -o "$PORTS_ROOT/sysutils/powerman/Makefile"
git -C "$PORTS_ROOT" init -q
git -C "$PORTS_ROOT" add .
printf 'keep\n' > "$PORTS_ROOT/devel/other/work/object"
touch "$state/built-other" "$state/kernel-key"
clean_log="$fixture/clean.log"
make() {
  [[ "$1" == -C && "$2" == "$PORTS_ROOT/sysutils/powerman" ]] || return 1
  [[ "$3" == clean && "$4" == NOCLEANDEPENDS=yes ]] || return 1
  printf 'powerman\n' >> "$clean_log"
  [[ "${FAIL_CLEAN:-false}" != true ]] || return 1
  rm -rf "$2/work"
}

prepare_powerman
grep -Eq '^USES=[[:space:]]+gmake libtool localbase perl5 shebangfix$' \
  "$PORTS_ROOT/sysutils/powerman/Makefile"
[[ -f "$state/powerman-gmake-key" && ! -d "$PORTS_ROOT/sysutils/powerman/work" ]]
[[ "$(wc -l < "$clean_log")" -eq 1 ]]
[[ -f "$PORTS_ROOT/devel/other/work/object" && -f "$state/built-other" && -f "$state/kernel-key" ]]
echo 'PASS: pinned port accepts GNU make fix without invalidating unrelated work'

mkdir -p "$PORTS_ROOT/sysutils/powerman/work"
touch "$PORTS_ROOT/sysutils/powerman/work/object"
prepare_powerman
[[ "$(wc -l < "$clean_log")" -eq 1 && -f "$PORTS_ROOT/sysutils/powerman/work/object" ]]
echo 'PASS: unchanged fix retains completed or partial dependency work'

git -C "$PORTS_ROOT" show :sysutils/powerman/Makefile > "$PORTS_ROOT/sysutils/powerman/Makefile"
prepare_powerman
[[ "$(wc -l < "$clean_log")" -eq 1 && -f "$PORTS_ROOT/sysutils/powerman/work/object" ]]
echo 'PASS: restoring an unpatched ports tree reapplies the fix idempotently'

old="$(cat "$state/powerman-gmake-key")"
FREEBSD_PORTS_REV=changed-for-test
if FAIL_CLEAN=true prepare_powerman; then
  echo 'FAIL: dependency cleanup failure was ignored' >&2
  exit 1
fi
[[ "$(cat "$state/powerman-gmake-key")" == "$old" ]]
prepare_powerman
[[ "$(cat "$state/powerman-gmake-key")" != "$old" ]]
echo 'PASS: failed cleanup cannot stamp the new dependency key'

git -C "$PORTS_ROOT" show :sysutils/powerman/Makefile > "$PORTS_ROOT/sysutils/powerman/Makefile"
sed -i 's/libtool localbase perl5 shebangfix/unknown-upstream-inputs/' \
  "$PORTS_ROOT/sysutils/powerman/Makefile"
if prepare_powerman; then
  echo 'FAIL: unknown upstream recipe was silently accepted' >&2
  exit 1
fi
echo 'PASS: an unexpected upstream recipe fails before expensive compilation'

mkdir -p "$fixture/alias"
printf '#!/bin/sh\nexit 0\n' > "$fixture/alias/powerman"
chmod +x "$fixture/alias/powerman"
printf 'LN_S=ln -s\nEXEEXT=\npm$(EXEEXT): powerman\n\trm -f $@\n\t$(LN_S) $< $@\n' \
  > "$fixture/alias/Makefile"
command make -C "$fixture/alias" pm
[[ "$(readlink "$fixture/alias/pm")" == powerman ]]
"$fixture/alias/pm"
echo 'PASS: GNU make creates an executable pm alias using the upstream rule'
