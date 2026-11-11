#!/bin/bash
#
#	Part of XigmaNAS® (https://www.xigmanas.com).
#	Copyright © 2018-2025 XigmaNAS® <info@xigmanas.com>.
#	All rights reserved.
#
WORKING_DIR="/var/run/rrdgraphs"
STORAGE_PATH=`/usr/local/bin/xml sel -t -v "//rrdgraphs/storage_path" /conf/config.xml`
if [ ! -d "${STORAGE_PATH}" ] || [ ! -d  "${STORAGE_PATH}/rrd" ]; then
	exit 1
fi
. "${STORAGE_PATH}/rrd_config"

#	function creates rrdtool update command for mounted disks -> parameters: mount_point(=$1) used_space(=$2) free_space(=$3)
CREATE_MOUNTS_CMD ()
{
	i=0
	counter=MOUNT${i}
	while [ "${!counter}" != "" ]; do
		if [ "${!counter}" == "$1" ]; then
			CLEAN_NAME=`echo -n "$1" | /usr/bin/openssl base64 -A | tr '+/=' '-_~'`
			FILE="${STORAGE_PATH}/rrd/mnt_${CLEAN_NAME}.rrd"
			if [ ! -f "$FILE" ]; then
				/usr/local/bin/rrdtool create "$FILE" \
					-s 300 \
					'DS:Used:GAUGE:600:U:U' \
					'DS:Free:GAUGE:600:U:U' \
					'RRA:AVERAGE:0.5:1:576' \
					'RRA:AVERAGE:0.5:6:672' \
					'RRA:AVERAGE:0.5:24:732' \
					'RRA:AVERAGE:0.5:144:1460'
			fi
			if [ -f "$FILE" ]; then
				/usr/local/bin/rrdtool update "$FILE" N:"$2":"$3" 2>> /tmp/rrdgraphs-error.log
			fi
			break
		fi
		i=$((i+1))
		counter=MOUNT${i}
	done
}
#	function creates rrdtool update command for pools -> parameters: pool_name(=$1) used_space(=$2) free_space(=$3)
CREATE_POOLS_CMD ()
{
	CLEAN_NAME=`echo -n "$1" | /usr/bin/openssl base64 -A | tr '+/=' '-_~'`
	FILE="${STORAGE_PATH}/rrd/mnt_${CLEAN_NAME}.rrd"
	if [ ! -f "$FILE" ]; then
		/usr/local/bin/rrdtool create "$FILE" \
			-s 300 \
			'DS:Used:GAUGE:600:U:U' \
			'DS:Free:GAUGE:600:U:U' \
			'RRA:AVERAGE:0.5:1:576' \
			'RRA:AVERAGE:0.5:6:672' \
			'RRA:AVERAGE:0.5:24:732' \
			'RRA:AVERAGE:0.5:144:1460'
	fi
	if [ -f "$FILE" ]; then
		/usr/local/bin/rrdtool update "$FILE" N:"$2":"$3" 2>> /tmp/rrdgraphs-error.log
	fi
}
#	function creates rrdtool update command for network interfaces -> parameters: interface_name(=$1)
CREATE_INTERFACE_CMD ()
{
while [ "$1" != "" ]; do
	FILE="${STORAGE_PATH}/rrd/${1}.rrd"
	if [ ! -f "$FILE" ]; then
		/usr/local/bin/rrdtool create "$FILE" \
			-s 300 \
			'DS:in:COUNTER:600:0:U' \
			'DS:out:COUNTER:600:0:U' \
			'RRA:AVERAGE:0.5:1:576' \
			'RRA:AVERAGE:0.5:6:672' \
			'RRA:AVERAGE:0.5:24:732' \
			'RRA:AVERAGE:0.5:144:1460'
	fi
	if [ -f "$FILE" ]; then
		valif=`netstat -I "$1" -nWb -f link | grep -v Name | awk '{print $8":"$11}'`
		/usr/local/bin/rrdtool update "$FILE" N:"$valif" 2>> /tmp/rrdgraphs-error.log
	fi
	shift 1
done
}
#	function extracts values from 'top' for processes -> parameters: var_name(=$3) var_value(=$4)
CREATE_PVARS ()
{
total="$2"
running=0
sleeping=0
waiting=0
starting=0
stopped=0
zombie=0
while [ "$3" != "" ]; do
	case "$3" in
		running)
			running="$4"
			;;
		sleeping)
			sleeping="$4"
			;;
		waiting)
			waiting="$4"
			;;
		starting)
			starting="$4"
			;;
		stopped)
			stopped="$4"
			;;
		zombie)
			zombie="$4"
			;;
	esac
	shift
