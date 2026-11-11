#!/bin/sh
#
# Part of XigmaNAS® (https://www.xigmanas.com).
# Copyright © 2018-2025 XigmaNAS® <info@xigmanas.com>.
# All rights reserved.
#
. /etc/rc.subr

name="upgrade"
load_rc_config "$name"

# Custom commands
extra_commands="clean"
start_cmd=":"
stop_cmd=":"
clean_cmd="upgrade_clean"

# Check for ZFS Boot Environments and set default path.
ZROOT=$(/sbin/mount | awk -F '/' '/ \/ / {print $1}')
BE_PATH=""
if zpool list -H -o bootfs | grep -qw "${ZROOT}/ROOT"; then
	BE_PATH=${BEPATH}
fi

# Defaults
upgrade_obsoletefiles=${upgrade_obsoletefiles:-"${BE_PATH}/etc/install/ObsoleteFiles.inc"}

upgrade_clean()
{
	local _path _file _filepath

	_path=$1

	if [ -n ${_path} ]; then
		for _file in $(cat ${upgrade_obsoletefiles} | grep -v "^#"); do
			_filepath="${_path}/${_file}"
			if [ -d "${_filepath}" ]; then
				rm -d -r ${_filepath}
				logger "Upgrade: Remove obsolete directory ${_file}."
			elif [ -f "${_filepath}" ]; then
				rm ${_filepath}
				logger "Upgrade: Remove obsolete file ${_file}."
			fi
		done
	fi
}

run_rc_command "$@"
