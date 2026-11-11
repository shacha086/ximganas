#!/usr/bin/env bash
#
# This script is designed to automate the assembly of XigmaNAS® builds.
#
# Part of XigmaNAS® (https://www.xigmanas.com).
# Copyright © 2018-2025 XigmaNAS® <info@xigmanas.com>.
# All rights reserved.
#
set -x # print
# set -o pipefail # propogate errors in the pipeline
# set -e # exit on error

. /usr/local/xigmanas/svn/build/functions.inc
XIGMANAS_LOGDIR="$XIGMANAS_ROOTDIR/logs"
XIGMANAS_PORTS_DIR=/usr/local/xigmanas/svn/build/ports
export BATCH=yes

mkdir -p $XIGMANAS_LOGDIR

# ports to build
BUILT_PORTS=(arcconf ataidle autosnapshot clog devcpu-data-amd devcpu-data-intel fdisk firefly isboot lcdproc-devel locale mkpw rconf sas2ircu sas3ircu tw_cli)
INSTALLED_PACKAGES="bash bsnmp-ucd ca_root_nss cdialog dmidecode e2fsprogs-core fusefs-exfat exfat-utils fusefs-ext2 fusefs-ntfs grub2-bhyve gzip icu inadyn iperf3 ipmitool istgt lighttpd mDNSResponder mariadb118-server mariadb118-client minidlna msmtp nano netatalk4 nss_ldap nut-devel open-vm-tools openssh-portable opie pam_ldap pam_mkhomedir php84 php84-pecl-APCu phpMyAdmin-php84 proftpd python311 py311-wsdd rrdtool rsync samba422 dns/samba-nsupdate scponly sipcalc smartmontools spindown sudo syncthing tftp-hpa tmux transmission-cli transmission-web transmission-daemon transmission-utils unison virtualbox-ose-nox11-72 virtualbox-ose-kmod-72 wait_on wol xmlstarlet zoneinfo php84-bcmath php84-bz2 php84-ctype php84-curl php84-dom php84-exif php84-filter php84-ftp php84-gd php84-gettext php84-gmp php84-iconv php84-pecl-imap php84-intl php84-ldap php84-mbstring php84-mysqli php84-opcache php84-pdo php84-pdo_mysql php84-pdo_sqlite php84-pear php84-pecl-APCu php84-pecl-mcrypt php84-session php84-simplexml php84-soap php84-sockets php84-sqlite3 php84-sysvmsg php84-sysvsem php84-sysvshm php84-tokenizer php84-xml php84-zip php84-zlib py311-markdown py311-importlib-metadata py311-zipp py311-rrdtool openzfs phpvirtualbox-72-php84"

### functions ###

# install port dependencies via pkg
pkg_deps() {
    DEPS_FILE="$(dirname "$0")"/pkg-deps.txt
    # delete existing file
    echo -n > ${DEPS_FILE}

    for port in "${BUILT_PORTS[@]}"; do
        echo "installing build deps for $port.." 2>&1 | tee -a ${XIGMANAS_LOGDIR}/pkg-deps.log
        echo cd ${XIGMANAS_PORTS_DIR}/${port}  2>&1 | tee -a ${XIGMANAS_LOGDIR}/pkg-deps.log
        cd ${XIGMANAS_PORTS_DIR}/${port} && \
        make build-depends-list | sed 's=/usr/ports/==' | tee -a ${XIGMANAS_LOGDIR}/pkg-deps.log >> ${DEPS_FILE}
    done

    # shellcheck disable=SC2046
    pkg install -fyr latest $( sort "$DEPS_FILE" | uniq ) 2>&1 | tee -a ${XIGMANAS_LOGDIR}/pkg-deps.log
}

# build ports that aren't installed by pkg
pkg_build() {
    echo "Building ports..." | tee ${XIGMANAS_LOGDIR}/pkg-build.log

    for port in "${BUILT_PORTS[@]}"; do
        echo "building $port" 2>&1 | tee -a ${XIGMANAS_LOGDIR}/pkg-build-"${port}".log
        echo cd ${XIGMANAS_PORTS_DIR}/"${port}"  2>&1 | tee -a ${XIGMANAS_LOGDIR}/pkg-build-"${port}".log
        cd ${XIGMANAS_PORTS_DIR}/"${port}" && \
        (make build do-install 2>&1 | tee -a ${XIGMANAS_LOGDIR}/pkg-build-"${port}".log) &
    done

    wait # wait for builds to finish
}

