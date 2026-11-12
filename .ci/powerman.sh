#!/usr/bin/env bash

prepare_powerman() {
  local ports="${PORTS_ROOT:-/usr/ports}" patch key
  patch="$source/.ci/patches/powerman-gmake.patch"
  key="$(printf '%s\n%s\n' "$FREEBSD_PORTS_REV" "$(cache_hash < "$patch")" | cache_hash)"

  # GNU $< in the explicit pm rule is empty with BSD make. Select GNU make
  # for this dependency, including configure's recursive MAKE commands.
  if git -C "$ports" apply --check "$patch" 2>/dev/null; then
    git -C "$ports" apply "$patch" || return $?
    touch "$state/dirty"
  elif ! git -C "$ports" apply --reverse --check "$patch" 2>/dev/null; then
    echo 'Cannot apply or verify the pinned powerman GNU make fix.' >&2
    return 1
  fi

  if [[ "$(cat "$state/powerman-gmake-key" 2>/dev/null || true)" != "$key" ]]; then
    touch "$state/dirty"
    # Its cached configure/stage still use BSD make; keep all dependency work.
    make -C "$ports/sysutils/powerman" clean NOCLEANDEPENDS=yes || return $?
    printf '%s\n' "$key" > "$state/powerman-gmake-key"
    echo 'CACHE powerman: selected GNU make; invalidated only this dependency.'
  else
    echo 'CACHE powerman: GNU make fix unchanged; retaining work.'
  fi
}
