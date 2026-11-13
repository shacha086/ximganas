#!/usr/bin/env bash

prepare_dependency_cache() {
  local port="$1" metadata spec required origin target dependency work stagedir marker
  local -a fields specs paths
  metadata="$(make -C "$port" -V '${NONEXISTENT}' -V '${BUILD_DEPENDS}' \
    -V '${DEPENDS_TARGET}')" || return $?
  mapfile -t fields <<< "$metadata"
  read -r -a specs <<< "${fields[1]}"

  # NONEXISTENT dependencies are NAS wrappers that copy build or stage output.
  for spec in "${specs[@]}"; do
    IFS=: read -r required origin target <<< "$spec"
    [[ "$required" == "${fields[0]}" ]] || continue
    case "$origin" in
      /usr/ports/*) dependency="$origin" ;;
      */*) dependency="/usr/ports/$origin" ;;
      *) echo "Invalid wrapped dependency: $spec" >&2; return 1 ;;
    esac
    metadata="$(make -C "$dependency" -V '${WRKDIR}' -V '${STAGEDIR}')" || return $?
    mapfile -t paths <<< "$metadata"
    work="${paths[0]}"
    stagedir="${paths[1]}"
    case "$work" in
      /usr/ports/*/work|/usr/ports/*/work-*) ;;
      *) echo "Invalid dependency work directory: $work" >&2; return 1 ;;
    esac
    if ! grep -qxF "$work" "$state/required-workdirs"; then
      printf '%s\n' "$work" >> "$state/required-workdirs"
      touch "$state/dirty"
    fi
    target="${target:-${fields[2]}}"
    marker='.build_done.*'
    case " $target " in
      *' stage '*) marker='.stage_done.*' ;;
    esac
    if [[ -n "$(find "$work" -maxdepth 1 -name "$marker" -print -quit 2>/dev/null)" ]] &&
      [[ "$marker" != '.stage_done.*' || -d "$stagedir" ]]; then
      echo "CACHE dependency ${dependency#/usr/ports/}: retaining required output."
    else
      touch "$state/dirty"
      echo "CACHE dependency ${dependency#/usr/ports/}: restoring missing output."
      case "$marker" in
        .stage_done.*) make -C "$dependency" stage || return $? ;;
        *) make -C "$dependency" build || return $? ;;
      esac
    fi
  done
}