done
}
#	function extracts values from 'top' for CPU usage -> parameters: var_name(=$1) var_value(=$2)
CREATE_CVARS ()
{
user=0
nice=0
system=0
interrupt=0
idle=0
while [ "$1" != "" ]; do
	case "$1" in
		user)
			user="$2"
			;;
		nice)
			nice="$2"
			;;
		system)
			system="$2"
			;;
		interrupt)
			interrupt="$2"
			;;
		idle)
			idle="$2"
			;;
		*)
			TYPE="$2"
			;;
	esac
	shift
done
}
CREATE_UPSVARS ()
{
#	Values:
#		battery.charge      %
#		ups.load            %
#		battery.voltage     V
#		output.voltage      V
#		input.voltage       V
#		battery.runtime     m
#		ups.status          OL [CHRG]
#				                OFF
#						        OB
#	$1: var name $2: value $3: CHRG
charge=nan
load=nan
bvoltage=nan
ovoltage=nan
ivoltage=nan
runtime=nan
OL=nan
OF=nan
OB=nan
CG=nan
if [ "$CMD" == "" ]; then
	OF=100
	return
fi
while [ "$1" != "" ]; do
	case "$1" in
		battery.charge:)
			charge="$2"
			;;
		ups.load:)
			load="$2"
			;;
		battery.voltage:)
			bvoltage="$2"
			;;
		output.voltage:)
			ovoltage="$2"
			;;
		input.voltage:)
			ivoltage="$2"
			;;
		battery.runtime:)
			runtime=`echo -e "$2" | awk '{calc=$1/60; print calc}'`
			;;		
		ups.status:)
			case "$2" in
				OL)
					OL=-10
					if [ "$charge" -lt 100 ]; then
						OL=nan
						CG=-10
					fi
					;;
				OFF)
					OF=-10
					;;
				OB)
#					OL=-10
					OB=-10
					;;
				CHRG)
#					OL=-10
					CG=-10
					;;
			esac;
			case "$3" in
				CHRG)
#					OL=-10;
					CG=-10
					;;
			esac
			;;
	esac
	shift
done
}
#	call 'top' once for: Load Averages, Processes, CPU Usage, Uptime
if [ "$RUN_AVG" -eq 1 ] || [ "$RUN_PRO" -eq 1 ] || [ "$RUN_CPU" -eq 1 ] || [ "$RUN_UPT" -eq 1 ] ; then
	TOP=`top -bSItud2`
else
	TOP=""
fi
####################################################
# Update graphs for:
####################################################
#	Network interfaces
if [ "$RUN_LAN" -eq 1 ]; then
#	interfaces, LAN & OPTx
	interfaces=`/usr/local/bin/xml sel -t -v "//interfaces/.//if" /conf/config.xml`
	CREATE_INTERFACE_CMD ${interfaces}
	interfaces=`/usr/local/bin/xml sel -t -v "//vinterfaces/.//if" /conf/config.xml`
	CREATE_INTERFACE_CMD ${interfaces}
