#!/bin/sh
#
# Part of XigmaNAS® (https://www.xigmanas.com).
# Copyright © 2018-2025 XigmaNAS® <info@xigmanas.com>.
# All rights reserved.
#

# Set environment.
PATH=${PATH}:/sbin:/bin:/usr/sbin:/usr/bin:/usr/local/sbin:/usr/local/bin
export PATH

# Set global variables.
PLATFORM=$(uname -m)
PRDNAME=$(cat /etc/prd.name)
CDMOUNTED=0
CDPATH="/tmp/cdrom"
INCLUDE="/etc/install/include/boot"
APPNAME="RootOnZFS"
ALTROOT="/mnt"
DATASET="/ROOT"
BOOTENV="/default-install"
ZROOT="zroot"
BOOTPOOL="bootpool"
GMIRRORBAL="load"
SYSBACKUP="/tmp/sysbackup"
DIRNOSCHG="bin lib libexec sbin usr"

# GELI options(bsdinstall).
GELIALGO="AES-XTS"
GELIINTER="256"
GELISECTOR="4096"
GELIBACKUP="/var/backups"

tmpfile=$(mktemp -q -t zfsinstall)
trap "rm -f ${tmpfile}" 0 1 2 5 15

# Notify message on error and exit.
error_exit()
{
	echo "$*" 1>&2
	read -p "Press enter to return to main installer menu." _wait_
	exit 1
}

# Mount CD/USB drive.
mount_cdrom()
{
	# Unmount for stale/interrupted previous attempts.
	umount_cdrom
	if [ ! -d "${CDPATH}" ]; then
		mkdir -p ${CDPATH}
	fi

	# Search for LiveMedia label information.
	if glabel status -s | grep -q "iso9660/XigmaNAS"; then
		FS_DEVICE="$(glabel status -s | grep "iso9660/XigmaNAS" | awk '{print $1}')"
		CD_DEVICE="$(glabel status -s | grep "iso9660/XigmaNAS" | awk '{print $3}')"
		# Check if cd-rom is mounted else auto mount cd-rom.
		if [ ! -f "${CDPATH}/version" ]; then
			# Try to auto mount cd-rom.
			echo "Mounting CD-ROM Drive"
			if ! mount_cd9660 /dev/${FS_DEVICE} ${CDPATH}; then
				echo "Unable to mount using gpt/label."
				echo "Trying to mount using device id..."
				if ! mount_cd9660 /dev/${CD_DEVICE} ${CDPATH}; then
					error_exit "Unable to mount using using device id."
				fi
			fi
		fi
	elif glabel status -s | grep -q "ufs/liveboot"; then
		USB_DEVICE_FS="$(glabel status -s | grep "ufs/liveboot" | awk '{print $1}')"
		USB_DEVICE_ID="$(glabel status -s | grep "ufs/liveboot" | awk '{print $3}')"
		# Check if liveusb is mounted else auto mount liveusb.
		if [ ! -f "${CDPATH}/version" ]; then
			# Try to auto mount liveusb.
			echo "Mounting LiveUSB Drive"
			if ! mount /dev/${USB_DEVICE_FS} ${CDPATH}; then
				echo "Unable to mount using gpt/label."
				echo "Trying to mount using device id..."
				if ! mount /dev/${USB_DEVICE_ID} ${CDPATH}; then
					error_exit "Unable to mount using using device id."
				fi
			fi

		fi
	else
		error_exit "Unable to detect ${PRDNAME} LiveMedia on this system."
	fi

	# If no cd/usb is mounted notify user.
	if [ ! -f "${CDPATH}/version" ]; then
		error_exit "Failed to detect/mount any CD/USB Drive"
	fi
}

umount_cdrom()
{
	FORCE_UMOUNT=
	if [ -d "${CDPATH}" ]; then
		echo "Unmount CD/USB Drive"
		# Properly unmount live media
		if df | grep -q "iso9660/XigmaNAS"; then
			umount_cmd
		elif df | grep -q "ufs/liveboot"; then
			umount_cmd
		elif [ -f "${CDPATH}/version" ]; then
			# Force unmount live media
			FORCE_UMOUNT="-f"
			umount_cmd
		fi
		rm -R ${CDPATH}
	fi
}

umount_cmd()
{
	umount ${FORCE_UMOUNT} ${CDPATH} > /dev/null 2>&1
}

# Clean any existing metadata on selected disks.
cleandisk_init()
{
	sysctl kern.geom.debugflags=0x10
	sleep 1

	# Ignore errors here since they may be expected.
	# We need this section to try to deal with problematic disk metadata.

	# Destroy existing geom mirror/swap.
	if kldstat -qn geom_mirror; then
		if gmirror status | grep -q "mirror/swap"; then
			echo "Destroying previous swap mirror provider..."
			gmirror forget swap
			gmirror destroy -f swap
		fi
		gmirror clear ${DISK} > /dev/null 2>&1
	fi

	DISKS=${DEVICE_LIST}
	NUM="0"
	WIPECOUNT="16384"
	for DISK in ${DISKS}
	do
		echo "Cleaning disk ${DISK}"

		# Destroy previous geli providers.
		if kldstat -qn geom_eli; then
			# Detach pervious geli providers for glabel.
			if geli status | grep -qw gpt/sysdisk${NUM}; then
				echo "Detaching previous geli provider on sysdisk${NUM}..."
				geli detach -f /dev/gpt/sysdisk${NUM} > /dev/null 2>&1
			fi

			# Detach pervious geli providers for diskid.
			if geli status | grep -q ${DISK}; then
				echo "Detaching previous geli provider on ${DISK}..."
				GELIDEV=$(geli status | grep ${DISK} | awk '{print $3}')
				geli detach -f /dev/${GELIDEV} > /dev/null 2>&1
			fi
		fi

		zpool labelclear -f /dev/gpt/sysdisk${NUM} > /dev/null 2>&1
		zpool labelclear -f /dev/${DISK} > /dev/null 2>&1
		gpart destroy -F ${DISK} > /dev/null 2>&1

		# Gather some disk information and proceed with cleanup.
		diskinfo ${DISK} | while read DISK sectorsize size sectors other
			do
				if [ "${WIPECOUNT}" -gt "${sectors}" ]; then
					WIPESECTORS=${sectors}
				else
					WIPESECTORS=${WIPECOUNT}
				fi
				# Delete MBR, GPT Primary, ZFS(L0L1)/other partition table.
				dd if="/dev/zero" of="/dev/${DISK}" bs=${sectorsize} count=${WIPESECTORS} > /dev/null 2>&1
				# Delete GEOM metadata, GPT Secondary, ZFS(L2L3)/other partition table.
				dd if="/dev/zero" of="/dev/${DISK}" bs=${sectorsize} seek=$(expr ${sectors} - ${WIPESECTORS}) count=${WIPESECTORS} > /dev/null 2>&1
			done
			NUM=$(expr $NUM + 1)
	done
}

load_geli_kmod()
{
	# Load geom_eli kernel module.
	if ! kldstat -qn geom_eli; then
		if [ -f "/boot/kernel/geom_eli.ko" ]; then
			kldload /boot/kernel/geom_eli.ko
		fi
	fi
}

load_gmirror_kmod()
{
	# Load geom_mirror kernel module.
	if ! kldstat | grep -q geom_mirror; then
		kldload /boot/kernel/geom_mirror.ko
	fi
}