# install ports from pkg and copy to rootfs
pkg_copy() {
    echo "Installing files from INSTALLED_PACKAGES: $INSTALLED_PACKAGES" | tee ${XIGMANAS_LOGDIR}/pkg-copy-install.log
    # shellcheck disable=SC2086
#    pkg install -yr FreeBSD $INSTALLED_PACKAGES 2>&1 | tee -a ${XIGMANAS_LOGDIR}/pkg-copy-install.log

    # upgrade to latest (starting with latest causes errors)
#   pkg upgrade -yr latest | tee ${XIGMANAS_LOGDIR}/pkg-copy-upgrade.log
    pkg install -fy $INSTALLED_PACKAGES 2>&1 | tee -a ${XIGMANAS_LOGDIR}/pkg-copy-install.log

    make -f "$(dirname "$0")"/pkg-copy-Makefile 2>&1 | tee ${XIGMANAS_LOGDIR}/pkg-copy-make.log
}

# upgrade system via pkg base (instead of freebsd-update)
# https://wiki.freebsd.org/PkgBase
sys_upgrade() {
    mkdir -p /usr/local/etc/pkg/repos
    # remove finch pkg file
    rm -f /usr/local/etc/pkg/repos/FreeBSD.conf

    # create/enable pkg base repo if doesn't exit/isn't enabled
    grep enabled /usr/local/etc/pkg/repos/base.conf || echo 'base: {
    url: "pkg+https://pkg.FreeBSD.org/${ABI}/base_release_3",
    mirror_type: "srv",
    signature_type: "fingerprints",
    fingerprints: "/usr/share/keys/pkg",
    enabled: yes
    }' >/usr/local/etc/pkg/repos/base.conf

    # create/enable latest pkg repo if doesn't exit/isn't enabled
    grep enabled /usr/local/etc/pkg/repos/latest.conf || echo 'latest: {
    url: "pkg+https://pkg.FreeBSD.org/${ABI}/latest",
    mirror_type: "srv",
    signature_type: "fingerprints",
    fingerprints: "/usr/share/keys/pkg",
    enabled: yes
    }' >/usr/local/etc/pkg/repos/latest.conf

    # update pkg to see packages from base and latest
    IGNORE_OSVERSION=yes pkg update

    # find security patches. filter unnecessary and current patch-level packages
    ### pkg search -r base -g 'FreeBSD-*p?' | awk '!/-(lib32|dbg|dev|src|tests|mmccam|minimal)-/ {print $1}' | fgrep -v $(uname -r | awk -F- '{ print $1$3}') | xargs pkg install -y -r base
 
    # upgrade to 14.2-RELEASE (from 14.1)
    pkg search -r base -g 'FreeBSD-*' | awk '!/-(lib32|dbg|dev|src|tests|mmccam|minimal|man)-/ {print $1}' | xargs pkg install -y -r base
    pkg info | awk '/^FreeBSD-/ { print $1 }' | xargs pkg lock -y

    pkg install -r base -y FreeBSD-clibs-dev
    pkg lock -y FreeBSD-clibs-dev

    # restore conf files overwritten by pkg base upgrade
    cp -p /etc/shells.pkgsave /etc/shells
    cp -p /etc/master.passwd.pkgsave /etc/master.passwd
    pwd_mkdb -p /etc/master.passwd
    cp -p /etc/group.pkgsave /etc/group
    cp -p /etc/profile.pkgsave /etc/profile
    cp -p /etc/hosts.pkgsave /etc/hosts

    #cp -p /etc/rc.conf.pkgsave /etc/rc.conf
    cp -p /etc/ssh/sshd_config.pkgsave /etc/sshd_config

    find / -name \*.pkgsave -print -delete

    # (linker.hints was recreated at kernel install and we had the old modules as .pkgsave so we need to recreate it, this will be done at the next reboot)
    rm /boot/kernel/linker.hints
}