fi
#	System load averages
if [ "$RUN_AVG" -eq 1 ]; then
	FILE="${STORAGE_PATH}/rrd/load_averages.rrd"
	if [ ! -f "$FILE" ]; then
		/usr/local/bin/rrdtool create "$FILE" \
			-s 300 \
			'DS:CPU:GAUGE:600:0:100' \
			'DS:CPU5:GAUGE:600:0:100' \
			'DS:CPU15:GAUGE:600:0:100' \
			'RRA:AVERAGE:0.5:1:576' \
			'RRA:AVERAGE:0.5:6:672' \
			'RRA:AVERAGE:0.5:24:732' \
			'RRA:AVERAGE:0.5:144:1460'
	fi
	if [ -f "$FILE" ]; then
		valla=`echo -e "$TOP" | awk '/averages:/ {gsub(",", ""); print $6":"$7":"$8; exit}'`
		/usr/local/bin/rrdtool update "$FILE" N:"$valla" 2>> /tmp/rrdgraphs-error.log
	fi
fi
#	CPU temperatures
if [ "$RUN_TMP" -eq 1 ]; then
	FILE="${STORAGE_PATH}/rrd/cpu_temp.rrd"
	if [ ! -f "$FILE" ]; then
		/usr/local/bin/rrdtool create "$FILE" \
			-s 300 \
			'DS:core0:GAUGE:600:0:U' \
			'DS:core1:GAUGE:600:0:U' \
			'RRA:AVERAGE:0.5:1:576' \
			'RRA:AVERAGE:0.5:6:672' \
			'RRA:AVERAGE:0.5:24:732' \
			'RRA:AVERAGE:0.5:144:1460'
	fi
	if [ -f "$FILE" ]; then
		valct0=`sysctl -q -n dev.cpu.0.temperature | tr -d C`; # core 0 temperature
#		skip recording when sysctl doesn't return anything
		if [ -n "$valct0" ]; then
			valct1=`sysctl -q -n dev.cpu.1.temperature | tr -d C`; # core 1 temperature
			if [ -z "$valct1" ]; then
				valct1=0;
			fi
			/usr/local/bin/rrdtool update "$FILE" N:"$valct0":"$valct1" 2>> /tmp/rrdgraphs-error.log
		fi
	fi
fi
#	CPU frequency
if [ "$RUN_FRQ" -eq 1 ]; then
	FILE="${STORAGE_PATH}/rrd/cpu_freq.rrd"
	if [ ! -f "$FILE" ]; then
		/usr/local/bin/rrdtool create "$FILE" \
			-s 300 \
			'DS:core0:GAUGE:600:0:U' \
			'DS:core1:GAUGE:600:0:U' \
			'RRA:AVERAGE:0.5:1:576' \
			'RRA:AVERAGE:0.5:6:672' \
			'RRA:AVERAGE:0.5:24:732' \
			'RRA:AVERAGE:0.5:144:1460'
	fi
	if [ -f "$FILE" ]; then
		valcf0=`sysctl -q -n dev.cpu.0.freq`;
#		skip recording when sysctl doesn't return anything
		if [ -n "$valcf0" ]; then
			valcf1=`sysctl -q -n dev.cpu.1.freq`;
			if [ -z "$valcf1" ]; then
				valcf1=0;
			fi
			/usr/local/bin/rrdtool update "$FILE" N:"$valcf0":"$valcf1" 2>> /tmp/rrdgraphs-error.log
		fi
	fi
fi
#	Processes
if [ "$RUN_PRO" -eq 1 ]; then
	FILE="${STORAGE_PATH}/rrd/processes.rrd"
	if [ ! -f "$FILE" ]; then
		/usr/local/bin/rrdtool create "$FILE" \
			-s 300 \
			'DS:total:GAUGE:600:U:U' \
			'DS:running:GAUGE:600:U:U' \
			'DS:sleeping:GAUGE:600:U:U' \
			'DS:waiting:GAUGE:600:U:U' \
			'DS:starting:GAUGE:600:U:U' \
			'DS:stopped:GAUGE:600:U:U' \
			'DS:zombie:GAUGE:600:U:U' \
			'RRA:AVERAGE:0.5:1:576' \
			'RRA:AVERAGE:0.5:6:672' \
			'RRA:AVERAGE:0.5:24:732' \
			'RRA:AVERAGE:0.5:144:1460'
	fi
	if [ -f "$FILE" ]; then
		valnp=`echo -e "$TOP" | awk '/processes:/ {gsub("[:,]", ""); print $2" "$1"  "$4" "$3"  "$6" "$5"  "$8" "$7"  "$10" "$9"  "$12" "$11"  "$14" "$13; exit}'`
		CREATE_PVARS ${valnp}
		/usr/local/bin/rrdtool update "$FILE" N:"$total":"$running":"$sleeping":"$waiting":"$starting":"$stopped":"$zombie" 2>> /tmp/rrdgraphs-error.log
	fi