# Create GPT/Partition on disk.
gptpart_init()
{
	DISKS=${DEVICE_LIST}
	swapdevlist=$(mktemp -q -t swapdevlist)
	gelidevlist=$(mktemp -q -t gelidevlist)
	cat /dev/null > ${tmpfile}
	NUM="0"
	for DISK in ${DISKS}
	do
		echo "Creating GPT/Partition on ${DISK}"
		gpart create -s gpt ${DISK} > /dev/null

		# Create boot partition.
		if [ "${BOOT_MODE}" = 2 ]; then
			gpart add -a 4k -s 260M -t efi -l efiboot${NUM} ${DISK} > /dev/null
		fi
		gpart add -a 4k -s 512K -t freebsd-boot -l sysboot${NUM} ${DISK} > /dev/null

		if [ ! -z "${SWAP_SIZE}" ]; then
			# Add swap partition to selected drives.
			gpart add -a 4m -s ${SWAP_SIZE} -t freebsd-swap -l swap${NUM} ${DISK} > /dev/null
			# Generate swap device list.
			echo "/dev/gpt/swap${NUM}" >> ${swapdevlist}
		fi
		gpart add -a 4m ${ZROOT_SIZE} -t freebsd-zfs -l sysdisk${NUM} ${DISK} > /dev/null
		# Generate the glabel device list.
		echo "/dev/gpt/sysdisk${NUM}" >> ${tmpfile}

		if echo ${GELI_MODE} | grep -qw "DISK+GELI"; then
			# Generate the geli device list.
			echo "/dev/gpt/sysdisk${NUM}.eli" >> ${gelidevlist}
		fi

		NUM=$(expr $NUM + 1)
	done

	if echo ${GELI_MODE} | grep -qw "DISK+GELI"; then
		GELI_DEVLIST=$(cat ${tmpfile})
		ZROOT_DEVLIST=$(cat ${gelidevlist})
		rm -f ${gelidevlist}
	else
		rm -f ${gelidevlist}
		ZROOT_DEVLIST=$(cat ${tmpfile})
	fi

	if [ ! -z "${SWAP_SIZE}" ]; then
		SWAP_DEVLIST=$(cat ${swapdevlist})
		rm -f ${swapdevlist}
	fi

	if echo ${GELI_MODE} | grep -qw "DISK+GELI"; then
		# Initialize encryption.
		geom_geli_init
	fi
}

# Create MBR/Partition on disk.
mbrpart_init()
{
	DISKS=${DEVICE_LIST}
	swapdevlist=$(mktemp -q -t swapdevlist)
	gelidevlist=$(mktemp -q -t gelidevlist)
	bootdevlist=$(mktemp -q -t bootdevlist)
	cat /dev/null > ${tmpfile}
	#NUM="0"
	for DISK in ${DISKS}
	do
		echo "Creating MBR/Partition on ${DISK}"
		gpart create -s mbr ${DISK} > /dev/null

		# Create active partition partition.
		if [ "${BOOT_MODE}" = 3 ]; then
			gpart add -a 4k -t freebsd ${DISK} > /dev/null
			gpart set -a active -i 1 ${DISK} > /dev/null
		fi

		# Create zfs boot partition.
		gpart create -s BSD "${DISK}s1" > /dev/null
		gpart add -i 1 -t freebsd-zfs -s 2048M "${DISK}s1" > /dev/null

		if [ ! -z "${SWAP_SIZE}" ]; then
			# Add swap partition to selected drives.
			gpart add -a 4k -s ${SWAP_SIZE} -i 2 -t freebsd-swap "${DISK}s1" > /dev/null
			# Generate swap device list.
			echo "/dev/${DISK}s1b" >> ${swapdevlist}
		fi

		# Create zfs os partition.
		gpart add -a 4m ${ZROOT_SIZE} -i 4 -t freebsd-zfs "${DISK}s1" > /dev/null

		# Write bootcode for ZFS on BIOS
		dd if="/boot/zfsboot" of="/dev/${DISK}s1" count=1 > /dev/null 2>&1

		# Generate the bootpool device list.
		echo "/dev/${DISK}s1a" >> ${bootdevlist}

		# Generate the mbr device list.
		echo "${DISK}s1d" >> ${tmpfile}

		if echo ${GELI_MODE} | grep -qw "DISK+GELI"; then
		# Generate the geli device list.
		echo "/dev/${DISK}s1d.eli" >> ${gelidevlist}
		fi

		#NUM=$(expr $NUM + 1)
	done

	if echo ${GELI_MODE} | grep -qw "DISK+GELI"; then
		GELI_DEVLIST=$(cat ${tmpfile})
		ZROOT_DEVLIST=$(cat ${gelidevlist})
		rm -f ${gelidevlist}
	else
		rm -f ${gelidevlist}
		ZROOT_DEVLIST=$(cat ${tmpfile})
	fi

	BOOTPOOL_DEVLIST=$(cat ${bootdevlist})
	rm -f ${bootdevlist}

	if [ ! -z "${SWAP_SIZE}" ]; then
		SWAP_DEVLIST=$(cat ${swapdevlist})
		rm -f ${swapdevlist}
	fi
}

# Initialize encryption.
geom_geli_init()
{
	load_geli_kmod
	DISKS=${GELI_DEVLIST}
	for DISK in ${DISKS}
	do
		echo "Encryption in progress for ${DISK}..."
		RETURN=1
		#cdialog --backtitle "${PRDNAME} ${APPNAME} Installer" --title "Encryption in progress!" --infobox \
		#"Initializing encryption on selected disks, this will take several seconds per disk." 4 46

		# Encrypt selected drives.
		if [ "${BOOT_MODE}" = 3 ]; then
			geli init -b -B ${ALTROOT}/${BOOTPOOL}/boot/${DISK}.eli -e ${GELIALGO} -J "${GELIPASS1}" -K ${ALTROOT}/${BOOTPOOL}/boot/encryption.key -l ${GELIINTER} -s ${GELISECTOR} ${DISK} > /dev/null
			geli attach -j "${GELIPASS1}" -k ${ALTROOT}/${BOOTPOOL}/boot/encryption.key ${DISK}
		else
			geli init -bg -e ${GELIALGO} -J "${GELIPASS1}" -l ${GELIINTER} -s ${GELISECTOR} ${DISK} > /dev/null
			geli attach -j "${GELIPASS1}" ${DISK}
		fi

		RETURN=$?
		if [ "${RETURN}" -eq 0 ]; then
			echo "Encryption success!"
		else
			error_exit "Encryption failed!"
		fi
	done
}

geli_meta_backup()
{
	if echo ${GELI_MODE} | grep -qw "DISK+GELI"; then
		if [ ! -d "${ALTROOT}${GELIBACKUP}" ]; then
			mkdir -p -m 750 ${ALTROOT}${GELIBACKUP}
		fi
		cd ${GELIBACKUP}
		for ITEM in *.eli
		do
			if [ -f "${ITEM}" ]; then
				RETURN=1
				mv -f ${ITEM} ${ALTROOT}${GELIBACKUP}/${ITEM}
				RETURN=$?
				if [ "${RETURN}" -ne 0 ]; then
					error_exit "Failed to backup geli providers metadata!"
				fi
			fi
		done
		cd
	fi
}