# setup/install prerequisites to build Xigmanas
prereq() {
    XIGMANAS_ROOTDIR="/usr/local/xigmanas"
    XIGMANAS_LOGDIR="$XIGMANAS_ROOTDIR/logs"
    mkdir -p "$XIGMANAS_LOGDIR"
    ln -s /usr/local/xigmanas /root/xigmanas

    # fetch latest ports
    (fetch https://download.freebsd.org/ftp/ports/ports/ports.tar.xz ; \
        tar xf ports.tar.xz -C /usr/) 2>&1 | tee ${XIGMANAS_LOGDIR}/prereq-ports.log &

    pkg install -y bash subversion cdrtools pigz


    # ===> Options unchanged
    # /!\ WARNING /!\

    # WITHOUT_X11 is unsupported, use WITHOUT=X11 on the command line, or one of
    # these in /etc/make.conf, OPTIONS_UNSET+=X11 to set it globally, or
    # emulators_virtualbox-ose_UNSET+=X11 for only this port.

    grep WITHOUT_X11=yes /etc/make.conf || echo WITHOUT_X11=yes >> /etc/make.conf
    grep WITHOUT=X11  /etc/make.conf || echo WITHOUT=X11  >> /etc/make.conf


    date; ls -l /boot/kernel/kernel
    sys_upgrade 2>&1 | tee ${XIGMANAS_LOGDIR}/prereq-sys_upgrade.log

    # install kernel source
    pkg install -yr base FreeBSD-src-sys 2>&1 | tee ${XIGMANAS_LOGDIR}/prereq-sys_upgrade-src.log &

    # install FreeBSD source (for clog/syslogd)
    pkg install -yr base FreeBSD-src 2>&1 | tee ${XIGMANAS_LOGDIR}/prereq-sys_upgrade-src.log &
 

    cd /usr/local/xigmanas/
    #svn co https://svn.code.sf.net/p/xigmanas/code/trunk svn 2>&1 | tee ${XIGMANAS_LOGDIR}/prereq-svn.log
    #cd svn; svn up -r10142 # 14.1.0.5.10142 RC1

    mkdir -p /usr/ports/distfiles
    cp /usr/local/xigmanas/svn/build/ports/distfiles/*.{gz,zip} /usr/ports/distfiles/

    # wait for pkg
    wait
}

# create all images using the build process from make.sh
build() {
    svn help > /dev/null || pkg install -y subversion &

    XIGMANAS_LOGDIR="$XIGMANAS_ROOTDIR/logs"
    mkdir -p "$XIGMANAS_LOGDIR"
    mkdir -p /usr/local/xigmanas/work

    ### Create Filesystem Structure
    create_rootfs | tee "${XIGMANAS_LOGDIR}/build-create_rootfs.log"

    # install port dependencies via pkg
    pkg_deps

    # build ports that aren't installed by pkg
    pkg_build &
    pkg_build_pid=$!

    ### Install Kernel + Modules
    mkdir -p /usr/local/xigmanas/work
    ### build/compress kernel
    compress_kernel 2>&1 | tee "${XIGMANAS_LOGDIR}/build-compress_kernel.log"
    ### install kernel modules
    install_kernel_modules | tee "${XIGMANAS_LOGDIR}/build-install_kernel_modules.log"


    ### Install World
    build_world | tee "${XIGMANAS_LOGDIR}/build-build_world.log"&

    # install ports from pkg and copy to rootfs
    pkg_copy
    # pkg copy test
    if [ ! -f /usr/local/xigmanas/rootfs/usr/local/bin/xml ]; then
        echo 'Missing usr/local/bin/xml, error in pkg_copy' | tee -a ${XIGMANAS_LOGDIR}/pkg-copy-install.log
        return 1
    fi

    ### Build Bootloader
    opt="-f -m";
    # shellcheck disable=SC2086
    "$XIGMANAS_SVNDIR"/build/xigmanas-create-bootdir.sh $opt "$XIGMANAS_BOOTDIR" | tee "${XIGMANAS_LOGDIR}/build-create-bootdir.log"

    wait ${pkg_build_pid} # wait for pkg-build

    ### 8 add libs
    add_libs | tee "${XIGMANAS_LOGDIR}/build-add_libs.log"

    ### 8.1 reinstall subversion (may have been deleted during pkg-copy)
    svn || pkg install -y subversion &

    ### 8.2 delete static libs
    del_libs | tee "${XIGMANAS_LOGDIR}/build-del_libs.log"

    ### 8.5 sym-link duplicate libs. ie: librrd.so -> librrd.so.8.3.0
    sym_libs | tee "${XIGMANAS_LOGDIR}/build-sym_libs.log"

    ### 8.6 etc/rc.conf for openzfs
    XIGMANAS_LOADER_CONF="$XIGMANAS_ROOTDIR/bootloader/loader.conf"
    sysrc -f "$XIGMANAS_LOADER_CONF" zfs_load=NO
    sysrc -f "$XIGMANAS_LOADER_CONF" opensolaris_load=NO
    sysrc -f "$XIGMANAS_LOADER_CONF" openzfs_load=YES
    #cp /boot/modules/openzfs.ko "$XIGMANAS_ROOTDIR/bootloader/modules/"

    ### 9 Modify permissions
    "$XIGMANAS_SVNDIR"/build/xigmanas-modify-permissions.sh "$XIGMANAS_ROOTFS"  | tee "${XIGMANAS_LOGDIR}/build-modify-permissions.log"

    ### 11 create MBR usb
    create_usb | tee "${XIGMANAS_LOGDIR}/build-create_usb.log"
    create_usb_gpt | tee "${XIGMANAS_LOGDIR}/build-create_usb_gpt.log"
    create_iso | tee "${XIGMANAS_LOGDIR}/build-create_iso.log"
    create_full | tee "${XIGMANAS_LOGDIR}/build-create_full.log"
    create_embedded | tee "${XIGMANAS_LOGDIR}/build-create_embedded.log"

    echo "success"

}
### end functions ###

date; prereq;
date; build; date;