fi
#	CPU usage
if [ "$RUN_CPU" -eq 1 ]; then
	FILE="${STORAGE_PATH}/rrd/cpu.rrd"
	if [ ! -f "$FILE" ]; then
		/usr/local/bin/rrdtool create "$FILE" \
			-s 300 \
			'DS:user:GAUGE:600:U:U' \
			'DS:nice:GAUGE:600:U:U' \
			'DS:system:GAUGE:600:U:U' \
			'DS:interrupt:GAUGE:600:U:U' \
			'DS:idle:GAUGE:600:U:U' \
			'RRA:AVERAGE:0.5:1:576' \
			'RRA:AVERAGE:0.5:6:672' \
			'RRA:AVERAGE:0.5:24:732' \
			'RRA:AVERAGE:0.5:144:1460'
	fi
	if [ -f "$FILE" ]; then
		valcu=`echo -e "$TOP" | awk '/CPU:/ {gsub("[%,]", ""); print $3" "$2" "$5" "$4" "$7" "$6" "$9" "$8" "$11" "$10; exit}'`
		CREATE_CVARS ${valcu}
		/usr/local/bin/rrdtool update "$FILE" N:"$user":"$nice":"$system":"$interrupt":"$idle" 2>> /tmp/rrdgraphs-error.log
	fi
fi
#	Disk usage
if [ "$RUN_DUS" -eq 1 ]; then
	DA=`df -h --libxo:X | /usr/local/bin/xml sel --text --template --match "//filesystem[starts-with(mounted-on,'/mnt/') and not(contains(mounted-on,'jail')) and not(contains(name,'jail'))]" --value-of "concat(str:replace(mounted-on,'/mnt/',''),'&#09;',used/@value,'&#09;',available/@value)" --nl --break | /usr/local/bin/xml unesc`
	if [ ! -z "${DA}" ]; then
		echo "${DA}" | while IFS=$'\t' read -r -a ONE_ROW ; do CREATE_MOUNTS_CMD "${ONE_ROW[0]}" "${ONE_ROW[1]}" "${ONE_ROW[2]}" ; done
	fi
	DA=`zfs list -H -p -t filesystem -o name,used,available`
	if [ ! -z "${DA}" ]; then
		echo "${DA}" | while IFS=$'\t' read -r -a ONE_ROW ; do CREATE_POOLS_CMD "${ONE_ROW[0]}" "${ONE_ROW[1]}" "${ONE_ROW[2]}" ; done
	fi
