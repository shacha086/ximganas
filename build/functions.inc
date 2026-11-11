#!/usr/bin/env bash
#
# This script is designed to automate the assembly of XigmaNAS® builds.
#
# Part of XigmaNAS® (https://www.xigmanas.com).
# Copyright © 2018-2025 XigmaNAS® <info@xigmanas.com>.
# All rights reserved.
#
# Debug script
# set -x
#
#
# Check and install pigz as requirement for make.sh
pigz -h 2&>/dev/null || pkg install -y pigz

################################################################################
#	Settings
################################################################################

#	Global variables
XIGMANAS_ROOTDIR="/usr/local/xigmanas"
XIGMANAS_WORKINGDIR="$XIGMANAS_ROOTDIR/work"
XIGMANAS_ROOTFS="$XIGMANAS_ROOTDIR/rootfs"
XIGMANAS_SVNDIR="$XIGMANAS_ROOTDIR/svn"
XIGMANAS_WORLD=""
XIGMANAS_PRODUCTNAME=$(cat $XIGMANAS_SVNDIR/etc/prd.name)
XIGMANAS_VERSION=$(cat $XIGMANAS_SVNDIR/etc/prd.version)
XIGMANAS_REVISION=$(svn info ${XIGMANAS_SVNDIR} | grep "Revision:" | awk '{print $2}')
if [ -f "${XIGMANAS_SVNDIR}/local.revision" ]; then
	XIGMANAS_REVISION=$(printf $(cat ${XIGMANAS_SVNDIR}/local.revision) ${XIGMANAS_REVISION})
fi
XIGMANAS_ARCH=$(uname -p)
XIGMANAS_KERNCONF="$(echo ${XIGMANAS_PRODUCTNAME} | tr '[:lower:]' '[:upper:]')-${XIGMANAS_ARCH}"
if [ "amd64" = ${XIGMANAS_ARCH} ]; then
	XIGMANAS_XARCH="x64"
elif [ "i386" = ${XIGMANAS_ARCH} ]; then
	echo "->> build script does not support 32-bit builds for the i386 architecture"
exit 1
else
	XIGMANAS_XARCH=$XIGMANAS_ARCH
fi
XIGMANAS_OBJDIRPREFIX="/usr/obj/$(echo ${XIGMANAS_PRODUCTNAME} | tr '[:upper:]' '[:lower:]')"
XIGMANAS_BOOTDIR="$XIGMANAS_ROOTDIR/bootloader"
XIGMANAS_TMPDIR="/tmp/xigmanastmp"

export XIGMANAS_ROOTDIR
export XIGMANAS_WORKINGDIR
export XIGMANAS_ROOTFS
export XIGMANAS_SVNDIR
export XIGMANAS_WORLD
export XIGMANAS_PRODUCTNAME
export XIGMANAS_VERSION
export XIGMANAS_ARCH
export XIGMANAS_XARCH
export XIGMANAS_KERNCONF
export XIGMANAS_OBJDIRPREFIX
export XIGMANAS_BOOTDIR
export XIGMANAS_REVISION
export XIGMANAS_TMPDIR

XIGMANAS_MK=${XIGMANAS_SVNDIR}/build/ports/xigmanas.mk
rm -rf ${XIGMANAS_MK}
echo "XIGMANAS_ROOTDIR=${XIGMANAS_ROOTDIR}" >> ${XIGMANAS_MK}
echo "XIGMANAS_WORKINGDIR=${XIGMANAS_WORKINGDIR}" >> ${XIGMANAS_MK}
echo "XIGMANAS_ROOTFS=${XIGMANAS_ROOTFS}" >> ${XIGMANAS_MK}
echo "XIGMANAS_SVNDIR=${XIGMANAS_SVNDIR}" >> ${XIGMANAS_MK}
echo "XIGMANAS_WORLD=${XIGMANAS_WORLD}" >> ${XIGMANAS_MK}
echo "XIGMANAS_PRODUCTNAME=${XIGMANAS_PRODUCTNAME}" >> ${XIGMANAS_MK}
echo "XIGMANAS_VERSION=${XIGMANAS_VERSION}" >> ${XIGMANAS_MK}
echo "XIGMANAS_ARCH=${XIGMANAS_ARCH}" >> ${XIGMANAS_MK}
echo "XIGMANAS_XARCH=${XIGMANAS_XARCH}" >> ${XIGMANAS_MK}
echo "XIGMANAS_KERNCONF=${XIGMANAS_KERNCONF}" >> ${XIGMANAS_MK}
echo "XIGMANAS_OBJDIRPREFIX=${XIGMANAS_OBJDIRPREFIX}" >> ${XIGMANAS_MK}
echo "XIGMANAS_BOOTDIR=${XIGMANAS_BOOTDIR}" >> ${XIGMANAS_MK}
echo "XIGMANAS_REVISION=${XIGMANAS_REVISION}" >> ${XIGMANAS_MK}
echo "XIGMANAS_TMPDIR=${XIGMANAS_TMPDIR}" >> ${XIGMANAS_MK}

#	Local variables
XIGMANAS_URL=$(cat $XIGMANAS_SVNDIR/etc/prd.url)
XIGMANAS_SVNURL="https://svn.code.sf.net/p/xigmanas/code/trunk"
XIGMANAS_GIT_SRCTREE="https://git.freebsd.org/src.git"
XIGMANAS_GIT_BRANCH="releng/14.3"

#	Size in MB of the MFS Root filesystem that will include all FreeBSD binary
#	and XigmaNAS® WebGUI/Scripts. Keep this file very small! This file is unzipped
#	to a RAM disk at XigmaNAS® startup.
#	The image must fit on 2GB CF/USB.
#	Actual size of MDLOCAL is defined in /etc/rc line 38.
XIGMANAS_MFSROOT_SIZE=132
XIGMANAS_MDLOCAL_SIZE=1192
XIGMANAS_MDLOCAL_MINI_SIZE=36
#	Now image size is less than 500MB (up to 476MiB - alignment)
XIGMANAS_IMG_SIZE=460
if [ "amd64" = ${XIGMANAS_ARCH} ]; then
	XIGMANAS_MFSROOT_SIZE=144
	XIGMANAS_MDLOCAL_SIZE=1332
	XIGMANAS_MDLOCAL_MINI_SIZE=52
	XIGMANAS_IMG_SIZE=500
fi

#	Set compression level from 1 to 9
#	1 offers the fastest compression speed but at a lower ratio, and 9 offers the highest compression ratio but at a lower speed.
XIGMANAS_COMPLEVEL=8
XIGMANAS_KERNCOMPLEVEL=9

#	Specify the number of worker threads to use.
#	Setting threads to a special value 0 makes xz use as many threads as there are CPU cores on the system.
XIGMANAS_XZ_THREADS=0

#	Set a memory usage limit for compression/decompression.
XIGMANAS_XZ_MEM_LIMIT=75%

#	Media geometry, only relevant if bios doesn't understand LBA.
XIGMANAS_IMG_SIZE_SEC=$(expr ${XIGMANAS_IMG_SIZE} \* 2048)
XIGMANAS_IMG_SECTS=63
#	XIGMANAS_IMG_HEADS=16
XIGMANAS_IMG_HEADS=255
#	cylinder alignment
XIGMANAS_IMG_SIZE_SEC=$(expr \( $XIGMANAS_IMG_SIZE_SEC / \( $XIGMANAS_IMG_SECTS \* $XIGMANAS_IMG_HEADS \) \) \* \( $XIGMANAS_IMG_SECTS \* $XIGMANAS_IMG_HEADS \))

#	aligned BSD partition on MBR slice
XIGMANAS_IMG_SSTART=$XIGMANAS_IMG_SECTS
XIGMANAS_IMG_SSIZE=$(expr $XIGMANAS_IMG_SIZE_SEC - $XIGMANAS_IMG_SSTART)
#	aligned by BLKSEC: 8=4KB, 64=32KB, 128=64KB, 2048=1MB
XIGMANAS_IMG_BLKSEC=8
#	XIGMANAS_IMG_BLKSEC=64
XIGMANAS_IMG_BLKSIZE=$(expr $XIGMANAS_IMG_BLKSEC \* 512)
#	PSTART must BLKSEC aligned in the slice.
XIGMANAS_IMG_POFFSET=16
XIGMANAS_IMG_PSTART=$(expr \( \( \( $XIGMANAS_IMG_SSTART + $XIGMANAS_IMG_POFFSET + $XIGMANAS_IMG_BLKSEC - 1 \) / $XIGMANAS_IMG_BLKSEC \) \* $XIGMANAS_IMG_BLKSEC \) - $XIGMANAS_IMG_SSTART)
XIGMANAS_IMG_PSIZE0=$(expr $XIGMANAS_IMG_SSIZE - $XIGMANAS_IMG_PSTART)
if [ $(expr $XIGMANAS_IMG_PSIZE0 % $XIGMANAS_IMG_BLKSEC) -ne 0 ]; then
	XIGMANAS_IMG_PSIZE=$(expr $XIGMANAS_IMG_PSIZE0 - \( $XIGMANAS_IMG_PSIZE0 % $XIGMANAS_IMG_BLKSEC \))
else
	XIGMANAS_IMG_PSIZE=$XIGMANAS_IMG_PSIZE0
fi

#	BSD partition only
XIGMANAS_IMG_SSTART=0
XIGMANAS_IMG_SSIZE=$XIGMANAS_IMG_SIZE_SEC
XIGMANAS_IMG_BLKSEC=1
XIGMANAS_IMG_BLKSIZE=512
XIGMANAS_IMG_POFFSET=16
XIGMANAS_IMG_PSTART=$XIGMANAS_IMG_POFFSET
XIGMANAS_IMG_PSIZE=$(expr $XIGMANAS_IMG_SSIZE - $XIGMANAS_IMG_PSTART)

#	newfs parameters
XIGMANAS_IMGFMT_SECTOR=512
XIGMANAS_IMGFMT_FSIZE=2048
#	XIGMANAS_IMGFMT_SECTOR=4096
#	XIGMANAS_IMGFMT_FSIZE=4096
XIGMANAS_IMGFMT_BSIZE=$(expr $XIGMANAS_IMGFMT_FSIZE \* 8)

#	echo "IMAGE=$XIGMANAS_IMG_SIZE_SEC"
#	echo "SSTART=$XIGMANAS_IMG_SSTART"
#	echo "SSIZE=$XIGMANAS_IMG_SSIZE"
#	echo "ALIGN=$XIGMANAS_IMG_BLKSEC"
#	echo "PSTART=$XIGMANAS_IMG_PSTART"
#	echo "PSIZE0=$XIGMANAS_IMG_PSIZE0"
#	echo "PSIZE=$XIGMANAS_IMG_PSIZE"

#	Options:
#	Support bootmenu
OPT_BOOTMENU=1
#	Support bootsplash
OPT_BOOTSPLASH=0
#	Support serial console
OPT_SERIALCONSOLE=0
#	Support efi boot
OPT_EFIBOOT_SUPPORT=1

#	Dialog command
DIALOG="dialog"

################################################################################
#	Functions
################################################################################

#	Update source tree and ports collection.
update_sources() {
	tempfile=$XIGMANAS_WORKINGDIR/tmp$$

	# Choose what to do.
	$DIALOG --ascii-lines --title "$XIGMANAS_PRODUCTNAME - Update Sources" --checklist "Please select what to update." 12 60 5 \
		"Git_src-source" "Pull FreeBSD src source tree" OFF \
		"Git_src-update" "Update FreeBSD src source tree" OFF \
		"FreeBSD_update" "Fetch and install binary updates" OFF \
		"Git_port-update" "Update ports collection" OFF \
		"pkg_upgrade" "Upgrade pkg on host" OFF 2> $tempfile
	if [ 0 != $? ]; then # successful?
		rm $tempfile
		return 1
	fi

	choices=$(cat $tempfile)
	rm $tempfile

	for choice in $(echo $choices | tr -d '"'); do
		case $choice in
			Git_src-source)
				rm -rf /usr/src;
				mkdir /usr/src; git clone -b ${XIGMANAS_GIT_BRANCH} ${XIGMANAS_GIT_SRCTREE} /usr/src;;
			Git_src-update)
				cd /usr/src; git pull;;
			FreeBSD_update)
				freebsd-update fetch install;;
			Git_port-update)
			    git -C /usr/ports pull;;
			pkg_upgrade)
			    pkg update && pkg upgrade;;
		esac
	done

	return $?
}

#	Build world. Copying required files defined in 'build/xigmanas.files'.
build_world() {
	# Make a pseudo 'chroot' to XigmaNAS® root.
	cd $XIGMANAS_ROOTFS

	echo
	echo "Building World:"

	[ -f $XIGMANAS_WORKINGDIR/xigmanas.files ] && rm -f $XIGMANAS_WORKINGDIR/xigmanas.files
	cp $XIGMANAS_SVNDIR/build/xigmanas.files $XIGMANAS_WORKINGDIR

	# Add custom binaries
	if [ -f $XIGMANAS_WORKINGDIR/xigmanas.custfiles ]; then
		cat $XIGMANAS_WORKINGDIR/xigmanas.custfiles >> $XIGMANAS_WORKINGDIR/xigmanas.files
	fi

	for i in $(cat $XIGMANAS_WORKINGDIR/xigmanas.files | grep -v "^#"); do
		file=$(echo "$i" | cut -d ":" -f 1)

		# Deal with directories
		dir=$(dirname $file)
		if [ ! -d ${XIGMANAS_WORLD}/$dir ]; then
			echo "skip: $file ($dir)"
			continue;
		fi
		if [ ! -d $dir ]; then
			mkdir -pv $dir
		fi
		# Copy files from world.
		cp -Rpv ${XIGMANAS_WORLD}/$file $(echo $file | rev | cut -d "/" -f 2- | rev)

		# Deal with links
		if [ $(echo "$i" | grep -c ":") -gt 0 ]; then
			for j in $(echo $i | cut -d ":" -f 2- | sed "s/:/ /g"); do
				ln -sv /$file $j
			done
		fi
	done

	# iconv files
	(cd ${XIGMANAS_WORLD}/; find -x usr/lib/i18n | cpio -pdv ${XIGMANAS_ROOTFS})
	(cd ${XIGMANAS_WORLD}/; find -x usr/share/i18n | cpio -pdv ${XIGMANAS_ROOTFS})

	# Copy required custom files from SVN to ROOTFS(early mfsroot)
	cp -v ${XIGMANAS_SVNDIR}/boot/loader.efi ${XIGMANAS_ROOTFS}/boot
	cp -v ${XIGMANAS_SVNDIR}/boot/loader_4th.efi ${XIGMANAS_ROOTFS}/boot
	cp -v ${XIGMANAS_SVNDIR}/boot/loader_lua.efi ${XIGMANAS_ROOTFS}/boot
	cp -v ${XIGMANAS_SVNDIR}/boot/loader_simp.efi ${XIGMANAS_ROOTFS}/boot

	# Cleanup
	chflags -R noschg $XIGMANAS_TMPDIR
	chflags -R noschg $XIGMANAS_ROOTFS
	[ -d $XIGMANAS_TMPDIR ] && rm -f $XIGMANAS_WORKINGDIR/xigmanas.files
	[ -f $XIGMANAS_WORKINGDIR/mfsroot.gz ] && rm -f $XIGMANAS_WORKINGDIR/mfsroot.gz

	return 0
}

