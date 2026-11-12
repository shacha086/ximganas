#!/usr/bin/env bash

prepare_open_vm_tools() {
  [[ "$FREEBSD_RELEASE" == 14.3 ]] || return 0
  local port="${PORTS_ROOT:-/usr/ports}/emulators/open-vm-tools" patch work
  patch="$port/files/patch-sys-stdarg.h"
  if [[ ! -f "$patch" ]]; then
    echo 'CACHE open-vm-tools: retaining the FreeBSD 14.3 headers and existing work.'
    return 0
  fi

  # This newer ports patch requires headers absent from 14.3. Undo it in
  # cached sources before disabling it, without cleaning compiled objects.
  for work in "$port"/work*/open-vm-tools-*/open-vm-tools; do
    [[ -f "$work/modules/shared/vmblock/stubs.c" ]] || continue
    if grep -Fq '#include <sys/stdarg.h>' "$work/modules/shared/vmblock/stubs.c"; then
      touch "$state/dirty"
      command patch -R -f -p0 -d "$work" -i "$patch" || return $?
    fi
  done
  touch "$state/dirty"
  rm -f "$patch"
  echo 'CACHE open-vm-tools: disabled the incompatible stdarg patch; retaining compiled objects.'
}
