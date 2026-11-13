#!/usr/bin/env bash

prepare_cups() {
  local options=/var/db/ports/print_cups/options

  # Old NAS options selected GnuTLS before OpenSSL became a default choice.
  if [[ -f "$options" ]] &&
    grep -qxF 'OPTIONS_FILE_SET+=GNUTLS' "$options" &&
    ! grep -Eq '^OPTIONS_FILE_(SET|UNSET)\+=OPENSSL$' "$options"; then
    printf '\nOPTIONS_FILE_UNSET+=OPENSSL\n' >> "$options"
    touch "$state/dirty"
    echo 'CACHE cups: retained GnuTLS and disabled the new OpenSSL default.'
  fi
}