#	Create rootfs
create_rootfs() {
	$XIGMANAS_SVNDIR/build/xigmanas-create-rootfs.sh -f $XIGMANAS_ROOTFS

	# Configuring platform variable
	echo ${XIGMANAS_VERSION} > ${XIGMANAS_ROOTFS}/etc/prd.version

	# Config file: config.xml
	cd $XIGMANAS_ROOTFS/conf.default/
	cp -v $XIGMANAS_SVNDIR/conf/config.xml .

	# Compress zoneinfo data, exclude some useless files.
	mkdir $XIGMANAS_TMPDIR
	echo "Factory" > $XIGMANAS_TMPDIR/zoneinfo.exlude
	echo "posixrules" >> $XIGMANAS_TMPDIR/zoneinfo.exlude
	echo "zone.tab" >> $XIGMANAS_TMPDIR/zoneinfo.exlude
	tar -c -v -f - -X $XIGMANAS_TMPDIR/zoneinfo.exlude -C /usr/share/zoneinfo/ . | xz -cv -T $XIGMANAS_XZ_THREADS -M $XIGMANAS_XZ_MEM_LIMIT > $XIGMANAS_ROOTFS/usr/share/zoneinfo.txz
	rm $XIGMANAS_TMPDIR/zoneinfo.exlude

	return 0
}