# Install RootOnZFS.
zroot_init()
{
	# Begin installation.
	printf '\033[1;37;44m RootOnZFS Working... \033[0m\033[1;37m\033[0m\n'

	# Mount cd-rom.
	if [ "${CDMOUNTED}" != 1 ]; then
		mount_cdrom
	fi

	# Check for existing zroot pool.
	zpool_check

	# Check for existing bootpool pool.
	if [ "${BOOT_MODE}" = 3 ]; then
		bootpool_check
	fi

	# Get rid of any metadata on selected disk.
	cleandisk_init

	# Create MBR/GPT/Partition on disk.
	if [ "${BOOT_MODE}" = 3 ]; then
		mbrpart_init
	else
		gptpart_init
	fi

	# Set the raid type.
	if [ "${STRIPE}" = 0 ]; then
		RAID=""
	elif [ "${MIRROR}" = 0 ]; then
		RAID="mirror"
	elif [ "${RAID10}" = 0 ]; then
		RAID="mirror"
	elif [ "${RAIDZ1}" = 0 ]; then
		RAID="raidz1"
	elif [ "${RAIDZ2}" = 0 ]; then
		RAID="raidz2"
	elif [ "${RAIDZ3}" = 0 ]; then
		RAID="raidz3"
	fi

	if [ "${BOOT_MODE}" = 3 ]; then
		# Create bootable bootpool pool for mbr.
		echo "Setting up boot pool..."
		mount -t tmpfs "none" ${ALTROOT}

		if [ "${RAID10}" = 0 ]; then
			# Generate raid10 list in groups of two(fixed to four disk).
			echo ${BOOTPOOL_DEVLIST} | xargs -n 2 | split -d -l 1 - ${tmpfile}.disks
			BOOTPOOL_DEVLIST1=$(cat ${tmpfile}.disks00)
			BOOTPOOL_DEVLIST2=$(cat ${tmpfile}.disks01)
			zpool create -o altroot=${ALTROOT}  -m /${BOOTPOOL} -f ${BOOTPOOL} ${RAID} ${BOOTPOOL_DEVLIST1} ${RAID} ${BOOTPOOL_DEVLIST2}
			rm -rf ${tmpfile}.disks*
		else
			zpool create -o altroot=${ALTROOT}  -m /${BOOTPOOL} -f ${BOOTPOOL} ${RAID} ${BOOTPOOL_DEVLIST}
		fi

		mkdir -p ${ALTROOT}/${BOOTPOOL}/boot
		if echo ${GELI_MODE} | grep -qw "DISK+GELI"; then
			dd if="/dev/random" of="${ALTROOT}/${BOOTPOOL}/boot/encryption.key" bs=4096 count=1 > /dev/null 2>&1
			chmod go-wrx ${ALTROOT}/${BOOTPOOL}/boot/encryption.key
			geom_geli_init
		fi
		zfs unmount ${BOOTPOOL}
		umount ${ALTROOT}
	fi

	# Create bootable zroot pool with boot environments support.
	echo "Creating bootable ${ZROOT} pool"

	if [ "${RAID10}" = 0 ]; then
		# Generate raid10 list in groups of two(fixed to four disk).
		echo ${ZROOT_DEVLIST} | xargs -n 2 | split -d -l 1 - ${tmpfile}.disks
		ZROOT_DEVLIST1=$(cat ${tmpfile}.disks00)
		ZROOT_DEVLIST2=$(cat ${tmpfile}.disks01)
		zpool create -o altroot=${ALTROOT} -O compress=lz4 -O atime=off -m none -f ${ZROOT} ${RAID} ${ZROOT_DEVLIST1} ${RAID} ${ZROOT_DEVLIST2}
		rm -rf ${tmpfile}.disks*
	else
		zpool create -o altroot=${ALTROOT} -O compress=lz4 -O atime=off -m none -f ${ZROOT} ${RAID} ${ZROOT_DEVLIST}
	fi

	# Create ZFS filesystem hierarchy.
	zfs create -o mountpoint=none ${ZROOT}${DATASET}
	zfs create -o mountpoint=/ ${ZROOT}${DATASET}${BOOTENV}
	zfs create -o mountpoint=/tmp -o exec=on -o setuid=off ${ZROOT}/tmp
	zfs create -o mountpoint=/usr -o canmount=off ${ZROOT}/usr
	zfs create  ${ZROOT}/usr/home
	zfs create -o setuid=off ${ZROOT}/usr/ports
	zfs create  ${ZROOT}/usr/src
	zfs create -o mountpoint=/var -o canmount=off ${ZROOT}/var
	zfs create -o exec=off -o setuid=off ${ZROOT}/var/audit
	zfs create -o exec=off -o setuid=off ${ZROOT}/var/crash
	zfs create -o exec=off -o setuid=off ${ZROOT}/var/log
	zfs create -o atime=on ${ZROOT}/var/mail
	zfs create -o setuid=off ${ZROOT}/var/tmp
	zfs set mountpoint=/${ZROOT} ${ZROOT}
	zpool set bootfs=${ZROOT}${DATASET}${BOOTENV} ${ZROOT}
	#zfs set canmount=noauto ${ZROOT}${DATASET}${BOOTENV}
	if [ $? -ne 0 ]; then
		error_exit "Error: A problem has occurred while creating ${ZROOT} pool."
	fi

	# Install system files.
	install_sys_files

	if [ "${BOOT_MODE}" = 3 ]; then
		# Temporarily exporting ZFS pool(s)...
		zpool export ${ZROOT}
		zpool export ${BOOTPOOL}
	fi

	# Write bootcode.
	echo "Writing bootcode..."
	DISKS=${DEVICE_LIST}
	for DISK in ${DISKS}
	do
		if [ "${BOOT_MODE}" = 3 ]; then
			gpart bootcode -b /boot/mbr ${DISK}
			echo "Updating MBR boot loader on ${DISK}"
			dd if="/boot/zfsboot" of="/dev/${DISK}s1a" skip=1 seek=1024 > /dev/null 2>&1
		elif [ "${BOOT_MODE}" = 2 ]; then
			#gpart bootcode -p /boot/boot1.efifat -i 1 ${DISK} # Legacy efi bootcode, keep for reference.
			if ! newfs_msdos -F 16 -L "EFISYS" /dev/${DISK}p1 > /dev/null 2>&1; then
				error_exit "Failed to create new filesystem on ${DISK}p1"
			else
				echo "Creating EFI system partition..."
				mkdir -p /tmp/zfsinstall_etc/esp
				mount -t msdosfs /dev/${DISK}p1 /tmp/zfsinstall_etc/esp
				mkdir -p /tmp/zfsinstall_etc/esp/efi/boot
				cp /boot/loader.efi /tmp/zfsinstall_etc/esp/efi/boot/BOOTx64.efi
				echo "BOOTx64.efi" > /tmp/zfsinstall_etc/esp/efi/boot/startup.nsh
				mkdir -p /tmp/zfsinstall_etc/esp/efi/freebsd
				cp /boot/loader.efi /tmp/zfsinstall_etc/esp/efi/freebsd/loader.efi
				umount /tmp/zfsinstall_etc/esp
				gpart bootcode -b /boot/pmbr -p /boot/gptzfsboot -i 2 ${DISK}
				rm -r /tmp/zfsinstall_etc
			fi
		else
			gpart bootcode -b /boot/pmbr -p /boot/gptzfsboot -i 1 ${DISK}
		fi
	done

	if [ "${BOOT_MODE}" = 3 ]; then
		# Re-importing ZFS pool(s)...
		zpool import -o altroot=${ALTROOT} ${ZROOT}
		zpool import -o altroot=${ALTROOT} -N ${BOOTPOOL}
		zfs mount ${BOOTPOOL}

		echo "Copying ${BOOTPOOL} content..."
		rsync -a ${ALTROOT}/boot/ ${ALTROOT}/${BOOTPOOL}/boot/
		rm -rf ${ALTROOT}/boot

		echo "Creating /boot symlink for ${BOOTPOOL}..."
		ln -sf ${BOOTPOOL}/boot ${ALTROOT}/boot
		mkdir -p ${ALTROOT}/boot/zfs
	fi

	# Set the zpool.cache file.
	zpool set cachefile=${ALTROOT}/boot/zfs/zpool.cache ${ZROOT}

	# Set canmount=noauto for the boot environment.
	zfs set canmount=noauto ${ZROOT}${DATASET}${BOOTENV}

	sysctl kern.geom.debugflags=0
	sleep 1

	# Add required ZFS mount points to fstab(legacy mountpoints only).
	#echo "${ZROOT}/tmp /tmp zfs rw,noatime 0 0" >> ${ALTROOT}/etc/fstab
	#echo "${ZROOT}/var /var zfs rw,noatime 0 0" >> ${ALTROOT}/etc/fstab

	# Creating/adding the fixed swap devices.
	if [ ! -z "${SWAP_SIZE}" ]; then
		if [ "${SWAPMODE}" = 1 ]; then
			load_gmirror_kmod
			echo "Creating/adding swap mirror..."
			gmirror label -b ${GMIRRORBAL} swap ${SWAP_DEVLIST}
			# Add swap mirror to fstab.
			if echo ${GELI_MODE} | grep -qw "SWAP+GELI"; then
				echo "/dev/mirror/swap.eli none swap sw 0 0" >> ${ALTROOT}/etc/fstab
			else
				echo "/dev/mirror/swap none swap sw 0 0" >> ${ALTROOT}/etc/fstab
			fi
		else
			# Add swap device to fstab.
			echo "Adding swap devices to fstab..."
			for swapdev in ${SWAP_DEVLIST}; do
				if echo ${GELI_MODE} | grep -qw "SWAP+GELI"; then
					echo "${swapdev}.eli none swap sw 0 0" >> ${ALTROOT}/etc/fstab
				else
					echo "${swapdev} none swap sw 0 0" >> ${ALTROOT}/etc/fstab
				fi
			done
		fi
	fi

	# Creating/adding the /boot/efi mountpoint.
	if [ "${BOOT_MODE}" = 2 ]; then
		if [ ! -d "${ALTROOT}/boot/efi" ]; then
			mkdir -p ${ALTROOT}/boot/efi
		fi
		for DISK in ${DISKS}
		do
			# Add the first esp partition to fstab.
			echo "Adding esp device to fstab..."
			echo "/dev/${DISK}p1 /boot/efi msdosfs rw 2 2" >> ${ALTROOT}/etc/fstab
			break
		done
	fi

	# Backup geli providers metadata.
	geli_meta_backup

	# Unmount cd-rom.
	umount_cdrom

	# Create user Dataset on request.
	create_user_dataset

	# Flush disk cache and wait 1 second.
	sync; sleep 1

	zpool_export

	# Final message.
	if [ $? -eq 0 ]; then
		cdialog --msgbox "${PRDNAME} ${APPNAME} Successfully Installed!" 6 60
		[ -f "${tmpfile}" ] && rm -f ${tmpfile}
		exit 0
	else
		error_exit "Error: A problem has occurred during the installation."
	fi
}

