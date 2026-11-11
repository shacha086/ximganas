<?php
/*
	disks_zfs_zpool_vdevice_edit.php

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
require_once 'co_geom_info.inc';

use common\arr;
use common\uuid;
use gui\document;

$sphere_scriptname = basename(__FILE__);
$sphere_header = 'Location: '.$sphere_scriptname;
$sphere_header_parent = 'Location: disks_zfs_zpool_vdevice.php';
$sphere_notifier = 'zfsvdev';
$sphere_array = [];
$sphere_record = [];
$checkbox_member_name = 'checkbox_member_array';
$checkbox_member_array = [];
$checkbox_member_record = [];
$gt_confirm_single = gtext('Do you want to create a virtual device from selected disk?');
$gt_confirm_stripe = gtext('Do you want to create a striped virtual device from selected disks?');
$gt_confirm_mirror = gtext('Do you want to create a mirrored virtual device from selected disks?');
$gt_confirm_raidz1 = gtext('Do you want to create a RAIDZ1 from selected disks?');
$gt_confirm_raidz2 = gtext('Do you want to create a RAIDZ2 from selected disks?');
$gt_confirm_raidz3 = gtext('Do you want to create a RAIDZ3 from selected disks?');
$gt_confirm_draid1 = gtext('Do you want to create a DRAID1 device from selected disk?');
$gt_confirm_draid2 = gtext('Do you want to create a DRAID2 device from selected disk?');
$gt_confirm_draid3 = gtext('Do you want to create a DRAID3 device from selected disk?');
$gt_confirm_spare = gtext('Do you want to create a hot spare device from selected disk?');
$gt_confirm_cache = gtext('Do you want to create a cache device from selected disks?');
$gt_confirm_log = gtext('Do you want to create a log device from selected disk?');
$gt_confirm_logmir = gtext('Do you want to create a mirrored log device from selected disks?');
$gt_confirm_dedup = gtext('Do you want to create a dedup device from selected disks?');
$gt_confirm_sac = gtext('Do you want to create a special allocation device from selected disks?');
$gt_record_loc = gtext('Virtual device is already in use.');
$gt_record_opn = gtext('Virtual device can be removed.');
$prerequisites_ok = true;
//	detect page mode
$mode_page = ($_POST) ? PAGE_MODE_POST : (($_GET) ? PAGE_MODE_EDIT : PAGE_MODE_ADD);
if($mode_page == PAGE_MODE_POST):
//	POST is Cancel
	if((isset($_POST['cancel']) && $_POST['cancel'])):
		header($sphere_header_parent);
		exit;
	endif;
endif;
if(($mode_page == PAGE_MODE_POST) && isset($_POST['uuid']) && uuid::is_v4($_POST['uuid'])):
	$sphere_record['uuid'] = $_POST['uuid'];
else:
	if(($mode_page == PAGE_MODE_EDIT) && isset($_GET['uuid']) && uuid::is_v4($_GET['uuid'])):
		$sphere_record['uuid'] = $_GET['uuid'];
	else:
//		Force ADD
		$mode_page = PAGE_MODE_ADD;
		$sphere_record['uuid'] = uuid::create_v4();
	endif;
endif;
$sphere_array = &arr::make_branch($config,'zfs','vdevices','vdevice');
if(empty($sphere_array)):
else:
	arr::sort_key($sphere_array,'name');
endif;
//	find index of uuid
$index = arr::search_ex($sphere_record['uuid'],$sphere_array,'uuid');
//	get updatenotify mode for uuid
$mode_updatenotify = updatenotify_get_mode($sphere_notifier,$sphere_record['uuid']);
$mode_record = RECORD_ERROR;
if($index !== false):
//	uuid found
	if(($mode_page == PAGE_MODE_POST) || ($mode_page == PAGE_MODE_EDIT)):
//		POST or EDIT
		switch ($mode_updatenotify):
			case UPDATENOTIFY_MODE_NEW:
				$mode_record = RECORD_NEW_MODIFY;
				break;
			case UPDATENOTIFY_MODE_MODIFIED:
			case UPDATENOTIFY_MODE_UNKNOWN:
				$mode_record = RECORD_MODIFY;
				break;
		endswitch;
	endif;
else:
//	uuid not found
	if(($mode_page == PAGE_MODE_POST) || ($mode_page == PAGE_MODE_ADD)):
		switch($mode_updatenotify):
			case UPDATENOTIFY_MODE_UNKNOWN:
				$mode_record = RECORD_NEW;
				break;
		endswitch;
	endif;
endif;
//	oops, something isn't right, over and out
if($mode_record == RECORD_ERROR):
	header($sphere_header_parent);
	exit;
endif;
$isrecordnew = ($mode_record === RECORD_NEW);
$isrecordnewmodify = ($mode_record == RECORD_NEW_MODIFY);
$isrecordmodify = ($mode_record === RECORD_MODIFY);
$isrecordnewornewmodify = ($isrecordnew || $isrecordnewmodify);
$a_disk = get_conf_disks_filtered_ex('fstype','zfs');
if($isrecordnewornewmodify && empty($a_disk) && empty($a_encrypteddisk)):
	$errormsg = gtext('No disks available.')
		. ' '
		. '<a href="' . 'disks_manage.php' . '">'
		. gtext('Please add a new disk first.')
		. '</a>';
	$prerequisites_ok = false;
endif;
$a_device = [];
foreach($a_disk as $r_disk):
	$helpinghand = $r_disk['devicespecialfile'] . (isset($r_disk['zfsgpt']) ? $r_disk['zfsgpt'] : '');
	$a_device[$helpinghand] = [
		'name' => $r_disk['name'] ?? '',
		'uuid' => $r_disk['uuid'] ?? '',
		'model' => $r_disk['model'] ?? '',
		'devicespecialfile' => $helpinghand ?? '',
		'partition' => ((isset($r_disk['zfsgpt']) && (!empty($r_disk['zfsgpt'])))? $r_disk['zfsgpt'] : gtext('Entire Device')),
		'controller' => sprintf('%s%s (%s)',$r_disk['controller'] ?? '',$r_disk['controller_id'] ?? '',$r_disk['controller_desc'] ?? ''),
		'size' => $r_disk['size'] ?? '',
		'serial' => $r_disk['serial'] ?? '',
		'desc' => $r_disk['desc'] ?? ''
	];
endforeach;
$o_geom = new co_geom_info();
$a_provider = $o_geom->get_provider();
if($mode_page === PAGE_MODE_POST):
	unset($input_errors);
	if(isset($_POST['submit']) && $_POST['submit']):
//		Submit is coming from Save button which is only shown when an existing vdevice is modified (RECORD_MODIFY)
		$sphere_record['name'] = $sphere_array[$index]['name'];
		$sphere_record['type'] = $sphere_array[$index]['type'];
		$sphere_record['device'] = $sphere_array[$index]['device'];
		$sphere_record['aft4k'] = isset($sphere_array[$index]['aft4k']);
		$sphere_record['desc'] = $_POST['desc'];
	elseif(isset($_POST['action']) && $_POST['action']):
//		RECORD_NEW or RECORD_NEW_MODIFY
		$sphere_record['name'] = $_POST['name'];
		$sphere_record['type'] = $_POST['action'];
		$sphere_record['device'] = $_POST[$checkbox_member_name] ?? [];
		$sphere_record['aft4k'] = isset($_POST['aft4k']);
		$sphere_record['desc'] = $_POST['desc'];
	endif;
//	Input validation
	$reqdfields = ['name','type'];
	$reqdfieldsn = [gtext('Name'),gtext('Type')];
	$reqdfieldst = ['string','string'];
	do_input_validation($sphere_record,$reqdfields,$reqdfieldsn,$input_errors);
	do_input_validation_type($sphere_record,$reqdfields,$reqdfieldsn,$reqdfieldst,$input_errors);
//	Check for duplicate name
	if($prerequisites_ok && empty($input_errors)):
		switch ($mode_record):
			case RECORD_NEW:
//				Error if name is found in the list of vdevice names
				if(arr::search_ex($sphere_record['name'],$sphere_array,'name') !== false):
					$input_errors[] = gtext('This virtual device name already exists.');
				endif;
				break;
			case RECORD_NEW_MODIFY:
//				Error if modified name is found in the list of vdevice names
				if($sphere_record['name'] !== $sphere_array[$index]['name']):
					if(arr::search_ex($sphere_record['name'],$sphere_array,'name') !== false):
						$input_errors[] = gtext('This virtual device name already exists.');
					endif;
				endif;
				break;
			case RECORD_MODIFY:
//				Error if name is changed, this error should never occur, just to cover all options
				if($sphere_record['name'] !== $sphere_array[$index]['name']):
					$input_errors[] = gtext('The name of this virtual device cannot be changed.');
				endif;
				break;
		endswitch;
	endif;
//	RECORD_NEW or RECORD_NEW_MODIFY
	if($prerequisites_ok && empty($input_errors)):
		if(isset($_POST['action'])):
			switch($_POST['action']):
				case 'log-mirror':
				case 'mirror':
					if(count($sphere_record['device']) <  2):
						$input_errors[] = gtext('There must be at least 2 disks in a mirror.');
					endif;
					break;
				case 'raidz':
				case 'raidz1':
					if(count($sphere_record['device']) <  2):
						$input_errors[] = gtext('There must be at least 2 disks in a raidz.');
					endif;
					break;
				case 'raidz2':
					if(count($sphere_record['device']) <  3):
						$input_errors[] = gtext('There must be at least 3 disks in a raidz2.');
					endif;
					break;
				case 'raidz3':
					if(count($sphere_record['device']) <  4):
						$input_errors[] = gtext('There must be at least 4 disks in a raidz3.');
					endif;
					break;
				default:
					if(count($sphere_record['device']) <  1):
						$input_errors[] = gtext('At least one disk must be selected.');
					endif;
					break;
			endswitch;
		endif;
	endif;
	if($prerequisites_ok && empty($input_errors)):
		if($isrecordnew):
			$sphere_array[] = $sphere_record;
			updatenotify_set($sphere_notifier,UPDATENOTIFY_MODE_NEW,$sphere_record['uuid']);
		else:
			$sphere_array[$index] = $sphere_record;
			if(UPDATENOTIFY_MODE_UNKNOWN == $mode_updatenotify):
				updatenotify_set($sphere_notifier,UPDATENOTIFY_MODE_MODIFIED,$sphere_record['uuid']);
			endif;
		endif;
		write_config();
		header($sphere_header_parent);
		exit;
	endif;
else:
//	EDIT / ADD
	switch ($mode_record):
		case RECORD_NEW:
			$sphere_record['name'] = '';
			$sphere_record['type'] = 'stripe';
			$sphere_record['device'] = [];
			$sphere_record['aft4k'] = false;
			$sphere_record['desc'] = '';
			break;
		case RECORD_NEW_MODIFY:
		case RECORD_MODIFY:
			$sphere_record['name'] = $sphere_array[$index]['name'];
			$sphere_record['type'] = $sphere_array[$index]['type'];
			$sphere_record['device'] = $sphere_array[$index]['device'];
			$sphere_record['aft4k'] = isset($sphere_array[$index]['aft4k']);
			$sphere_record['desc'] = $sphere_array[$index]['desc'];
			break;
	endswitch;
endif;
$pgtitle = [gtext('Disks'),gtext('ZFS'),gtext('Pools'),gtext('Virtual Device'),(!$isrecordnew) ? gtext('Edit') : gtext('Add')];
include 'fbegin.inc';
?>
<script>
//<![CDATA[
$(window).on("load", function() {
	$("input[name='<?=$checkbox_member_name;?>[]']").click(function() {
		controlactionbuttons(this, '<?=$checkbox_member_name;?>[]');
	});
	$("#togglebox").click(function() {
		toggleselection($(this)[0], "<?=$checkbox_member_name;?>[]");
	});
	$("#button_single").click(function () { return confirm('<?=$gt_confirm_single;?>'); });
	$("#button_stripe").click(function () { return confirm('<?=$gt_confirm_stripe;?>'); });
	$("#button_mirror").click(function () { return confirm('<?=$gt_confirm_mirror;?>'); });
	$("#button_raidz1").click(function () { return confirm('<?=$gt_confirm_raidz1;?>'); });
	$("#button_raidz2").click(function () { return confirm('<?=$gt_confirm_raidz2;?>'); });
	$("#button_raidz3").click(function () { return confirm('<?=$gt_confirm_raidz3;?>'); });
//	$("#button_draid1").click(function () { return confirm('<?=$gt_confirm_draid1;?>'); });
//	$("#button_draid2").click(function () { return confirm('<?=$gt_confirm_draid2;?>'); });
//	$("#button_draid3").click(function () { return confirm('<?=$gt_confirm_draid3;?>'); });
	$("#button_spare").click(function () { return confirm('<?=$gt_confirm_spare;?>'); });
	$("#button_cache").click(function () { return confirm('<?=$gt_confirm_cache;?>'); });
	$("#button_log").click(function () { return confirm('<?=$gt_confirm_log;?>'); });
	$("#button_logmir").click(function () { return confirm('<?=$gt_confirm_logmir;?>'); });
//	$("#button_dedup").click(function () { return confirm('<?=$gt_confirm_dedup;?>'); });
//	$("#button_sac").click(function () { return confirm('<?=$gt_confirm_sac;?>'); });
	controlactionbuttons(this,'<?=$checkbox_member_name;?>[]');
	// Init spinner onsubmit()
	$("#iform").submit(function() { spinner(); });
	$(".spin").click(function() { spinner(); });
});
function disableactionbuttons(n) {
	var ab_element;
	var ab_disable = [];
	if (typeof(n) !== 'number') { n = 0; }
	switch (n) { //            single, stripe, mirror, raidz1, raidz2, raidz3, hotspa, cache , log   , logmir , dedup , sac
		case  0: ab_disable = [true  , true  , true  , true  , true  , true  , true  , true  , true  , true   , true  , true ]; break;
		case  1: ab_disable = [false , false , true  , true  , true  , true  , false , false , false , true   , false , false]; break;
		case  2: ab_disable = [true  , false , false , true  , true  , true  , true  , true  , true  , false  , false , false]; break;
		case  3: ab_disable = [true  , false , false , false , true  , true  , true  , true  , true  , false  , false , false]; break;
		case  4: ab_disable = [true  , false , false , false , false , true  , true  , true  , true  , false  , false , false]; break;
		default: ab_disable = [true  , false , false , false , false , false , true  , true  , true  , false  , false , false]; break; // setting for 5 or more disks
	}
	ab_element = document.getElementById('button_single'); if ((ab_element !== null) && (ab_element.disabled !== ab_disable[0])) { ab_element.disabled = ab_disable[0]; }
	ab_element = document.getElementById('button_stripe'); if ((ab_element !== null) && (ab_element.disabled !== ab_disable[1])) { ab_element.disabled = ab_disable[1]; }
	ab_element = document.getElementById('button_mirror'); if ((ab_element !== null) && (ab_element.disabled !== ab_disable[2])) { ab_element.disabled = ab_disable[2]; }
	ab_element = document.getElementById('button_raidz1'); if ((ab_element !== null) && (ab_element.disabled !== ab_disable[3])) { ab_element.disabled = ab_disable[3]; }
	ab_element = document.getElementById('button_raidz2'); if ((ab_element !== null) && (ab_element.disabled !== ab_disable[4])) { ab_element.disabled = ab_disable[4]; }
	ab_element = document.getElementById('button_raidz3'); if ((ab_element !== null) && (ab_element.disabled !== ab_disable[5])) { ab_element.disabled = ab_disable[5]; }
//	ab_element = document.getElementById('button_draid1'); if ((ab_element !== null) && (ab_element.disabled !== ab_disable[3])) { ab_element.disabled = ab_disable[3]; }
//	ab_element = document.getElementById('button_draid2'); if ((ab_element !== null) && (ab_element.disabled !== ab_disable[4])) { ab_element.disabled = ab_disable[4]; }
//	ab_element = document.getElementById('button_draid3'); if ((ab_element !== null) && (ab_element.disabled !== ab_disable[5])) { ab_element.disabled = ab_disable[5]; }
	ab_element = document.getElementById('button_spare'); if ((ab_element !== null) && (ab_element.disabled !== ab_disable[6])) { ab_element.disabled = ab_disable[6]; }
	ab_element = document.getElementById('button_cache'); if ((ab_element !== null) && (ab_element.disabled !== ab_disable[7])) { ab_element.disabled = ab_disable[7]; }
	ab_element = document.getElementById('button_log'); if ((ab_element !== null) && (ab_element.disabled !== ab_disable[8])) { ab_element.disabled = ab_disable[8]; }
	ab_element = document.getElementById('button_logmir'); if ((ab_element !== null) && (ab_element.disabled !== ab_disable[9])) { ab_element.disabled = ab_disable[9]; }
//	ab_element = document.getElementById('button_dedup'); if ((ab_element !== null) && (ab_element.disabled !== ab_disable[10])) { ab_element.disabled = ab_disable[10]; }
//	ab_element = document.getElementById('button_sac'); if ((ab_element !== null) && (ab_element.disabled !== ab_disable[11])) { ab_element.disabled = ab_disable[11]; }
}
function controlactionbuttons(ego, triggerbyname) {
	var a_trigger = document.getElementsByName(triggerbyname);
	var n_trigger = a_trigger.length;
	var i = 0;
	var n = 0;
	for (; i < n_trigger; i++) {
		if ((a_trigger[i].type === 'checkbox') && !a_trigger[i].disabled && a_trigger[i].checked) {
			n++;
		}
	}
	disableactionbuttons(n);
}
function toggleselection(ego, triggerbyname) {
	var a_trigger = document.getElementsByName(triggerbyname);
	var n_trigger = a_trigger.length;
	var i = 0;
	var n = 0;
	for (; i < n_trigger; i++) {
		if ((a_trigger[i].type === 'checkbox') && !a_trigger[i].disabled) {
			a_trigger[i].checked = !a_trigger[i].checked;
			if (a_trigger[i].checked) {
				n++;
			}
		}
	}
	disableactionbuttons(n);
	$("#togglebox").prop("checked", false);
}
//]]>
</script>
<?php
$document = new document();
$document->
	add_area_tabnav()->
		push()->
		add_tabnav_upper()->
			ins_tabnav_record('disks_zfs_zpool.php',gettext('Pools'),gettext('Reload page'),true)->
			ins_tabnav_record('disks_zfs_dataset.php',gettext('Datasets'))->
			ins_tabnav_record('disks_zfs_volume.php',gettext('Volumes'))->
			ins_tabnav_record('disks_zfs_snapshot.php',gettext('Snapshots'))->
			ins_tabnav_record('disks_zfs_scheduler_snapshot_create.php',gettext('Scheduler'))->
			ins_tabnav_record('disks_zfs_config.php',gettext('Configuration'))->
			ins_tabnav_record('disks_zfs_settings.php',gettext('Settings'))->
		pop()->
		add_tabnav_lower()->
			ins_tabnav_record('disks_zfs_zpool_vdevice.php',gettext('Virtual Device'),gettext('Reload page'),true)->
			ins_tabnav_record('disks_zfs_zpool.php',gettext('Management'))->
			ins_tabnav_record('disks_zfs_zpool_tools.php',gettext('Tools'))->
			ins_tabnav_record('disks_zfs_zpool_info.php',gettext('Information'))->
			ins_tabnav_record('disks_zfs_zpool_io.php',gettext('I/O Statistics'));
$document->render();
?>
<form action="<?=$sphere_scriptname;?>" method="post" name="iform" id="iform" class="pagecontent"><div class="area_data_top"></div><div id="area_data_frame">
<?php
	if(!empty($errormsg)):
		print_error_box($errormsg);
	endif;
	if(!empty($input_errors)):
		print_input_errors($input_errors);
	endif;
	if(file_exists($d_sysrebootreqd_path)):
		print_info_box(get_std_save_message(0));
	endif;
?>
	<table class="area_data_settings">
		<colgroup>
			<col class="area_data_settings_col_tag">
			<col class="area_data_settings_col_data">
		</colgroup>
		<thead>
<?php
			html_titleline2(gettext('Settings'));
?>
		</thead>
		<tbody>
<?php
			html_inputbox2('name',gettext('Name'),$sphere_record['name'],'',true,20,$isrecordmodify);
			if($isrecordmodify):
				html_inputbox2('type',gettext('Type'),$sphere_record['type'],'',true,20,$isrecordmodify);
			endif;
			html_checkbox2('aft4k',gettext('4KB wrapper'),!empty($sphere_record['aft4k']),gettext('Create 4KB wrapper (nop device).'),'',false,$isrecordmodify);
			html_inputbox2('desc',gettext('Description'),$sphere_record['desc'],gettext('You may enter a description here for your reference.'),false,40);
?>
		</tbody>
	</table>
	<table class="area_data_selection">
		<colgroup>
			<col style="width:5%">
			<col style="width:8%">
			<col style="width:10%">
			<col style="width:15%">
			<col style="width:10%">
			<col style="width:10%">
			<col style="width:20%">
			<col style="width:14%">
			<col style="width:8%">
		</colgroup>
		<thead>
<?php
			html_titleline2(gettext('Device List'),9);
?>
			<tr>
				<th class="lhelc">
<?php
					if ($isrecordnewornewmodify):
?>
						<input type="checkbox" class="oneemhigh" id="togglebox" name="togglebox" title="<?=gtext('Invert Selection');?>">
<?php
					else:
?>
						<input type="checkbox" class="oneemhigh" id="togglebox" name="togglebox" disabled="disabled">
<?php
					endif;
?>
				</th>
				<th class="lhell"><?=gtext('Device');?></th>
				<th class="lhell"><?=gtext('Partition');?></th>
				<th class="lhell"><?=gtext('Model');?></th>
				<th class="lhell"><?=gtext('Serial Number');?></th>
				<th class="lhell"><?=gtext('Size');?></th>
				<th class="lhell"><?=gtext('Controller');?></th>
				<th class="lhell"><?=gtext('Name');?></th>
				<th class="lhebl"><?=gtext('Toolbox');?></th>
			</tr>
		</thead>
		<tbody>
<?php
			if($isrecordnewornewmodify):
				foreach($a_device as $r_device):
					$isnotmemberofavdev = (arr::search_ex($r_device['devicespecialfile'],$sphere_array,'device') === false);
					$ismemberofthisvdev = (isset($sphere_record['device']) && is_array($sphere_record['device']) && in_array($r_device['devicespecialfile'],$sphere_record['device']));
					if($isnotmemberofavdev || $ismemberofthisvdev):
?>
						<tr>
							<td class="lcelc">
<?php
								if($ismemberofthisvdev):
?>
									<input type="checkbox" name="<?=$checkbox_member_name;?>[]" value="<?=$r_device['devicespecialfile'];?>" id="<?=$r_device['uuid'];?>" checked="checked">
<?php
								else:
?>
									<input type="checkbox" name="<?=$checkbox_member_name;?>[]" value="<?=$r_device['devicespecialfile'];?>" id="<?=$r_device['uuid'];?>">
<?php
								endif;
?>
							</td>
							<td class="lcell"><?=htmlspecialchars($r_device['name']);?></td>
							<td class="lcell"><?=htmlspecialchars($r_device['partition']);?></td>
							<td class="lcell"><?=htmlspecialchars($r_device['model']);?></td>
							<td class="lcell"><?=htmlspecialchars($r_device['serial']);?></td>
							<td class="lcell"><?=htmlspecialchars($r_device['size']);?></td>
							<td class="lcell"><?=htmlspecialchars($r_device['controller']);?></td>
							<td class="lcell"><?=htmlspecialchars($r_device['desc']);?></td>
							<td class="lcebld">
<?php
								if($ismemberofthisvdev):
?>
									<img src="<?=$g_img['unl'];?>" title="<?=$gt_record_opn;?>" alt="<?=$gt_record_opn;?>">
<?php
								endif;
?>
							</td>
						</tr>
<?php
					endif;
				endforeach;
			elseif($isrecordmodify):
				$use_si = is_sidisksizevalues();
				foreach($sphere_record['device'] as $config_device):
					$r_device = [];
//					strip /dev/ from $config_device
					unset($matches);
					if(preg_match('#^/dev/(.+)$#',$config_device,$matches)):
						$r_device['name'] = $matches[1];
					else:
						$r_device['name'] = $config_device;
					endif;
//					search disks
					$index = arr::search_ex($r_device['name'],$a_device,'name');
					if($index !== false):
						$r_device = $a_device[$index];
					else:
//						search geom provider
						$index = arr::search_ex($r_device['name'],$a_provider,'name');
						if($index !== false):
							$r_device['devicespecialfile'] = $config_device;
							$r_device['partition'] = '';
							$r_device['model'] = '';
							$r_device['serial'] = '';
							$r_device['size'] = format_bytes($a_provider[$index]['mediasize'],2,false,$use_si);
							$r_device['controller'] = '';
							$r_device['desc'] = '';
						else:
							$r_device['devicespecialfile'] = $config_device;
							$r_device['partition'] = '';
							$r_device['model'] = '';
							$r_device['serial'] = '';
							$r_device['size'] = '';
							$r_device['controller'] = '';
							$r_device['desc'] = '';
						endif;
					endif;
?>
					<tr>
						<td class="lcelcd">
							<input type="checkbox" name="<?=$checkbox_member_name;?>[]" value="<?=$r_device['devicespecialfile'];?>" checked="checked" disabled="disabled">
						</td>
						<td class="lcelld"><?=htmlspecialchars($r_device['name'] ?? '');?></td>
						<td class="lcelld"><?=htmlspecialchars($r_device['partition'] ?? '');?></td>
						<td class="lcelld"><?=htmlspecialchars($r_device['model'] ?? '');?></td>
						<td class="lcelld"><?=htmlspecialchars($r_device['serial'] ?? '');?></td>
						<td class="lcelld"><?=htmlspecialchars($r_device['size'] ?? '');?></td>
						<td class="lcelld"><?=htmlspecialchars($r_device['controller'] ?? '');?></td>
						<td class="lcelld"><?=htmlspecialchars($r_device['desc'] ?? '');?></td>
						<td class="lcebld">
							<img src="<?=$g_img['loc'];?>" title="<?=$gt_record_loc;?>" alt="<?=$gt_record_loc;?>">
						</td>
					</tr>
<?php
				endforeach;
			endif;
?>
		</tbody>
	</table>
	<div id="submit">
<?php
		if($isrecordmodify):
?>
			<input name="submit" type="submit" class="formbtn" value="<?=gtext('Save');?>">
<?php
		endif;
?>
		<input name="cancel" type="submit" class="formbtn" value="<?=gtext('Cancel');?>">
<?php
		if ($isrecordnewornewmodify):
?>
			<button name="action" id="button_single" type="submit" class="formbtn" value="stripe" title="<?=gtext('Create a virtual device containing a single device');?>"><?=gtext('DISK');?></button>
			<button name="action" id="button_stripe" type="submit" class="formbtn" value="stripe" title="<?=gtext('Create a virtual device containing concatenated devices');?>"><?=gtext('STRIPE');?></button>
			<button name="action" id="button_mirror" type="submit" class="formbtn" value="mirror" title="<?=gtext('Create a virtual device containing mirrored devices');?>"><?=gtext('MIRROR');?></button>
			<button name="action" id="button_raidz1" type="submit" class="formbtn" value="raidz1" title="<?=gtext('Create a virtual device which tolerates a single device failure');?>"><?=gtext('RAID-Z1');?></button>
			<button name="action" id="button_raidz2" type="submit" class="formbtn" value="raidz2" title="<?=gtext('Create a virtual device which tolerates 2 device failures');?>"><?=gtext('RAID-Z2');?></button>
			<button name="action" id="button_raidz3" type="submit" class="formbtn" value="raidz3" title="<?=gtext('Create a virtual device which tolerates 3 device failures');?>"><?=gtext('RAID-Z3');?></button>
<!--
			<button name="action" id="button_draid1" type="submit" class="formbtn" value="draid1" title="<?=gtext('Create a virtual device which tolerates a single device failure');?>"><?=gtext('DRAID-1');?></button>
			<button name="action" id="button_draid2" type="submit" class="formbtn" value="draid2" title="<?=gtext('Create a virtual device which tolerates 2 device failures');?>"><?=gtext('DRAID-2');?></button>
			<button name="action" id="button_draid3" type="submit" class="formbtn" value="draid3" title="<?=gtext('Create a virtual device which tolerates 3 device failures');?>"><?=gtext('DRAID-3');?></button>
-->
			<button name="action" id="button_spare"  type="submit" class="formbtn" value="spare" title="<?=gtext('Create a spare device');?>"><?=gtext('HOT SPARE');?></button>
			<button name="action" id="button_cache"  type="submit" class="formbtn" value="cache" title="<?=gtext('Create a L2ARC device for ARC');?>"><?=gtext('CACHE');?></button>
			<button name="action" id="button_log"    type="submit" class="formbtn" value="log" title="<?=gtext('Create a SLOG device for ZIL');?>"><?=gtext('LOG');?></button>
			<button name="action" id="button_logmir" type="submit" class="formbtn" value="log-mirror" title="<?=gtext('Create a mirrored SLOG device for ZIL');?>"><?=gtext('LOG (Mirror)');?></button>
<!--
			<button name="action" id="button_dedup" type="submit" class="formbtn" value="dedup" title="<?=gtext('Create a dedup device');?>"><?=gtext('DEDUP');?></button>
			<button name="action" id="button_sac" type="submit" class="formbtn" value="sac" title="<?=gtext('Create a special allocation device');?>"><?=gtext('SPECIAL');?></button>
-->
<?php
		endif;
?>
		<input name="uuid" type="hidden" value="<?=$sphere_record['uuid'];?>">
	</div>
<?php
	include 'formend.inc';
?>
</div><div class="area_data_pot"></div></form>
<?php
include 'fend.inc';