fi
#	Memory
if [ "$RUN_MEM" -eq 1 ]; then
	FILE="${STORAGE_PATH}/rrd/memory.rrd"
	if [ ! -f "$FILE" ]; then
		/usr/local/bin/rrdtool create "$FILE" \
			-s 300 \
			'DS:active:GAUGE:600:U:U' \
			'DS:inact:GAUGE:600:U:U' \
			'DS:wired:GAUGE:600:U:U' \
			'DS:cache:GAUGE:600:U:U' \
			'DS:buf:GAUGE:600:U:U' \
			'DS:free:GAUGE:600:U:U' \
			'DS:total:GAUGE:600:U:U' \
			'DS:used:GAUGE:600:U:U' \
			'RRA:AVERAGE:0.5:1:576' \
			'RRA:AVERAGE:0.5:6:672' \
			'RRA:AVERAGE:0.5:24:732' \
			'RRA:AVERAGE:0.5:144:1460'
	fi
	if [ -f "$FILE" ]; then
		valm=( `sysctl -q -n \
			vm.stats.vm.v_page_size \
			vm.stats.vm.v_active_count \
			vm.stats.vm.v_inactive_count \
			vm.stats.vm.v_wire_count \
			vm.stats.vm.v_cache_count \
			vm.stats.vm.v_free_count \
			vfs.bufspace` )
		valm[1]=$(( valm[0] * valm[1]  ))
		valm[2]=$(( valm[0] * valm[2]  ))
		valm[3]=$(( valm[0] * valm[3]  ))
		valm[4]=$(( valm[0] * valm[4]  ))
		valm[5]=$(( valm[0] * valm[5]  ))
		vals=( `swapctl -k -s | awk '/Total:/ { print $2 * 1024,$3 * 1024 }'` )
		/usr/local/bin/rrdtool update "$FILE" N:"${valm[1]}":"${valm[2]}":"${valm[3]}":"${valm[4]}":"${valm[6]}":"${valm[5]}":"${vals[0]}":"${vals[1]}" 2>> /tmp/rrdgraphs-error.log
	fi
fi
#	ZFS ARC
if [ "$RUN_ARC" -eq 1 ]; then
	FILE="${STORAGE_PATH}/rrd/zfs_arc.rrd"
	if [ ! -f "$FILE" ]; then
		/usr/local/bin/rrdtool create "$FILE" \
			-s 300 \
			'DS:Total:GAUGE:600:U:U' \
			'DS:MFU:GAUGE:600:U:U' \
			'DS:MRU:GAUGE:600:U:U' \
			'DS:Anon:GAUGE:600:U:U' \
			'DS:Header:GAUGE:600:U:U' \
			'DS:Other:GAUGE:600:U:U' \
			'RRA:AVERAGE:0.5:1:576' \
			'RRA:AVERAGE:0.5:6:672' \
			'RRA:AVERAGE:0.5:24:732' \
			'RRA:AVERAGE:0.5:144:1460'
	fi
	if [ -f "$FILE" ]; then
		valarc=( `sysctl -q -n \
			kstat.zfs.misc.arcstats.size \
			kstat.zfs.misc.arcstats.mfu_size \
			kstat.zfs.misc.arcstats.mru_size \
			kstat.zfs.misc.arcstats.anon_size \
			kstat.zfs.misc.arcstats.hdr_size \
			kstat.zfs.misc.arcstats.other_size` )
		/usr/local/bin/rrdtool update "$FILE" N:"${valarc[0]}":"${valarc[1]}":"${valarc[2]}":"${valarc[3]}":"${valarc[4]}":"${valarc[5]}" 2>> /tmp/rrdgraphs-error.log
	fi
fi
#	ZFS L2ARC
if [ "$RUN_L2ARC" -eq 1 ]; then
	FILE="${STORAGE_PATH}/rrd/zfs_l2arc.rrd"
	if [ ! -f "$FILE" ]; then
		/usr/local/bin/rrdtool create "$FILE" \
			-s 300 \
			'DS:L2_SIZE:GAUGE:600:U:U' \
			'DS:L2_ASIZE:GAUGE:600:U:U' \
			'RRA:AVERAGE:0.5:1:576' \
			'RRA:AVERAGE:0.5:6:672' \
			'RRA:AVERAGE:0.5:24:732' \
			'RRA:AVERAGE:0.5:144:1460'
	fi
	if [ -f "$FILE" ]; then
		vall2a=( `sysctl -q -n \
			kstat.zfs.misc.arcstats.l2_size \
			kstat.zfs.misc.arcstats.l2_asize` )
		/usr/local/bin/rrdtool update "$FILE" N:"${vall2a[0]}":"${vall2a[1]}" 2>> /tmp/rrdgraphs-error.log
	fi
