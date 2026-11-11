<?php
/*
	disks_zfs_config.php

	Part of XigmaNAS® (https://www.xigmanas.com).
	Copyright © 2018-2025 XigmaNAS® <info@xigmanas.com>.
	All rights reserved.

	Redistribution and use in source and binary forms, with or without
	modification, are permitted provided that the following conditions are met:

	1. Redistributions of source code must retain the above copyright notice, this
	   list of conditions and the following disclaimer.

	2. Redistributions in binary form must reproduce the above copyright notice,
	   this list of conditions and the following disclaimer in the documentation
	   and/or other materials provided with the distribution.

	THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
	ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
	WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
	DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR
	ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
	(INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
	LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
	ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
	(INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
	SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.

	The views and conclusions contained in the software and documentation are those
	of the authors and should not be interpreted as representing official policies
	of XigmaNAS®, either expressed or implied.
*/

require_once 'autoload.php';
require_once 'auth.inc';
require_once 'guiconfig.inc';

use common\arr;
use common\uuid;

$use_si = is_sidisksizevalues();
$zfs = [
	'vdevices' => ['vdevice' => []],
	'pools' => ['pool' => []],
	'datasets' => ['dataset' => []],
	'volumes' => ['volume' => []],
];
if(isset($_POST['import']) or isset($_POST['import_force'])):
	$param = [];
	$param['cmd'] = 'zpool';
	$param['sub'] = 'import';
	$param['gpt'] = is_dir('/dev/gpt') ? '-d /dev/gpt' : null;
	$param['gptid'] = is_dir('/dev/gptid') ? '-d /dev/gptid' : null;
	$param['dev'] = is_dir('/dev') ? '-d /dev' : null;
	$param['all'] = '-a';
	$param['force'] = isset($_POST['import_force']) ? '-f' : null;
	$cmd = implode(' ',array_filter($param));
	$retval = mwexec($cmd);
//	remove existing pool cache
	conf_mount_rw();
	unlink_if_exists("{$g['cf_path']}/boot/zfs/zpool.cache");
	conf_mount_ro();
endif;
$cmd = 'zfs list -H -t filesystem -o name,mountpoint,compression,canmount,quota,used,available,xattr,snapdir,readonly,origin,reservation,dedup,sync,atime,aclinherit,aclmode,primarycache,secondarycache';
unset($rawdata);
unset($retval);
mwexec2($cmd,$rawdata,$retval);
if($retval == 0):
	foreach($rawdata as $line):
		if($line == 'no datasets available'):
			continue;
		endif;
		list($fname,$mpoint,$compress,$canmount,$quota,$used,$avail,$xattr,$snapdir,$readonly,$origin,$reservation,$dedup,$sync,$atime,$aclinherit,$aclmode,$primarycache,$secondarycache) = explode("\t",$line);
		if(strpos($fname,'/') !== false): // dataset
			if(empty($origin) || $origin != '-'):
				continue;
			endif;
			[$pool,$name] = explode('/',$fname,2);
			$zfs['datasets']['dataset'][$fname] = [
				'identifier' => $fname,
				'uuid' => uuid::create_v4(),
				'name' => $name,
				'pool' => $pool,
				'compression' => $compress,
				'canmount' => ($canmount == 'on') ? null : $canmount,
				'quota' => ($quota == 'none') ? null : $quota,
				'reservation' => ($reservation == 'none') ? null : $reservation,
				'xattr' => ($xattr == 'on'),
				'snapdir' => ($snapdir == 'visible'),
				'readonly' => ($readonly == 'on'),
				'dedup' => $dedup,
				'sync' => $sync,
				'atime' => $atime,
				'aclinherit' => $aclinherit,
				'aclmode' => $aclmode,
				'primarycache' => $primarycache,
				'secondarycache' => $secondarycache,
				'desc' => '',
			];
			$mp_owner = 'root';
			$mp_group = 'wheel';
			$mp_mode = 0777;
			if($canmount == 'on' && !empty($mpoint) && file_exists($mpoint)):
				$mp_uid = fileowner($mpoint);
				$mp_gid = filegroup($mpoint);
				$mp_perm = (fileperms($mpoint) & 0777);
				$tmp = posix_getpwuid($mp_uid);
				if(!empty($tmp) && !empty($tmp['name'])):
					$mp_owner = $tmp['name'];
				endif;
				$tmp = posix_getgrgid($mp_gid);
				if(!empty($tmp) && !empty($tmp['name'])):
					$mp_group = $tmp['name'];
				endif;
				$mp_mode = sprintf("0%o",$mp_perm);
			endif;
			$zfs['datasets']['dataset'][$fname]['accessrestrictions'] = [
				'owner' => $mp_owner,
				'group' => $mp_group,
				'mode' => $mp_mode,
			];
		else: // zpool
			$zfs['pools']['pool'][$fname] = [
				'uuid' => uuid::create_v4(),
				'name' => $fname,
				'vdevice' => [],
				'root' => null,
				'mountpoint' => ($mpoint == "/mnt/{$fname}") ? null : $mpoint,
				'desc' => '',
			];
			$zfs['extra']['pools']['pool'][$fname] = [
				'size' => null,
				'used' => $used,
				'avail' => $avail,
				'cap' => null,
				'health' => null,
			];
		endif;
	endforeach;
