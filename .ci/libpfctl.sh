#!/usr/bin/env bash

prepare_libpfctl() {
  [[ "$FREEBSD_RELEASE" == 14.3 ]] || return 0
  local ports="${PORTS_ROOT:-/usr/ports}" patch key
  patch="$source/.ci/patches/libpfctl-14.3.patch"
  key="$(printf '%s\n%s\n' "$FREEBSD_PORTS_REV" "$(cache_hash < "$patch")" | cache_hash)"

  # Restore only the official 14.3 checksum removed at EOL. Provenance:
  # freebsd-ports 9f3680c82a1a0f4e7dc4149564488ddd333a75db/net/libpfctl/distinfo.
  if git -C "$ports" apply --check "$patch" 2>/dev/null; then
    git -C "$ports" apply "$patch" || return $?
    touch "$state/dirty"
  elif ! git -C "$ports" apply --reverse --check "$patch" 2>/dev/null; then
    echo 'Cannot apply or verify the official libpfctl 14.3 checksum restoration.' >&2
    return 1
  fi

  if [[ "$(cat "$state/libpfctl-14.3-key" 2>/dev/null || true)" != "$key" ]]; then
    touch "$state/dirty"
    make -C "$ports/net/libpfctl" clean NOCLEANDEPENDS=yes || return $?
    printf '%s\n' "$key" > "$state/libpfctl-14.3-key"
    echo 'CACHE libpfctl: restored official 14.3 checksum; invalidated only this dependency.'
  else
    echo 'CACHE libpfctl: 14.3 checksum fix unchanged; retaining work.'
  fi
}
