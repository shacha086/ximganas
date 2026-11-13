#!/usr/bin/env bash

prepare_virtualbox() {
  local ports="${PORTS_ROOT:-/usr/ports}" patch config
  local setting='VBOX_WITHOUT_SPLIT_SOAPC = 1'
  patch="$source/.ci/patches/virtualbox-gsoap.patch"

  # freebsd-ports ceb61fac0ca320f0b0f7a62505801c2c11d6680f.
  if git -C "$ports" apply --check "$patch" 2>/dev/null; then
    git -C "$ports" apply "$patch" || return $?
    touch "$state/dirty"
  elif ! git -C "$ports" apply --reverse --check "$patch" 2>/dev/null; then
    echo 'Cannot apply or verify the official VirtualBox gSOAP fix.' >&2
    return 1
  fi

  # post-patch has already run in cached work trees; retain their objects.
  for config in "$ports"/emulators/virtualbox-ose-72/work*/VirtualBox-*/LocalConfig.kmk; do
    [[ -f "$config" ]] || continue
    if ! grep -qxF "$setting" "$config"; then
      printf '\n%s\n' "$setting" >> "$config"
      touch "$state/dirty"
    fi
  done
  echo 'CACHE virtualbox: disabled SOAP splitting; retaining compiled objects.'
}
