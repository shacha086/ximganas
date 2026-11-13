#!/usr/bin/env bash

prepare_samba_nsupdate() {
  local patch="$source/.ci/patches/samba-nsupdate.patch"
  local options=/var/db/ports/dns_bind-tools/options current settings
  settings=$'OPTIONS_FILE_SET+=GSSAPI_MIT\nOPTIONS_FILE_UNSET+=GSSAPI_BASE\nOPTIONS_FILE_UNSET+=GSSAPI_HEIMDAL\nOPTIONS_FILE_UNSET+=GSSAPI_NONE'

  # Only runtime dependencies and image copies change; keep Samba's objects.
  if git -C "$source" apply --check "$patch" 2>/dev/null; then
    git -C "$source" apply "$patch" || return $?
  elif ! git -C "$source" apply --reverse --check "$patch" 2>/dev/null; then
    echo 'Cannot apply or verify the Samba nsupdate compatibility fix.' >&2
    return 1
  fi

  # FreeBSD 14.3 defaults to no GSSAPI; its base Heimdal is unsupported here.
  current="$(grep -E '^OPTIONS_FILE_(SET|UNSET)\+=GSSAPI_(BASE|HEIMDAL|MIT|NONE)$' \
    "$options" 2>/dev/null || true)"
  if [[ "$current" != "$settings" ]]; then
    mkdir -p "${options%/*}"
    touch "$options"
    sed -i '' -E '/^OPTIONS_FILE_(SET|UNSET)\+=GSSAPI_(BASE|HEIMDAL|MIT|NONE)$/d' "$options"
    printf '%s\n' "$settings" >> "$options"
    touch "$state/dirty"
    make -C /usr/ports/dns/bind-tools clean NOCLEANDEPENDS=yes || return $?
    echo 'CACHE bind-tools: selected MIT GSSAPI for authenticated DNS updates.'
  fi
  echo 'CACHE samba: migrated nsupdate runtime dependency; retaining compiled objects.'
}
