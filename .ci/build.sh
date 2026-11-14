#!/usr/bin/env bash
set -eE -o pipefail

root=/usr/local/xigmanas
source="$root/svn"
state="$root/state"
cd "$source"
. .ci/freebsd.env
mkdir -p "$state" "$root/work/pkg" "$root/artifacts"

package_state() {
  pkg query -a '%n-%v:%o' | LC_ALL=C sort | sha256 -q
}

packages_before="$(package_state)"
finish_build() {
  local rc=$? packages_after
  trap - EXIT
  if packages_after="$(package_state)"; then
    if [[ "$packages_after" != "$packages_before" ]]; then
      touch "$state/dirty"
      echo 'CACHE packages: installed build dependencies changed.'
    fi
  else
    touch "$state/dirty"
    echo 'CACHE packages: cannot compare installed dependencies; preserving state.'
  fi
  printf '%s\n' "$rc" > "$state/exit-code"
  exit "$rc"
}
trap finish_build EXIT

clone_at() {
  local url="$1" revision="$2" destination="$3"
  if [[ "$(git -C "$destination" rev-parse HEAD 2>/dev/null || true)" != "$revision" ]]; then
    touch "$state/dirty"
    mkdir -p "$destination"
    if [[ ! -d "$destination/.git" ]]; then
      git -C "$destination" init
      git -C "$destination" remote add origin "$url"
    fi
    git -C "$destination" fetch --depth 1 origin "$revision"
    git -C "$destination" checkout --force --detach FETCH_HEAD
  fi
}

clone_at https://github.com/freebsd/freebsd-src.git "$FREEBSD_SRC_REV" /usr/src
clone_at https://github.com/freebsd/freebsd-ports.git "$FREEBSD_PORTS_REV" /usr/ports
ln -sfn /usr/src/sys /sys

make_conf_before="$(sha256 -q /etc/make.conf 2>/dev/null || true)"
cp "$source/build/make.conf" /etc/make.conf
cat >> /etc/make.conf <<'MAKECONF'
BATCH=yes
ALLOW_UNSUPPORTED_SYSTEM=yes
DISABLE_VULNERABILITIES=yes
DEFAULT_VERSIONS+=python=3.11 python3=3.11
CCACHE_DIR=/var/cache/ccache
WITH_CCACHE_BUILD=yes
MAKE_JOBS_NUMBER=4
MAKECONF
if [[ "$(sha256 -q /etc/make.conf)" != "$make_conf_before" ]]; then
  touch "$state/dirty"
fi
export BATCH=yes ASSUME_ALWAYS_YES=yes IGNORE_OSVERSION=yes
export CCACHE_DIR=/var/cache/ccache
ccache --max-size 8G
if [[ "${CI_MODE:-build}" == environment ]]; then
  printf 'Environment ready: src=%s ports=%s\n' "$FREEBSD_SRC_REV" "$FREEBSD_PORTS_REV"
  exit 0
fi

# Upstream supplies the filesystem and image assembly logic. Do not enter its
# menus or call update_svn: the Git checkout is the only XigmaNAS source.
. "$source/.ci/image-size.sh"
prepare_image_builder
set +e
. "$source/build/functions.inc"
set -e
# The kernel config and object directory are internal identities, not branding.
XIGMANAS_KERNCONF="XIGMANAS-${XIGMANAS_ARCH}"
XIGMANAS_OBJDIRPREFIX=/usr/obj/xigmanas
svn_revision="$(sed -n 's/.*"revision": *"\([^"]*\)".*/\1/p' "$source/.ci/svn-import.json")"
XIGMANAS_REVISION="${svn_revision}-git${BUILD_SHA:0:8}"
export XIGMANAS_REVISION
printf 'XIGMANAS_REVISION=%s\n' "$XIGMANAS_REVISION" >> "$source/build/ports/xigmanas.mk"

# Upstream intentionally unmounts not-yet-mounted temporary filesystems and
# repeats directory and symlink creation during image assembly.
umount() { command umount "$@" || true; }
mkdir() { command mkdir -p "$@"; }
ln() { command ln -f "$@"; }
ldd() {
  # Upstream scans scripts, static executables, and data as well as ELF files.
  case "$(file -b "${@: -1}")" in
    *ELF*"dynamically linked"*|*ELF*"shared object"*) command ldd "$@" ;;
    *) return 0 ;;
  esac
}
install() {
  local argument
  for argument in "$@"; do
    if [[ "$argument" == /boot/modules/openzfs.ko && ! -f "$argument" ]]; then
      echo "Skipping optional openzfs.ko; the base ZFS module is included."
      return 0
    fi
  done
  command install "$@"
}
trap 'rc=$?
  printf "Build failed at line %s: %s\n" "$LINENO" "$BASH_COMMAND" >&2
  swapinfo -h >&2 || true
  dmesg | tail -50 >&2 || true
  exit "$rc"' ERR
