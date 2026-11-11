<?php
/*
	services_syncthing.php

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

arr::make_branch($config,'syncthing');
$pconfig['enable'] = isset($config['syncthing']['enable']);
$pconfig['homedir'] = $config['syncthing']['homedir'] ?? '';
$if = get_ifname($config['interfaces']['lan']['if']);
$gui_ipaddr = get_ipaddr($if);
$gui_port = 8384;
$syncthing_user = rc_getenv_ex('syncthing_user','syncthing');
$syncthing_group = rc_getenv_ex('syncthing_group','syncthing');
if($_POST):
	unset($input_errors);
	unset($errormsg);
	$pconfig = $_POST;
	if(isset($_POST['enable'])):
		$reqdfields = ['homedir'];
		$reqdfieldsn = [gtext('Database Directory')];
		$reqdfieldst = ['string'];
		do_input_validation($_POST,$reqdfields,$reqdfieldsn,$input_errors);
		do_input_validation_type($_POST,$reqdfields,$reqdfieldsn,$reqdfieldst,$input_errors);
	endif;
	$dir = $_POST['homedir'];
	if(!file_exists($dir)):
		$input_errors[] = sprintf(gtext('The path %s does not exist.'),escapeshellarg($dir));
	endif;
	if(!is_dir($dir)):
		$input_errors[] = sprintf(gtext('The path %s is not a directory.'),escapeshellarg($dir));
	endif;
	if(empty($input_errors)):
		$config['syncthing']['enable'] = isset($_POST['enable']);
		$config['syncthing']['homedir'] = $_POST['homedir'];
		$dir = $_POST['homedir'];
		if($dir != '/mnt' && file_exists($dir) && !file_exists("{$dir}/config.xml")):
			$user = $syncthing_user;
			$group = $syncthing_group;
			chmod($dir,0700);
			chown($dir,$user);
			chgrp($dir,$group);
//			create default config
			$cmd = "/usr/local/bin/sudo -u {$user} /usr/local/bin/syncthing generate --home=\"{$dir}\"";
			mwexec2("$cmd 2>&1",$rawdata,$result);
//			fix GUI address
			$cmd = "/usr/local/bin/sudo -u {$user} /usr/bin/sed -i '' 's/127.0.0.1:8384/{$gui_ipaddr}:{$gui_port}/' {$dir}/config.xml";
			mwexec2("$cmd 2>&1",$rawdata,$result);
		endif;
		write_config();
		$retval = 0;
		if(!file_exists($d_sysrebootreqd_path)):
			config_lock();
			$retval |= rc_update_service('syncthing');
			config_unlock();
		endif;
		$savemsg = get_std_save_message($retval);
		if($retval == 0 && !isset($config['syncthing']['enable']) && file_exists('/var/run/syncthing.pid')):
//			remove pidfile if service is disabled
			unlink('/var/run/syncthing.pid');
		endif;
	endif;
endif;
$pgtitle = [gtext('Services'),gtext('Syncthing')];
include 'fbegin.inc';
?>
<script>
//<![CDATA[
$(document).ready(function(){
	function enable_change(enable_change) {
		var endis = !($('#enable').prop('checked') || enable_change);
		$('#homedir').prop('disabled',endis);
		$('#homedirbrowsebtn').prop('disabled',endis);
		if (endis) {
			$('#a_url').on('click',function(){ return false; });
		} else {
			$('#a_url').off('click');
		}
	}
	$('#enable').click(function(){
		enable_change(false);
	});
	$('input:submit').click(function(){
		enable_change(true);
	});
	enable_change(false);
});
//]]>
</script>
<form action="services_syncthing.php" method="post" name="iform" id="iform" class="pagecontent" onsubmit="spinner()">
	<div class="area_data_top"></div>
	<div id="area_data_frame">
<?php
		if(!empty($errormsg)):
			print_error_box($errormsg);
		endif;
		if(!empty($input_errors)):
			print_input_errors($input_errors);
		endif;
		if(!empty($savemsg)):
			print_info_box($savemsg);
		endif;
		$enabled = isset($config['syncthing']['enable']);
?>
		<table class="area_data_settings">
			<colgroup>
				<col class="area_data_settings_col_tag">
				<col class="area_data_settings_col_data">
			</colgroup>
			<thead>
<?php
				html_titleline_checkbox2('enable',gettext('Syncthing'),!empty($pconfig['enable']),gettext('Enable'),'');
?>
			</thead>
			<tbody>
<?php
				html_filechooser2('homedir',gettext('Database Directory'),$pconfig['homedir'],gettext('Enter the path to the database directory. The config files will be created under the specified directory.'),$g['media_path'],false,60);
?>
			</tbody>
		</table>
<?php
		if($enabled):
?>
		<table class="area_data_settings">
			<colgroup>
				<col class="area_data_settings_col_tag">
				<col class="area_data_settings_col_data">
			</colgroup>
			<thead>
<?php
				html_separator2();
				html_titleline2(gettext('Administrative WebGUI'));
?>
			</thead>
			<tbody>
<?php
				$url = "http://{$gui_ipaddr}:{$gui_port}/";
				$text = "<a href='{$url}' id='a_url' target='_blank'>{$url}</a>";
				html_text2('url',gettext('URL'),$text);
?>
			</tbody>
		</table>
<?php
		endif;
?>
		<div id="submit">
			<input name="Submit" type="submit" class="formbtn" value="<?=gtext('Save & Restart');?>" />
		</div>
<?php
		include 'formend.inc';
?>
	</div>
	<div class="area_data_pot"></div>
</form>
<?php
include 'fend.inc';
