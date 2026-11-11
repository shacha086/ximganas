<?php
/*
	disks_zfs_config_current.php

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

arr::make_branch($config,'zfs','pools');
arr::make_branch($config,'zfs','vdevices');
arr::make_branch($config,'zfs','datasets');
arr::make_branch($config,'zfs','volumes');
$zfs_info = $config['zfs'];
arr::make_branch($zfs_info,'pools','pool');
arr::make_branch($zfs_info,'vdevices','vdevice');
arr::make_branch($zfs_info,'datasets','dataset');
arr::make_branch($zfs_info,'volumes','volume');
//	add additional fields
foreach($zfs_info['pools']['pool'] as $index => $zfs_info_pool):
	$unknown = gettext('Unknown');
	$zfs_info['pools']['pool'][$index]['size'] = $unknown;
	$zfs_info['pools']['pool'][$index]['used'] = $unknown;
	$zfs_info['pools']['pool'][$index]['avail'] = $unknown;
	$zfs_info['pools']['pool'][$index]['expandsz'] = $unknown;
	$zfs_info['pools']['pool'][$index]['frag'] = $unknown;
	$zfs_info['pools']['pool'][$index]['dedup'] = $unknown;
	$zfs_info['pools']['pool'][$index]['health'] = $unknown;
	foreach($zfs_info_pool['vdevice'] as $vdevice):
		$index = arr::search_ex($vdevice,$zfs_info['vdevices']['vdevice'],'name');
		if($index !== false):
			$zfs_info['vdevices']['vdevice'][$index]['pool'] = $zfs_info_pool['name'];
		endif;
	endforeach;
endforeach;
//	collect information from pools via zfs command
unset($rawdata);
unset($exitstatus);
$cmd = 'zfs list -pH -t filesystem -o name,used,available';
mwexec2($cmd,$rawdata,$exitstatus);
if($exitstatus == 0):
	foreach($rawdata as $line):
		if($line == 'no datasets available'):
			continue;
		endif;
		[$fname,$used,$avail] = explode("\t",$line);
		$index = arr::search_ex($fname,$zfs_info['pools']['pool'],'name');
		if($index !== false):
			if(strpos($fname,'/') === false):
//				pool found
				$zfs_info['pools']['pool'][$index]['used'] = format_bytes($used,2,false,is_sidisksizevalues());
				$zfs_info['pools']['pool'][$index]['avail'] = format_bytes($avail,2,false,is_sidisksizevalues());
			endif;
		endif;
	endforeach;
endif;
//	collect information from pools via zpool command
unset($rawdata);
unset($exitstatus);
$cmd = 'zpool list -pH -o name,altroot,size,allocated,free,expandsz,frag,health,dedup';
mwexec2($cmd,$rawdata,$exitstatus);
if($exitstatus == 0):
	foreach($rawdata as $line):
		if($line == 'no pools available'):
			continue;
		endif;
		[$poolname,$root,$size,$alloc,$free,$expandsz,$frag,$health,$dedup] = explode("\t",$line);
		$index = arr::search_ex($poolname,$zfs_info['pools']['pool'],'name');
		if($index === false):
			continue;
		endif;
		unset($row);
		$row = &$zfs_info['pools']['pool'][$index];
		if($root != '-'):
			$row['root'] = $root;
		endif;
		$row['size'] = format_bytes($size,2,false,is_sidisksizevalues());
		$row['alloc'] = format_bytes($alloc,2,false,is_sidisksizevalues());
		$row['free'] = format_bytes($free,2,false,is_sidisksizevalues());
		$row['expandsz'] = $expandsz;
		$row['frag'] = $frag;
		$row['dedup'] = $dedup;
		$row['health'] = $health;
	endforeach;
endif;
unset($rawdata);
unset($exitstatus);
if(updatenotify_exists('zfs_import_config')):
	$notifications = updatenotify_get('zfs_import_config');
	$retval = 0;
	foreach($notifications as $notification):
		$retval |= !($notification['data'] == true);
	endforeach;
	$savemsg = get_std_save_message($retval);
	if($retval == 0):
		updatenotify_delete('zfs_import_config');
	endif;
endif;
$showusedavail = isset($config['zfs']['settings']['showusedavail']);
$pgtitle = [gettext('Disks'),gettext('ZFS'),gettext('Configuration'),gettext('Current')];
$document = new_page($pgtitle);
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
			ins_tabnav_record('disks_zfs_config_current.php',gettext('Current'),gettext('Reload page'),true)->
			ins_tabnav_record('disks_zfs_config.php',gettext('Detected'))->
			ins_tabnav_record('disks_zfs_config_sync.php',gettext('Synchronize'));
//	create data area
$content = $pagecontent->add_area_data();
//	display information, warnings and errors
$content->ins_info_box($savemsg);
$table = $content->add_table_data_selection();
$a_col_width = ['16%','10%','9%','9%','9%','9%','9%','9%','10%','10%'];
$n_col_width = count($a_col_width);
$table->
	ins_colgroup_with_styles('width',$a_col_width);
$thead = $table->addTHEAD();
$tbody = $table->addTBODY();
$tfoot = $table->addTFOOT();
$thead->
	ins_titleline(sprintf('%s (%d)',gettext('Pools'),count($zfs_info['pools']['pool'])),$n_col_width);
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
foreach($zfs_info['pools']['pool'] as $zfs_info_pool):
	$tr = $tbody->addTR();
	$tr->
		insTDwC('lcell',$zfs_info_pool['name'])->
		insTDwC('lcell',$zfs_info_pool['size']);
		if($showusedavail):
			$tr->
				insTDwC('lcell',$zfs_info_pool['used'])->
				insTDwC('lcell',$zfs_info_pool['avail']);
		else:
			$tr->
				insTDwC('lcell',$zfs_info_pool['alloc'])->
				insTDwC('lcell',$zfs_info_pool['free']);
		endif;
	$tr->
		insTDwC('lcell',$zfs_info_pool['expandsz'])->
		insTDwC('lcell',$zfs_info_pool['frag'])->
		insTDwC('lcell',$zfs_info_pool['dedup'])->
		insTDwC('lcell',$zfs_info_pool['health'])->
		insTDwC('lcell',empty($zfs_info_pool['mountpoint']) ? sprintf('/mnt/%s',$zfs_info_pool['name']) : $zfs_info_pool['mountpoint'])->
		insTDwC('lcebl',empty($zfs_info_pool['root']) ? '-' : $zfs_info_pool['root']);
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
	ins_titleline(sprintf('%s (%d)',gettext('Virtual Devices'),count($zfs_info['vdevices']['vdevice'])),$n_col_width);
$thead->
	addTR()->
		insTHwC('lhell',gettext('Name'))->
		insTHwC('lhell',gettext('Type'))->
		insTHwC('lhell',gettext('Pool'))->
		insTHwC('lhebl',gettext('Devices'));
foreach($zfs_info['vdevices']['vdevice'] as $vdevice):
	$tbody->
		addTR()->
			insTDwC('lcell',$vdevice['name'])->
			insTDwC('lcell',$vdevice['type'])->
			insTDwC('lcell',!empty($vdevice['pool']) ? $vdevice['pool'] : '')->
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
	ins_titleline(sprintf('%s (%d)',gettext('Datasets'),count($zfs_info['datasets']['dataset'])),$n_col_width);
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
foreach($zfs_info['datasets']['dataset'] as $zfs_info_filesystem):
	$tbody->
		addTR()->
			insTDwC('lcell',$zfs_info_filesystem['name'])->
			insTDwC('lcell',$zfs_info_filesystem['pool'][0])->
			insTDwC('lcell',$zfs_info_filesystem['compression'])->
			insTDwC('lcell',$zfs_info_filesystem['dedup'])->
			insTDwC('lcell',$zfs_info_filesystem['sync'])->
			insTDwC('lcell',$zfs_info_filesystem['aclinherit'])->
			insTDwC('lcell',$zfs_info_filesystem['aclmode'])->
			insTDwC('lcell',isset($zfs_info_filesystem['canmount']) ? gettext('on') : gettext('off'))->
			insTDwC('lcell',empty($zfs_info_filesystem['quota']) ? gettext('none') : $zfs_info_filesystem['quota'])->
			insTDwC('lcell',isset($zfs_info_filesystem['readonly']) ? gettext('on') : gettext('off'))->
			insTDwC('lcebl',isset($zfs_info_filesystem['snapdir']) ? gettext('visible') : gettext('hidden'));
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
	ins_titleline(sprintf('%s (%d)',gettext('Volumes'),count($zfs_info['volumes']['volume'])),$n_col_width);
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
foreach($zfs_info['volumes']['volume'] as $zfs_info_volume):
	$tbody->
		addTR()->
			insTDwC('lcell',$zfs_info_volume['name'])->
			insTDwC('lcell',$zfs_info_volume['pool'][0])->
			insTDwC('lcell',$zfs_info_volume['volsize'])->
			insTDwC('lcell',!empty($zfs_info_volume['volblocksize']) ? $zfs_info_volume['volblocksize'] : '-')->
			insTDwC('lcell',!isset($zfs_info_volume['sparse']) ? '-' : gettext('on'))->
			insTDwC('lcell',$zfs_info_volume['compression'])->
			insTDwC('lcell',$zfs_info_volume['dedup'])->
			insTDwC('lcebl',$zfs_info_volume['sync']);
endforeach;
$content->
	add_area_remarks()->
		ins_remark('note',gettext('Note'),gettext('This page reflects the configuration that has been created with the WebGUI.'));
$document->render();