create_checksum_file() {
  # The upstream builders call this separately; hash each artifact once below.
  return 0
}

. "$source/.ci/port-cache.sh"
. "$source/.ci/powerman.sh"
. "$source/.ci/libpfctl.sh"
. "$source/.ci/open-vm-tools.sh"
. "$source/.ci/lmdb.sh"
. "$source/.ci/cups.sh"
. "$source/.ci/virtualbox.sh"
. "$source/.ci/dependency-cache.sh"
. "$source/.ci/samba.sh"
kernel_key="$(printf '%s\n%s\n' "$FREEBSD_SRC_REV" \
  "$(input_hash build/kernel-config build/kernel-patches)" | sha256 -q)"
seed_legacy_port_cache
ports_key="$(ports_common_key)"

if [[ "$(cat "$state/rootfs-sha" 2>/dev/null || true)" != "$BUILD_SHA" ]]; then
  chflags -R noschg "$root/rootfs" "$root/bootloader" 2>/dev/null || true
  rm -rf "$root/rootfs" "$root/bootloader" "$root/artifacts"
  mkdir -p "$root/artifacts"
  create_rootfs
  build_world
  rm -f "$state"/installed-*
  printf '%s\n' "$BUILD_SHA" > "$state/rootfs-sha"
fi

kernel_started=$SECONDS
kernel_cache=hit
if [[ "$(cat "$state/kernel-key" 2>/dev/null || true)" != "$kernel_key" ]]; then
  kernel_cache=miss
  touch "$state/dirty"
  git -C /usr/src reset --hard "$FREEBSD_SRC_REV"
  git -C /usr/src clean -fd
  for patch in "$source"/build/kernel-patches/*; do
    [[ -f "$patch/pkg-state" ]] || continue
    [[ "$(tr '[:lower:]' '[:upper:]' < "$patch/pkg-state")" == ON ]] || continue
    make -C "$patch" install
  done
  cp "$source/build/kernel-config/$XIGMANAS_KERNCONF" "/sys/$XIGMANAS_ARCH/conf/"
  if [[ "$(cat "$state/kernel-working-key" 2>/dev/null || true)" != "$kernel_key" ]]; then
    rm -rf "$XIGMANAS_OBJDIRPREFIX"
    printf '%s\n' "$kernel_key" > "$state/kernel-working-key"
  fi
  env MAKEOBJDIRPREFIX="$XIGMANAS_OBJDIRPREFIX" \
    make -C /usr/src -j4 buildkernel KERNCONF="$XIGMANAS_KERNCONF" \
    WITH_CCACHE_BUILD=yes NO_KERNELCLEAN=yes
  env MAKEOBJDIRPREFIX="$XIGMANAS_OBJDIRPREFIX" \
    make -C /usr/src installkernel KERNCONF="$XIGMANAS_KERNCONF" KODIR=/boot/kernel
  printf '%s\n' "$kernel_key" > "$state/kernel-key"
fi
printf 'TIMING kernel cache=%s seconds=%s\n' "$kernel_cache" "$((SECONDS - kernel_started))"
compress_kernel
install_kernel_modules

if [[ "$(cat "$state/ports-common-key" 2>/dev/null || true)" != "$ports_key" ]]; then
  touch "$state/dirty"
  base-ports
  make -C "$source/build/ports/options"
  printf '%s\n' "$ports_key" > "$state/ports-common-key"
fi

for port in "$source"/build/ports/*; do
  [[ -f "$port/pkg-state" ]] || continue
  [[ "$(tr '[:lower:]' '[:upper:]' < "$port/pkg-state")" == ON ]] || continue
  name="${port##*/}"
  echo "===== XigmaNAS port: $name ====="
  if [[ "$name" == nut-devel ]]; then
    prepare_powerman
  elif [[ "$name" == open-vm-tools ]]; then
    prepare_libpfctl
    prepare_open_vm_tools
  elif [[ "$name" == samba422 ]]; then
    prepare_lmdb
    prepare_samba_nsupdate
  fi
  prepare_port_cache "$port" "$ports_key"
  port_started=$SECONDS
  port_cache=hit
  if [[ ! -f "$state/built-$name" ]]; then
    port_cache=miss
    touch "$state/dirty"
    if [[ "$name" == vbox ]]; then
      prepare_cups
      prepare_virtualbox
      if ! pkg info -e llvm19; then
        # Finish the memory-heavy dependency without cleaning its cached objects.
        echo 'CACHE llvm19: resuming with one compiler job.'
        make -C /usr/ports/devel/llvm19 install MAKE_JOBS_NUMBER=1
      else
        echo 'CACHE llvm19: retaining the installed dependency.'
      fi
    fi
    make -C "$port" build
    if [[ "$name" == nut-devel ]]; then
      test -x /usr/local/bin/pm
      pkg which /usr/local/bin/pm
    fi
    touch "$state/built-$name"
  fi
  printf 'TIMING port=%s cache=%s seconds=%s\n' "$name" "$port_cache" "$((SECONDS - port_started))"