set_boot_path()
{
	BOOTPATH=${ALTROOT}
	if [ "${MBRPART}" = 1 ]; then
		BOOTPATH=${ALTROOT}/${BOOTPOOL}
	fi
}

backup_chflags() {
	# Dump chflag from common directories/* to zipped files.
	cd ${ALTROOT}
	for FILES in ${DIRNOSCHG}; do
		mtree -q -P -c -p ${FILES} | gzip > ${SYSBACKUP}/chflags.${ZROOT}.${FILES}.gz
	done
	if [ 0 != $? ]; then
		error_exit "Error: Failed to backup chflags."
	fi
}

remove_chflags() {
	# Remove chflag from common directories/*.
	cd ${ALTROOT}
	for FILES in ${DIRNOSCHG}; do
		chflags -R noschg ${FILES}
		chmod -R u+rw ${FILES}
	done
	if [ 0 != $? ]; then
		error_exit "Error: Failed to remove chflags."
	fi
}

restore_chflags() {
	# Restore chflag from previous zipped files.
	cd ${ALTROOT}
	for FILES in ${DIRNOSCHG}; do
		zcat ${SYSBACKUP}/chflags.${ZROOT}.${FILES}.gz | mtree -q -P -U -p ${FILES} > /dev/null
	done
	if [ 0 != $? ]; then
		error_exit "Error: Failed to restore chflags."
	fi
}

backup_sys_files() {
	# Backup common system configuration files.
	# Only files present in the firmware upgrade file are affected.
	echo "Backup system configuration..."

	if [ ! -d "${SYSBACKUP}" ]; then
		mkdir -p ${SYSBACKUP}
	fi

	# Look for possible files in /boot
	if [ -f "${BOOTPATH}/boot/loader.conf" ]; then
		cp -p ${BOOTPATH}/boot/loader.conf ${SYSBACKUP}/
	fi
	if [ -f "${BOOTPATH}/boot/loader.conf.local" ]; then
		cp -p ${BOOTPATH}/boot/loader.conf.local ${SYSBACKUP}/
	fi
	if [ -f "${ALTROOT}/boot.config" ]; then
		cp -p ${ALTROOT}/boot.config ${SYSBACKUP}/
	fi

	# Look for possible files in /etc
	if [ -f "${ALTROOT}/etc/fstab" ]; then
		cp -p ${ALTROOT}/etc/fstab ${SYSBACKUP}/
	fi
	if [ -f "${ALTROOT}/etc/cfdevice" ]; then
		cp -p ${ALTROOT}/etc/cfdevice ${SYSBACKUP}/
	fi
	if [ -f "${ALTROOT}/etc/platform" ]; then
		cp -p ${ALTROOT}/etc/platform ${SYSBACKUP}/
	fi
	if [ -f "${ALTROOT}/etc/rc.conf" ]; then
		cp -p ${ALTROOT}/etc/rc.conf ${SYSBACKUP}/
	fi
	if [ -f "${ALTROOT}/etc/rc.conf.local" ]; then
		cp -p ${ALTROOT}/etc/rc.conf.local ${SYSBACKUP}/
	fi

	# Keep a copy of the previous kernel on each upgrade.
	if [ -d "${BOOTPATH}/boot/kernel" ]; then
		if [ "${MBRPART}" = 1 ]; then
			# Keep a copy of the previous kernel and append the date on mbr installs.
			KDATE=$(date +%Y-%m-%d-%H%M)
			mv ${BOOTPATH}/boot/kernel ${BOOTPATH}/boot/kernel.old.${KDATE}
		else
			if [ -d "${BOOTPATH}/boot/kernel.old" ]; then
				# Keep only the current kernel per boot environment.
				rm -rf ${BOOTPATH}/boot/kernel.old
			fi
			mv ${BOOTPATH}/boot/kernel ${BOOTPATH}/boot/kernel.old
		fi
	fi
}

restore_sys_files() {
	# Restore previous backup files on the boot environment.
	# Only files present in the firmware upgrade file are affected.
	echo "Restore system configuration..."

	if [ -f "${SYSBACKUP}/loader.conf" ]; then
		cp -pf ${SYSBACKUP}/loader.conf ${BOOTPATH}/boot/
	fi
	if [ -f "${SYSBACKUP}/rc.conf" ]; then
		cp -pf ${SYSBACKUP}/rc.conf ${ALTROOT}/etc/
	fi
	cd
}

# Upgrade RootOnZFS.
upgrade_init()
{
	export BEPATH=${ALTROOT}

	# Begin installation.
	printf '\033[1;37;44m RootOnZFS Working... \033[0m\033[1;37m\033[0m\n'

	# Mount cd-rom.
	if [ "${CDMOUNTED}" != 1 ]; then
		mount_cdrom
	fi

	# Set the proper boot path for backup.
	set_boot_path

	# Backup system configuration.
	backup_sys_files
	if [ 0 -ne $? ]; then
		error_exit "Error: Failed to backup configuration."
	fi

	# Start upgrade script to remove obsolete files. This should be done
	# before system is updated because it may happen that some files
	# may be reintroduced in the system.
	echo "Remove obsolete files..."
	${BEPATH}/etc/install/upgrade.sh clean ${BEPATH}

	# Backup chflags before removal.
	backup_chflags

	# Remove chflags for protected files before upgrade process.
	remove_chflags

	# Upgrade system files.
	upgrade_sys_files

	# Restore chflags.
	restore_chflags

	# Restore system configuration.
	restore_sys_files

	# Unmount cd-rom.
	umount_cdrom

	# Flush disk cache, cleanup and sync.
	rm -rf ${SYSBACKUP}
	sync; sleep 1

	# Export zpool
	zpool_export

	# Final message.
	if [ $? -eq 0 ]; then
		cdialog --msgbox "${PRDNAME} ${APPNAME} Successfully Upgraded!" 6 60
		[ -f "${tmpfile}" ] && rm -f ${tmpfile}
		exit 0
	else
		error_exit "Error: A problem has occurred during the upgrade."
	fi
}