fi
#	ZFS Cache Efficiency
if [ "$RUN_ARCEFF" -eq 1 ]; then
	FILE="${STORAGE_PATH}/rrd/zfs_arceff.rrd"
	if [ ! -f "$FILE" ]; then
		/usr/local/bin/rrdtool create "$FILE" \
			-s 300 \
			'DS:EFF_ARC:GAUGE:600:0:U' \
			'DS:EFF_DEMAND:GAUGE:600:0:U' \
			'DS:EFF_PREFETCH:GAUGE:600:0:U' \
			'DS:EFF_METADATA:GAUGE:600:0:U' \
			'DS:EFF_L2ARC:GAUGE:600:0:U' \
			'RRA:AVERAGE:0.5:1:576' \
			'RRA:AVERAGE:0.5:6:672' \
			'RRA:AVERAGE:0.5:24:732' \
			'RRA:AVERAGE:0.5:144:1460'
	fi
	if [ -f "$FILE" ]; then
		valahm=( `sysctl -q -n \
			kstat.zfs.misc.arcstats.hits \
			kstat.zfs.misc.arcstats.misses \
			kstat.zfs.misc.arcstats.demand_data_hits \
			kstat.zfs.misc.arcstats.demand_data_misses \
			kstat.zfs.misc.arcstats.demand_metadata_hits \
			kstat.zfs.misc.arcstats.demand_metadata_misses \
			kstat.zfs.misc.arcstats.prefetch_data_hits \
			kstat.zfs.misc.arcstats.prefetch_data_misses \
			kstat.zfs.misc.arcstats.prefetch_metadata_hits \
			kstat.zfs.misc.arcstats.prefetch_metadata_misses \
			kstat.zfs.misc.arcstats.l2_hits \
			kstat.zfs.misc.arcstats.l2_misses` )
		valaef=()
#		arc hits / misses
		valaef[0]=$( bc <<< "${valahm[0]}+${valahm[1]}" )
		if [ "${valaef[0]}" -gt 0 ]; then
			valaef[0]=$( bc <<< "scale=2;100*${valahm[0]}/${valaef[0]}" )
		else
			valaef[0]=0
		fi
#		demand hits / misses
		valaef[1]=$( bc <<< "${valahm[2]}+${valahm[3]}+${valahm[4]}+${valahm[5]}" )
		if [ "${valaef[1]}" -gt 0 ]; then
			valaef[1]=$( bc <<< "scale=2;100*(${valahm[2]}+${valahm[4]})/${valaef[1]}" )
		else
			valaef[1]=0
		fi
#		prefetch hits / misses
		valaef[2]=$( bc <<< "${valahm[6]}+${valahm[7]}+${valahm[8]}+${valahm[9]}" )
		if [ "${valaef[2]}" -gt 0 ]; then
			valaef[2]=$( bc <<< "scale=2;100*(${valahm[6]}+${valahm[8]})/${valaef[2]}" )
		else
			valaef[2]=0
		fi
#		metadata hits / misses
		valaef[3]=$( bc <<< "${valahm[4]}+${valahm[5]}+${valahm[8]}+${valahm[9]}" )
		if [ "${valaef[3]}" -gt 0 ]; then
			valaef[3]=$( bc <<< "scale=2;100*(${valahm[4]}+${valahm[8]})/${valaef[3]}" )
		else
			valaef[3]=0
		fi
#		l2arc hits / misses
		valaef[4]=$( bc <<< "${valahm[10]}+${valahm[11]}" )
		if [ "${valaef[4]}" -gt 0 ]; then
			valaef[4]=$( bc <<< "scale=2;100*${valahm[10]}/${valaef[4]}" )
		else
			valaef[4]=0
		fi
		/usr/local/bin/rrdtool update "$FILE" N:"${valaef[0]}":"${valaef[1]}":"${valaef[2]}":"${valaef[3]}":"${valaef[4]}" 2>> /tmp/rrdgraphs-error.log
	fi
