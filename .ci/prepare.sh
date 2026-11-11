#!/bin/sh
set -eu
export ASSUME_ALWAYS_YES=yes
export IGNORE_OSVERSION=yes
pkg install -y bash git-lite subversion cdrtools pigz ccache rsync
mkdir -p /var/cache/ccache /var/db/ports /boot/modules /usr/local/xigmanas/state /usr/obj
ccache --set-config=cache_dir=/var/cache/ccache
ccache --max-size=2G
uname -a
df -h
