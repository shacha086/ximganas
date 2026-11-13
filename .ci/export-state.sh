#!/bin/sh
set -eu

# NAS wrappers still copy files from some installed dependencies' work/stage.
# Retain those as well as every custom port's work tree.
find /usr/ports -type d -name work -prune -print |
while IFS= read -r work; do
  if grep -qxF "$work" /usr/local/xigmanas/state/required-workdirs 2>/dev/null; then
    continue
  fi
  if find "$work" -maxdepth 1 -name '.install_done.*' | grep -q .; then
    rm -rf "$work"
  fi
done
sync
mkdir -p /var/db/ports /boot/modules /usr/src /usr/ports /usr/obj
test -f /etc/make.conf || touch /etc/make.conf
tar -cpf - -C / \
  --exclude='*/.ssh' \
  --exclude='*/ssh_host_*_key*' \
  --exclude='usr/local/etc/ssh' \
  --exclude='usr/local/xigmanas/artifacts' \
  --exclude='usr/local/xigmanas/*.iso' \
  --exclude='usr/local/xigmanas/*.img.gz' \
  --exclude='usr/local/xigmanas/*.img.xz' \
  --exclude='usr/local/xigmanas/*.txz' \
  usr/local usr/src usr/ports usr/obj \
  var/db/pkg var/db/ports var/cache/ccache \
  boot/kernel boot/modules \
  etc/make.conf