install_sys_files()
{
	echo "Installing system files on ${ZROOT}..."

	# Install system files and discard unwanted folders.
	EXCLUDEDIRS="--exclude .snap/ --exclude resources/ --exclude mnt/ --exclude dev/ --exclude var/ --exclude tmp/ --exclude cf/"
	tar ${EXCLUDEDIRS} -c -f - -C / . | tar -xpf - -C ${ALTROOT}
	if [ ! -f "${ALTROOT}/etc/rc.d/var" ]; then
		cp -r /etc/rc.d/var ${ALTROOT}/etc/rc.d/var
	fi

	# Copy files from live media source.
	copy_media_files

	# Install configuration file.
	mkdir -p ${ALTROOT}/cf/conf
	cp /conf.default/config.xml ${ALTROOT}/cf/conf

	# Generate new loader.conf file.
	cat << EOF > ${ALTROOT}/boot/loader.conf
kernel="kernel"
bootfile="kernel"
kernel_options=""
hw.est.msr_info="0"
hw.hptrr.attach_generic="0"
hw.msk.msi_disable="1"
kern.maxfiles="6289573"
kern.cam.boot_delay="12000"
kern.cam.ada.legacy_aliases="0"
kern.geom.label.disk_ident.enable="0"
kern.geom.label.gptid.enable="0"
hint.acpi_throttle.0.disabled="0"
hint.p4tcc.0.disabled="0"
loader_brand="${PRDNAME}"
autoboot_delay="3"
isboot_load="YES"
zfs_load="YES"
EOF
	if [ "${BOOT_MODE}" = 3 ]; then
		echo "vfs.root.mountfrom=\"zfs:${ZROOT}${DATASET}${BOOTENV}\"" >> ${ALTROOT}/boot/loader.conf
	fi

	if [ "${SWAPMODE}" = 1 ]; then
		if [ ! -z "${SWAP_SIZE}" ]; then
			echo 'geom_mirror_load="YES"' >> ${ALTROOT}/boot/loader.conf
		fi
	fi

	# Update loader.conf file if geli encryption.
	if echo ${GELI_MODE} | grep -qw "DISK+GELI"; then
		if [ "${BOOT_MODE}" = 3 ]; then
			DISKS=${DEVICE_LIST}
			for DISK in ${DISKS}
			do
				echo "geli_${DISK}s1d_keyfile0_load=\"YES\"" >> ${ALTROOT}/boot/loader.conf
				echo "geli_${DISK}s1d_keyfile0_type=\"${DISK}s1d:geli_keyfile0\"" >> ${ALTROOT}/boot/loader.conf
				echo "geli_${DISK}s1d_keyfile0_name=\"/boot/encryption.key\"" >> ${ALTROOT}/boot/loader.conf
			done
				echo 'zpool_cache_load="YES"' >> ${ALTROOT}/boot/loader.conf
				echo 'zpool_cache_type="/boot/zfs/zpool.cache"' >> ${ALTROOT}/boot/loader.conf
				echo 'zpool_cache_name="/boot/zfs/zpool.cache"' >> ${ALTROOT}/boot/loader.conf
				echo 'geom_eli_passphrase_prompt="YES"' >> ${ALTROOT}/boot/loader.conf
		fi
		echo 'geom_eli_load="YES"' >> ${ALTROOT}/boot/loader.conf
	fi

	# Generate new rc.conf file.
	#cat << EOF > ${ALTROOT}/etc/rc.conf

#EOF

	# Set the release type.
	if [ "${PLATFORM}" = "amd64" ]; then
		echo "x64-full" > ${ALTROOT}/etc/platform
	elif [ "${PLATFORM}" = "i386" ]; then
		echo "x86-full" > ${ALTROOT}/etc/platform
	fi

	# Generate /etc/fstab.
	cat << EOF > ${ALTROOT}/etc/fstab
# Device    Mountpoint    FStype    Options    Dump    Pass#
#
EOF

	# Generate /etc/swapdevice.
	if [ ! -z "${SWAP_SIZE}" ]; then
		cat << EOF > ${ALTROOT}/etc/swapdevice
swapinfo
EOF
	fi

	# Generating the /etc/cfdevice (this file is linked in /var/etc at bootup)
	# This file is used by the firmware and mount check and is normally
	# generated with 'liveCD' and 'embedded' during startup, but need to be
	# created during install of 'full'.
	# This is for backward compatibility only.
	if [ ! -z "${disklist}" ]; then
		if echo ${GELI_MODE} | grep -qw "DISK+GELI"; then
			# Use diskid if encryption enabled.
			CFDEVS=${disklist}
		elif [ "${RAID10}" = 0 ]; then
			CFDEVS="${ZROOT_DEVLIST1} ${ZROOT_DEVLIST2}"
		else
			CFDEVS="${ZROOT_DEVLIST}"
		fi
		cat << EOF > ${ALTROOT}/etc/cfdevice
${CFDEVS}
EOF
	fi

	# Post install configuration.
	post_install_config
	echo "Done!"
}

upgrade_sys_files()
{
	EXCLUDEBOOT=
	if [ "${MBRPART}" = 1 ]; then
		echo "Extracting files for ${BOOTPOOL}..."
		RESULT=1
		EXCLUDEBOOT="--exclude=boot/"
		# Extract boot to /bootpool on ZFS/MBR installs since boot is a symlink.
		tar --keep-newer-files -c -f - -C /boot . | tar -xpf - -C ${ALTROOT}/${BOOTPOOL}/boot
		RESULT=$?
		if [ 0 != "${RESULT}" ]; then
			error_exit "Error: Failed file extraction for ${BOOTPOOL}."
		fi
	fi

	# Install system files and discard unwanted folders.
	echo "Upgrading system files on ${BOOTENV_ITEM}..."
	EXCLUDEDIRS="${EXCLUDEBOOT} --exclude=.snap/ --exclude=resources/ --exclude=mnt/ --exclude=dev/ --exclude=var/ --exclude=tmp/ --exclude=cf/ --exclude=version --exclude=platform --exclude=fstab --exclude=cfdevice"
	tar ${EXCLUDEDIRS} --keep-newer-files -c -f - -C / . | tar -xpf - -C ${ALTROOT}

	# Copy files from live media source.
	copy_media_files
}

