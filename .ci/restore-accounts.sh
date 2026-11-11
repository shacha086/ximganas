#!/bin/sh
set -eu
set -o pipefail

state=/usr/local/xigmanas/state
mkdir -p "$state"
# Package metadata contains service names, not passwords. This also repairs
# older checkpoints without an explicit account manifest.
pkg query -a '%G' | sort -u > "$state/ports-groups"
pkg query -a '%U' | sort -u > "$state/ports-users"

while IFS= read -r wanted; do
  awk -F: -v name="$wanted" '$1 == name { print }' /usr/ports/GIDs |
  while IFS=: read -r name unused gid members; do
    if ! pw groupshow -n "$name" >/dev/null 2>&1; then
      pw groupadd -n "$name" -g "$gid"
    fi
    test "$(pw groupshow -n "$name" | cut -d: -f3)" = "$gid"
  done
done < "$state/ports-groups"

while IFS= read -r wanted; do
  awk -F: -v name="$wanted" '$1 == name { print }' /usr/ports/UIDs |
  while IFS=: read -r name unused uid gid class change expire comment home shell; do
    if ! pw usershow -n "$name" >/dev/null 2>&1; then
      pw useradd -n "$name" -u "$uid" -g "$gid" \
        -c "$comment" -d "$home" -s "$shell" -w no
    fi
    test "$(pw usershow -n "$name" | cut -d: -f3)" = "$uid"
  done
done < "$state/ports-users"
