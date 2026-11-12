#!/bin/sh
set -eu
set -o pipefail

state=/usr/local/xigmanas/state
payload=/usr/obj/cache-roundtrip/payload.bin

fingerprints() {
  sha256 "$state/roundtrip.txt" "$state/roundtrip-mode" \
    /usr/src/cache-roundtrip.txt /usr/ports/cache-roundtrip.txt \
    /var/cache/ccache/cache-roundtrip.txt /boot/modules/cache-roundtrip.txt "$payload"
}

case "${1:?seed, verify, or clear}" in
  seed)
    mkdir -p "$state/.ssh" /usr/obj/cache-roundtrip /usr/src /usr/ports \
      /var/cache/ccache /boot/modules /usr/local/etc/ssh /usr/local/xigmanas/artifacts
    touch /usr/ports/UIDs /usr/ports/GIDs
    printf 'run=%s\n' "${TEST_RUN_ID:?}" > "$state/roundtrip.txt"
    printf 'permission fixture\n' > "$state/roundtrip-mode"
    chmod 640 "$state/roundtrip-mode"
    for path in /usr/src/cache-roundtrip.txt /usr/ports/cache-roundtrip.txt \
      /var/cache/ccache/cache-roundtrip.txt /boot/modules/cache-roundtrip.txt; do
      cp "$state/roundtrip.txt" "$path"
    done
    dd if=/dev/urandom of="$payload" bs=1m count=16 status=none
    ln -s "$payload" "$state/roundtrip-link"
    fingerprints > "$state/roundtrip.sha256"
    printf 'synthetic exclusion marker, not a private key\n' > "$state/.ssh/roundtrip-key"
    printf 'synthetic SSH configuration marker\n' > /usr/local/etc/ssh/roundtrip-secret
    printf 'synthetic image marker\n' > /usr/local/xigmanas/artifacts/roundtrip.iso
    touch "$state/dirty"
    ;;
  verify)
    fingerprints | cmp - "$state/roundtrip.sha256"
    test "$(stat -f '%u:%g:%Lp' "$state/roundtrip-mode")" = 0:0:640
    test -L "$state/roundtrip-link"
    test "$(readlink "$state/roundtrip-link")" = "$payload"
    test ! -e "$state/.ssh/roundtrip-key"
    test ! -e /usr/local/etc/ssh/roundtrip-secret
    test ! -e /usr/local/xigmanas/artifacts/roundtrip.iso
    test ! -e "$state/dirty"
    ccache --show-stats
    pkg check -d
    echo 'PASS: checksums, root ownership, mode 0640, symlink, cache locations, exclusions, dependency registration'
    ;;
  clear)
    rm -f "$state/roundtrip.txt" "$state/roundtrip-mode" "$state/roundtrip-link" \
      "$state/roundtrip.sha256" "$payload" /usr/src/cache-roundtrip.txt \
      /usr/ports/cache-roundtrip.txt /var/cache/ccache/cache-roundtrip.txt \
      /boot/modules/cache-roundtrip.txt
    test ! -e "$payload"
    ;;
  *) exit 2 ;;
esac