fi
#	UPS
if [ "$RUN_UPS" -eq 1 ]; then
	FILE="${STORAGE_PATH}/rrd/ups.rrd"
	if [ ! -f "$FILE" ]; then
		/usr/local/bin/rrdtool create "$FILE" \
			-s 300 \
			'DS:charge:GAUGE:600:U:U' \
			'DS:load:GAUGE:600:U:U' \
			'DS:bvoltage:GAUGE:600:U:U' \
			'DS:ovoltage:GAUGE:600:U:U' \
			'DS:ivoltage:GAUGE:600:U:U' \
			'DS:runtime:GAUGE:600:U:U' \
			'DS:OL:GAUGE:600:U:U' \
			'DS:OF:GAUGE:600:U:U' \
			'DS:OB:GAUGE:600:U:U' \
			'DS:CG:GAUGE:600:U:U' \
			'RRA:AVERAGE:0.5:1:576' \
			'RRA:AVERAGE:0.5:6:672' \
			'RRA:AVERAGE:0.5:24:732' \
			'RRA:AVERAGE:0.5:144:1460'
	fi
	if [ -f "$FILE" ]; then
		CMD=`/usr/local/bin/upsc ${UPS_AT}`
		CREATE_UPSVARS ${CMD}
		/usr/local/bin/rrdtool update "$FILE" N:"$charge":"$load":"$bvoltage":"$ovoltage":"$ivoltage":"$runtime":"$OL":"$OF":"$OB":"$CG" 2>> /tmp/rrdgraphs-error.log
	fi
fi
#	Latency
if [ "$RUN_LAT" -eq 1 ]; then
	FILE="${STORAGE_PATH}/rrd/latency.rrd"
	if [ ! -f "$FILE" ]; then
		/usr/local/bin/rrdtool create "$FILE" \
			-s 300 \
			'DS:min:GAUGE:600:U:U' \
			'DS:avg:GAUGE:600:U:U' \
			'DS:max:GAUGE:600:U:U' \
			'DS:stddev:GAUGE:600:U:U' \
			'RRA:AVERAGE:0.5:1:576' \
			'RRA:AVERAGE:0.5:6:672' \
			'RRA:AVERAGE:0.5:24:732' \
			'RRA:AVERAGE:0.5:144:1460'
	fi
	if [ -f "$FILE" ]; then
		vallat=`ping $LATENCY_PARAMETERS -S $LATENCY_INTERFACE_IP -c $LATENCY_COUNT $LATENCY_HOST | awk '/round-trip/ {gsub("/", ":"); print $4}'`
		if [ "$vallat" == "" ]; then
			vallat="0:0:0:0"
		fi
		/usr/local/bin/rrdtool update "$FILE" N:"$vallat" 2>> /tmp/rrdgraphs-error.log
	fi
fi
#	Uptime
if [ "$RUN_UPT" -eq 1 ]; then
	FILE="${STORAGE_PATH}/rrd/uptime.rrd"
	if [ ! -f "$FILE" ]; then
		/usr/local/bin/rrdtool create "$FILE" \
			-s 300 \
			'DS:uptime:GAUGE:600:U:U' \
			'RRA:AVERAGE:0.5:1:576' \
			'RRA:AVERAGE:0.5:6:672' \
			'RRA:AVERAGE:0.5:24:732' \
			'RRA:AVERAGE:0.5:144:1460'
	fi
	if [ -f "$FILE" ]; then
		valupt=`echo -e "$TOP" | awk '/averages:/ {gsub("[,+:]", " "); print $10*24+$11+$12/60; exit}'`
		/usr/local/bin/rrdtool update "$FILE" N:"$valupt" 2>> /tmp/rrdgraphs-error.log
	fi
fi
#	push error log to syslog
if [ -f /tmp/rrdgraphs-error.log ]; then logger -f /tmp/rrdgraphs-error.log; rm /tmp/rrdgraphs-error.log; exit 1; fi