endif;
$cmd = 'zfs list -pH -t volume -o name,volsize,volblocksize,compression,origin,dedup,sync,refreservation';
unset($rawdata);
unset($retval);
mwexec2($cmd,$rawdata,$retval);
if($retval == 0):
	foreach($rawdata as $line):
		if($line == 'no datasets available'):
			continue;
		endif;
		list($fname,$volsize,$volblocksize,$compress,$origin,$dedup,$sync,$refreservation) = explode("\t",$line);
		if(strpos($fname,'/') !== false): // volume
			if(empty($origin) || $origin != '-'):
				continue;
			endif;
			[$pool,$name] = explode('/',$fname,2);
			$zfs['volumes']['volume'][$fname] = [
				'identifier' => $fname,
				'uuid' => uuid::create_v4(),
				'name' => $name,
				'pool' => $pool,
				'volsize' => format_bytes($volsize,2,false,$use_si),
				'volblocksize' => format_bytes($volblocksize,0,false,false),
				'compression' => $compress,
				'dedup' => $dedup,
				'sync' => $sync,
				'sparse' => ($refreservation == 'none'),
				'desc' => '',
			];
		endif;
	endforeach;
endif;
$cmd = 'zpool list -pH -o name,altroot,size,allocated,free,capacity,expandsz,frag,health,dedup';
unset($rawdata);
unset($retval);
mwexec2($cmd,$rawdata,$retval);
if($retval == 0):
	foreach($rawdata as $line):
		if($line == 'no pools available'):
			continue;
		endif;
		list($pool,$root,$size,$alloc,$free,$cap,$expandsz,$frag,$health,$dedup) = explode("\t",$line);
		if ($root != '-'):
			$zfs['pools']['pool'][$pool]['root'] = $root;
		endif;
		$zfs['extra']['pools']['pool'][$pool]['size'] = format_bytes($size,2,false,$use_si);
		$zfs['extra']['pools']['pool'][$pool]['alloc'] = format_bytes($alloc,2,false,$use_si);
		$zfs['extra']['pools']['pool'][$pool]['free'] = format_bytes($free,2,false,$use_si);
		$zfs['extra']['pools']['pool'][$pool]['expandsz'] = $expandsz;
		$zfs['extra']['pools']['pool'][$pool]['frag'] = $frag;
		$zfs['extra']['pools']['pool'][$pool]['cap'] = sprintf('%d%%',$cap);
		$zfs['extra']['pools']['pool'][$pool]['health'] = $health;
		$zfs['extra']['pools']['pool'][$pool]['dedup'] = $dedup;
	endforeach;