copy_media_files()
{
	# Copy files from live media source.
	if [ "${UPDATE}" != 0 ]; then
		DIR_LIST="/dev /mnt /tmp /var /tmp /var/tmp /boot/defaults"
		for _DIR in ${DIR_LIST}; do
			if [ ! -d "${ALTROOT}${_DIR}" ]; then
				mkdir -p ${ALTROOT}${_DIR}
			fi
		done

		chmod 1777 ${ALTROOT}/tmp
		chmod 1777 ${ALTROOT}/var/tmp
	fi

	# Set the proper boot path for copying.
	set_boot_path

	cp -r ${CDPATH}/boot/* ${BOOTPATH}/boot/
	cp -r ${CDPATH}/boot/defaults/* ${BOOTPATH}/boot/defaults/
	cp -r ${CDPATH}/boot/kernel/* ${BOOTPATH}/boot/kernel/

	# Decompress kernel.
	gzip -d -f ${BOOTPATH}/boot/kernel/kernel.gz

	# Decompress modules (legacy versions).
	cd ${BOOTPATH}/boot/kernel
	for FILE in *.gz
	do
		if [ -f "${FILE}" ]; then
			gzip -d -f ${FILE}
		fi
	done
	cd
}

post_install_config()
{
	# Configure some rc variables here before reboot.
	# Clear default router and netwait ip on new installs.
	if sysrc -f ${ALTROOT}/etc/rc.conf -qc defaultrouter=""; then
		sysrc -f ${ALTROOT}/etc/rc.conf defaultrouter="" > /dev/null 2>&1
	fi
	if sysrc -f ${ALTROOT}/etc/rc.conf -qc netwait_ip=""; then
		sysrc -f ${ALTROOT}/etc/rc.conf netwait_ip="" > /dev/null 2>&1
	fi

	# Disable /var md option as it may override our zroot/tmp dataset.
	if sysrc -f ${ALTROOT}/etc/rc.conf -qc varmfs="YES"; then
		sysrc -f ${ALTROOT}/etc/rc.conf varmfs="NO" > /dev/null 2>&1
	fi

	# Set zfs_enable to yes to automount our datasets on boot.
	if [ -f "${ALTROOT}/etc/rc.conf.local" ]; then
		if ! sysrc -f ${ALTROOT}/etc/rc.conf.local -qc zfs_enable="YES"; then
			sysrc -f ${ALTROOT}/etc/rc.conf.local zfs_enable="YES" > /dev/null 2>&1
		fi
	else
		touch ${ALTROOT}/etc/rc.conf.local
		chmod 0644 ${ALTROOT}/etc/rc.conf.local
		sysrc -f ${ALTROOT}/etc/rc.conf.local zfs_enable="YES" > /dev/null 2>&1
	fi
}

create_user_dataset()
{
	# Create user Dataset on request.
	if [ ! -z "${DATASET_NAME}" ]; then
		echo "Creating dataset ${DATASET_NAME}..."
		# Prevent for dataset directory creation here(backward compatibility only).
		# Users should never write data to this directory unless dataset mounted.
		# So we will set canmount after dataset creation for safety.
		zfs create -o compression=lz4 -o canmount=off -o atime=off -o mountpoint=${ALTROOT}/${DATASET_NAME} ${ZROOT}/${DATASET_NAME}
		zfs set canmount=on ${ZROOT}/${DATASET_NAME}
		if [ $? -ne 0 ]; then
			error_exit "Error: A problem has occurred while creating ${DATASET_NAME} dataset."
		fi
		echo "Done!"
		sleep 1
	fi
}

zpool_check()
{
	# Check if a zroot pool already exist and/or mounted.
	echo "Check for existing ${ZROOT} pool..."
	if zpool import | grep -qw ${ZROOT} || zpool status | grep -qw ${ZROOT}; then
		printf '\033[1;37;43m WARNING \033[0m\033[1;37m A pool called '${ZROOT}' already exist.\033[0m\n'
		while :
			do
				read -p "Do you wish to proceed with the installation anyway? [y/N]:" yn
				case ${yn} in
				[Yy]) break;;
				[Nn]) exit 0;;
				esac
			done
		echo "Proceeding..."
		# Export existing zroot pool.
		if zpool status | grep -q ${ZROOT}; then
			zpool export ${ZROOT} > /dev/null 2>&1
		fi
	fi
}

bootpool_check()
{
	# Check if a bootpool pool already exist and/or mounted.
	echo "Check for existing ${BOOTPOOL} pool..."
	if zpool import | grep -qw ${BOOTPOOL} || zpool status | grep -qw ${BOOTPOOL}; then
		printf '\033[1;37;43m WARNING \033[0m\033[1;37m A pool called '${BOOTPOOL}' already exist.\033[0m\n'
		while :
			do
				read -p "Do you wish to proceed with the installation anyway? [y/N]:" yn
				case ${yn} in
				[Yy]) break;;
				[Nn]) exit 0;;
				esac
			done
		echo "Proceeding..."
		# Export existing zroot pool.
		if zpool status | grep -q ${BOOTPOOL}; then
			zpool export ${BOOTPOOL} > /dev/null 2>&1
		fi
	fi
}

install_confirm()
{
	DISKS=$(echo ${disklist})
	cdialog --title "Proceed with ${PRDNAME} ${APPNAME} Install" \
	--backtitle "${PRDNAME} ${APPNAME} Installer" \
	--yesno "Continuing with the installation will destroy all data on <${DISKS}> device(s), do you really want to continue?" 0 0
	if [ 0 -ne $? ]; then
		exit 0
	fi
}

upgrade_confirm()
{
	cdialog --title "Proceed with ${PRDNAME} ${APPNAME} Upgrade" \
	--backtitle "${PRDNAME} ${APPNAME} Installer" \
	--yesno "Continuing with the upgrade will overwrite system data on <${BOOTENV_ITEM}> Boot Environment, do you really want to continue?" 0 0
	if [ 0 -ne $? ]; then
		zpool_export
		exit 0
	fi
}

menu_poolname()
{
		pool_name=
		cdialog --backtitle "${PRDNAME} ${APPNAME} Installer" --title "Pool Name" \
		--form "Enter a desired pool name, default is ${ZROOT}." 0 0 0 \
		"Enter pool name:" 1 1 "${ZROOT}" 1 25 25 25 \
		2>${tmpfile}
		if [ 0 -ne $? ]; then
			exit 0
		fi

		pool_name=$(cat ${tmpfile})
		if [ ! -z "${pool_name}" ]; then
			VERIFY_TOPIC="Pool"
			FUNC_CMD="menu_poolname"
			name_verify="${pool_name}"
			check_name
			ZROOT="${pool_name}"
		fi
}

menu_swap()
{
	swap_size=
	cdialog --backtitle "${PRDNAME} ${APPNAME} Installer" --title "Customize Swap size" \
	--form "Enter a desired SWAP size, default size is 2G, leave empty for none." 0 0 0 \
	"Enter swap size:" 1 1 "2G" 1 25 25 25 \
	2>${tmpfile}
	if [ 0 -ne $? ]; then
		exit 0
	fi

	swap_size=$(cat ${tmpfile})
	if [ ! -z "${swap_size}" ]; then
		# Perform some input validation.
		if [ ! $(echo "${swap_size}" | egrep -o '^[1-9][0-9]*[kmgKMG]$') ]; then
			echo ""
			echo "ERROR: Invalid swap size specified, allowed suffixes are K,M,G."
			read -p "Press Enter to retry." RETRY
			menu_swap
		fi

		SWAP_SIZE="${swap_size}"

		if [ "${choice}" -ge 2 ]; then
			cdialog --backtitle "${PRDNAME} ${APPNAME} Installer" --title "System Swap Mode" \
			--radiolist "Select system Swap mode, (default mirrored)." 10 50 4 \
			1 "Mirrored System Swap" on \
			2 "Multiple System Swap" off \
			2>${tmpfile}
			if [ 0 -ne $? ]; then
				exit 0
			fi
			SWAPMODE=$(cat ${tmpfile})
		fi
	fi
}

menu_zrootsize()
{
	root_size=
	cdialog --backtitle "${PRDNAME} ${APPNAME} Installer" --title "Customize zroot pool partition" \
	--radiolist "Would you like to customize the ${ZROOT} partition size?" 10 60 4 \
	1 "Yes, I want to customize." off \
	2 "No, leave the defaults." on \
	2>${tmpfile}
	if [ 0 -ne $? ]; then
		exit 0
	fi

	CUSTOM_ROOT=$(cat ${tmpfile})
	if [ "${CUSTOM_ROOT}" = 1 ]; then
		cdialog --backtitle "${PRDNAME} ${APPNAME} Installer" --title "Customize zroot pool size" \
		--form "Enter a desired zroot pool size, for example 10G, or leave empty to use all remaining disk space (default empty)." 0 0 0 \
		"Enter zroot pool size:" 1 1 "" 1 25 25 25 \
		2>${tmpfile}
		if [ 0 -ne $? ]; then
			exit 0
		fi

		root_size=$(cat ${tmpfile})
		if [ ! -z "${root_size}" ]; then
			# Perform some input validation.
			if [ ! $(echo "${root_size}" | egrep -o '^[1-9][0-9]*[kmgKMG]$') ]; then
				echo ""
				echo "ERROR: Invalid zroot size specified, allowed suffixes are K,M,G."
				read -p "Press Enter to retry." RETRY
				menu_zrootsize
			fi

			ZROOT_SIZE="-s ${root_size}"
		fi
	fi
}

menu_dataset()
{
	dataset_name=
	cdialog --backtitle "${PRDNAME} ${APPNAME} Installer" --title "Create user Dataset" \
	--radiolist "Would you like to create a ZFS Dataset for data store?" 10 60 4 \
	1 "Yes, I want a Dataset." off \
	2 "No, leave the defaults." on \
	2>${tmpfile}
	if [ 0 -ne $? ]; then
		exit 0
	fi

	dataset=$(cat ${tmpfile})
	if [ "${dataset}" = 1 ]; then
		cdialog --backtitle "${PRDNAME} ${APPNAME} Installer" --title "Enter Dataset name" \
		--form "Enter a desired Dataset name, or leave empty to cancel Dataset creation (default empty)." 0 0 0 \
		"Enter Dataset name:" 1 1 "" 1 25 25 25 \
		2>${tmpfile}
		if [ 0 -ne $? ]; then
			exit 0
		fi

		dataset_name=$(cat ${tmpfile})
		if [ ! -z "${dataset_name}" ]; then
			VERIFY_TOPIC="Dataset"
			FUNC_CMD="menu_dataset"
			name_verify="${dataset_name}"
			check_name
			DATASET_NAME="${dataset_name}"
		fi
	fi
}

check_name()
{
	# Perform some input validation.
	DATASETNAME="${name_verify}"
	CHECK_NAME=$(echo "${name_verify}" | tr -c -d 'a-zA-Z0-9-_.:')
	if [ "${DATASETNAME}" != "${CHECK_NAME}" ]; then

		echo -e "
ERROR: Can not create ZFS ${VERIFY_TOPIC} with '${DATASETNAME}' name.

    Allowed characters for ZFS ${VERIFY_TOPIC} are:
      Alphanumeric: (a-z) (A-Z) (0-9)
      Hypen: (-)
      Underscore: (_)
      Dot: (.)
      Colon: (:)

    Name '${CHECK_NAME}' wish uses only allowed characters can be used.
"
		read -p "Press Enter to retry." RETRY
		${FUNC_CMD}
	fi
}

menu_bootmode()
{
	cdialog --backtitle "${PRDNAME} ${APPNAME} Installer" --title "Boot mode selection menu" \
	--radiolist "Select system boot mode, default is BIOS+UEFI for compatibility.
	\n
	\n Option 1:  Support for most modern systems.
	\n Option 2:  Support for either modern and current systems.
	\n Option 3:  Support for most older systems." 14 68 4 \
	1 "GPT BIOS System Boot" off \
	2 "GPT BIOS+UEFI System Boot" on \
	3 "MBR BIOS System Boot (Legacy)" off \
	2>${tmpfile}
	if [ 0 -ne $? ]; then
		exit 0
	fi

	BOOT_MODE=$(cat ${tmpfile})
	if [ "${BOOT_MODE}" = 3 ] && [ "${ZROOT}" = "bootpool" ]; then
		cdialog --msgbox "Warning: The name 'bootpool' is reserved on MBR setups. \nPlease restart the installer and select a different name for the 'zroot' pool." 7 60
		exit 1
	fi
}

menu_encryption()
{
	PASS1=
	PASS2=
	cdialog --backtitle "${PRDNAME} ${APPNAME} Installer" --title "Encryption option menu" \
	--checklist "Select the desired items to be encrypted (optional), if you're inexperienced with GELI/Encryption, please disregard this and press Enter to continue." 12 55 8 \
	"DISK+GELI" "Encrypt Disks?" off \
	"SWAP+GELI" "Encrypt Swap?" off \
	2>${tmpfile}
	if [ 0 -ne $? ]; then
		exit 0
	fi

	GELI_MODE=$(cat ${tmpfile})

	if echo ${GELI_MODE} | grep -qw "DISK+GELI"; then
		GELIPASS1=$(mktemp -q -t gelipass1)
		GELIPASS2=$(mktemp -q -t gelipass2)
		trap "rm -f ${GELIPASS1} ${GELIPASS2}" 0 1 2 5 15

		# Ask for a password.
		cdialog --backtitle "${PRDNAME} ${APPNAME} Installer" --title "Encryption option menu" --clear --insecure --passwordbox \
		"Enter a strong passphrase, used to protect your encryption keys. You will be required to enter this passphrase each time the system is booted." 8 80 \
		2> ${GELIPASS1}

		# Re-enter password.
		cdialog --backtitle "${PRDNAME} ${APPNAME} Installer" --title "Encryption option menu" --clear --insecure --passwordbox "Re-enter password." 8 60 \
		2> ${GELIPASS2}

		# Check passwords.
		PASS1=$(cat ${GELIPASS1})
		PASS2=$(cat ${GELIPASS2})
		if [ "${PASS1}" != "${PASS2}" -o -z "${PASS1}" -o -z "${PASS2}" ]; then
			rm -f ${GELIPASS1} ${GELIPASS2}
			cdialog --backtitle "${PRDNAME} ${APPNAME} Installer" --title "Encryption option menu" --msgbox "Passwords do not match." 5 28
			menu_encryption
		fi
	fi
}

menu_zroot_create()
{
	menu_install
	menu_poolname
	menu_swap
	menu_zrootsize
	menu_dataset
	menu_bootmode
	menu_encryption
	install_confirm
	zroot_init
}

menu_zroot_update()
{
	menu_poolname
	update_zroot_check
	menu_beselect
	update_bootpool_check
	upgrade_confirm
	upgrade_init
}

sort_disklist()
{
	sed 's/\([^0-9]*\)/\1 /' | sort +0 -1 +1n | tr -d ' '
}

get_disklist()
{
	local disklist

	for disklist in $(sysctl -n kern.disks)
	do
		VAL="${VAL} ${disklist}"
	done

	VAL=$(echo ${VAL} | tr ' ' '\n'| grep -v '^cd' | sort_disklist)
}

get_media_desc()
{
	local media
	local description
	local cap

	media=$1
	VAL=""
	if [ -n "${media}" ]; then
		# Try to get model information for each detected device.
		description=$(camcontrol identify ${media} | grep 'device model' | sed 's/device\ model\ *//g')
	if [ -z "${description}" ] ; then
		# Re-try with "camcontrol inquiry" instead.
		description=$(camcontrol inquiry ${media} | awk 'NR==1' | cut -d '<' -f2 | cut -d '>' -f1)
		if [ -z "${description}" ] ; then
			description="Disk Drive"
		fi
	fi
		cap=$(diskinfo ${media} | awk '{
			capacity = $3;
			if (capacity >= 1099511627776) {
				printf("%.1f TiB", capacity / 1099511627776.0);
			} else if (capacity >= 1073741824) {
				printf("%.1f GiB", capacity / 1073741824.0);
			} else if (capacity >= 1048576) {
				printf("%.1f MiB", capacity / 1048576.0);
			} else {
				printf("%d Bytes", capacity);
			}}')
		VAL="${description} -- ${cap}"
	fi
}

