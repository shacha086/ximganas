<?php
/*
	disks_manage_smart_edit.php

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
require_once 'cs_scheduletime.php';

use common\arr;
use common\uuid;

$sphere_scriptname = basename(__FILE__);
$uuid = null;
if(isset($_GET['uuid'])):
	$uuid = $_GET['uuid'];
endif;
if(isset($_POST['uuid'])):
	$uuid = $_POST['uuid'];
endif;
$a_selftest = &arr::make_branch($config,'smartd','selftest');
//	Get list of all configured physical disks.
$a_disk = get_conf_physical_disks_list();
if(isset($uuid)):
	$cnid = arr::search_ex($uuid,$a_selftest,'uuid');
else:
	$cnid = false;
endif;
if(isset($uuid) && ($cnid !== false)):
	$pconfig['uuid'] = $a_selftest[$cnid]['uuid'];
	$pconfig['devicespecialfile'] = $a_selftest[$cnid]['devicespecialfile'];
	$pconfig['type'] = $a_selftest[$cnid]['type'];
	$pconfig['hour'] = $a_selftest[$cnid]['hour'];
	$pconfig['day'] = $a_selftest[$cnid]['day'];
	$pconfig['month'] = $a_selftest[$cnid]['month'];
	$pconfig['weekday'] = $a_selftest[$cnid]['weekday'];
	$pconfig['all_hours'] = $a_selftest[$cnid]['all_hours'];
	$pconfig['all_days'] = $a_selftest[$cnid]['all_days'];
	$pconfig['all_months'] = $a_selftest[$cnid]['all_months'];
	$pconfig['all_weekdays'] = $a_selftest[$cnid]['all_weekdays'];
	$pconfig['desc'] = $a_selftest[$cnid]['desc'];
else:
	$pconfig['uuid'] = uuid::create_v4();
	$pconfig['type'] = 'S';
	$pconfig['desc'] = '';
	$pconfig['all_hours'] = 1;
	$pconfig['all_days'] = 1;
	$pconfig['all_months'] = 1;
	$pconfig['all_weekdays'] = 1;
endif;
if($_POST):
	unset($input_errors);
	unset($errormsg);
	$pconfig = $_POST;
	if(isset($_POST['Cancel']) && $_POST['Cancel']):
		header("Location: disks_manage_smart.php");
		exit;
	endif;
	$reqdfields = ['devicespecialfile','type'];
	$reqdfieldsn = [gtext('Disk'),gtext('Type')];
	do_input_validation($_POST,$reqdfields,$reqdfieldsn,$input_errors);
	$_POST['all_mins'] = true; // cheat on minutes
	do_input_validate_synctime($_POST,$input_errors);
	$_POST['all_mins'] = false;
	if(empty($input_errors)):
		$selftest = [];
		$selftest['uuid'] = $_POST['uuid'];
		$selftest['devicespecialfile'] = $_POST['devicespecialfile'];
		$selftest['type'] = $_POST['type'];
		$selftest['hour'] = !empty($_POST['hour']) ? $_POST['hour'] : null;
		$selftest['day'] = !empty($_POST['day']) ? $_POST['day'] : null;
		$selftest['month'] = !empty($_POST['month']) ? $_POST['month'] : null;
		$selftest['weekday'] = !empty($_POST['weekday']) ? $_POST['weekday'] : null;
		$selftest['all_hours'] = $_POST['all_hours'];
		$selftest['all_days'] = $_POST['all_days'];
		$selftest['all_months'] = $_POST['all_months'];
		$selftest['all_weekdays'] = $_POST['all_weekdays'];
		$selftest['desc'] = $_POST['desc'];
		if(isset($uuid) && (false !== $cnid)):
			$a_selftest[$cnid] = $selftest;
			$mode = UPDATENOTIFY_MODE_MODIFIED;
		else:
			$a_selftest[] = $selftest;
			$mode = UPDATENOTIFY_MODE_NEW;
		endif;
		updatenotify_set('smartssd',$mode,$selftest['uuid']);
		write_config();
		header('Location: disks_manage_smart.php');
		exit;
	endif;
endif;
$l_devicespecialfiles = [];
$l_devicespecialfiles[''] = gettext('Must choose one');
$use_si = is_sidisksizevalues();
foreach($a_disk as $r_disk):
	if(0 == strcmp($r_disk['size'],'NA')):
		continue;
	endif;
	if(1 == disks_exists($r_disk['devicespecialfile'])):
		continue;
	endif;
	if(!isset($r_disk['smart'])):
		continue;
	endif;
	$diskinfo = disks_get_diskinfo($r_disk['devicespecialfile']);
	$helpinghand = format_bytes($diskinfo['mediasize_bytes'],2,true,$use_si);
	$l_devicespecialfiles[$r_disk['devicespecialfile']] = sprintf('%s: %s (%s)',$r_disk['name'],$helpinghand,$r_disk['desc']);
endforeach;
$l_types = [
	'S' => gettext('Short Self-Test'),
	'L' => gettext('Long Self-Test'),
	'C' => gettext('Conveyance Self-Test'),
	'O' => gettext('Offline Immediate Test')
];
$pgtitle = [gtext('Disks'),gtext('Management'),gtext('S.M.A.R.T.'),gtext('Scheduled Self-Test'),isset($uuid) ? gtext('Edit') : gtext('Add')];
include 'fbegin.inc';
?>
<script>
//<![CDATA[
$(window).on("load", function() {
<?php // Init spinner.?>
	$("#iform").submit(function() { spinner(); });
	$(".spin").click(function() { spinner(); });
});
function set_selected(name) {
	document.getElementsByName(name)[1].checked = true;
}
//]]>
</script>
<table id="area_navigator">
	<tr><td class="tabnavtbl"><ul id="tabnav">
		<li class="tabinact"><a href="disks_manage.php"><span><?=gtext('HDD Management');?></span></a></li>
		<li class="tabinact"><a href="disks_init.php"><span><?=gtext('HDD Format');?></span></a></li>
		<li class="tabact"><a href="disks_manage_smart.php" title="<?=gtext('Reload page');?>"><span><?=gtext('S.M.A.R.T.');?></span></a></li>
		<li class="tabinact"><a href="disks_manage_iscsi.php"><span><?=gtext('iSCSI Initiator');?></span></a></li>
	</ul></td></tr>
</table>
<form action="<?=$sphere_scriptname;?>" method="post" name="iform" id="iform"><table id="area_data"><tbody><tr><td id="area_data_frame">
<?php
	if(!empty($input_errors)):
		print_input_errors($input_errors);
	endif;
?>
	<table class="area_data_settings">
		<colgroup>
			<col class="area_data_settings_col_tag">
			<col class="area_data_settings_col_data">
		</colgroup>
		<thead>
<?php
			html_titleline2(gettext('Scheduled Self-Test Settings'));
?>
		</thead>
		<tbody>
<?php
			html_combobox2('devicespecialfile',gettext('Disk'),$pconfig['devicespecialfile'] ?? '',$l_devicespecialfiles,gettext('Select a disk that is enabled for S.M.A.R.T. monitoring.'),true);
			html_radiobox2('type',gettext('Type'),$pconfig['type'],$l_types,'',true);
?>
			<tr>
				<td class="celltagreq"><?=gtext('Schedule Time');?></td>
				<td class="celldatareq">
<?php
					render_scheduler($pconfig,false);
?>
				</td>
			</tr>
<?php
			html_inputbox2('desc',gettext('Description'),$pconfig['desc'],'',false,40);
?>
		</tbody>
	</table>
	<div id="submit">
		<input name="Submit" type="submit" class="formbtn" value="<?=(isset($uuid) && (false !== $cnid)) ? gtext('Save') : gtext('Add')?>"/>
		<input name="Cancel" type="submit" class="formbtn" value="<?=gtext('Cancel');?>" />
		<input name="uuid" type="hidden" value="<?=$pconfig['uuid'];?>" />
	</div>
<?php
	include 'formend.inc';
?>
</td></tr></tbody></table></form>
<?php
include 'fend.inc';