done

touch "$state/required-workdirs"
for port in "$source"/build/ports/*; do
  [[ -f "$port/pkg-state" ]] || continue
  [[ "$(tr '[:lower:]' '[:upper:]' < "$port/pkg-state")" == ON ]] || continue
  prepare_dependency_cache "$port"
done

if [[ "$(cat "$state/pkg-db-sha" 2>/dev/null || true)" != "$BUILD_SHA" ]]; then
  cp -p /var/db/pkg/local.sqlite "$root/work/pkg/local.sqlite"
  printf '%s\n' "$BUILD_SHA" > "$state/pkg-db-sha"
fi
for port in "$source"/build/ports/*; do
  [[ -f "$port/pkg-state" ]] || continue
  [[ "$(tr '[:lower:]' '[:upper:]' < "$port/pkg-state")" == ON ]] || continue
  name="${port##*/}"
  if [[ ! -f "$state/installed-$name" ]]; then
    # Image copies and their private package database are not new compiled state.
    rm -f "$port"/work/.install_done.* "$port"/work/.stage_done.*
    env PKG_DBDIR="$root/work/pkg" FORCE_PKG_REGISTER=1 make -C "$port" install
    touch "$state/installed-$name"
  fi
done

"$source/build/xigmanas-create-bootdir.sh" -f -m "$root/bootloader"
add_libs
"$source/build/xigmanas-modify-permissions.sh" "$root/rootfs"
use_svn
configure_image_sizes
unset TINY_ISO
create_iso
create_usb_gpt
pigz -t "$root"/*-LiveUSB-GPT-*.img.gz
for target in LiveCD LiveUSB-GPT; do
  case "$target" in
    LiveCD) pattern='*-LiveCD-*.iso' ;;
    LiveUSB-GPT) pattern='*-LiveUSB-GPT-*.img.gz' ;;
  esac
  destination="$root/artifacts/$target"
  mkdir -p "$destination"
  find "$root" -maxdepth 1 -type f -name "$pattern" \
    -exec cp -p {} "$destination/" \;
  test -n "$(find "$destination" -name "$pattern" -size +100M)"
  (cd "$destination" && find . -maxdepth 1 -type f -name "$pattern" \
    -exec sha512 {} + > SHA512-CHECKSUM)
  printf 'target=%s\ngit_sha=%s\nsvn_revision=%s\nfreebsd_src=%s\nfreebsd_ports=%s\n' \
    "$target" "$BUILD_SHA" "$svn_revision" "$FREEBSD_SRC_REV" "$FREEBSD_PORTS_REV" \
    > "$destination/build-info.txt"
done
ccache --show-stats
