#!/usr/bin/env bash

cache_hash() {
  if command -v sha256 >/dev/null 2>&1; then
    sha256 -q
  else
    sha256sum | cut -d ' ' -f 1
  fi
}

input_hash() {
  git -C "$source" ls-tree -r HEAD -- "$@" | cache_hash
}

ports_common_key() {
  {
    printf 'port-cache-v1\n%s\n%s\n%s\n%s\n' \
      "$FREEBSD_RELEASE" "$FREEBSD_SRC_REV" "$FREEBSD_PORTS_REV" "$(uname -m)"
    input_hash build/functions.inc build/make.conf \
      build/ports/base-ports build/ports/options build/ports/distfiles \
      etc/prd.name etc/prd.version
    git -C "$source" ls-tree HEAD:build/ports | awk '$2 != "tree"'
    cache_hash < "${PORT_CACHE_MAKE_CONF:-/etc/make.conf}"
  } | cache_hash
}

port_input_key() {
  printf '%s\n%s\n' "$2" "$(input_hash "build/ports/${1##*/}")" | cache_hash
}

seed_legacy_port_cache() {
  local legacy common port count=0
  [[ -f "$state/ports-key" && ! -f "$state/ports-common-key" ]] || return 0
  legacy="$(printf '%s\n%s\n' "$FREEBSD_PORTS_REV" \
    "$(input_hash build/ports build/make.conf)" | cache_hash)"
  if [[ "$(cat "$state/ports-key")" != "$legacy" ]]; then
    echo "CACHE migration: legacy inputs do not match; cached ports need validation."
    return 0
  fi
  common="$(ports_common_key)"
  for port in "$source"/build/ports/*; do
    [[ -f "$port/pkg-state" ]] || continue
    [[ -d "$port/work" || -f "$state/built-${port##*/}" ]] || continue
    port_input_key "$port" "$common" > "$state/port-key-${port##*/}"
    count=$((count + 1))
  done
  printf '%s\n' "$common" > "$state/ports-common-key"
  touch "$state/dirty"
  echo "CACHE migration: preserved $count completed or partial port work trees."
}

prepare_port_cache() {
  local port="$1" common="$2" name="${1##*/}" key previous
  key="$(port_input_key "$port" "$common")"
  previous="$(cat "$state/port-key-$name" 2>/dev/null || true)"
  if [[ "$previous" != "$key" ]]; then
    touch "$state/dirty"
    rm -f "$state/built-$name" "$state/installed-$name"
    if [[ -d "$port/work" ]]; then
      make -C "$port" clean NOCLEANDEPENDS=yes || return $?
    fi
    printf '%s\n' "$key" > "$state/port-key-$name"
    echo "CACHE port $name: inputs changed; invalidated only this port."
  elif [[ -f "$state/built-$name" && ! -d "$port/work" ]]; then
    touch "$state/dirty"
    rm -f "$state/built-$name" "$state/installed-$name"
    echo "CACHE port $name: work tree missing; rebuilding."
  else
    echo "CACHE port $name: inputs unchanged; retaining completed or partial work."
  fi
}

if [[ "${BASH_SOURCE[0]}" == "$0" ]]; then
  set -euo pipefail
  [[ "${1:-}" == seed ]] || exit 2
  source="${2:?cached source directory}"
  state="${3:?cached state directory}"
  if [[ -f "$state/ports-key" && ! -f "$state/ports-common-key" ]]; then
    . "$source/.ci/freebsd.env"
    seed_legacy_port_cache
  fi
fi
