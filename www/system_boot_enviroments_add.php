<?php
/*
	system_boot_enviroments_add.php

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

$pgtitle = [gtext("System"), gtext('Boot Environment'),gtext('Add')];

if($_POST):
	unset($input_errors);
	$pconfig = $_POST;
	if(isset($_POST['Cancel']) && $_POST['Cancel']):
		header('Location: system_boot_enviroments.php');
		exit;
	endif;
	if(isset($_POST['create_new_be']) && $_POST['create_new_be']):
		$bename = $pconfig['bename'];
		// Input validation.
		if(preg_match("/[^a-zA-Z0-9\-_:.]/", $bename)):
			$input_errors[] = sprintf(gtext("The attribute '%s' contains invalid characters."), gtext("Name"));
		else:
			$date = (strftime('%Y-%m-%d-%H%M%S'));
			unset ($bename_date);
			if ($_POST['dateadd']):
				$cmd1 = ("/sbin/bectl create {$$bename}{$date}");
			else:
				if(!$pconfig['bename']):
					$bename = $date;
					$bename_date = "1";
				endif;
				$cmd1 = ("/sbin/bectl create $bename");
			endif;
			if ($_POST['activate']):
				if ($_POST['dateadd'] && $bename_date):
					$cmd2 = ("/sbin/bectl create $bename && /sbin/bectl activate $bename");
				elseif ($_POST['dateadd']):
					$cmd2 = ("/sbin/bectl create {$bename}{$date} && /sbin/bectl activate {$bename}{$date}");
				else:
					$cmd2 = ("/sbin/bectl create $bename && /sbin/bectl activate $bename");
				endif;
				unset($output,$retval);mwexec2($cmd2,$output,$retval);
				if($retval == 0):
					header('Location: system_boot_enviroments.php');
					exit;
				else:
					$errormsg .= gtext("Failed to create and/or activate Boot Environment.");
				endif;
			else:
				unset($output,$retval);mwexec2($cmd1,$output,$retval);
				if($retval == 0):
					header('Location: system_boot_enviroments.php');
					exit;
				else:
					$errormsg .= gtext("Failed to create Boot Environment.");
				endif;
			endif;
		endif;
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
<form action="system_boot_enviroments_add.php" method="post" name="iform" id="iform"><table id="area_data"><tbody><tr><td id="area_data_frame">
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
			html_titleline2(gettext('Boot Environment'));
?>
		</thead>
		<tbody>
<?php
			html_inputbox2('bename',gettext('Name'),$pconfig['bename'],'',true,50);
			html_checkbox2('activate',gettext('Activate'),!empty($pconfig['activate']) ? true : false,gettext('Activate Boot Environment after creation, note that system reboot is required for this boot environment to take place.'),'',false);
			html_checkbox2('dateadd',gettext('Date'),!empty($pconfig['dateadd']) ? true : false,gettext('Append the date in the following format: YYYY-MM-DD-HHMMSS, if the name field is empty, the date will always be used as the boot environment name.'),'',false);

?>
		</tbody>
	</table>
	<div id="submit">
		<input name="create_new_be" type="submit" class="formbtn" value="<?=gtext('Add');?>"/>
		<input name="Cancel" type="submit" class="formbtn" value="<?=gtext('Cancel');?>" />

	</div>
<?php
	include 'formend.inc';
?>
</td></tr></tbody></table></form>
<?php
include 'fend.inc';
