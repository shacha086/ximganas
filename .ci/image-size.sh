#!/usr/bin/env bash

prepare_image_builder() {
  local patch="$source/.ci/patches/image-size.patch"
  if git -C "$source" apply --check "$patch" 2>/dev/null; then
    git -C "$source" apply "$patch" || return $?
  elif ! git -C "$source" apply --reverse --check "$patch" 2>/dev/null; then
    echo 'Cannot apply or verify the image capacity fix.' >&2
    return 1
  fi
}

image_size_mib() {
  local kib="$1" minimum="$2" mib
  # Reserve space for UFS metadata, fragment rounding, and later boot files.
  mib=$(((kib * 5 / 4 + 65536 + 1023) / 1024))
  ((mib >= minimum)) || mib="$minimum"
  printf '%s\n' "$(((mib + 15) / 16 * 16))"
}

configure_image_sizes() {
  local total_kib local_kib
  total_kib="$(du -sk "$XIGMANAS_ROOTFS" | awk '{print $1}')"
  local_kib="$(du -sk "$XIGMANAS_ROOTFS/usr/local" | awk '{print $1}')"
  XIGMANAS_MFSROOT_SIZE="$(image_size_mib "$((total_kib - local_kib))" "$XIGMANAS_MFSROOT_SIZE")"
  XIGMANAS_MDLOCAL_SIZE="$(image_size_mib "$local_kib" "$XIGMANAS_MDLOCAL_SIZE")"

  # create_mfsroot calls use_svn again; update its source and current rootfs.
  sed -i '' -E "s/^MDLOCALSIZE=[0-9]+m$/MDLOCALSIZE=${XIGMANAS_MDLOCAL_SIZE}m/" \
    "$source/etc/rc"
  cp -p "$source/etc/rc" "$XIGMANAS_ROOTFS/etc/rc"
  printf 'IMAGE capacity root=%sMiB local=%sMiB payload=%sKiB\n' \
    "$XIGMANAS_MFSROOT_SIZE" "$XIGMANAS_MDLOCAL_SIZE" "$local_kib"
}

pack_mini_image() {
  local kib md mountdir
  kib="$(du -sk "$XIGMANAS_TMPDIR/usr/local" | awk '{print $1}')"
  XIGMANAS_MDLOCAL_MINI_SIZE="$(image_size_mib "$kib" "$XIGMANAS_MDLOCAL_MINI_SIZE")"
  dd if=/dev/zero of="$XIGMANAS_WORKINGDIR/mdlocal-mini" bs=1k \
    seek="$((XIGMANAS_MDLOCAL_MINI_SIZE * 1024))" count=0
  md="$(mdconfig -a -t vnode -f "$XIGMANAS_WORKINGDIR/mdlocal-mini")"
  newfs -S "$XIGMANAS_IMGFMT_SECTOR" -b "$XIGMANAS_IMGFMT_BSIZE" \
    -f "$XIGMANAS_IMGFMT_FSIZE" -O2 -o space -m 0 -U -t "/dev/$md"
  mountdir="$(mktemp -d "$XIGMANAS_WORKINGDIR/mdlocal-mini-mount.XXXXXX")"
  mount "/dev/$md" "$mountdir"
  tar -cf - -C "$XIGMANAS_TMPDIR/usr/local" . | tar -xpf - -C "$mountdir"
  umount "$mountdir"
  mdconfig -d -u "$md"
  cd "$XIGMANAS_WORKINGDIR"
  rm -rf -- "$XIGMANAS_TMPDIR" "$mountdir"
  printf 'IMAGE capacity mini=%sMiB payload=%sKiB\n' "$XIGMANAS_MDLOCAL_MINI_SIZE" "$kib"
}

size_embedded_image() {
  local payload_bytes boot_kib
  payload_bytes=$(($(stat -f '%z' "$XIGMANAS_WORKINGDIR/mfsroot.gz") +
    $(stat -f '%z' "$XIGMANAS_WORKINGDIR/mdlocal.xz")))
  boot_kib="$(du -sk "$XIGMANAS_BOOTDIR" | awk '{print $1}')"
  IMGSIZEM="$(image_size_mib "$(((payload_bytes + 1023) / 1024 + boot_kib))" 470)"
  XIGMANAS_IMG_SIZE=$((IMGSIZEM + 32))
  XIGMANAS_IMG_SIZE_SEC=$((XIGMANAS_IMG_SIZE * 2048))
  printf 'IMAGE capacity embedded=%sMiB partition=%sMiB\n' \
    "$XIGMANAS_IMG_SIZE" "$IMGSIZEM"
}