endif;
//	get all pool names, sorted by length, descending
$poolnames_sorted_by_length = array_keys($zfs['pools']['pool']);
usort($poolnames_sorted_by_length,function($element1,$element2) {
    return mb_strlen($element2) <=> mb_strlen($element1);
});
$pool = null;
$vdev = null;
$type = null;
$i = 0;
$vdev_type = array('mirror','raidz1','raidz2','raidz3','draid1','draid2','draid3');
$cmd = 'zpool status';
unset($rawdata);
mwexec2($cmd,$rawdata);
foreach($rawdata as $line):
	if(empty($line[0]) || $line[0] != "\t"):
		continue;
	endif;
	if(!is_null($vdev) && preg_match('/^\t    (\S+)/',$line,$m)): // dev
		$dev = $m[1];
		if(preg_match("/^(.+)\.nop$/",$dev,$m)):
			$zfs['vdevices']['vdevice'][$vdev]['device'][] = "/dev/{$m[1]}";
			$zfs['vdevices']['vdevice'][$vdev]['aft4k'] = true;
		elseif(preg_match("/^(.+)\.eli$/",$dev,$m)):
			//$zfs['vdevices']['vdevice'][$vdev]['device'][] = "/dev/{$m[1]}";
			$zfs['vdevices']['vdevice'][$vdev]['device'][] = "/dev/{$dev}";
		else:
			$zfs['vdevices']['vdevice'][$vdev]['device'][] = "/dev/{$dev}";
		endif;
	elseif(!is_null($pool) && preg_match('/^\t  (\S+)/',$line,$m)): // vdev or dev (type disk)
		$is_vdev_type = true;
		if($type == 'spare'): // disk in vdev type spares
			$dev = $m[1];
		elseif($type == 'cache'):
			$dev = $m[1];
		elseif($type == 'log'):
			$dev = $m[1];
			if(preg_match("/^mirror-([0-9]+)$/",$dev,$m)):
				$type = "log-mirror";
			endif;
		else: // vdev or dev (type disk)
			$type = $m[1];
			if(preg_match("/^(.*)\-\d+$/",$type,$m)):
				$tmp = $m[1];
				$is_vdev_type = in_array($tmp,$vdev_type);
				if($is_vdev_type):
					$type = $tmp;
				endif;
			else:
				$is_vdev_type = in_array($type,$vdev_type);
			endif;
			if(!$is_vdev_type): // type disk
				$dev = $type;
				$type = 'disk';
				$vdev = sprintf("%s_%s_%d",$pool,$type,$i++);
			else: // vdev
				$vdev = sprintf("%s_%s_%d",$pool,$type,$i++);
			endif;
		endif;
		if(!array_key_exists($vdev,$zfs['vdevices']['vdevice'])):
			$zfs['vdevices']['vdevice'][$vdev] = [
				'uuid' => uuid::create_v4(),
				'name' => $vdev,
				'type' => $type,
				'device' => [],
				'desc' => '',
			];
			$zfs['extra']['vdevices']['vdevice'][$vdev]['pool'] = $pool;
			$zfs['pools']['pool'][$pool]['vdevice'][] = $vdev;
		endif;
		if($type == 'spare' || $type == 'cache' || $type == 'log' || $type == 'disk'):
			if(preg_match("/^(.+)\.nop$/",$dev,$m)):
				$zfs['vdevices']['vdevice'][$vdev]['device'][] = "/dev/{$m[1]}";
				$zfs['vdevices']['vdevice'][$vdev]['aft4k'] = true;
			elseif(preg_match("/^(.+)\.eli$/",$dev,$m)):
				//$zfs['vdevices']['vdevice'][$vdev]['device'][] = "/dev/{$m[1]}";
				$zfs['vdevices']['vdevice'][$vdev]['device'][] = "/dev/{$dev}";
			else:
				$zfs['vdevices']['vdevice'][$vdev]['device'][] = "/dev/{$dev}";
			endif;
		endif;
	elseif(preg_match('/^\t(\S+)/',$line,$m)): // zpool or spares
		$vdev = null;
		$type = null;
		switch($m[1]):
			case 'spares':
				$type = 'spare';
				$vdev = sprintf("%s_%s_%d",$pool,$type,$i++);
				break;
			case 'cache':
				$type = 'cache';
				$vdev = sprintf("%s_%s_%d",$pool,$type,$i++);
				break;
			case 'logs':
				$type = 'log';
				$vdev = sprintf("%s_%s_%d",$pool,$type,$i++);
				break;
			default:
//				search for the longest match because of whitespaces
				foreach($poolnames_sorted_by_length as $poolname_to_match):
					if(preg_match('/^\t' . preg_quote($poolname_to_match) . '/',$line) === 1):
						$pool = $poolname_to_match;
						break;
					endif;
				endforeach;
				break;
		endswitch;
	endif;
endforeach;
$show_button = 'none';
if(count($zfs['pools']['pool']) <= 0):
	if(isset($_POST['import_force'])):
		if(isset($retval) && $retval != 0):
			$message_box_type = 'error';
			$message_box_text = gettext('No pool was found.');
		endif;
	elseif(isset($_POST['import'])):
		if(isset($retval) && $retval != 0):
			$show_button = 'import_force';
			$message_box_type = 'warning';
			$message_box_text = gettext('No pool was found. Try to force import from on-disk ZFS configuration.');
		endif;
	else:
		$show_button = 'import';
		$message_box_type = 'info';
		$message_box_text = gettext('No pool was found. Try to import from on-disk ZFS configuration.');
	endif;
endif;
$health = true;
if(!empty($zfs['extra']) && !empty($zfs['extra']['pools']) && !empty($zfs['extra']['pools']['pool'])):
	$health &= (bool)!arr::search_ex('DEGRADED',$zfs['extra']['pools']['pool'],'health');
	$health &= (bool)!arr::search_ex('FAULTED',$zfs['extra']['pools']['pool'],'health');
endif;
if(!$health):
	$message_box_type = 'warning';
	$message_box_text = gettext('Your ZFS system is not healthy.');
endif;
$showusedavail = isset($config['zfs']['settings']['showusedavail']);
$pgtitle = [gettext('Disks'),gettext('ZFS'),gettext('Configuration'),gettext('Detected')];
switch($show_button):
	case 'import':
	case 'import_force':
		$document = new_page(page_title: $pgtitle,action_url: 'disks_zfs_config.php');
		break;
	default:
		$document = new_page(page_title: $pgtitle);
		break;
endswitch;
//	get areas
$body = $document->getElementById('main');
$pagecontent = $document->getElementById('pagecontent');
//	add tab navigation
$document->
	add_area_tabnav()->
		push()->
		add_tabnav_upper()->
			ins_tabnav_record('disks_zfs_zpool.php',gettext('Pools'))->
			ins_tabnav_record('disks_zfs_dataset.php',gettext('Datasets'))->
			ins_tabnav_record('disks_zfs_volume.php',gettext('Volumes'))->
			ins_tabnav_record('disks_zfs_snapshot.php',gettext('Snapshots'))->
			ins_tabnav_record('disks_zfs_scheduler_snapshot_create.php',gettext('Scheduler'))->
			ins_tabnav_record('disks_zfs_config.php',gettext('Configuration'),gettext('Reload page'),true)->
			ins_tabnav_record('disks_zfs_settings.php',gettext('Settings'))->
		pop()->
		add_tabnav_lower()->
			ins_tabnav_record('disks_zfs_config_current.php',gettext('Current'))->
			ins_tabnav_record('disks_zfs_config.php',gettext('Detected'),gettext('Reload page'),true)->
			ins_tabnav_record('disks_zfs_config_sync.php',gettext('Synchronize'));
//	create data area
$content = $pagecontent->add_area_data();
//	display information, warnings and errors
if(!empty($message_box_text)):
	switch($message_box_type):
		case 'info':
			$content->ins_info_box(message: $message_box_text);
			break;
		case 'warning':
			$content->ins_warning_box(message: $message_box_text);
			break;
		case 'error':
			$content->ins_error_box(message: $message_box_text);
			break;
	endswitch;
endif;
$table = $content->add_table_data_selection();
$a_col_width = ['16%','10%','9%','9%','9%','9%','9%','9%','10%','10%'];
$n_col_width = count($a_col_width);
$table->
	ins_colgroup_with_styles('width',$a_col_width);
$thead = $table->addTHEAD();
$tbody = $table->addTBODY();
$tfoot = $table->addTFOOT();
$thead->
	ins_titleline(sprintf('%s (%d)',gettext('Pools'),count($zfs['pools']['pool'])),$n_col_width);