#	Actions before building kernel (e.g. install special/additional kernel patches).
pre_build_kernel() {
	tempfile=$XIGMANAS_WORKINGDIR/tmp$$
	patches=$XIGMANAS_WORKINGDIR/patches$$

	# Create list of available packages.
	echo "#! /bin/sh
$DIALOG --ascii-lines --title \"$XIGMANAS_PRODUCTNAME - Kernel Patches\" \\
--checklist \"Select the patches you want to add. Make sure you have clean/origin kernel sources (via suvbersion) to apply patches successful.\" 22 88 14 \\" > $tempfile

	for s in $XIGMANAS_SVNDIR/build/kernel-patches/*; do
		[ ! -d "$s" ] && continue
		package=$(basename $s)
		desc=$(cat $s/pkg-descr)
		state=$(cat $s/pkg-state)
		echo "\"$package\" \"$desc\" $state \\" >> $tempfile
	done

	# Display list of available kernel patches.
	sh $tempfile 2> $patches
	if [ 0 != $? ]; then # successful?
		rm $tempfile
		return 1
	fi
	rm $tempfile

	echo "Remove old patched files..."
	for file in $(find /usr/src -name "*.orig"); do
		rm -rv ${file}
	done

	for patch in $(cat $patches | tr -d '"'); do
		echo
		echo "-------------------------------------------------------------------------------";
		echo ">>> Adding kernel patch: ${patch}"
		echo "-------------------------------------------------------------------------------";
		cd $XIGMANAS_SVNDIR/build/kernel-patches/$patch
		make install
		[ 0 != $? ] && return 1 # successful?
	done
	rm $patches
}

build_kernel() {
	# Copy kernel configuration.
	cd /sys/${XIGMANAS_ARCH}/conf;
	cp -f $XIGMANAS_SVNDIR/build/kernel-config/${XIGMANAS_KERNCONF} .;
	# Clean object directory.
	rm -f -r ${XIGMANAS_OBJDIRPREFIX};
	# Compiling and compressing the kernel.
	cd /usr/src;
	env MAKEOBJDIRPREFIX=${XIGMANAS_OBJDIRPREFIX} make -j $(sysctl -n hw.ncpu) buildkernel KERNCONF=${XIGMANAS_KERNCONF};
	env MAKEOBJDIRPREFIX=${XIGMANAS_OBJDIRPREFIX} make installkernel KERNCONF=${XIGMANAS_KERNCONF};

	compress_kernel
}

# compress system kernel (/boot/kernel/kernel)
compress_kernel() {
	pigz -${XIGMANAS_KERNCOMPLEVEL}cnv ${XIGMANAS_WORLD}/boot/kernel/kernel  > ${XIGMANAS_WORKINGDIR}/kernel.gz
}

install_kernel_modules() {
	# Installing the modules.
	echo "-------------------------------------------------------------------------------";
	echo ">>> Install Kernel Modules";
	echo "-------------------------------------------------------------------------------";

	[ -f ${XIGMANAS_WORKINGDIR}/modules.files ] && rm -f ${XIGMANAS_WORKINGDIR}/modules.files;
	cp ${XIGMANAS_SVNDIR}/build/kernel-config/modules.files ${XIGMANAS_WORKINGDIR};

	modulesdir=${XIGMANAS_WORLD}/boot/kernel
	for module in $(grep -v "^#" ${XIGMANAS_WORKINGDIR}/modules.files | xargs basename); do
		install -v -o root -g wheel -m 555 ${modulesdir}/${module} ${XIGMANAS_ROOTFS}/boot/kernel
	done

}
#	Build/Install the kernel menu.
build_kernel_menu() {
	tempfile=$XIGMANAS_WORKINGDIR/tmp$$

	# Make sure kernel directory exists.
	[ ! -d "${XIGMANAS_ROOTFS}/boot/kernel" ] && mkdir -p ${XIGMANAS_ROOTFS}/boot/kernel

	# Choose what to do.
	$DIALOG --ascii-lines --title "$XIGMANAS_PRODUCTNAME - Build/Install Kernel" --checklist "Please select whether you want to build or install the kernel." 10 75 3 \
		"prebuild" "Apply kernel patches" OFF \
		"build" "Build kernel" OFF \
		"install" "Install kernel + modules" ON 2> $tempfile
	if [ 0 != $? ]; then # successful?
		rm $tempfile
		return 1
	fi

	choices=$(cat $tempfile)
	rm $tempfile

	for choice in $(echo $choices | tr -d '"'); do
		case $choice in
			prebuild)
				# Apply kernel patches.
				pre_build_kernel;
				[ 0 != $? ] && return 1;; # successful?
			build)
				build_kernel;;
			install)
				install_kernel_modules;;
		esac
	done

	return 0
}

#	Adding the libraries
add_libs() {
	echo
	echo "Adding required libs:"

	# Identify required libs.
	[ -f /tmp/lib.list ] && rm -f /tmp/lib.list
	dirs=(${XIGMANAS_ROOTFS}/bin ${XIGMANAS_ROOTFS}/sbin ${XIGMANAS_ROOTFS}/usr/bin ${XIGMANAS_ROOTFS}/usr/sbin ${XIGMANAS_ROOTFS}/usr/local/bin ${XIGMANAS_ROOTFS}/usr/local/sbin ${XIGMANAS_ROOTFS}/usr/lib ${XIGMANAS_ROOTFS}/usr/local/lib ${XIGMANAS_ROOTFS}/usr/libexec ${XIGMANAS_ROOTFS}/usr/local/libexec)
	for i in ${dirs[@]}; do
		for file in $(find -L ${i} -type f -print); do
			ldd -f "%p\n" ${file} 2> /dev/null >> /tmp/lib.list
		done
	done

	# Copy identified libs.
	for i in $(sort -u /tmp/lib.list); do
		if [ -e "${XIGMANAS_WORLD}${i}" ]; then
			DESTDIR=${XIGMANAS_ROOTFS}$(echo $i | rev | cut -d '/' -f 2- | rev)
			if [ ! -d ${DESTDIR} ]; then
				DESTDIR=${XIGMANAS_ROOTFS}/usr/local/lib
			fi
			FILE=$(basename ${i})
			if [ -L "${DESTDIR}/${FILE}" ]; then
				# do not remove symbolic link
				echo "link: ${i}"
			else
				install -c -s -v ${XIGMANAS_WORLD}${i} ${DESTDIR}
			fi
		fi
	done

	# for compatibility
	install -c -s -v ${XIGMANAS_WORLD}/usr/lib/libblacklist.so.* ${XIGMANAS_ROOTFS}/usr/lib
	install -c -s -v ${XIGMANAS_WORLD}/usr/lib/libgssapi_krb5.so.* ${XIGMANAS_ROOTFS}/usr/lib
	install -c -s -v ${XIGMANAS_WORLD}/usr/lib/libgssapi_ntlm.so.* ${XIGMANAS_ROOTFS}/usr/lib
	install -c -s -v ${XIGMANAS_WORLD}/usr/lib/libgssapi_spnego.so.* ${XIGMANAS_ROOTFS}/usr/lib

	# Cleanup.
	rm -f /tmp/lib.list

	return 0
}

#   Delete static libraries (.a files)
del_libs() {
	echo
	echo "Deleting static libs:"

    if [ -n "${XIGMANAS_ROOTFS}" ]; then
        find "${XIGMANAS_ROOTFS}" -path '*/lib/*' -name '*.a' -print -delete
    fi

    return 0
}

#	Replace duplicate libraries with symlinks (ie: librrd.so -> librrd.so.8)
sym_libs() {
	echo
	echo "Linking duplicate libs:"

	cd "$XIGMANAS_ROOTFS"/usr/local/lib || exit

    # Save checksums, file sizes, and filenames
    find . -type f -exec sh -c 'md5 -q "$1" ; stat -f "%z" "$1"; echo "$1"' sh {} \; \
        | paste - - - | sort > /tmp/checksums_sizes.txt

    # Find duplicates based on the checksum and file size
    awk '{print $1 " " $2}' /tmp/checksums_sizes.txt | uniq -d \
        > /tmp/duplicate_checksums_sizes.txt

    # Process duplicates
    while read -r checksum size; do
        # Get the names of the files with the same checksum and size
        files=$(awk -v cks="$checksum" -v sz="$size" '$1==cks && $2==sz {print $3}' \
            /tmp/checksums_sizes.txt)
        longest_file=""
        max_len=0
        for file in $files; do
            file_len=$(echo "$file" | wc -c | tr -d ' ')
            if [ "$file_len" -gt $max_len ]; then
            max_len=$file_len
            longest_file=$file
            fi
        done

        for file in $files; do
            if [ "$file" != "$longest_file" ]; then
                # Replace other files with symlinks to the longest filename
                echo "Linking $file to $longest_file"
                rm "$file"
                ln -s "$longest_file" "$file"
            fi
        done
    done < /tmp/duplicate_checksums_sizes.txt

    # Cleanup temporary files
    rm /tmp/checksums_sizes.txt /tmp/duplicate_checksums_sizes.txt

    return 0
}

#	Display menu for handling libraries
lib_menu() {
    # Define options for the checklist (tag item status text)
    options=(
        add_libs    "Add required libraries" on
        del_libs    "Delete static libraries (.a files) [optional]" off
        sym_libs    "Link duplicate libs (ie: librrd.so -> librrd.so.8) [optional]" off
    )

    # Show a checklist using dialog
    # The output is captured into the variable `choices`
    choices=$($DIALOG --ascii-lines --title "$XIGMANAS_PRODUCTNAME - Handle Libraries" \
    --checklist "Select library handling options:" 10 85 3 \
    "${options[@]}" \
    2>&1 >/dev/tty)

    # If no choices were selected, return 1
    if [ -z "$choices" ]; then
        return 1
    fi

    # Output choices
    for choice in $choices; do
        case $choice in
            add_libs)
                add_libs;;
            del_libs)
                del_libs;;
            sym_libs)
                sym_libs;;
        esac
    done

    return $?
}

#	Create checksum file
create_checksum_file() {
	echo "Generating SHA512 CHECKSUM File"
	XIGMANAS_CHECKSUMFILENAME="${XIGMANAS_PRODUCTNAME}-${XIGMANAS_XARCH}-${XIGMANAS_VERSION}.${XIGMANAS_REVISION}.SHA512-CHECKSUM"
	cd ${XIGMANAS_ROOTDIR} && sha512 *.img.gz *.xz *.iso *.txz > ${XIGMANAS_ROOTDIR}/${XIGMANAS_CHECKSUMFILENAME}

	return 0
}

#	Creating mdlocal-mini
create_mdlocal_mini() {
	echo "-------------------------------------------------------------------------------";
	echo ">>> Generating MDLOCAL-MINI Filesystem"
	echo "-------------------------------------------------------------------------------";

	cd $XIGMANAS_WORKINGDIR

	[ -f $XIGMANAS_WORKINGDIR/mdlocal-mini ] && rm -f $XIGMANAS_WORKINGDIR/mdlocal-mini
	[ -f $XIGMANAS_WORKINGDIR/mdlocal-mini.xz ] && rm -f $XIGMANAS_WORKINGDIR/mdlocal-mini.xz
	[ -f $XIGMANAS_WORKINGDIR/mdlocal-mini.files ] && rm -f $XIGMANAS_WORKINGDIR/mdlocal-mini.files
	cp $XIGMANAS_SVNDIR/build/xigmanas-mdlocal-mini.files $XIGMANAS_WORKINGDIR/mdlocal-mini.files

	# Make mfsroot to have the size of the XIGMANAS_MFSROOT_SIZE variable
	dd if=/dev/zero of=$XIGMANAS_WORKINGDIR/mdlocal-mini bs=1k seek=$(expr ${XIGMANAS_MDLOCAL_MINI_SIZE} \* 1024) count=0
	# Configure this file as a memory disk
	md=$(mdconfig -a -t vnode -f $XIGMANAS_WORKINGDIR/mdlocal-mini)
	# Format memory disk using UFS
	newfs -S $XIGMANAS_IMGFMT_SECTOR -b $XIGMANAS_IMGFMT_BSIZE -f $XIGMANAS_IMGFMT_FSIZE -O2 -o space -m 0 -U -t /dev/${md}
	# Umount memory disk (if already used)
	umount $XIGMANAS_TMPDIR >/dev/null 2>&1
	# Mount memory disk
	mkdir -p ${XIGMANAS_TMPDIR}/usr/local
	mount /dev/${md} ${XIGMANAS_TMPDIR}/usr/local

	# Create tree
	cd $XIGMANAS_ROOTFS/usr/local
	find . -type d | cpio -pmd ${XIGMANAS_TMPDIR}/usr/local

	# Copy selected files
	cd $XIGMANAS_TMPDIR
	for i in $(cat $XIGMANAS_WORKINGDIR/mdlocal-mini.files | grep -v "^#"); do
		d=$(dirname $i)
		b=$(basename $i)
		echo "cp $XIGMANAS_ROOTFS/$d/$b  ->  $XIGMANAS_TMPDIR/$d/$b"
		cp $XIGMANAS_ROOTFS/$d/$b $XIGMANAS_TMPDIR/$d/$b
		# Copy required libraries
		for j in $(ldd $XIGMANAS_ROOTFS/$d/$b | cut -w -f 4 | grep /usr/local | sed -e '/:/d' -e 's/^\///'); do
			d=$(dirname $j)
			b=$(basename $j)
			if [ ! -e $XIGMANAS_TMPDIR/$d/$b ]; then
				echo "cp $XIGMANAS_ROOTFS/$d/$b  ->  $XIGMANAS_TMPDIR/$d/$b"
				cp $XIGMANAS_ROOTFS/$d/$b $XIGMANAS_TMPDIR/$d/$b
			fi
		done
	done

	# Identify required libs.
	[ -f /tmp/lib.list ] && rm -f /tmp/lib.list
	dirs=(${XIGMANAS_TMPDIR}/usr/local/bin ${XIGMANAS_TMPDIR}/usr/local/sbin ${XIGMANAS_TMPDIR}/usr/local/lib ${XIGMANAS_TMPDIR}/usr/local/libexec)
	for i in ${dirs[@]}; do
		for file in $(find -L ${i} -type f -print); do
			ldd -f "%p\n" ${file} 2> /dev/null >> /tmp/lib.list
		done
	done

	# Copy identified libs.
	for i in $(sort -u /tmp/lib.list); do
		if [ -e "${XIGMANAS_WORLD}${i}" ]; then
			d=$(dirname $i)
			b=$(basename $i)
			if [ "$d" = "/lib" -o "$d" = "/usr/lib" ]; then
				# skip lib in mfsroot
				[ -e ${XIGMANAS_ROOTFS}${i} ] && continue
			fi
			DESTDIR=${XIGMANAS_TMPDIR}$(echo $i | rev | cut -d '/' -f 2- | rev)
			if [ ! -d ${DESTDIR} ]; then
				DESTDIR=${XIGMANAS_TMPDIR}/usr/local/lib
			fi
			install -c -s -v ${XIGMANAS_WORLD}${i} ${DESTDIR}
		fi
	done

	# Cleanup.
	rm -f /tmp/lib.list

	# Umount memory disk
	umount $XIGMANAS_TMPDIR/usr/local
	# Detach memory disk
	mdconfig -d -u ${md}

	echo "Compressing mdlocal-mini"
	xz -${XIGMANAS_COMPLEVEL}v -T $XIGMANAS_XZ_THREADS -M $XIGMANAS_XZ_MEM_LIMIT $XIGMANAS_WORKINGDIR/image.bin
	[ -f $XIGMANAS_WORKINGDIR/mdlocal-mini.files ] && rm -f $XIGMANAS_WORKINGDIR/mdlocal-mini.files

	return 0
}

#	Creating msfroot
create_mfsroot() {
	echo "-------------------------------------------------------------------------------";
	echo ">>> Generating MFSROOT Filesystem"
	echo "-------------------------------------------------------------------------------";

	cd $XIGMANAS_WORKINGDIR

	[ -f $XIGMANAS_WORKINGDIR/mfsroot ] && rm -f $XIGMANAS_WORKINGDIR/mfsroot
	[ -f $XIGMANAS_WORKINGDIR/mfsroot.gz ] && rm -f $XIGMANAS_WORKINGDIR/mfsroot.gz
	[ -f $XIGMANAS_WORKINGDIR/mfsroot.uzip ] && rm -f $XIGMANAS_WORKINGDIR/mfsroot.uzip
	[ -f $XIGMANAS_WORKINGDIR/mdlocal ] && rm -f $XIGMANAS_WORKINGDIR/mdlocal
	[ -f $XIGMANAS_WORKINGDIR/mdlocal.xz ] && rm -f $XIGMANAS_WORKINGDIR/mdlocal.xz
	[ -f $XIGMANAS_WORKINGDIR/mdlocal.uzip ] && rm -f $XIGMANAS_WORKINGDIR/mdlocal.uzip
	[ -d $XIGMANAS_SVNDIR ] && use_svn ;

	# Make mfsroot to have the size of the XIGMANAS_MFSROOT_SIZE variable
	dd if=/dev/zero of=$XIGMANAS_WORKINGDIR/mfsroot bs=1k seek=$(expr ${XIGMANAS_MFSROOT_SIZE} \* 1024) count=0
	dd if=/dev/zero of=$XIGMANAS_WORKINGDIR/mdlocal bs=1k seek=$(expr ${XIGMANAS_MDLOCAL_SIZE} \* 1024) count=0
	# Configure this file as a memory disk
	md=$(mdconfig -a -t vnode -f $XIGMANAS_WORKINGDIR/mfsroot)
	md2=$(mdconfig -a -t vnode -f $XIGMANAS_WORKINGDIR/mdlocal)
	# Format memory disk using UFS
	newfs -S $XIGMANAS_IMGFMT_SECTOR -b $XIGMANAS_IMGFMT_BSIZE -f $XIGMANAS_IMGFMT_FSIZE -O2 -o space -m 0 /dev/${md}
	newfs -S $XIGMANAS_IMGFMT_SECTOR -b $XIGMANAS_IMGFMT_BSIZE -f $XIGMANAS_IMGFMT_FSIZE -O2 -o space -m 0 -U -t /dev/${md2}
	# Umount memory disk (if already used)
	umount $XIGMANAS_TMPDIR >/dev/null 2>&1
	# Mount memory disk
	mount /dev/${md} ${XIGMANAS_TMPDIR}
	mkdir -p ${XIGMANAS_TMPDIR}/usr/local
	mount /dev/${md2} ${XIGMANAS_TMPDIR}/usr/local
	cd $XIGMANAS_TMPDIR
	tar -cf - -C $XIGMANAS_ROOTFS ./ | tar -xvpf -

	echo "Creating linker.hints"
	kldxref -R $XIGMANAS_TMPDIR/boot

	cd $XIGMANAS_WORKINGDIR
	# Umount memory disk
	umount $XIGMANAS_TMPDIR/usr/local
	umount $XIGMANAS_TMPDIR
	# Detach memory disk
	mdconfig -d -u ${md2}
	mdconfig -d -u ${md}

	echo "Compressing mfsroot"
	pigz -${XIGMANAS_COMPLEVEL}kfnv $XIGMANAS_WORKINGDIR/mfsroot
	echo "Compressing mdlocal"
	xz -${XIGMANAS_COMPLEVEL}kv -T $XIGMANAS_XZ_THREADS -M $XIGMANAS_XZ_MEM_LIMIT $XIGMANAS_WORKINGDIR/mdlocal

	create_mdlocal_mini;

	return 0
}

update_mfsroot() {
	echo "-------------------------------------------------------------------------------";
	echo ">>> Generating MFSROOT Filesystem (use existing image)"
	echo "-------------------------------------------------------------------------------";

	# Check if mfsroot exists.
	if [ ! -f $XIGMANAS_WORKINGDIR/mfsroot ]; then
		echo "==> Error: $XIGMANAS_WORKINGDIR/mfsroot does not exist."
		return 1
	fi

	# Cleanup.
	[ -f $XIGMANAS_WORKINGDIR/mfsroot.gz ] && rm -f $XIGMANAS_WORKINGDIR/mfsroot.gz
	[ -f $XIGMANAS_WORKINGDIR/mfsroot.uzip ] && rm -f $XIGMANAS_WORKINGDIR/mfsroot.uzip

	cd $XIGMANAS_WORKINGDIR
	pigz -${XIGMANAS_COMPLEVEL}kfnv $XIGMANAS_WORKINGDIR/mfsroot

	return 0
}

copy_kmod() {
	local kmodlist
	echo "Copy kmod to $XIGMANAS_TMPDIR/boot/kernel"
	kmodlist=$( (cd ${XIGMANAS_WORLD}/boot/kernel; find . -name '*.ko' | sed -e 's/\.\///') )
	for f in $kmodlist; do
		if grep -q "^${f}" $XIGMANAS_SVNDIR/build/xigmanas.kmod.exclude > /dev/null; then
			echo "skip: $f"
			continue;
		fi
		b=$(basename ${f})
		# (cd ${XIGMANAS_OBJDIRPREFIX}/usr/src/amd64.amd64/sys/${XIGMANAS_KERNCONF}/modules/usr/src/sys/modules; install -v -o root -g wheel -m 555 ${f} $XIGMANAS_TMPDIR/boot/kernel/${b}; pigz -${XIGMANAS_COMPLEVEL} $XIGMANAS_TMPDIR/boot/kernel/${b})
		(cd ${XIGMANAS_WORLD}/boot/kernel; install -v -o root -g wheel -m 555 ${f} $XIGMANAS_TMPDIR/boot/kernel/${b})
	done
	return 0;
}

create_image() {
	echo "-------------------------------------------------------------------------------";
	echo ">>> Generating ${XIGMANAS_PRODUCTNAME} image File (to be rawrite on CF/USB/HD/SSD)"
	echo "-------------------------------------------------------------------------------";

	# Check if rootfs (containing OS image) exists.
	if [ ! -d "$XIGMANAS_ROOTFS" ]; then
		echo "==> Error: ${XIGMANAS_ROOTFS} does not exist."
		return 1
	fi

	# Cleanup.
	[ -f ${XIGMANAS_WORKINGDIR}/image.bin ] && rm -f ${XIGMANAS_WORKINGDIR}/image.bin
	[ -f ${XIGMANAS_WORKINGDIR}/image.bin.xz ] && rm -f ${XIGMANAS_WORKINGDIR}/image.bin.xz

	# Set platform information.
	PLATFORM="${XIGMANAS_XARCH}-embedded"
	echo $PLATFORM > ${XIGMANAS_ROOTFS}/etc/platform

	# Set build time.
	date > ${XIGMANAS_ROOTFS}/etc/prd.version.buildtime
	date "+%s" > ${XIGMANAS_ROOTFS}/etc/prd.version.buildtimestamp

	# Set revision.
	echo ${XIGMANAS_REVISION} > ${XIGMANAS_ROOTFS}/etc/prd.revision

	IMGFILENAME="${XIGMANAS_PRODUCTNAME}-${PLATFORM}-${XIGMANAS_VERSION}.${XIGMANAS_REVISION}.img"

	echo "===> Generating tempory $XIGMANAS_TMPDIR folder"
	mkdir $XIGMANAS_TMPDIR
	create_mfsroot;

	echo "===> Creating Empty image File"
	dd if=/dev/zero of=${XIGMANAS_WORKINGDIR}/image.bin bs=512 seek=$(expr ${XIGMANAS_IMG_SIZE_SEC}) count=0
	echo "===> Use IMG as a memory disk"
	md=$(mdconfig -a -t vnode -f ${XIGMANAS_WORKINGDIR}/image.bin -x ${XIGMANAS_IMG_SECTS} -y ${XIGMANAS_IMG_HEADS})
	diskinfo -v ${md}

	IMGSIZEM=470

	# create 1MB aligned MBR image
	echo "===> Creating MBR partition on this memory disk"
	gpart create -s mbr ${md}
	gpart add -t freebsd ${md}
	gpart set -a active -i 1 ${md}
	gpart bootcode -b ${XIGMANAS_BOOTDIR}/mbr ${md}

	echo "===> Creating BSD partition on this memory disk"
	gpart create -s bsd ${md}s1
	gpart bootcode -b ${XIGMANAS_BOOTDIR}/boot ${md}s1
	gpart add -a 1m -s ${IMGSIZEM}m -t freebsd-ufs ${md}s1
	mdp=${md}s1a

	echo "===> Formatting this memory disk using UFS"
	newfs -S $XIGMANAS_IMGFMT_SECTOR -b $XIGMANAS_IMGFMT_BSIZE -f $XIGMANAS_IMGFMT_FSIZE -O2 -U -o space -m 0 -L "embboot" /dev/${mdp}
	echo "===> Mount this virtual disk on $XIGMANAS_TMPDIR"
	mount /dev/${mdp} $XIGMANAS_TMPDIR
	echo "===> Copying previously generated MFSROOT file to memory disk"
	cp $XIGMANAS_WORKINGDIR/mfsroot.gz $XIGMANAS_TMPDIR
	cp $XIGMANAS_WORKINGDIR/mdlocal.xz $XIGMANAS_TMPDIR
	echo "${XIGMANAS_PRODUCTNAME}-${PLATFORM}-${XIGMANAS_VERSION}.${XIGMANAS_REVISION}" > $XIGMANAS_TMPDIR/version

	echo "===> Copying Bootloader File(s) to memory disk"
	mkdir -p $XIGMANAS_TMPDIR/boot
	mkdir -p $XIGMANAS_TMPDIR/boot/dtb/overlays
	mkdir -p $XIGMANAS_TMPDIR/boot/images
	mkdir -p $XIGMANAS_TMPDIR/boot/kernel
	mkdir -p $XIGMANAS_TMPDIR/boot/lua
	mkdir -p $XIGMANAS_TMPDIR/boot/defaults
	mkdir -p $XIGMANAS_TMPDIR/boot/zfs
	mkdir -p $XIGMANAS_TMPDIR/conf
	mkdir -p $XIGMANAS_TMPDIR/boot/modules

	cp $XIGMANAS_ROOTFS/conf.default/config.xml $XIGMANAS_TMPDIR/conf
	cp $XIGMANAS_BOOTDIR/kernel/kernel.gz $XIGMANAS_TMPDIR/boot/kernel
	cp $XIGMANAS_BOOTDIR/entropy $XIGMANAS_TMPDIR/boot
	cp $XIGMANAS_BOOTDIR/lua/*.lua $XIGMANAS_TMPDIR/boot/lua
	cp $XIGMANAS_ROOTFS/boot/efi.4th $XIGMANAS_TMPDIR/boot
	cp $XIGMANAS_ROOTFS/boot/loader_4th $XIGMANAS_TMPDIR/boot
	cp $XIGMANAS_SVNDIR/boot/loader_4th.efi $XIGMANAS_TMPDIR/boot
	cp $XIGMANAS_ROOTFS/boot/loader_lua $XIGMANAS_TMPDIR/boot
	cp $XIGMANAS_SVNDIR/boot/loader_lua.efi $XIGMANAS_TMPDIR/boot
	cp $XIGMANAS_ROOTFS/boot/loader_simp $XIGMANAS_TMPDIR/boot
	cp $XIGMANAS_SVNDIR/boot/loader_simp.efi $XIGMANAS_TMPDIR/boot
	cp $XIGMANAS_ROOTFS/boot/userboot_4th.so $XIGMANAS_TMPDIR/boot
	cp $XIGMANAS_ROOTFS/boot/userboot_lua.so $XIGMANAS_TMPDIR/boot
	cp $XIGMANAS_BOOTDIR/loader $XIGMANAS_TMPDIR/boot
	cp $XIGMANAS_BOOTDIR/loader.conf $XIGMANAS_TMPDIR/boot
	cp $XIGMANAS_BOOTDIR/loader.rc $XIGMANAS_TMPDIR/boot
	cp $XIGMANAS_BOOTDIR/loader.4th $XIGMANAS_TMPDIR/boot
	cp $XIGMANAS_BOOTDIR/support.4th $XIGMANAS_TMPDIR/boot
	cp $XIGMANAS_BOOTDIR/defaults/loader.conf $XIGMANAS_TMPDIR/boot/defaults/
	cp $XIGMANAS_BOOTDIR/device.hints $XIGMANAS_TMPDIR/boot
	# cp $XIGMANAS_BOOTDIR/kernel/linker.hints $XIGMANAS_TMPDIR/boot/kernel/
	if [ 0 != $OPT_BOOTMENU ]; then
		cp $XIGMANAS_SVNDIR/boot/lua/drawer.lua $XIGMANAS_TMPDIR/boot/lua
		cp $XIGMANAS_SVNDIR/boot/lua/gfx-${XIGMANAS_PRODUCTNAME}.lua $XIGMANAS_TMPDIR/boot/lua
		cp $XIGMANAS_SVNDIR/boot/images/xigmanas-brand-rev.png $XIGMANAS_TMPDIR/boot/images
		cp $XIGMANAS_SVNDIR/boot/brand-${XIGMANAS_PRODUCTNAME}.4th $XIGMANAS_TMPDIR/boot
		cp $XIGMANAS_SVNDIR/boot/menu.4th $XIGMANAS_TMPDIR/boot
		cp $XIGMANAS_BOOTDIR/menu.rc $XIGMANAS_TMPDIR/boot
		cp $XIGMANAS_BOOTDIR/menusets.4th $XIGMANAS_TMPDIR/boot
		cp $XIGMANAS_BOOTDIR/beastie.4th $XIGMANAS_TMPDIR/boot
		# cp $XIGMANAS_ROOTFS/boot/loader.efi $XIGMANAS_TMPDIR/boot
		cp $XIGMANAS_SVNDIR/boot/loader.efi $XIGMANAS_TMPDIR/boot
		cp $XIGMANAS_SVNDIR/boot/efiboot.img $XIGMANAS_TMPDIR/boot
		cp $XIGMANAS_BOOTDIR/brand.4th $XIGMANAS_TMPDIR/boot
		cp $XIGMANAS_BOOTDIR/check-password.4th $XIGMANAS_TMPDIR/boot
		cp $XIGMANAS_BOOTDIR/color.4th $XIGMANAS_TMPDIR/boot
		cp $XIGMANAS_BOOTDIR/delay.4th $XIGMANAS_TMPDIR/boot
		cp $XIGMANAS_BOOTDIR/frames.4th $XIGMANAS_TMPDIR/boot
		cp $XIGMANAS_BOOTDIR/menu-commands.4th $XIGMANAS_TMPDIR/boot
		cp $XIGMANAS_BOOTDIR/screen.4th $XIGMANAS_TMPDIR/boot
		cp $XIGMANAS_BOOTDIR/shortcuts.4th $XIGMANAS_TMPDIR/boot
		cp $XIGMANAS_BOOTDIR/version.4th $XIGMANAS_TMPDIR/boot
	fi
	if [ 0 != $OPT_BOOTSPLASH ]; then
		cp $XIGMANAS_SVNDIR/boot/splash.bmp $XIGMANAS_TMPDIR/boot
		install -v -o root -g wheel -m 555 ${XIGMANAS_WORLD}/boot/kernel/splash_bmp.ko $XIGMANAS_TMPDIR/boot/kernel
	fi
	if [ "amd64" != ${XIGMANAS_ARCH} ]; then
		install -v -o root -g wheel -m 555 ${XIGMANAS_WORLD}/boot/kernel/apm.ko $XIGMANAS_TMPDIR/boot/kernel
	fi
	# iSCSI driver
	install -v -o root -g wheel -m 555 ${XIGMANAS_ROOTFS}/boot/kernel/isboot.ko $XIGMANAS_TMPDIR/boot/kernel
	# preload kernel drivers
	install -v -o root -g wheel -m 555 ${XIGMANAS_WORLD}/boot/kernel/opensolaris.ko $XIGMANAS_TMPDIR/boot/kernel
	install -v -o root -g wheel -m 555 ${XIGMANAS_WORLD}/boot/kernel/zfs.ko $XIGMANAS_TMPDIR/boot/kernel
	# Ignore error: /boot/modules/openzfs.ko: No such file or directory when build by make.sh.
	install -v -o root -g wheel -m 555 ${XIGMANAS_WORLD}/boot/modules/openzfs.ko $XIGMANAS_TMPDIR/boot/modules
	# copy kernel modules
	copy_kmod

	# Custom company brand(fallback).
	if [ -f ${XIGMANAS_SVNDIR}/boot/brand-${XIGMANAS_PRODUCTNAME}.4th ]; then
		echo "loader_brand=\"${XIGMANAS_PRODUCTNAME}\"" >> $XIGMANAS_TMPDIR/boot/loader.conf
	fi

	echo "===> Creating linker.hints"
	kldxref -R $XIGMANAS_TMPDIR/boot

	cd $XIGMANAS_WORKINGDIR
	echo "===> Unmount memory disk"
	umount $XIGMANAS_TMPDIR
	echo "===> Detach memory disk"
	mdconfig -d -u ${md}
	echo "===> Compress the IMG file"
	xz -${XIGMANAS_COMPLEVEL}v -T $XIGMANAS_XZ_THREADS -M $XIGMANAS_XZ_MEM_LIMIT $XIGMANAS_WORKINGDIR/image.bin
	cp $XIGMANAS_WORKINGDIR/image.bin.xz $XIGMANAS_ROOTDIR/${IMGFILENAME}.xz

	# Cleanup.
	[ -d $XIGMANAS_TMPDIR ] && rm -rf $XIGMANAS_TMPDIR
	[ -f $XIGMANAS_WORKINGDIR/mfsroot.gz ] && rm -f $XIGMANAS_WORKINGDIR/mfsroot.gz
	[ -f $XIGMANAS_WORKINGDIR/mfsroot.uzip ] && rm -f $XIGMANAS_WORKINGDIR/mfsroot.uzip
	# [ -f $XIGMANAS_WORKINGDIR/mdlocal.xz ] && rm -f $XIGMANAS_WORKINGDIR/mdlocal.xz
	[ -f $XIGMANAS_WORKINGDIR/image.bin ] && rm -f $XIGMANAS_WORKINGDIR/image.bin

	return 0
}

create_iso () {
	# Check if rootfs (containing OS image) exists.
	if [ ! -d "$XIGMANAS_ROOTFS" ]; then
		echo "==> Error: ${XIGMANAS_ROOTFS} does not exist!."
		return 1
	fi

	# Cleanup.
	[ -d $XIGMANAS_TMPDIR ] && rm -rf $XIGMANAS_TMPDIR
	[ -f $XIGMANAS_WORKINGDIR/mfsroot.gz ] && rm -f $XIGMANAS_WORKINGDIR/mfsroot.gz
	[ -f $XIGMANAS_WORKINGDIR/mfsroot.uzip ] && rm -f $XIGMANAS_WORKINGDIR/mfsroot.uzip
	[ -f $XIGMANAS_WORKINGDIR/mdlocal.xz ] && rm -f $XIGMANAS_WORKINGDIR/mdlocal.xz
	[ -f $XIGMANAS_WORKINGDIR/mdlocal.uzip ] && rm -f $XIGMANAS_WORKINGDIR/mdlocal.uzip
	[ -f $XIGMANAS_WORKINGDIR/mdlocal-mini.xz ] && rm -f $XIGMANAS_WORKINGDIR/mdlocal-mini.xz

	if [ ! $TINY_ISO ]; then
		LABEL="${XIGMANAS_PRODUCTNAME}-${XIGMANAS_XARCH}-LiveCD-${XIGMANAS_VERSION}.${XIGMANAS_REVISION}"
		VOLUMEID="${XIGMANAS_PRODUCTNAME}-${XIGMANAS_XARCH}-LiveCD-${XIGMANAS_VERSION}"
		echo "ISO: Generating the $XIGMANAS_PRODUCTNAME Image file:"
		create_image;
	else
		LABEL="${XIGMANAS_PRODUCTNAME}-${XIGMANAS_XARCH}-LiveCD-Tin-${XIGMANAS_VERSION}.${XIGMANAS_REVISION}"
		VOLUMEID="${XIGMANAS_PRODUCTNAME}-${XIGMANAS_XARCH}-LiveCD-Tin-${XIGMANAS_VERSION}"
	fi

	# Set Platform Information.
	PLATFORM="${XIGMANAS_XARCH}-liveCD"
	echo $PLATFORM > ${XIGMANAS_ROOTFS}/etc/platform

	# Set Revision.
	echo ${XIGMANAS_REVISION} > ${XIGMANAS_ROOTFS}/etc/prd.revision
	echo "ISO: Generating temporary folder '$XIGMANAS_TMPDIR'"
	mkdir $XIGMANAS_TMPDIR
	if [ $TINY_ISO ]; then
		# Do not call create_image if TINY_ISO
		create_mfsroot;
	elif [ -z "$FORCE_MFSROOT" -o "$FORCE_MFSROOT" != "0" ]; then
		# Mount mfsroot/mdlocal created by create_image
		md=$(mdconfig -a -t vnode -f $XIGMANAS_WORKINGDIR/mfsroot)
		mount /dev/${md} ${XIGMANAS_TMPDIR}
		# Update mfsroot/mdlocal
		echo $PLATFORM > ${XIGMANAS_TMPDIR}/etc/platform
		# Umount and update mfsroot/mdlocal
		umount $XIGMANAS_TMPDIR
		mdconfig -d -u ${md}
		update_mfsroot;
	else
		create_mfsroot;
	fi

	echo "ISO: Copying previously generated MFSROOT file to $XIGMANAS_TMPDIR"
	cp $XIGMANAS_WORKINGDIR/mfsroot.gz $XIGMANAS_TMPDIR
	cp $XIGMANAS_WORKINGDIR/mdlocal.xz $XIGMANAS_TMPDIR
	cp $XIGMANAS_WORKINGDIR/mdlocal-mini.xz $XIGMANAS_TMPDIR
	echo "${LABEL}" > $XIGMANAS_TMPDIR/version

	echo "ISO: Copying Bootloader file(s) to $XIGMANAS_TMPDIR"
	mkdir -p $XIGMANAS_TMPDIR/boot
	mkdir -p $XIGMANAS_TMPDIR/boot/dtb/overlays
	mkdir -p $XIGMANAS_TMPDIR/boot/images
	mkdir -p $XIGMANAS_TMPDIR/boot/kernel
	mkdir -p $XIGMANAS_TMPDIR/boot/lua
	mkdir -p $XIGMANAS_TMPDIR/boot/defaults
	mkdir -p $XIGMANAS_TMPDIR/boot/zfs
	mkdir -p $XIGMANAS_TMPDIR/boot/modules

	cp $XIGMANAS_BOOTDIR/lua/*.lua $XIGMANAS_TMPDIR/boot/lua
	cp $XIGMANAS_ROOTFS/boot/efi.4th $XIGMANAS_TMPDIR/boot
	cp $XIGMANAS_ROOTFS/boot/loader_4th $XIGMANAS_TMPDIR/boot
	cp $XIGMANAS_SVNDIR/boot/loader_4th.efi $XIGMANAS_TMPDIR/boot
	cp $XIGMANAS_ROOTFS/boot/loader_lua $XIGMANAS_TMPDIR/boot
	cp $XIGMANAS_SVNDIR/boot/loader_lua.efi $XIGMANAS_TMPDIR/boot
	cp $XIGMANAS_ROOTFS/boot/loader_simp $XIGMANAS_TMPDIR/boot
	cp $XIGMANAS_SVNDIR/boot/loader_simp.efi $XIGMANAS_TMPDIR/boot
	cp $XIGMANAS_ROOTFS/boot/userboot_4th.so $XIGMANAS_TMPDIR/boot
	cp $XIGMANAS_ROOTFS/boot/userboot_lua.so $XIGMANAS_TMPDIR/boot
	cp $XIGMANAS_BOOTDIR/entropy $XIGMANAS_TMPDIR/boot
	cp $XIGMANAS_BOOTDIR/kernel/kernel.gz $XIGMANAS_TMPDIR/boot/kernel
	cp $XIGMANAS_BOOTDIR/cdboot $XIGMANAS_TMPDIR/boot
	cp $XIGMANAS_BOOTDIR/loader $XIGMANAS_TMPDIR/boot
	cp $XIGMANAS_BOOTDIR/loader.conf $XIGMANAS_TMPDIR/boot
	cp $XIGMANAS_BOOTDIR/loader.rc $XIGMANAS_TMPDIR/boot
	cp $XIGMANAS_BOOTDIR/loader.4th $XIGMANAS_TMPDIR/boot
	cp $XIGMANAS_BOOTDIR/support.4th $XIGMANAS_TMPDIR/boot
	cp $XIGMANAS_BOOTDIR/defaults/loader.conf $XIGMANAS_TMPDIR/boot/defaults/
	cp $XIGMANAS_BOOTDIR/device.hints $XIGMANAS_TMPDIR/boot
	# cp $XIGMANAS_BOOTDIR/kernel/linker.hints $XIGMANAS_TMPDIR/boot/kernel/
	if [ 0 != $OPT_BOOTMENU ]; then
		cp $XIGMANAS_SVNDIR/boot/efiboot.img $XIGMANAS_TMPDIR/boot
		cp $XIGMANAS_SVNDIR/boot/lua/drawer.lua $XIGMANAS_TMPDIR/boot/lua
		cp $XIGMANAS_SVNDIR/boot/lua/gfx-${XIGMANAS_PRODUCTNAME}.lua $XIGMANAS_TMPDIR/boot/lua
		cp $XIGMANAS_SVNDIR/boot/images/xigmanas-brand-rev.png $XIGMANAS_TMPDIR/boot/images
		cp $XIGMANAS_SVNDIR/boot/brand-${XIGMANAS_PRODUCTNAME}.4th $XIGMANAS_TMPDIR/boot
		# cp $XIGMANAS_ROOTFS/boot/loader.efi $XIGMANAS_TMPDIR/boot
		cp $XIGMANAS_SVNDIR/boot/loader.efi $XIGMANAS_TMPDIR/boot
		cp $XIGMANAS_SVNDIR/boot/menu.4th $XIGMANAS_TMPDIR/boot
		cp $XIGMANAS_BOOTDIR/menu.rc $XIGMANAS_TMPDIR/boot
		cp $XIGMANAS_BOOTDIR/menusets.4th $XIGMANAS_TMPDIR/boot
		cp $XIGMANAS_ROOTFS/boot/beastie.4th $XIGMANAS_TMPDIR/boot
		cp $XIGMANAS_BOOTDIR/brand.4th $XIGMANAS_TMPDIR/boot
		cp $XIGMANAS_BOOTDIR/check-password.4th $XIGMANAS_TMPDIR/boot
		cp $XIGMANAS_BOOTDIR/color.4th $XIGMANAS_TMPDIR/boot
		cp $XIGMANAS_BOOTDIR/delay.4th $XIGMANAS_TMPDIR/boot
		cp $XIGMANAS_BOOTDIR/frames.4th $XIGMANAS_TMPDIR/boot
		cp $XIGMANAS_BOOTDIR/menu-commands.4th $XIGMANAS_TMPDIR/boot
		cp $XIGMANAS_BOOTDIR/screen.4th $XIGMANAS_TMPDIR/boot
		cp $XIGMANAS_BOOTDIR/shortcuts.4th $XIGMANAS_TMPDIR/boot
		cp $XIGMANAS_BOOTDIR/version.4th $XIGMANAS_TMPDIR/boot
	fi
	if [ 0 != $OPT_BOOTSPLASH ]; then
		cp $XIGMANAS_SVNDIR/boot/splash.bmp $XIGMANAS_TMPDIR/boot
		install -v -o root -g wheel -m 555 ${XIGMANAS_WORLD}/boot/kernel/splash_bmp.ko $XIGMANAS_TMPDIR/boot/kernel
	fi
	if [ "amd64" != ${XIGMANAS_ARCH} ]; then
		install -v -o root -g wheel -m 555 ${XIGMANAS_WORLD}/boot/kernel/apm.ko $XIGMANAS_TMPDIR/boot/kernel
	fi
	# iSCSI driver
	install -v -o root -g wheel -m 555 ${XIGMANAS_ROOTFS}/boot/kernel/isboot.ko $XIGMANAS_TMPDIR/boot/kernel
	# preload kernel drivers
	install -v -o root -g wheel -m 555 ${XIGMANAS_WORLD}/boot/kernel/opensolaris.ko $XIGMANAS_TMPDIR/boot/kernel
	install -v -o root -g wheel -m 555 ${XIGMANAS_WORLD}/boot/kernel/zfs.ko $XIGMANAS_TMPDIR/boot/kernel
	# Ignore error: /boot/modules/openzfs.ko: No such file or directory when build by make.sh.
	install -v -o root -g wheel -m 555 ${XIGMANAS_WORLD}/boot/modules/openzfs.ko $XIGMANAS_TMPDIR/boot/modules
	# copy kernel modules
	copy_kmod

	# Custom company brand(fallback).
	if [ -f ${XIGMANAS_SVNDIR}/boot/brand-${XIGMANAS_PRODUCTNAME}.4th ]; then
		echo "loader_brand=\"${XIGMANAS_PRODUCTNAME}\"" >> $XIGMANAS_TMPDIR/boot/loader.conf
	fi

	echo "ISO: Creating linker.hints"
	kldxref -R $XIGMANAS_TMPDIR/boot

	if [ ! $TINY_ISO ]; then
		echo "ISO: Copying image file to $XIGMANAS_TMPDIR"
		cp ${XIGMANAS_WORKINGDIR}/image.bin.xz ${XIGMANAS_TMPDIR}/${XIGMANAS_PRODUCTNAME}-${XIGMANAS_XARCH}-embedded.xz
	fi

	echo "ISO: Generating $XIGMANAS_PRODUCTNAME ISO File"
	if [ "${OPT_EFIBOOT_SUPPORT}" = 0 ]; then
		# Generate standard iso file.
		mkisofs -b "boot/cdboot" -no-emul-boot -r -J -A "${XIGMANAS_PRODUCTNAME} CD-ROM image" -publisher "${XIGMANAS_URL}" -V "${VOLUMEID}" -o "${XIGMANAS_ROOTDIR}/${LABEL}.iso" ${XIGMANAS_TMPDIR}
	else
		# Generate iso file with UEFI/BIOS boot support.
		mkisofs -b "boot/cdboot" -no-emul-boot -eltorito-alt-boot -b "boot/efiboot.img" -no-emul-boot -r -J -A "${XIGMANAS_PRODUCTNAME} CD-ROM image" -publisher "${XIGMANAS_URL}" -V "${VOLUMEID}" -o "${XIGMANAS_ROOTDIR}/${LABEL}.iso" ${XIGMANAS_TMPDIR}
	fi
	[ 0 != $? ] && return 1 # successful?

	create_checksum_file;

	# Cleanup.
	[ -d $XIGMANAS_TMPDIR ] && rm -rf $XIGMANAS_TMPDIR
	[ -f $XIGMANAS_WORKINGDIR/mfsroot ] && rm -f $XIGMANAS_WORKINGDIR/mfsroot
	[ -f $XIGMANAS_WORKINGDIR/mfsroot.gz ] && rm -f $XIGMANAS_WORKINGDIR/mfsroot.gz
	[ -f $XIGMANAS_WORKINGDIR/mfsroot.uzip ] && rm -f $XIGMANAS_WORKINGDIR/mfsroot.uzip
	[ -f $XIGMANAS_WORKINGDIR/mdlocal ] && rm -f $XIGMANAS_WORKINGDIR/mdlocal
	[ -f $XIGMANAS_WORKINGDIR/mdlocal.xz ] && rm -f $XIGMANAS_WORKINGDIR/mdlocal.xz
	[ -f $XIGMANAS_WORKINGDIR/mdlocal.uzip ] && rm -f $XIGMANAS_WORKINGDIR/mdlocal.uzip
	[ -f $XIGMANAS_WORKINGDIR/mdlocal-mini.xz ] && rm -f $XIGMANAS_WORKINGDIR/mdlocal-mini.xz
	[ -f $XIGMANAS_WORKINGDIR/image.bin.xz ] && rm -f $XIGMANAS_WORKINGDIR/image.bin.xz

	return 0
}

create_iso_tiny() {
	TINY_ISO=1
	create_iso;
	unset TINY_ISO

	return 0
}

create_embedded() {
	echo "Embedded: Start generating the $XIGMANAS_PRODUCTNAME Image file"
	create_image;
	create_checksum_file;

	# Cleanup.
	[ -d $XIGMANAS_TMPDIR ] && rm -rf $XIGMANAS_TMPDIR
	[ -f $XIGMANAS_WORKINGDIR/mfsroot ] && rm -f $XIGMANAS_WORKINGDIR/mfsroot
	[ -f $XIGMANAS_WORKINGDIR/mfsroot.gz ] && rm -f $XIGMANAS_WORKINGDIR/mfsroot.gz
	[ -f $XIGMANAS_WORKINGDIR/mfsroot.uzip ] && rm -f $XIGMANAS_WORKINGDIR/mfsroot.uzip
	[ -f $XIGMANAS_WORKINGDIR/mdlocal ] && rm -f $XIGMANAS_WORKINGDIR/mdlocal
	[ -f $XIGMANAS_WORKINGDIR/mdlocal.xz ] && rm -f $XIGMANAS_WORKINGDIR/mdlocal.xz
	[ -f $XIGMANAS_WORKINGDIR/mdlocal.uzip ] && rm -f $XIGMANAS_WORKINGDIR/mdlocal.uzip
	[ -f $XIGMANAS_WORKINGDIR/mdlocal-mini.xz ] && rm -f $XIGMANAS_WORKINGDIR/mdlocal-mini.xz
	[ -f $XIGMANAS_WORKINGDIR/image.bin.xz ] && rm -f $XIGMANAS_WORKINGDIR/image.bin.xz
	echo "Embedded: Finished generating the $XIGMANAS_PRODUCTNAME Image file"

	return 0
}

create_usb () {
	# Check if rootfs (contining OS image) exists.
	if [ ! -d "$XIGMANAS_ROOTFS" ]; then
		echo "==> Error: ${XIGMANAS_ROOTFS} does not exist!."
		return 1
	fi

	# Cleanup.
	[ -d $XIGMANAS_TMPDIR ] && rm -rf $XIGMANAS_TMPDIR
	[ -f ${XIGMANAS_WORKINGDIR}/image.bin ] && rm -f ${XIGMANAS_WORKINGDIR}/image.bin
	[ -f ${XIGMANAS_WORKINGDIR}/image.bin.xz ] && rm -f ${XIGMANAS_WORKINGDIR}/image.bin.xz
	[ -f ${XIGMANAS_WORKINGDIR}/mfsroot.gz ] && rm -f ${XIGMANAS_WORKINGDIR}/mfsroot.gz
	[ -f ${XIGMANAS_WORKINGDIR}/mfsroot.uzip ] && rm -f ${XIGMANAS_WORKINGDIR}/mfsroot.uzip
	[ -f ${XIGMANAS_WORKINGDIR}/mdlocal.xz ] && rm -f ${XIGMANAS_WORKINGDIR}/mdlocal.xz
	[ -f ${XIGMANAS_WORKINGDIR}/mdlocal.uzip ] && rm -f ${XIGMANAS_WORKINGDIR}/mdlocal.uzip
	[ -f ${XIGMANAS_WORKINGDIR}/mdlocal-mini.xz ] && rm -f ${XIGMANAS_WORKINGDIR}/mdlocal-mini.xz
	[ -f ${XIGMANAS_WORKINGDIR}/usb-image.bin ] && rm -f ${XIGMANAS_WORKINGDIR}/usb-image.bin
	[ -f ${XIGMANAS_WORKINGDIR}/usb-image.bin.gz ] && rm -f ${XIGMANAS_WORKINGDIR}/usb-image.bin.gz

	echo "USB: Start generating the $XIGMANAS_PRODUCTNAME Image file for MBR:"
	create_image;

	# Set Platform Informations.
	PLATFORM="${XIGMANAS_XARCH}-liveUSB"
	echo $PLATFORM > ${XIGMANAS_ROOTFS}/etc/platform

	# Set Revision.
	echo ${XIGMANAS_REVISION} > ${XIGMANAS_ROOTFS}/etc/prd.revision

	IMGFILENAME="${XIGMANAS_PRODUCTNAME}-${XIGMANAS_XARCH}-LiveUSB-MBR-${XIGMANAS_VERSION}.${XIGMANAS_REVISION}.img"

	echo "USB: Generating temporary folder '$XIGMANAS_TMPDIR'"
	mkdir $XIGMANAS_TMPDIR
	if [ -z "$FORCE_MFSROOT" -o "$FORCE_MFSROOT" != "0" ]; then
		# Mount mfsroot/mdlocal created by create_image
		md=$(mdconfig -a -t vnode -f $XIGMANAS_WORKINGDIR/mfsroot)
		mount /dev/${md} ${XIGMANAS_TMPDIR}
		# Update mfsroot/mdlocal
		echo $PLATFORM > ${XIGMANAS_TMPDIR}/etc/platform
		# Umount and update mfsroot/mdlocal
		umount $XIGMANAS_TMPDIR
		mdconfig -d -u ${md}
		update_mfsroot;
	else
		create_mfsroot;
	fi

	# for 1GB USB stick
	IMGSIZE=$(stat -f "%z" ${XIGMANAS_WORKINGDIR}/image.bin.xz)
	MFSSIZE=$(stat -f "%z" ${XIGMANAS_WORKINGDIR}/mfsroot.gz)
	MDLSIZE=$(stat -f "%z" ${XIGMANAS_WORKINGDIR}/mdlocal.xz)
	MDLSIZE2=$(stat -f "%z" ${XIGMANAS_WORKINGDIR}/mdlocal-mini.xz)
	IMGSIZEM=$(expr \( $IMGSIZE + $MFSSIZE + $MDLSIZE + $MDLSIZE2 - 1 + 1024 \* 1024 \) / 1024 / 1024)
	USBROOTM=776
	USB_SECTS=63
	USB_HEADS=255

	# 4MB alignment 800M image.
	USBSYSSIZEM=$(expr $USBROOTM + 4)
	USBIMGSIZEM=$(expr $USBSYSSIZEM + 28)

	# 4MB aligned USB stick
	echo "USB: Creating Empty IMG File"
	dd if=/dev/zero of=${XIGMANAS_WORKINGDIR}/usb-image.bin bs=1m seek=${USBIMGSIZEM} count=0
	echo "USB: Use IMG as a memory disk"
	md=$(mdconfig -a -t vnode -f ${XIGMANAS_WORKINGDIR}/usb-image.bin -x ${USB_SECTS} -y ${USB_HEADS})
	diskinfo -v ${md}

	echo "USB: Creating BSD partition on this memory disk"
	gpart create -s mbr ${md}
	gpart add -s ${USBSYSSIZEM}m -t freebsd ${md}
	gpart set -a active -i 1 ${md}
	gpart bootcode -b ${XIGMANAS_BOOTDIR}/mbr ${md}

	# s1 (UFS/SYSTEM)
	gpart create -s bsd ${md}s1
	gpart bootcode -b ${XIGMANAS_BOOTDIR}/boot ${md}s1
	gpart add -a 4m -s ${USBROOTM}m -t freebsd-ufs ${md}s1
	# SYSTEM partition
	mdp=${md}s1a

	echo "USB: Formatting this memory disk using UFS"
	newfs -S 4096 -b 32768 -f 4096 -O2 -U -j -o space -m 0 -L "liveboot" /dev/${mdp}

	echo "USB: Mount this virtual disk on $XIGMANAS_TMPDIR"
	mount /dev/${mdp} $XIGMANAS_TMPDIR

	echo "USB: Copying previously generated MFSROOT file to memory disk"
	cp $XIGMANAS_WORKINGDIR/mfsroot.gz $XIGMANAS_TMPDIR
	cp $XIGMANAS_WORKINGDIR/mdlocal.xz $XIGMANAS_TMPDIR
	cp $XIGMANAS_WORKINGDIR/mdlocal-mini.xz $XIGMANAS_TMPDIR
	echo "${XIGMANAS_PRODUCTNAME}-${XIGMANAS_XARCH}-LiveUSB-${XIGMANAS_VERSION}.${XIGMANAS_REVISION}" > $XIGMANAS_TMPDIR/version

	echo "USB: Copying Bootloader File(s) to memory disk"
	mkdir -p $XIGMANAS_TMPDIR/boot
	mkdir -p $XIGMANAS_TMPDIR/boot/dtb/overlays
	mkdir -p $XIGMANAS_TMPDIR/boot/images
	mkdir -p $XIGMANAS_TMPDIR/boot/kernel
	mkdir -p $XIGMANAS_TMPDIR/boot/lua
	mkdir -p $XIGMANAS_TMPDIR/boot/defaults
	mkdir -p $XIGMANAS_TMPDIR/boot/zfs
	mkdir -p $XIGMANAS_TMPDIR/conf

	cp $XIGMANAS_ROOTFS/conf.default/config.xml $XIGMANAS_TMPDIR/conf
	cp $XIGMANAS_BOOTDIR/kernel/kernel.gz $XIGMANAS_TMPDIR/boot/kernel
	cp $XIGMANAS_BOOTDIR/entropy $XIGMANAS_TMPDIR/boot
	cp $XIGMANAS_BOOTDIR/lua/*.lua $XIGMANAS_TMPDIR/boot/lua
	cp $XIGMANAS_ROOTFS/boot/efi.4th $XIGMANAS_TMPDIR/boot
	cp $XIGMANAS_ROOTFS/boot/loader_4th $XIGMANAS_TMPDIR/boot
	cp $XIGMANAS_SVNDIR/boot/loader_4th.efi $XIGMANAS_TMPDIR/boot
	cp $XIGMANAS_ROOTFS/boot/loader_lua $XIGMANAS_TMPDIR/boot
	cp $XIGMANAS_SVNDIR/boot/loader_lua.efi $XIGMANAS_TMPDIR/boot
	cp $XIGMANAS_ROOTFS/boot/loader_simp $XIGMANAS_TMPDIR/boot
	cp $XIGMANAS_SVNDIR/boot/loader_simp.efi $XIGMANAS_TMPDIR/boot
	cp $XIGMANAS_ROOTFS/boot/userboot_4th.so $XIGMANAS_TMPDIR/boot
	cp $XIGMANAS_ROOTFS/boot/userboot_lua.so $XIGMANAS_TMPDIR/boot
	cp $XIGMANAS_BOOTDIR/loader $XIGMANAS_TMPDIR/boot
	cp $XIGMANAS_BOOTDIR/loader.conf $XIGMANAS_TMPDIR/boot
	cp $XIGMANAS_BOOTDIR/loader.rc $XIGMANAS_TMPDIR/boot
	cp $XIGMANAS_BOOTDIR/loader.4th $XIGMANAS_TMPDIR/boot
	cp $XIGMANAS_BOOTDIR/support.4th $XIGMANAS_TMPDIR/boot
	cp $XIGMANAS_BOOTDIR/defaults/loader.conf $XIGMANAS_TMPDIR/boot/defaults/
	cp $XIGMANAS_BOOTDIR/device.hints $XIGMANAS_TMPDIR/boot
	# cp $XIGMANAS_BOOTDIR/kernel/linker.hints $XIGMANAS_TMPDIR/boot/kernel/
	if [ 0 != $OPT_BOOTMENU ]; then
		cp $XIGMANAS_SVNDIR/boot/lua/drawer.lua $XIGMANAS_TMPDIR/boot/lua
		cp $XIGMANAS_SVNDIR/boot/lua/gfx-${XIGMANAS_PRODUCTNAME}.lua $XIGMANAS_TMPDIR/boot/lua
		cp $XIGMANAS_SVNDIR/boot/images/xigmanas-brand-rev.png $XIGMANAS_TMPDIR/boot/images
		cp $XIGMANAS_SVNDIR/boot/brand-${XIGMANAS_PRODUCTNAME}.4th $XIGMANAS_TMPDIR/boot
		cp $XIGMANAS_SVNDIR/boot/menu.4th $XIGMANAS_TMPDIR/boot
		cp $XIGMANAS_BOOTDIR/menu.rc $XIGMANAS_TMPDIR/boot
		cp $XIGMANAS_BOOTDIR/menusets.4th $XIGMANAS_TMPDIR/boot
		cp $XIGMANAS_BOOTDIR/beastie.4th $XIGMANAS_TMPDIR/boot
		# cp $XIGMANAS_ROOTFS/boot/loader.efi $XIGMANAS_TMPDIR/boot
		cp $XIGMANAS_SVNDIR/boot/loader.efi $XIGMANAS_TMPDIR/boot
		cp $XIGMANAS_SVNDIR/boot/efiboot.img $XIGMANAS_TMPDIR/boot
		cp $XIGMANAS_BOOTDIR/brand.4th $XIGMANAS_TMPDIR/boot
		cp $XIGMANAS_BOOTDIR/check-password.4th $XIGMANAS_TMPDIR/boot
		cp $XIGMANAS_BOOTDIR/color.4th $XIGMANAS_TMPDIR/boot
		cp $XIGMANAS_BOOTDIR/delay.4th $XIGMANAS_TMPDIR/boot
		cp $XIGMANAS_BOOTDIR/frames.4th $XIGMANAS_TMPDIR/boot
		cp $XIGMANAS_BOOTDIR/menu-commands.4th $XIGMANAS_TMPDIR/boot
		cp $XIGMANAS_BOOTDIR/screen.4th $XIGMANAS_TMPDIR/boot
		cp $XIGMANAS_BOOTDIR/shortcuts.4th $XIGMANAS_TMPDIR/boot
		cp $XIGMANAS_BOOTDIR/version.4th $XIGMANAS_TMPDIR/boot
	fi
	if [ 0 != $OPT_BOOTSPLASH ]; then
		cp $XIGMANAS_SVNDIR/boot/splash.bmp $XIGMANAS_TMPDIR/boot
		install -v -o root -g wheel -m 555 ${XIGMANAS_WORLD}/boot/kernel/splash_bmp.ko $XIGMANAS_TMPDIR/boot/kernel
	fi
	if [ "amd64" != ${XIGMANAS_ARCH} ]; then
		install -v -o root -g wheel -m 555 ${XIGMANAS_WORLD}/boot/kernel/apm.ko $XIGMANAS_TMPDIR/boot/kernel
	fi
	# iSCSI driver
	install -v -o root -g wheel -m 555 ${XIGMANAS_ROOTFS}/boot/kernel/isboot.ko $XIGMANAS_TMPDIR/boot/kernel
	# preload kernel drivers
	install -v -o root -g wheel -m 555 ${XIGMANAS_WORLD}/boot/kernel/opensolaris.ko $XIGMANAS_TMPDIR/boot/kernel
	install -v -o root -g wheel -m 555 ${XIGMANAS_WORLD}/boot/kernel/zfs.ko $XIGMANAS_TMPDIR/boot/kernel
	# copy kernel modules
	copy_kmod

	# Custom company brand(fallback).
	if [ -f ${XIGMANAS_SVNDIR}/boot/brand-${XIGMANAS_PRODUCTNAME}.4th ]; then
		echo "loader_brand=\"${XIGMANAS_PRODUCTNAME}\"" >> $XIGMANAS_TMPDIR/boot/loader.conf
	fi

	echo "USB: Creating linker.hints"
	kldxref -R $XIGMANAS_TMPDIR/boot

	echo "USB: Copying IMG file to $XIGMANAS_TMPDIR"
	cp ${XIGMANAS_WORKINGDIR}/image.bin.xz ${XIGMANAS_TMPDIR}/${XIGMANAS_PRODUCTNAME}-${XIGMANAS_XARCH}-embedded.xz

	echo "USB: Unmount memory disk"
	umount $XIGMANAS_TMPDIR
	echo "USB: Detach memory disk"
	mdconfig -d -u ${md}
	cp $XIGMANAS_WORKINGDIR/usb-image.bin $XIGMANAS_ROOTDIR/$IMGFILENAME
	echo "Compress LiveUSB.img to LiveUSB.img.gz"
	pigz -${XIGMANAS_COMPLEVEL}n $XIGMANAS_ROOTDIR/$IMGFILENAME

	create_checksum_file;

	# Cleanup.
	[ -d $XIGMANAS_TMPDIR ] && rm -rf $XIGMANAS_TMPDIR
	[ -f $XIGMANAS_WORKINGDIR/mfsroot ] && rm -f $XIGMANAS_WORKINGDIR/mfsroot
	[ -f $XIGMANAS_WORKINGDIR/mfsroot.gz ] && rm -f $XIGMANAS_WORKINGDIR/mfsroot.gz
	[ -f $XIGMANAS_WORKINGDIR/mfsroot.uzip ] && rm -f $XIGMANAS_WORKINGDIR/mfsroot.uzip
	[ -f $XIGMANAS_WORKINGDIR/mdlocal ] && rm -f $XIGMANAS_WORKINGDIR/mdlocal
	[ -f $XIGMANAS_WORKINGDIR/mdlocal.xz ] && rm -f $XIGMANAS_WORKINGDIR/mdlocal.xz
	[ -f $XIGMANAS_WORKINGDIR/mdlocal.uzip ] && rm -f $XIGMANAS_WORKINGDIR/mdlocal.uzip
	[ -f $XIGMANAS_WORKINGDIR/mdlocal-mini.xz ] && rm -f $XIGMANAS_WORKINGDIR/mdlocal-mini.xz
	[ -f $XIGMANAS_WORKINGDIR/image.bin.xz ] && rm -f $XIGMANAS_WORKINGDIR/image.bin.xz
	[ -f $XIGMANAS_WORKINGDIR/usb-image.bin ] && rm -f $XIGMANAS_WORKINGDIR/usb-image.bin

	return 0
}

create_usb_gpt() {
	# Check if rootfs (contining OS image) exists.
	if [ ! -d "$XIGMANAS_ROOTFS" ]; then
		echo "==> Error: ${XIGMANAS_ROOTFS} does not exist!."
		return 1
	fi

	# Cleanup.
	[ -d $XIGMANAS_TMPDIR ] && rm -rf $XIGMANAS_TMPDIR
	[ -f ${XIGMANAS_WORKINGDIR}/image.bin ] && rm -f ${XIGMANAS_WORKINGDIR}/image.bin
	[ -f ${XIGMANAS_WORKINGDIR}/image.bin.xz ] && rm -f ${XIGMANAS_WORKINGDIR}/image.bin.xz
	[ -f ${XIGMANAS_WORKINGDIR}/mfsroot.gz ] && rm -f ${XIGMANAS_WORKINGDIR}/mfsroot.gz
	[ -f ${XIGMANAS_WORKINGDIR}/mfsroot.uzip ] && rm -f ${XIGMANAS_WORKINGDIR}/mfsroot.uzip
	[ -f ${XIGMANAS_WORKINGDIR}/mdlocal.xz ] && rm -f ${XIGMANAS_WORKINGDIR}/mdlocal.xz
	[ -f ${XIGMANAS_WORKINGDIR}/mdlocal.uzip ] && rm -f ${XIGMANAS_WORKINGDIR}/mdlocal.uzip
	[ -f ${XIGMANAS_WORKINGDIR}/mdlocal-mini.xz ] && rm -f ${XIGMANAS_WORKINGDIR}/mdlocal-mini.xz
	[ -f ${XIGMANAS_WORKINGDIR}/usb-image.bin ] && rm -f ${XIGMANAS_WORKINGDIR}/usb-image.bin
	[ -f ${XIGMANAS_WORKINGDIR}/usb-image.bin.gz ] && rm -f ${XIGMANAS_WORKINGDIR}/usb-image.bin.gz

	echo "USB: Generating the $XIGMANAS_PRODUCTNAME Image file for GPT:"
	create_image;

	# Set Platform Informations.
	PLATFORM="${XIGMANAS_XARCH}-liveUSB"
	echo $PLATFORM > ${XIGMANAS_ROOTFS}/etc/platform

	# Set Revision.
	echo ${XIGMANAS_REVISION} > ${XIGMANAS_ROOTFS}/etc/prd.revision

	IMGFILENAME="${XIGMANAS_PRODUCTNAME}-${XIGMANAS_XARCH}-LiveUSB-GPT-${XIGMANAS_VERSION}.${XIGMANAS_REVISION}.img"

	echo "USB: Generating temporary folder '$XIGMANAS_TMPDIR'"
	mkdir $XIGMANAS_TMPDIR
	if [ -z "$FORCE_MFSROOT" -o "$FORCE_MFSROOT" != "0" ]; then
		# Mount mfsroot/mdlocal created by create_image.
		md=$(mdconfig -a -t vnode -f $XIGMANAS_WORKINGDIR/mfsroot)
		mount /dev/${md} ${XIGMANAS_TMPDIR}
		# Update mfsroot/mdlocal.
		echo $PLATFORM > ${XIGMANAS_TMPDIR}/etc/platform
		# Umount and update mfsroot/mdlocal.
		umount $XIGMANAS_TMPDIR
		mdconfig -d -u ${md}
		update_mfsroot;
	else
		create_mfsroot;
	fi

	# For 1GB USB stick.
	IMGSIZE=$(stat -f "%z" ${XIGMANAS_WORKINGDIR}/image.bin.xz)
	MFSSIZE=$(stat -f "%z" ${XIGMANAS_WORKINGDIR}/mfsroot.gz)
	MDLSIZE=$(stat -f "%z" ${XIGMANAS_WORKINGDIR}/mdlocal.xz)
	MDLSIZE2=$(stat -f "%z" ${XIGMANAS_WORKINGDIR}/mdlocal-mini.xz)
	IMGSIZEM=$(expr \( $IMGSIZE + $MFSSIZE + $MDLSIZE + $MDLSIZE2 - 1 + 1024 \* 1024 \) / 1024 / 1024)
	UEFISIZE=200
	BOOTSIZE=512
	USBROOTM=768
	USB_SECTS=63
	USB_HEADS=255
	EFI_MOUNT="/tmp/install_efi"

	# 4MB alignment, 800M image.
	USBEFISIZEM=$(expr $UEFISIZE + 4)
	USBROOTSIZEM=$(expr $USBROOTM + 4)
	USBIMGSIZEM=$(expr $USBEFISIZEM + $USBROOTSIZEM + 8)

	# GPT labels.
	UEFILABEL="usbefiboot"
	BOOTLABEL="usbgptboot"
	ROOTLABEL="usbsysdisk"

	# 4MB aligned USB stick.
	echo "USB: Creating Empty IMG File"
	dd if=/dev/zero of=${XIGMANAS_WORKINGDIR}/usb-image.bin bs=1m seek=${USBIMGSIZEM} count=0
	echo "USB: Use IMG as a memory disk"
	md=$(mdconfig -a -t vnode -f ${XIGMANAS_WORKINGDIR}/usb-image.bin -x ${USB_SECTS} -y ${USB_HEADS})
	diskinfo -v /dev/${md}

	echo "USB: Creating GPT partition on this memory disk"
	gpart create -s gpt /dev/${md}

	# Add P1 for UEFI.
	gpart add -a 4k -s ${UEFISIZE}m -t efi -l ${UEFILABEL} /dev/${md}
	# Add P2 for GPTBOOT.
	gpart add -a 4k -s ${BOOTSIZE}k -t freebsd-boot -l ${BOOTLABEL} /dev/${md}
	# Add P3 for UFS/SYSTEM.
	gpart add -a 4m -s ${USBROOTM}m -t freebsd-ufs -l ${ROOTLABEL} /dev/${md}

	# Write boot code.
	echo "USB: Writing boot code on this memory disk"

	if ! newfs_msdos -F 16 -L "EFISYS" /dev/${md}p1 > /dev/null 2>&1; then
		echo "Failed to create new filesystem on /dev/${md}p1"
	else
		echo "Creating EFI system partition..."
		mkdir -p ${EFI_MOUNT}/esp
		mount -t msdosfs /dev/${md}p1 ${EFI_MOUNT}/esp
		mkdir -p ${EFI_MOUNT}/esp/efi/boot
		cp /boot/loader.efi ${EFI_MOUNT}/esp/efi/boot/BOOTx64.efi
		echo "BOOTx64.efi" > ${EFI_MOUNT}/esp/efi/boot/startup.nsh
		mkdir -p ${EFI_MOUNT}/esp/efi/freebsd
		cp /boot/loader.efi ${EFI_MOUNT}/esp/efi/freebsd/loader.efi
		umount ${EFI_MOUNT}/esp
		rm -r ${EFI_MOUNT}
	fi

	echo "Writing bootcode..."
	gpart bootcode -b /boot/pmbr -p /boot/gptboot -i 2 /dev/${md}

	# SYSTEM partition.
	mdp=${md}p3

	echo "USB: Formatting this memory disk using UFS"
	newfs -S 4096 -b 32768 -f 4096 -O2 -U -j -o space -m 0 -L "liveboot" /dev/${mdp}

	echo "USB: Mount this virtual disk on $XIGMANAS_TMPDIR"
	mount /dev/${mdp} $XIGMANAS_TMPDIR

	echo "USB: Copying previously generated MFSROOT file to memory disk"
	cp $XIGMANAS_WORKINGDIR/mfsroot.gz $XIGMANAS_TMPDIR
	cp $XIGMANAS_WORKINGDIR/mdlocal.xz $XIGMANAS_TMPDIR
	cp $XIGMANAS_WORKINGDIR/mdlocal-mini.xz $XIGMANAS_TMPDIR
	echo "${XIGMANAS_PRODUCTNAME}-${XIGMANAS_XARCH}-LiveUSB-${XIGMANAS_VERSION}.${XIGMANAS_REVISION}" > $XIGMANAS_TMPDIR/version

	echo "USB: Copying Bootloader File(s) to memory disk"
	mkdir -p $XIGMANAS_TMPDIR/boot
	mkdir -p $XIGMANAS_TMPDIR/boot/dtb/overlays
	mkdir -p $XIGMANAS_TMPDIR/boot/images
	mkdir -p $XIGMANAS_TMPDIR/boot/kernel
	mkdir -p $XIGMANAS_TMPDIR/boot/lua
	mkdir -p $XIGMANAS_TMPDIR/boot/defaults
	mkdir -p $XIGMANAS_TMPDIR/boot/zfs
	mkdir -p $XIGMANAS_TMPDIR/conf

	cp $XIGMANAS_ROOTFS/conf.default/config.xml $XIGMANAS_TMPDIR/conf
	cp $XIGMANAS_BOOTDIR/kernel/kernel.gz $XIGMANAS_TMPDIR/boot/kernel
	cp $XIGMANAS_BOOTDIR/lua/*.lua $XIGMANAS_TMPDIR/boot/lua
	cp $XIGMANAS_ROOTFS/boot/efi.4th $XIGMANAS_TMPDIR/boot
	cp $XIGMANAS_ROOTFS/boot/loader_4th $XIGMANAS_TMPDIR/boot
	cp $XIGMANAS_SVNDIR/boot/loader_4th.efi $XIGMANAS_TMPDIR/boot
	cp $XIGMANAS_ROOTFS/boot/loader_lua $XIGMANAS_TMPDIR/boot
	cp $XIGMANAS_SVNDIR/boot/loader_lua.efi $XIGMANAS_TMPDIR/boot
	cp $XIGMANAS_ROOTFS/boot/loader_simp $XIGMANAS_TMPDIR/boot
	cp $XIGMANAS_SVNDIR/boot/loader_simp.efi $XIGMANAS_TMPDIR/boot
	cp $XIGMANAS_ROOTFS/boot/userboot_4th.so $XIGMANAS_TMPDIR/boot
	cp $XIGMANAS_ROOTFS/boot/userboot_lua.so $XIGMANAS_TMPDIR/boot
	cp $XIGMANAS_BOOTDIR/entropy $XIGMANAS_TMPDIR/boot
	cp $XIGMANAS_BOOTDIR/loader $XIGMANAS_TMPDIR/boot
	cp $XIGMANAS_BOOTDIR/loader.conf $XIGMANAS_TMPDIR/boot
	cp $XIGMANAS_BOOTDIR/loader.rc $XIGMANAS_TMPDIR/boot
	cp $XIGMANAS_BOOTDIR/loader.4th $XIGMANAS_TMPDIR/boot
	cp $XIGMANAS_BOOTDIR/support.4th $XIGMANAS_TMPDIR/boot
	cp $XIGMANAS_BOOTDIR/defaults/loader.conf $XIGMANAS_TMPDIR/boot/defaults/
	cp $XIGMANAS_BOOTDIR/device.hints $XIGMANAS_TMPDIR/boot
	# cp $XIGMANAS_BOOTDIR/kernel/linker.hints $XIGMANAS_TMPDIR/boot/kernel/
	if [ 0 != $OPT_BOOTMENU ]; then
		cp $XIGMANAS_SVNDIR/boot/lua/drawer.lua $XIGMANAS_TMPDIR/boot/lua
		cp $XIGMANAS_SVNDIR/boot/lua/gfx-${XIGMANAS_PRODUCTNAME}.lua $XIGMANAS_TMPDIR/boot/lua
		cp $XIGMANAS_SVNDIR/boot/images/xigmanas-brand-rev.png $XIGMANAS_TMPDIR/boot/images
		cp $XIGMANAS_SVNDIR/boot/brand-${XIGMANAS_PRODUCTNAME}.4th $XIGMANAS_TMPDIR/boot
		cp $XIGMANAS_SVNDIR/boot/menu.4th $XIGMANAS_TMPDIR/boot
		cp $XIGMANAS_BOOTDIR/menu.rc $XIGMANAS_TMPDIR/boot
		cp $XIGMANAS_BOOTDIR/menusets.4th $XIGMANAS_TMPDIR/boot
		cp $XIGMANAS_BOOTDIR/beastie.4th $XIGMANAS_TMPDIR/boot
		# cp $XIGMANAS_ROOTFS/boot/loader.efi $XIGMANAS_TMPDIR/boot
		cp $XIGMANAS_SVNDIR/boot/loader.efi $XIGMANAS_TMPDIR/boot
		cp $XIGMANAS_SVNDIR/boot/efiboot.img $XIGMANAS_TMPDIR/boot
		cp $XIGMANAS_BOOTDIR/brand.4th $XIGMANAS_TMPDIR/boot
		cp $XIGMANAS_BOOTDIR/check-password.4th $XIGMANAS_TMPDIR/boot
		cp $XIGMANAS_BOOTDIR/color.4th $XIGMANAS_TMPDIR/boot
		cp $XIGMANAS_BOOTDIR/delay.4th $XIGMANAS_TMPDIR/boot
		cp $XIGMANAS_BOOTDIR/frames.4th $XIGMANAS_TMPDIR/boot
		cp $XIGMANAS_BOOTDIR/menu-commands.4th $XIGMANAS_TMPDIR/boot
		cp $XIGMANAS_BOOTDIR/screen.4th $XIGMANAS_TMPDIR/boot
		cp $XIGMANAS_BOOTDIR/shortcuts.4th $XIGMANAS_TMPDIR/boot
		cp $XIGMANAS_BOOTDIR/version.4th $XIGMANAS_TMPDIR/boot
	fi
	if [ 0 != $OPT_BOOTSPLASH ]; then
		cp $XIGMANAS_SVNDIR/boot/splash.bmp $XIGMANAS_TMPDIR/boot
		install -v -o root -g wheel -m 555 ${XIGMANAS_WORLD}/boot/kernel/splash_bmp.ko $XIGMANAS_TMPDIR/boot/kernel
	fi
	if [ "amd64" != ${XIGMANAS_ARCH} ]; then
		install -v -o root -g wheel -m 555 ${XIGMANAS_WORLD}/boot/kernel/apm.ko $XIGMANAS_TMPDIR/boot/kernel
	fi
	# iSCSI driver.
	install -v -o root -g wheel -m 555 ${XIGMANAS_ROOTFS}/boot/kernel/isboot.ko $XIGMANAS_TMPDIR/boot/kernel
	# Preload kernel drivers.
	install -v -o root -g wheel -m 555 ${XIGMANAS_WORLD}/boot/kernel/opensolaris.ko $XIGMANAS_TMPDIR/boot/kernel
	install -v -o root -g wheel -m 555 ${XIGMANAS_WORLD}/boot/kernel/zfs.ko $XIGMANAS_TMPDIR/boot/kernel
	# Copy kernel modules.
	copy_kmod

	# Custom company brand(fallback).
	if [ -f ${XIGMANAS_SVNDIR}/boot/brand-${XIGMANAS_PRODUCTNAME}.4th ]; then
		echo "loader_brand=\"${XIGMANAS_PRODUCTNAME}\"" >> $XIGMANAS_TMPDIR/boot/loader.conf
	fi

	echo "USB: Creating linker.hints"
	kldxref -R $XIGMANAS_TMPDIR/boot

	echo "USB: Copying IMG file to $XIGMANAS_TMPDIR"
	cp ${XIGMANAS_WORKINGDIR}/image.bin.xz ${XIGMANAS_TMPDIR}/${XIGMANAS_PRODUCTNAME}-${XIGMANAS_XARCH}-embedded.xz

	echo "USB: Unmount memory disk"
	umount $XIGMANAS_TMPDIR
	echo "USB: Detach memory disk"
	mdconfig -d -u ${md}
	cp $XIGMANAS_WORKINGDIR/usb-image.bin $XIGMANAS_ROOTDIR/$IMGFILENAME
	echo "Compress LiveUSB.img to LiveUSB.img.gz"
	pigz -${XIGMANAS_COMPLEVEL}n $XIGMANAS_ROOTDIR/$IMGFILENAME

	create_checksum_file;

	# Cleanup.
	[ -d $XIGMANAS_TMPDIR ] && rm -rf $XIGMANAS_TMPDIR
	[ -f $XIGMANAS_WORKINGDIR/mfsroot ] && rm -f $XIGMANAS_WORKINGDIR/mfsroot
	[ -f $XIGMANAS_WORKINGDIR/mfsroot.gz ] && rm -f $XIGMANAS_WORKINGDIR/mfsroot.gz
	[ -f $XIGMANAS_WORKINGDIR/mfsroot.uzip ] && rm -f $XIGMANAS_WORKINGDIR/mfsroot.uzip
	[ -f $XIGMANAS_WORKINGDIR/mdlocal ] && rm -f $XIGMANAS_WORKINGDIR/mdlocal
	[ -f $XIGMANAS_WORKINGDIR/mdlocal.xz ] && rm -f $XIGMANAS_WORKINGDIR/mdlocal.xz
	[ -f $XIGMANAS_WORKINGDIR/mdlocal.uzip ] && rm -f $XIGMANAS_WORKINGDIR/mdlocal.uzip
	[ -f $XIGMANAS_WORKINGDIR/mdlocal-mini.xz ] && rm -f $XIGMANAS_WORKINGDIR/mdlocal-mini.xz
	[ -f $XIGMANAS_WORKINGDIR/image.bin.xz ] && rm -f $XIGMANAS_WORKINGDIR/image.bin.xz
	[ -f $XIGMANAS_WORKINGDIR/usb-image.bin ] && rm -f $XIGMANAS_WORKINGDIR/usb-image.bin

	return 0
}

create_full() {
	[ -d $XIGMANAS_SVNDIR ] && use_svn ;

	# Set archive format tgz or txz
	EXTENSION="txz"

	echo "FULL: Start generating the $XIGMANAS_PRODUCTNAME ${EXTENSION} update file"

	# Set platform information.
	PLATFORM="${XIGMANAS_XARCH}-full"
	echo $PLATFORM > ${XIGMANAS_ROOTFS}/etc/platform

	# Set Revision.
	echo ${XIGMANAS_REVISION} > ${XIGMANAS_ROOTFS}/etc/prd.revision

	FULLFILENAME="${XIGMANAS_PRODUCTNAME}-${PLATFORM}-${XIGMANAS_VERSION}.${XIGMANAS_REVISION}.${EXTENSION}"

	echo "FULL: Generating tempory $XIGMANAS_TMPDIR folder"
	# Clean TMP dir:
	[ -d $XIGMANAS_TMPDIR ] && rm -rf $XIGMANAS_TMPDIR
	mkdir $XIGMANAS_TMPDIR

	# Copying all XigmaNAS® rootfilesystem (including symlink) on this folder
	cd $XIGMANAS_TMPDIR
	tar -cf - -C $XIGMANAS_ROOTFS ./ | tar -xvpf -
	echo "${XIGMANAS_PRODUCTNAME}-${PLATFORM}-${XIGMANAS_VERSION}.${XIGMANAS_REVISION}" > $XIGMANAS_TMPDIR/version

	echo "Copying bootloader file(s) to root filesystem"
	mkdir -p $XIGMANAS_TMPDIR/boot
	mkdir -p $XIGMANAS_TMPDIR/boot/dtb/overlays
	mkdir -p $XIGMANAS_TMPDIR/boot/images
	mkdir -p $XIGMANAS_TMPDIR/boot/kernel
	mkdir -p $XIGMANAS_TMPDIR/boot/lua
	mkdir -p $XIGMANAS_TMPDIR/boot/defaults
	mkdir -p $XIGMANAS_TMPDIR/boot/zfs

	# mkdir $XIGMANAS_TMPDIR/conf
	cp $XIGMANAS_ROOTFS/conf.default/config.xml $XIGMANAS_TMPDIR/conf
	cp $XIGMANAS_BOOTDIR/lua/*.lua $XIGMANAS_TMPDIR/boot/lua
	cp $XIGMANAS_ROOTFS/boot/efi.4th $XIGMANAS_TMPDIR/boot
	cp $XIGMANAS_ROOTFS/boot/loader_4th $XIGMANAS_TMPDIR/boot
	cp $XIGMANAS_SVNDIR/boot/loader_4th.efi $XIGMANAS_TMPDIR/boot
	cp $XIGMANAS_ROOTFS/boot/loader_lua $XIGMANAS_TMPDIR/boot
	cp $XIGMANAS_SVNDIR/boot/loader_lua.efi $XIGMANAS_TMPDIR/boot
	cp $XIGMANAS_ROOTFS/boot/loader_simp $XIGMANAS_TMPDIR/boot
	cp $XIGMANAS_SVNDIR/boot/loader_simp.efi $XIGMANAS_TMPDIR/boot
	cp $XIGMANAS_ROOTFS/boot/userboot_4th.so $XIGMANAS_TMPDIR/boot
	cp $XIGMANAS_ROOTFS/boot/userboot_lua.so $XIGMANAS_TMPDIR/boot
	cp $XIGMANAS_BOOTDIR/kernel/kernel.gz $XIGMANAS_TMPDIR/boot/kernel
	cp $XIGMANAS_BOOTDIR/entropy $XIGMANAS_TMPDIR/boot
	unpigz $XIGMANAS_TMPDIR/boot/kernel/kernel.gz
	cp $XIGMANAS_BOOTDIR/loader $XIGMANAS_TMPDIR/boot
	cp $XIGMANAS_BOOTDIR/loader.rc $XIGMANAS_TMPDIR/boot
	cp $XIGMANAS_BOOTDIR/loader.4th $XIGMANAS_TMPDIR/boot
	cp $XIGMANAS_BOOTDIR/support.4th $XIGMANAS_TMPDIR/boot
	cp $XIGMANAS_BOOTDIR/defaults/loader.conf $XIGMANAS_TMPDIR/boot/defaults/
	cp $XIGMANAS_BOOTDIR/device.hints $XIGMANAS_TMPDIR/boot
	# cp $XIGMANAS_BOOTDIR/kernel/linker.hints $XIGMANAS_TMPDIR/boot/kernel/
	if [ 0 != $OPT_BOOTMENU ]; then
		cp $XIGMANAS_SVNDIR/boot/lua/drawer.lua $XIGMANAS_TMPDIR/boot/lua
		cp $XIGMANAS_SVNDIR/boot/lua/gfx-${XIGMANAS_PRODUCTNAME}.lua $XIGMANAS_TMPDIR/boot/lua
		cp $XIGMANAS_SVNDIR/boot/images/xigmanas-brand-rev.png $XIGMANAS_TMPDIR/boot/images
		cp $XIGMANAS_SVNDIR/boot/brand-${XIGMANAS_PRODUCTNAME}.4th $XIGMANAS_TMPDIR/boot
		cp $XIGMANAS_SVNDIR/boot/menu.4th $XIGMANAS_TMPDIR/boot
		cp $XIGMANAS_BOOTDIR/menu.rc $XIGMANAS_TMPDIR/boot
		cp $XIGMANAS_BOOTDIR/menusets.4th $XIGMANAS_TMPDIR/boot
		# cp $XIGMANAS_ROOTFS/boot/loader.efi $XIGMANAS_TMPDIR/boot
		cp $XIGMANAS_SVNDIR/boot/loader.efi $XIGMANAS_TMPDIR/boot
		cp $XIGMANAS_SVNDIR/boot/efiboot.img $XIGMANAS_TMPDIR/boot
		cp $XIGMANAS_BOOTDIR/brand.4th $XIGMANAS_TMPDIR/boot
		cp $XIGMANAS_BOOTDIR/check-password.4th $XIGMANAS_TMPDIR/boot
		cp $XIGMANAS_BOOTDIR/color.4th $XIGMANAS_TMPDIR/boot
		cp $XIGMANAS_BOOTDIR/delay.4th $XIGMANAS_TMPDIR/boot
		cp $XIGMANAS_BOOTDIR/frames.4th $XIGMANAS_TMPDIR/boot
		cp $XIGMANAS_BOOTDIR/menu-commands.4th $XIGMANAS_TMPDIR/boot
		cp $XIGMANAS_BOOTDIR/screen.4th $XIGMANAS_TMPDIR/boot
		cp $XIGMANAS_BOOTDIR/shortcuts.4th $XIGMANAS_TMPDIR/boot
		cp $XIGMANAS_BOOTDIR/version.4th $XIGMANAS_TMPDIR/boot
	fi
	if [ 0 != $OPT_BOOTSPLASH ]; then
		cp $XIGMANAS_SVNDIR/boot/splash.bmp $XIGMANAS_TMPDIR/boot
		install -v -o root -g wheel -m 555 ${XIGMANAS_WORLD}/boot/kernel/splash_bmp.ko $XIGMANAS_TMPDIR/boot/kernel
	fi
	if [ "amd64" != ${XIGMANAS_ARCH} ]; then
		install -v -o root -g wheel -m 555 ${XIGMANAS_WORLD}/boot/kernel/apm.ko $XIGMANAS_TMPDIR/boot/kernel
	fi
	# iSCSI driver
	install -v -o root -g wheel -m 555 ${XIGMANAS_ROOTFS}/boot/kernel/isboot.ko $XIGMANAS_TMPDIR/boot/kernel
	# preload kernel drivers
	install -v -o root -g wheel -m 555 ${XIGMANAS_WORLD}/boot/kernel/opensolaris.ko $XIGMANAS_TMPDIR/boot/kernel
	install -v -o root -g wheel -m 555 ${XIGMANAS_WORLD}/boot/kernel/zfs.ko $XIGMANAS_TMPDIR/boot/kernel
	# copy kernel modules
	copy_kmod

	# Generate a loader.conf for full mode:
	echo 'kernel="kernel"' >> $XIGMANAS_TMPDIR/boot/loader.conf
	echo 'bootfile="kernel"' >> $XIGMANAS_TMPDIR/boot/loader.conf
	echo 'kernel_options=""' >> $XIGMANAS_TMPDIR/boot/loader.conf
	echo 'hw.est.msr_info="0"' >> $XIGMANAS_TMPDIR/boot/loader.conf
	echo 'hw.hptrr.attach_generic="0"' >> $XIGMANAS_TMPDIR/boot/loader.conf
	echo 'hw.msk.msi_disable="1"' >> $XIGMANAS_TMPDIR/boot/loader.conf
	echo 'kern.maxfiles="6289573"' >> $XIGMANAS_TMPDIR/boot/loader.conf
	echo 'kern.cam.boot_delay="12000"' >> $XIGMANAS_TMPDIR/boot/loader.conf
	echo 'kern.geom.label.disk_ident.enable="0"' >> $XIGMANAS_TMPDIR/boot/loader.conf
	echo 'kern.geom.label.gptid.enable="0"' >> $XIGMANAS_TMPDIR/boot/loader.conf
	echo 'hint.acpi_throttle.0.disabled="0"' >> $XIGMANAS_TMPDIR/boot/loader.conf
	echo 'hint.p4tcc.0.disabled="0"' >> $XIGMANAS_TMPDIR/boot/loader.conf
	echo 'autoboot_delay="3"' >> $XIGMANAS_TMPDIR/boot/loader.conf
	echo 'hostuuid_load="YES"' >> $XIGMANAS_TMPDIR/boot/loader.conf
	echo 'hostuuid_name="/etc/hostid"' >> $XIGMANAS_TMPDIR/boot/loader.conf
	echo 'hostuuid_type="hostuuid"' >> $XIGMANAS_TMPDIR/boot/loader.conf
	echo 'isboot_load="YES"' >> $XIGMANAS_TMPDIR/boot/loader.conf
	echo 'zfs_load="YES"' >> $XIGMANAS_TMPDIR/boot/loader.conf

	# Custom company brand(fallback).
	if [ -f ${XIGMANAS_SVNDIR}/boot/brand-${XIGMANAS_PRODUCTNAME}.4th ]; then
		echo "loader_brand=\"${XIGMANAS_PRODUCTNAME}\"" >> $XIGMANAS_TMPDIR/boot/loader.conf
	fi

	echo "FULL: Creating linker.hints"
	kldxref -R $XIGMANAS_TMPDIR/boot

	# Check that there is no /etc/fstab file! This file can be generated only during install, and must be kept
	[ -f $XIGMANAS_TMPDIR/etc/fstab ] && rm -f $XIGMANAS_TMPDIR/etc/fstab

	# Check that there is no /etc/cfdevice file! This file can be generated only during install, and must be kept
	[ -f $XIGMANAS_TMPDIR/etc/cfdevice ] && rm -f $XIGMANAS_TMPDIR/etc/cfdevice

	echo "FULL: Creating ${EXTENSION} compressed file"
	cd $XIGMANAS_ROOTDIR
	if [ "${EXTENSION}" = "tgz" ]; then
		tar cvfz ${FULLFILENAME} -C ${XIGMANAS_TMPDIR} ./
	elif [ "${EXTENSION}" = "txz" ]; then
		tar -c -f - -C ${XIGMANAS_TMPDIR} ./ | xz -8 -v -T $XIGMANAS_XZ_THREADS -M $XIGMANAS_XZ_MEM_LIMIT  > ${FULLFILENAME}
	fi

	# Cleanup.
	echo "Cleaning temp .o file(s)"
	if [ -f $XIGMANAS_TMPDIR/lib/librt.so.* ]; then
		chflags -R noschg $XIGMANAS_TMPDIR/lib/*
	fi
	[ -d $XIGMANAS_TMPDIR ] && rm -rf $XIGMANAS_TMPDIR

	create_checksum_file;

	return 0
}

create_all_images() {
	echo "Generating all $XIGMANAS_PRODUCTNAME release images at once...."
	echo "Build LiveCD-Tin is skipped"
	echo

	# List of the images to be generated, comment to disable.
	create_embedded
	create_usb
	create_usb_gpt
	create_iso
	# create_iso_tiny
	create_full

	echo "All $XIGMANAS_PRODUCTNAME release images created successfully!"
	return 0
}

#	Update Subversion Sources.
update_svn() {
	# Update sources from repository.
	cd $XIGMANAS_ROOTDIR
	svn co $XIGMANAS_SVNURL svn

	# Update Revision Number.
	XIGMANAS_REVISION=$(svn info ${XIGMANAS_SVNDIR} | grep Revision | awk '{print $2}')

	return 0
}

use_svn() {
	echo "===> Update files, we use latest files from svn directory"

	cd ${XIGMANAS_SVNDIR}/build && cp -pv CHANGES ${XIGMANAS_ROOTFS}/usr/local/www
	cd ${XIGMANAS_SVNDIR}/build/scripts && cp -pv carp-hast-switch ${XIGMANAS_ROOTFS}/usr/local/sbin
	cd ${XIGMANAS_SVNDIR}/build/scripts && cp -pv hastswitch ${XIGMANAS_ROOTFS}/usr/local/sbin
	cd ${XIGMANAS_SVNDIR}/root && find . \! -iregex ".*/\.svn.*" -print | cpio -pdumv ${XIGMANAS_ROOTFS}/root
	cd ${XIGMANAS_SVNDIR}/etc && find . \! -iregex ".*/\.svn.*" -print | cpio -pdumv ${XIGMANAS_ROOTFS}/etc
	cd ${XIGMANAS_SVNDIR}/www && find . \! -iregex ".*/\.svn.*" -print | cpio -pdumv ${XIGMANAS_ROOTFS}/usr/local/www
	cd ${XIGMANAS_SVNDIR}/conf && find . \! -iregex ".*/\.svn.*" -print | cpio -pdumv ${XIGMANAS_ROOTFS}/conf.default
	# Set file permission.
	chmod -R 0555 ${XIGMANAS_ROOTFS}/etc/rc.d/*

	return 0
}

build_system() {
	while true; do
echo -n '
-------------------------------------------------------------
		Build Environment XigmaNAS®                  
-------------------------------------------------------------
Compile Menu:

1) - Update FreeBSD Source Tree and Ports Collections.
2) - Create Filesystem Structure.
3) - Build/Install the Kernel.
4) - Build World.
5) - Import base-ports to their locations.
6) - Build Ports.
7) - Build Bootloader.
8) - Add Necessary Libraries.
9) - Modify File Permissions.
-------------------------------------------------------------

*) - Exit Compile Menu.

Press # '
		read choice
		case $choice in
			1)	update_sources;;
			2)	create_rootfs;;
			3)	build_kernel_menu;;
			4)	build_world;;
			5)	base-ports;;
			6)	build_ports;;
			7)	opt="-f";
					if [ 0 != $OPT_BOOTMENU ]; then
						opt="$opt -m"
					fi;
					if [ 0 != $OPT_BOOTSPLASH ]; then
						opt="$opt -b"
					fi;
					if [ 0 != $OPT_SERIALCONSOLE ]; then
						opt="$opt -s"
					fi;
					$XIGMANAS_SVNDIR/build/xigmanas-create-bootdir.sh $opt $XIGMANAS_BOOTDIR;;
			8)	lib_menu;;
			9)	$XIGMANAS_SVNDIR/build/xigmanas-modify-permissions.sh $XIGMANAS_ROOTFS;;
			*)	main; return $?;;
		esac
		[ 0 == $? ] && echo "=> Successfully done <=" || echo "=> Failed!"
		sleep 1
	done
}

#	Import svn base-ports on the host system & install required files to locations. 
base-ports() {
	# Install base-ports to the host ports directory usr/ports.
	echo "-------------------------------------------------------------------------------";
	echo ">>> Install base-ports to the host ports directory.";
	echo "-------------------------------------------------------------------------------";
	echo "===> Start install base-ports to ports directory"
	echo "===> Delete current libvncserver port from base OS"
	rm -rf /usr/ports/net/libvncserver
	echo "===> Delete completed!"
	echo ""
	echo "===> Adding new port libvncserver to ports/net/"
	cp -Rpv ${XIGMANAS_SVNDIR}/build/ports/base-ports/ports/libvncserver /usr/ports/net
	echo "===> New port libvncserver has been created!"
	echo ""
	echo "===> Delete current nss_ldap port from base OS"
	rm -rf /usr/ports/net/nss_ldap
	echo "===> Delete completed!"
	echo ""
	echo "===> Adding new port nss_ldap to ports/net/"
	cp -Rpv ${XIGMANAS_SVNDIR}/build/ports/base-ports/ports/nss_ldap /usr/ports/net
	echo "===> New port nss_ldap has been created!"
	echo ""
	echo "===> Delete current open-vm-tools from base OS"
	rm -rf /usr/ports/emulators/vopen-vm-tools
	echo "===> Delete completed!"
	echo ""
	echo "===> Adding new port open-vm-tools to ports/emulators/"
	cp -Rpv ${XIGMANAS_SVNDIR}/build/ports/base-ports/ports/open-vm-tools /usr/ports/emulators
	echo "===> New port open-vm-tools has been created!"
	echo ""
	echo "===> Delete current pecl-APCu port from base OS"
	rm -rf /usr/ports/devel/pecl-APCu
	echo "===> Delete completed!"
	echo ""
	echo "===> Adding new port pecl-APCu to ports/devel/"
	cp -Rpv ${XIGMANAS_SVNDIR}/build/ports/base-ports/ports/pecl-APCu /usr/ports/devel
	echo "===> New port pecl-APCu has been created!"
	echo ""
	echo "===> Delete current rrdtool port from base OS"
	rm -rf /usr/ports/databases/rrdtool
	echo "===> Delete completed!"
	echo ""
	echo "===> Adding new port rrdtool to ports/databases/"
	cp -Rpv ${XIGMANAS_SVNDIR}/build/ports/base-ports/ports/rrdtool /usr/ports/databases
	echo "===> New port rrdtool has been created!"
	echo ""
	echo "===> Delete current sudo port from base OS"
	rm -rf /usr/ports/security/sudo
	echo "===> Delete completed!"
	echo ""
	echo "===> Adding new port sudo to ports/security/"
	cp -Rpv ${XIGMANAS_SVNDIR}/build/ports/base-ports/ports/sudo /usr/ports/security
	echo "===> New port sudo has been created!"
	echo ""
	echo "===> Delete current virtualbox-ose-72 from base OS"
	rm -rf /usr/ports/emulators/virtualbox-ose-72
	echo "===> Delete completed!"
	echo ""
	echo "===> Adding new port virtualbox-ose-72 to ports/emulators/"
	cp -Rpv ${XIGMANAS_SVNDIR}/build/ports/base-ports/ports/virtualbox-ose-72 /usr/ports/emulators
	echo "===> New port virtualbox-ose has been created!"
	echo ""
	echo "===> Delete current virtualbox-ose-kmod-72 from base OS"
	rm -rf /usr/ports/emulators/virtualbox-ose-kmod-72
	echo "===> Delete completed!"
	echo ""
	echo "===> Adding new port virtualbox-ose-kmod to ports/emulators/"
	cp -Rpv ${XIGMANAS_SVNDIR}/build/ports/base-ports/ports/virtualbox-ose-kmod-72 /usr/ports/emulators
	echo "===> New port virtualbox-ose-kmod-72 has been created!"
	echo "===> End install base-ports to ports directory"
#
	# Install required sources to the host distfiles directory usr/ports/distfiles.
	echo;
	echo "-------------------------------------------------------------------------------";
	echo ">>> Install required sources to the host distfiles directory.";
	echo "-------------------------------------------------------------------------------";
	echo "===> Start install sources to distfiles directory"
	mkdir /usr/ports/distfiles
	cp -f ${XIGMANAS_SVNDIR}/build/ports/distfiles/arcconf_B27960.zip /usr/ports/distfiles
	echo "arcconf_B27960.zip"
	cp -f ${XIGMANAS_SVNDIR}/build/ports/distfiles/CLI_freebsd-from_the_10.2.2.1_9.5.5.1_codesets.zip /usr/ports/distfiles
	echo "CLI_freebsd-from_the_10.2.2.1_9.5.5.1_codesets.zip"
	cp -f ${XIGMANAS_SVNDIR}/build/ports/distfiles/isboot-0.3.4.tar.gz /usr/ports/distfiles
	echo "isboot-0.3.4.tar.gz"
	cp -f ${XIGMANAS_SVNDIR}/build/ports/distfiles/istgt-20180521.tar.gz /usr/ports/distfiles
	echo "istgt-20180521.tar.gz"
	cp -f ${XIGMANAS_SVNDIR}/build/ports/distfiles/SAS3IRCU_P16.zip /usr/ports/distfiles
	echo "SAS3IRCU_P16.zip"
	echo "===> End install sources to distfiles directory"
	return 0
}
build_ports() {
	tempfile=$XIGMANAS_WORKINGDIR/tmp$$
	ports=$XIGMANAS_WORKINGDIR/ports$$

	# Choose what to do.
	$DIALOG --ascii-lines --title "$XIGMANAS_PRODUCTNAME - Build/Install Ports" --menu "Please select whether you want to build or install ports." 11 65 4 \
		"build" "Build ports" \
		"nosel" "Build ports (dev only, no preselection)" \
		"rebuild" "Re-build ports (dev only)" \
		"install" "Install ports" 2> $tempfile
	if [ 0 != $? ]; then # successful?
		rm $tempfile
		return 1
	fi

	choice=$(cat $tempfile)
	rm $tempfile

	# Create list of available ports.
	echo "#! /bin/sh
$DIALOG --ascii-lines --title \"$XIGMANAS_PRODUCTNAME - Ports\" \\
--checklist \"Select the ports you want to process.\" 21 130 14 \\" > $tempfile

	for s in $XIGMANAS_SVNDIR/build/ports/*; do
		[ ! -d "$s" ] && continue
		port=$(basename $s)
		state=$(cat $s/pkg-state)
		case ${choice} in
			nosel)
				state="OFF"
				;;
			rebuild)
				t=$(echo $s/work/.build_done.*)
				if [ -e "$t" ]; then
					state="OFF"
				fi
				;;
		esac
		case ${state} in
			[hH][iI][dD][eE])
				;;
			*)
				desc=$(cat $s/pkg-descr);
				echo "\"$port\" \"$desc\" $state \\" >> $tempfile;
				;;
		esac
	done

	# Display list of available ports.
	sh $tempfile 2> $ports
	if [ 0 != $? ]; then # successful?
		rm $tempfile
		rm $ports
		return 1
	fi
	rm $tempfile

	case ${choice} in
		build|nosel|rebuild)
			# Set ports options
			echo;
			echo "-------------------------------------------------------------------------------";
			echo ">>> Set Ports Options.";
			echo "-------------------------------------------------------------------------------";
			cd ${XIGMANAS_SVNDIR}/build/ports/options && make
			# Clean ports.
			echo;
			echo "-------------------------------------------------------------------------------";
			echo ">>> Cleaning Ports.";
			echo "-------------------------------------------------------------------------------";
			for port in $(cat ${ports} | tr -d '"'); do
				cd ${XIGMANAS_SVNDIR}/build/ports/${port};
				make clean;
			done;
			if [ "i386" = ${XIGMANAS_ARCH} ]; then
				# workaround patch
				cp ${XIGMANAS_SVNDIR}/build/ports/vbox/files/extra-patch-src-VBox-Devices-Graphics-DevVGA.h /usr/ports/emulators/virtualbox-ose/files/patch-src-VBox-Devices-Graphics-DevVGA.h
			fi
			# Build ports.
			for port in $(cat $ports | tr -d '"'); do
				echo;
				echo "-------------------------------------------------------------------------------";
				echo ">>> Building Port: ${port}";
				echo "-------------------------------------------------------------------------------";
				cd ${XIGMANAS_SVNDIR}/build/ports/${port};
				make build;
				[ 0 != $? ] && return 1; # successful?
			done;
			;;
		install)
			if [ -f /var/db/pkg/local.sqlite ]; then
				cp -p /var/db/pkg/local.sqlite $XIGMANAS_WORKINGDIR/pkg
			fi
			for port in $(cat ${ports} | tr -d '"'); do
				echo;
				echo "-------------------------------------------------------------------------------";
				echo ">>> Installing Port: ${port}";
				echo "-------------------------------------------------------------------------------";
				cd ${XIGMANAS_SVNDIR}/build/ports/${port};
				# Delete cookie first, otherwise Makefile will skip this step.
				rm -f ./work/.install_done.* ./work/.stage_done.*;
				env PKG_DBDIR=$XIGMANAS_WORKINGDIR/pkg FORCE_PKG_REGISTER=1 make install;
				[ 0 != $? ] && return 1; # successful?
			done;
			;;
	esac
	rm ${ports}

	return 0
}

main() {
	# Ensure we are in $XIGMANAS_WORKINGDIR
	[ ! -d "$XIGMANAS_WORKINGDIR" ] && mkdir $XIGMANAS_WORKINGDIR
	[ ! -d "$XIGMANAS_WORKINGDIR/pkg" ] && mkdir $XIGMANAS_WORKINGDIR/pkg
	cd $XIGMANAS_WORKINGDIR

	echo -n "
"-------------------------------------------------------------"
		Build Environment XigmaNAS® 
"-------------------------------------------------------------"
Choice Menu:

1) - Update XIGMANAS_SVNDIR to latest XigmaNAS Source Files.
2) - Enter Compile Menu.

"-------------------------------------------------------------"
Generate Images:
10) - Create 'Embedded.img.xz' File.         (Firmware Update)
11) - Create 'LiveUSB.img.gz MBR' File.   (Rawrite to USB Key)
12) - Create 'LiveUSB.img.gz GPT' File.   (Rawrite to USB Key)
13) - Create 'LiveCD' (ISO) File.
14) - Create 'LiveCD-Tin' (ISO) Without 'Embedded' File.
15) - Create 'Full' (TGZ) Update File.
16) - Create All Release images at once.
"-------------------------------------------------------------"
17) - Create translations xigmanas.pot file from Source files.

*)  - Exit.

Press # "
	read choice
	case $choice in
		1)	update_svn;;
		2)	build_system;;
		10)	create_embedded;;
		11)	create_usb;;
		12)	create_usb_gpt;;
		13)	create_iso;;
		14)	create_iso_tiny;;
		15)	create_full;;
		16)	create_all_images;;
		17)	$XIGMANAS_SVNDIR/build/xigmanas-create-pot.sh;;
		*)	exit 0;;
	esac

	[ 0 == $? ] && echo "=> Successfully done <=" || echo "=> Failed! <="
	sleep 1

	return 0
}