menu_install()
{
	tmplist=$(mktemp -q -t zfsinstall)
	get_disklist
	disklist="${VAL}"
	list=""
	items=0

	for disklist in ${disklist}
		do
			get_media_desc "${disklist}"
			desc="${VAL}"
			list="${list} ${disklist} '${desc}' off"
			items=$((${items} + 1))
		done

	if [ "${items}" -ge 10 ]; then
		items=10
		menuheight=20
	else
		menuheight=10
		menuheight=$((${menuheight} + ${items}))
	fi

	if [ "${items}" -eq 0 ]; then
		eval "cdialog --title 'Choose destination drive(s)' --msgbox 'No drives available' 5 60" \
		2>${tmpfile}
		exit 1
	fi

	eval "cdialog --backtitle '${PRDNAME} ${APPNAME} Installer'   --title 'Choose destination drive' \
		--checklist 'Select (one) or (more) drives where ${PRDNAME} should be installed, use arrow keys to navigate to the drive(s) for installation then select a drive with the spacebar.' \
		${menuheight} 60 ${items} ${list}" 2>${tmpfile}
	if [ 0 -ne $? ]; then
		exit 0
	fi

	if [ ! -z "${tmpfile}" ]; then
		#disklist=$(eval "echo $(cat ${tmpfile})")
		disklist=$(echo $(cat ${tmpfile}))
	fi

	count="1"
	for item in ${disklist}
	do
		DEV_COUNT=$(echo $count)
		count=$(expr $count + 1)
	done

	# Check for empty user input.
	if [ -z "${disklist}" ]; then
		if [ "${choice}" = 1 ]; then
			cdialog --msgbox "Notice: You need to select at least one disk!" 6 55; exit 1
		elif [ "${choice}" = 2 ]; then
			cdialog --msgbox "Notice: You need to select at least two disks!" 6 55; exit 1
		elif [ "${choice}" = 3 ]; then
			cdialog --msgbox "Notice: You need to select at least four disks!" 6 55; exit 1
		elif [ "${choice}" = 4 ]; then
			cdialog --msgbox "Notice: You need to select at least three disks!" 6 55; exit 1
		elif [ "${choice}" = 5 ]; then
			cdialog --msgbox "Notice: You need to select at least four disks!" 6 55; exit 1
		elif [ "${choice}" = 6 ]; then
			cdialog --msgbox "Notice: You need to select at least five disks!" 6 55; exit 1
		fi
	fi

	# Check for absolute minimum disks selection.
	if [ -n "${disklist}" ]; then
		if [ "${choice}" = 2 ]; then
			if [ "${DEV_COUNT}" -le 1 ]; then
				cdialog --msgbox "Notice: You need to select a minimum of two disks!" 6 60; exit 1
			fi
		elif [ "${choice}" = 3 ]; then
			if [ "${DEV_COUNT}" -le 3 ]; then
				cdialog --msgbox "Notice: You need to select a minimum of four disks!" 6 60; exit 1
			fi
		elif [ "${choice}" = 4 ]; then
			if [ "${DEV_COUNT}" -le 2 ]; then
				cdialog --msgbox "Notice: You need to select a minimum of three disks!" 6 60; exit 1
			fi
		elif [ "${choice}" = 5 ]; then
			if [ "${DEV_COUNT}" -le 3 ]; then
				cdialog --msgbox "Notice: You need to select a minimum of four disks!" 6 60; exit 1
			fi
		elif [ "${choice}" = 6 ]; then
			if [ "${DEV_COUNT}" -le 4 ]; then
				cdialog --msgbox "Notice: You need to select a minimum of five disks!" 6 60; exit 1
			fi
		fi
	fi
	cat /dev/null > ${tmplist}
	for disk in ${disklist}; do
		#echo "/dev/${disk}" >> ${tmplist}
		echo "${disk}" >> ${tmplist}
	done

	DEVICE_LIST=$(cat ${tmplist})
	rm -f ${tmplist}
}