$tr = $thead->addTR();
$tr->
	insTHwC('lhell',gettext('Name'))->
	insTHwC('lhell',gettext('Size'));
if($showusedavail):
	$tr->
		insTHwC('lhell',gettext('Used'))->
		insTHwC('lhell',gettext('Avail'));
else:
	$tr->
		insTHwC('lhell',gettext('Alloc'))->
		insTHwC('lhell',gettext('Free'));
endif;
$tr->
	insTHwC('lhell',gettext('Expandsz'))->
	insTHwC('lhell',gettext('Frag'))->
	insTHwC('lhell',gettext('Dedup'))->
	insTHwC('lhell',gettext('Health'))->
	insTHwC('lhell',gettext('Mount Point'))->
	insTHwC('lhebl',gettext('AltRoot'));
foreach($zfs['pools']['pool'] as $key => $pool):
	$tr = $tbody->addTR();
	$tr->
		insTDwC('lcell',$pool['name'])->
		insTDwC('lcell',$zfs['extra']['pools']['pool'][$key]['size']);
		if($showusedavail):
			$tr->
				insTDwC('lcell',$zfs['extra']['pools']['pool'][$key]['used'])->
				insTDwC('lcell',$zfs['extra']['pools']['pool'][$key]['avail']);
		else:
			$tr->
				insTDwC('lcell',sprintf('%s (%s)',$zfs['extra']['pools']['pool'][$key]['alloc'],$zfs['extra']['pools']['pool'][$key]['cap']))->
				insTDwC('lcell',$zfs['extra']['pools']['pool'][$key]['free']);
		endif;
	$tr->
		insTDwC('lcell',$zfs['extra']['pools']['pool'][$key]['expandsz'])->
		insTDwC('lcell',$zfs['extra']['pools']['pool'][$key]['frag'])->
		insTDwC('lcell',$zfs['extra']['pools']['pool'][$key]['dedup'])->
		insTDwC('lcell',$zfs['extra']['pools']['pool'][$key]['health'])->
		insTDwC('lcell',empty($pool['mountpoint']) ? sprintf('/mnt/%s',$pool['name']) : $pool['mountpoint'])->
		insTDwC('lcebl',empty($pool['root']) ? '-' : $pool['root']);
endforeach;
$tfoot->
	addTR()->
		addTD(['class' => 'lcenl','colspan' => $n_col_width]);
$table = $content->add_table_data_selection();
$a_col_width = ['16%','21%','21%','42%'];
$n_col_width = count($a_col_width);
$table->
	ins_colgroup_with_styles('width',$a_col_width);
$thead = $table->addTHEAD();
$tbody = $table->addTBODY();
$tfoot = $table->addTFOOT();
$thead->
	ins_titleline(sprintf('%s (%d)',gettext('Virtual Devices'),count($zfs['vdevices']['vdevice'])),$n_col_width);
$thead->
	addTR()->
		insTHwC('lhell',gettext('Name'))->
		insTHwC('lhell',gettext('Type'))->
		insTHwC('lhell',gettext('Pool'))->
		insTHwC('lhebl',gettext('Devices'));
foreach($zfs['vdevices']['vdevice'] as $key => $vdevice):
	$tbody->
		addTR()->
			insTDwC('lcell',$vdevice['name'])->
			insTDwC('lcell',$vdevice['type'])->
			insTDwC('lcell',$zfs['extra']['vdevices']['vdevice'][$key]['pool'])->
			insTDwC('lcebl',implode(',',$vdevice['device']));
endforeach;
$tfoot->
	addTR()->
		addTD(['class' => 'lcenl','colspan' => $n_col_width]);
$table = $content->add_table_data_selection();
$a_col_width = ['14%','14%','7%','7%','9%','9%','9%','7%','8%','7%','9%'];
$n_col_width = count($a_col_width);
$table->
	ins_colgroup_with_styles('width',$a_col_width);
$thead = $table->addTHEAD();
$thead->
	ins_titleline(sprintf('%s (%d)',gettext('Datasets'),count($zfs['datasets']['dataset'])),$n_col_width);
