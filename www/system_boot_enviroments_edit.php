<?php
/*
	system_boot_enviroments_edit.php

	Part of XigmaNAS® (https://www.xigmanas.com).
	Copyright © 2018-2026 XigmaNAS® <info@xigmanas.com>.
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

require_once 'auth.inc';
require_once 'guiconfig.inc';

$pgtitle = [gtext("System"), gtext('Boot Environment'),gtext('Edit')];

// Give the user some useful remarks.
$gt_docu = sprintf(gettext('For additional information about boot environments and %s,'),'bectl(8)')
	. ' '
	. '<a href="https://man.freebsd.org/cgi/man.cgi?bectl(8)" target="_blank">'
	. gettext('Please check the documentation')
	. '</a>.';

// Initialize some variables.
if(isset($_GET['uuid'])):
	$uuid = $_GET['uuid'];
endif;
if(isset($_GET['bename'])):
	$bootenv = $_GET['bename'];
endif;
if(isset($_POST['uuid'])):
	$uuid = $_POST['uuid'];
endif;
if(isset($_POST['bename'])):
	$bootenv = $_POST['bename'];
endif;

if(isset($bootenv) && !empty($bootenv)):
	$pconfig['uuid'] = uuid();
	$pconfig['bename'] = $bootenv;
	$pconfig['newname'] = '';
	$pconfig['recursive'] = false;
	$pconfig['action'] = 'activate';
else:
	$pconfig = [];
endif;

if($_POST):
	unset($input_errors);
	$pconfig = $_POST;
	if(isset($_POST['Cancel']) && $_POST['Cancel']):
		header('Location: system_boot_enviroments.php');
		exit;
	endif;
	if(isset($_POST['action'])):
		$action = $_POST['action'];
	endif;
	if(empty($action)):
		$input_errors[] = sprintf(gtext("The attribute '%s' is required."), gtext("Action"));
	else:
		switch($action):
			case 'activate':
				if(empty($input_errors)):
					$bootenv = [];
					$bootenv['uuid'] = $_POST['uuid'];
					$bootenv['bename'] = $_POST['bename'];
					$item = $bootenv['bename'];
					$cmd = ("/sbin/bectl activate {$item}");
					unset($output,$retval);mwexec2($cmd,$output,$retval);
					if($return_val == 0):
						header('Location: system_boot_enviroments.php');
						exit;
					else:
						$errormsg .= gtext("Failed to activate Boot Environment.");
					endif;
				endif;
				break;

			case 'rename':
				// Input validation.
				$reqdfields = ['newname'];
				$reqdfieldsn = [gtext('Name')];
				$reqdfieldst = ['string'];
				do_input_validation($_POST, $reqdfields, $reqdfieldsn, $input_errors);
				do_input_validation_type($_POST, $reqdfields, $reqdfieldsn, $reqdfieldst, $input_errors);
				if(preg_match("/[^a-zA-Z0-9\-_:.]/", $_POST['newname'])):
					$input_errors[] = sprintf(gtext("The attribute '%s' contains invalid characters."), gtext("Name"));
				endif;
				if(empty($input_errors)):
					$bootenv = [];
					$bootenv['uuid'] = $_POST['uuid'];
					$bootenv['name'] = $_POST['newname'];
					$bootenv['bename'] = $_POST['bename'];
					$bootenv['dateadd'] = isset($_POST['dateadd']) ? true : false;
					$item = $bootenv['bename'];
					$date = date('Y-m-d-His', time());
					$newname = $bootenv['name'];
					$cmd1 = ("/sbin/bectl rename {$item} {$newname}");
					$cmd2 = ("/sbin/bectl rename {$item} {$newname}{$date}");
					if ($_POST['dateadd']):
						unset($output,$retval);mwexec2($cmd2,$output,$retval);
						if($retval == 0):
							header('Location: system_boot_enviroments.php');
							exit;
						else:
							$errormsg .= gtext("Failed to rename Boot Environment.");
						endif;
					else:
						unset($output,$retval);mwexec2($cmd1,$output,$retval);
						if($retval == 0):
							header('Location: system_boot_enviroments.php');
							exit;
						else:
							$errormsg .= gtext("Failed to rename Boot Environment.");
						endif;
					endif;
				endif;
				break;

			case 'mount':
				if(empty($input_errors)):
					$bootenv = [];
					$bootenv['uuid'] = $_POST['uuid'];
					$bootenv['bename'] = $_POST['bename'];
					$item = $bootenv['bename'];
					// We will use the default 'bectl' mount method here.
					// Equivalent cmd for random named alt directory mount is:
					// 'bectl mount beName $(mktemp -d /mnt/be_mount.XXXX)'
					$cmd = ("/sbin/bectl mount $item");
					unset($output,$retval);mwexec2($cmd,$output,$retval);
					if($return_val == 0):
						header('Location: system_boot_enviroments.php');
						exit;
					else:
						$errormsg .= gtext("Failed to mount Boot Environment.");
					endif;
				endif;
				break;

			case 'unmount':
				if(empty($input_errors)):
					$bootenv = [];
					$bootenv['uuid'] = $_POST['uuid'];
					$bootenv['bename'] = $_POST['bename'];
					$item = $bootenv['bename'];
					$cmd = ("/sbin/bectl unmount $item");
					unset($output,$retval);mwexec2($cmd,$output,$retval);
					if($retval == 0):
						header('Location: system_boot_enviroments.php');
						exit;
					else:
						$errormsg .= gtext("Failed to unmount Boot Environment.");
					endif;
				endif;
				break;

			case 'delete':
				if(empty($input_errors)):
					$bootenv = [];
					$bootenv['uuid'] = $_POST['uuid'];
					$bootenv['bename'] = $_POST['bename'];
					$item = $bootenv['bename'];
					if($_POST['forcedel']):
						$cmd = ("/sbin/bectl destroy -F -o $item");
					else:
						$cmd = ("/sbin/bectl destroy $item");
					endif;
					unset($output,$retval);mwexec2($cmd,$output,$retval);
					if($retval == 0):
						header('Location: system_boot_enviroments.php');
						exit;
					else:
						$errormsg .= gtext("Failed to delete Boot Environment.");
					endif;
				endif;
				break;
			default:
				$input_errors[] = sprintf(gtext("The attribute '%s' is invalid."), 'action');
				break;
		endswitch;
	endif;
endif;
include 'fbegin.inc';
?>
<script>
//<![CDATA[
$(window).on("load",function() {
	$("#iform").submit(function() { spinner(); });
	$(".spin").click(function() { spinner(); });
});
function enable_change(enable_change) {
	document.iform.name.disabled = !enable_change;
}
function action_change() {
	showElementById('newname_tr','hide');
	showElementById('dateadd_tr','hide');
	showElementById('forcedel_tr','hide');
	var action = document.iform.action.value;
	switch (action) {
		case "rename":
			showElementById('newname_tr','show');
			showElementById('dateadd_tr','show');
			break;
		case "delete":
			showElementById('forcedel_tr','show');
			break;
		default:
			break;
	}
}
//]]>
</script>
<?php
$document = new co_DOMDocument();
$document->
	add_area_tabnav()->
		push()->
		add_tabnav_upper()->
			ins_tabnav_record('system_boot_enviroments.php',gettext('Boot Environments'),gettext('Reload page'),true)->
			ins_tabnav_record('system_boot_enviroments_info.php',gettext('Information'),gettext('Reload page'),true);
$document->render();
?>
<form action="system_boot_enviroments_edit.php" method="post" name="iform" id="iform"><table id="area_data"><tbody><tr><td id="area_data_frame">
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
			html_titleline2(gettext('Edit Boot Environment'));
?>
		</thead>
		<tbody>
<?php
			html_text2('bename',gettext('Target'),htmlspecialchars($pconfig['bename']));
			$a_action = [
				'activate' => gettext('Activate'),
				'rename' => gettext('Rename'),
				'mount' => gettext('Mount'),
				'unmount' => gettext('Unmount'),
				'delete' => gettext('Delete'),
			];
			html_combobox2('action',gettext('Action'),$pconfig['action'],$a_action,'',true,false,'action_change()');
			html_inputbox2('newname',gettext('Name'),$pconfig['newname'],'',true,50);
			html_checkbox2('dateadd',gettext('Date'),!empty($pconfig['dateadd']) ? true : false,gettext('Append the date in the following format: YYYY-MM-DD-HHMMSS.'),'',false);
			html_checkbox2('forcedel',gettext('Force'),!empty($pconfig['forcedel']) ? true : false,gettext('Destroy the boot environment and its origin if any, without confirmation.'),'',false);
?>
		</tbody>
	</table>
	<div id="submit">
		<input name="Submit" type="submit" class="formbtn" value="<?=gtext("Apply");?>" onclick="enable_change(true)" />
		<input name="Cancel" type="submit" class="formbtn" value="<?=gtext("Cancel");?>" />
		<input name="uuid" type="hidden" value="<?=$pconfig['uuid'];?>" />
		<input name="bename" type="hidden" value="<?=$pconfig['bename'];?>" />
	</div>
	<div id="remarks">
		<?php html_remark("note", gtext("Note"), $gt_docu);?>
	</div>
<?php
	include 'formend.inc';
?>
</td></tr></tbody></table></form>
<script type="text/javascript">
<!--
enable_change(true);
action_change();
//-->
</script>
<?php
include 'fend.inc';