update_zroot_check()
{
	# Check if the defined zroot pool exist.
	echo
	echo "Checking for ${pool_name} pool existence..."
	if ! zpool status | grep -qw ${pool_name}; then
		if zpool import | grep -qw ${pool_name}; then
			printf '\033[1;37;43m WARNING \033[0m\033[1;37m Pool '${pool_name}' found and may be importable.\033[0m\n'
			while :
				do
					read -p "Do you wish to proceed with ${pool_name} import? [y/N]:" yn
					case ${yn} in
					[Yy]) break;;
					[Nn]) exit 0;;
					esac
				done
			echo "Trying to import ${pool_name}..."
			# Try import existing zroot pool.
			if ! zpool import -f -R ${ALTROOT} ${pool_name}; then
				error_exit "Error: A problem has occurred while trying to import ${pool_name}."
			fi
		else
			error_exit "Error: Pool ${pool_name} not found."
		fi
	else
		echo "Error: Looks like ${pool_name} pool has been already imported."
		error_exit "Please export the pool manually and retry upgrade."
	fi
}

update_bootpool_check()
{
	# Check if a bootpool pool exist and auto import it.
	echo
	echo "Check for existing ${BOOTPOOL} pool existence..."
	MBRPART=
	if ! zpool status | grep -qw ${BOOTPOOL}; then
		if zpool import | grep -qw ${BOOTPOOL}; then
			MBRPART=1
			# Try import existing bootpool pool.
			if ! zpool import -f -R ${ALTROOT} ${BOOTPOOL}; then
				zpool_export
				error_exit "Error: A problem has occurred while trying to import ${BOOTPOOL}."
			fi
		fi
	else
		echo "Error: Looks like ${BOOTPOOL} pool has been already imported."
		error_exit "Please export the pool manually and retry upgrade."
	fi
}

zpool_export()
{
	# Export zroot and bootpool if any.
	if zpool status | grep -q ${ZROOT}; then
		if [ "${BOOT_MODE}" = 3 ] || [ "${MBRPART}" = 1 ]; then
			if zpool status | grep -q ${BOOTPOOL}; then
				zpool export ${BOOTPOOL}
			fi
		fi
		zpool export ${ZROOT}
	fi
}

get_be_list()
{
	# Generate a list of possible Boot Environments.
	BOOTENV_LIST=$(zfs list -H -r "${pool_name}${DATASET}" | awk '{print $1}' | sed "s|${pool_name}${DATASET}/*||;1d")
	VAL=""
	local BE
	for BE in ${BOOTENV_LIST}; do
		VAL="${VAL} ${BE}"
	done
	VAL=$(echo ${VAL} | tr ' ' '\n')
}

menu_beselect()
{
	# Build the Boot Environments selection menu.
	get_be_list
	BE="${VAL}"
	list=""
	items=0
	for BE in ${BE}; do
		if [ "${ITEMSTATS}" != 0 ]; then
			list="${list} ${BE} '${desc}' off"
		else
			list="${list} ${BE} '' off"
		fi
		items=$((${items} + 1))
	done

	if [ "${items}" -ge 10 ]; then
		items=10
		menuheight=20
	else
		menuheight=10
		menuheight=$((${menuheight} + ${items}))
	fi

	if [ "${items}" -eq 0 ]; then
		eval "dialog --title 'Select Boot Environment' --msgbox 'No Boot Environments available!' 5 60" \
		2>${tmpfile}
		zpool_export
		exit 1
	fi

	eval "dialog --backtitle '${PRDNAME} ${APPNAME} Installer' --title 'Select Boot Environment' \
		--radiolist 'Select any Boot Environment you wish to upgrade, use [up] [down] keys to navigate the menu then select a item with the [spacebar].' \
		${menuheight} 80 ${items} ${list}" \
		2>${tmpfile}
	if [ 0 -ne $? ]; then
		zpool_export
		exit 0
	fi

	BOOTENV_ITEM=$(cat ${tmpfile})
	if [ -z "${BOOTENV_ITEM}" ]; then
		menu_beselect
	else
		# Try to mount the selected boot environment.
		if ! zfs mount ${pool_name}${DATASET}/${BOOTENV_ITEM}; then
			zpool_export
			error_exit "Error: A problem has occurred while trying mount ${BOOTENV_ITEM} Boot Environment."
		fi
	fi
}

menu_main()
{
	while :
		do
			cdialog --backtitle "${PRDNAME} ${APPNAME} Installer" --clear --title "${PRDNAME} Installer Options" --cancel-label "Exit" --menu "" 13 50 10 \
			"1" "Install ${PRDNAME} ${APPNAME} on Stripe" \
			"2" "Install ${PRDNAME} ${APPNAME} on Mirror" \
			"3" "Install ${PRDNAME} ${APPNAME} on RAID10" \
			"4" "Install ${PRDNAME} ${APPNAME} on RAIDZ1" \
			"5" "Install ${PRDNAME} ${APPNAME} on RAIDZ2" \
			"6" "Install ${PRDNAME} ${APPNAME} on RAIDZ3" \
			"7" "Upgrade ${PRDNAME} ${APPNAME} from Media" \
			2>${tmpfile}
			if [ 0 -ne $? ]; then
				exit 0
			fi
			choice=$(cat "${tmpfile}")
			case "${choice}" in
				1) STRIPE="0" && menu_zroot_create ;;
				2) MIRROR="0" && menu_zroot_create ;;
				3) RAID10="0" && menu_zroot_create ;;
				4) RAIDZ1="0" && menu_zroot_create ;;
				5) RAIDZ2="0" && menu_zroot_create ;;
				6) RAIDZ3="0" && menu_zroot_create ;;
				7) UPDATE="0" && menu_zroot_update ;;
			esac
		done
}
menu_main