$thead->
	addTR()->
		insTHwC('lhell',gettext('Name'))->
		insTHwC('lhell',gettext('Pool'))->
		insTHwC('lhell',gettext('Compression'))->
		insTHwC('lhell',gettext('Dedup'))->
		insTHwC('lhell',gettext('Sync'))->
		insTHwC('lhell',gettext('ACL Inherit'))->
		insTHwC('lhell',gettext('ACL Mode'))->
		insTHwC('lhell',gettext('Canmount'))->
		insTHwC('lhell',gettext('Quota'))->
		insTHwC('lhell',gettext('Readonly'))->
		insTHwC('lhebl',gettext('Snapshot Visibility'));
$tbody = $table->addTBODY();
foreach($zfs['datasets']['dataset'] as $dataset):
	$tbody->
		addTR()->
			insTDwC('lcell',$dataset['name'])->
			insTDwC('lcell',$dataset['pool'])->
			insTDwC('lcell',$dataset['compression'])->
			insTDwC('lcell',$dataset['dedup'])->
			insTDwC('lcell',$dataset['sync'])->
			insTDwC('lcell',$dataset['aclinherit'])->
			insTDwC('lcell',$dataset['aclmode'])->
			insTDwC('lcell',empty($dataset['canmount']) ? gettext('on') : gettext($dataset['canmount']))->
			insTDwC('lcell',empty($dataset['quota']) ? gettext('none') : $dataset['quota'])->
			insTDwC('lcell',empty($dataset['readonly']) ? gettext('off') : gettext('on'))->
			insTDwC('lcebl',empty($dataset['snapdir']) ? gettext('hidden') : gettext('visible'));
endforeach;
$tfoot = $table->addTFOOT();
$tfoot->
	addTR()->
		addTD(['class' => 'lcenl','colspan' => $n_col_width]);
$table = $content->add_table_data_selection();
$a_col_width = ['16%','12%','12%','12%','12%','12%','12%','12%'];
$n_col_width = count($a_col_width);
$table->
	ins_colgroup_with_styles('width',$a_col_width);
$thead = $table->addTHEAD();
$tbody = $table->addTBODY();
$thead->
	ins_titleline(sprintf('%s (%d)',gettext('Volumes'),count($zfs['volumes']['volume'])),$n_col_width);
$thead->
	addTR()->
		insTHwC('lhell',gettext('Name'))->
		insTHwC('lhell',gettext('Pool'))->
		insTHwC('lhell',gettext('Size'))->
		insTHwC('lhell',gettext('Blocksize'))->
		insTHwC('lhell',gettext('Sparse'))->
		insTHwC('lhell',gettext('Compression'))->
		insTHwC('lhell',gettext('Dedup'))->
		insTHwC('lhebl',gettext('Sync'));
foreach($zfs['volumes']['volume'] as $volume):
	$tbody->
		addTR()->
			insTDwC('lcell',$volume['name'])->
			insTDwC('lcell',$volume['pool'])->
			insTDwC('lcell',$volume['volsize'])->
			insTDwC('lcell',$volume['volblocksize'])->
			insTDwC('lcell',empty($volume['sparse']) ? '-' : gettext('on'))->
			insTDwC('lcell',$volume['compression'])->
			insTDwC('lcell',$volume['dedup'])->
			insTDwC('lcebl',$volume['sync']);
endforeach;
$content->
	add_area_remarks()->
		ins_remark('note',gettext('Note'),gettext('This page reflects the current system configuration. It may be different to the configuration which has been created with the WebGUI if changes has been done via command line'));
switch($show_button):
	case'import':
		$import_button_value = gettext('Import on-disk ZFS configuration');
		$document->
			add_area_buttons()->
				ins_button_submit(name: 'import',value: 'import',content: $import_button_value);
		break;
	case 'import_force':
		$import_button_value = gettext('Force import on-disk ZFS configuration');
		$document->
			add_area_buttons()->
				ins_button_submit(name: 'import_force',value: 'import_force',content: $import_button_value);
		break;
endswitch;
$document->render();
