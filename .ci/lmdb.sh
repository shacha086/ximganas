#!/usr/bin/env bash

prepare_lmdb() {
  local ports="${PORTS_ROOT:-/usr/ports}" patch
  patch="$source/.ci/patches/lmdb-gmake.patch"

  # LMDB's explicit link rules use GNU $^; its existing objects remain usable.
  if git -C "$ports" apply --check "$patch" 2>/dev/null; then
    git -C "$ports" apply "$patch" || return $?
    touch "$state/dirty"
    echo 'CACHE lmdb: selected GNU make; retaining compiled objects.'
  elif git -C "$ports" apply --reverse --check "$patch" 2>/dev/null; then
    echo 'CACHE lmdb: GNU make fix unchanged; retaining work.'
  else
    echo 'Cannot apply or verify the pinned lmdb GNU make fix.' >&2
    return 1
  fi
}
