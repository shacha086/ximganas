<?php
/*
	services_websrv.php

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
require_once 'services.inc';

use gui\document;
use common\arr;

arr::make_branch($config,'websrv','authentication','url');
arr::make_branch($config,'websrv','auxparam');
$default_uploaddir = '/var/tmp/ftmp';
$default_runas = 'server.username = "www"';
$pconfig['enable'] = isset($config['websrv']['enable']);
$pconfig['protocol'] = $config['websrv']['protocol'];
$pconfig['port'] = $config['websrv']['port'];
$pconfig['documentroot'] = $config['websrv']['documentroot'];
$pconfig['uploaddir'] = !empty($config['websrv']['uploaddir']) ? $config['websrv']['uploaddir'] : $default_uploaddir;
$pconfig['runasuser'] = isset($config['websrv']['runasuser']) ? $config['websrv']['runasuser'] : $default_runas;
$pconfig['privatekey'] = base64_decode($config['websrv']['privatekey']);
$pconfig['certificate'] = base64_decode($config['websrv']['certificate']);
$pconfig['authentication'] = isset($config['websrv']['authentication']['enable']);
$pconfig['dirlisting'] = isset($config['websrv']['dirlisting']);
$pconfig['auxparam'] = '';
if(isset($config['websrv']['auxparam']) && is_array($config['websrv']['auxparam'])):
	$pconfig['auxparam'] = implode("\n",$config['websrv']['auxparam']);
endif;
if($_POST):
	unset($input_errors);
	$pconfig = $_POST;
	//	Input validation.
	if(isset($_POST['enable']) && $_POST['enable']):
		$reqdfields = ['port','documentroot'];
		$reqdfieldsn = [gtext('Port'),gtext('Document Root')];
		$reqdfieldst = ['port','string'];
		if('https' === $_POST['protocol']):
			$reqdfields = array_merge($reqdfields,['certificate','privatekey']);
			$reqdfieldsn = array_merge($reqdfieldsn,[gtext('Certificate'),gtext('Private key')]);
			$reqdfieldst = array_merge($reqdfieldst,['certificate','privatekey']);
		endif;
		do_input_validation($_POST,$reqdfields,$reqdfieldsn,$input_errors);
		do_input_validation_type($_POST,$reqdfields,$reqdfieldsn,$reqdfieldst,$input_errors);
		//	Check if port is already used.
		if(services_is_port_used($_POST['port'],'websrv')):
			$input_errors[] = sprintf(gtext('Port %ld is already used by another service.'),$_POST['port']);
		endif;
		//	Check Webserver document root if auth is required
		if(isset($_POST['authentication']) && !is_dir($_POST['documentroot'])):
			$input_errors[] = gtext('Webserver document root is missing.');
		endif;
	endif;
	if(empty($input_errors)):
		$config['websrv']['enable'] = isset($_POST['enable']);
		$config['websrv']['protocol'] = $_POST['protocol'];
		$config['websrv']['port'] = $_POST['port'];
		$config['websrv']['documentroot'] = $_POST['documentroot'];
		$config['websrv']['uploaddir'] = $_POST['uploaddir'];
		$config['websrv']['runasuser'] = $_POST['runasuser'];
		$config['websrv']['privatekey'] = base64_encode($_POST['privatekey']);
		$config['websrv']['certificate'] = base64_encode($_POST['certificate']);
		$config['websrv']['authentication']['enable'] = isset($_POST['authentication']);
		$config['websrv']['dirlisting'] = isset($_POST['dirlisting']);
//		Write additional parameters.
		unset($config['websrv']['auxparam']);
		foreach(explode("\n",$_POST['auxparam']) as $auxparam):
			$auxparam = trim($auxparam,"\t\n\r");
			if(!empty($auxparam)):
				$config['websrv']['auxparam'][] = $auxparam;
			endif;
		endforeach;
		write_config();
		$retval = 0;
		if(!file_exists($d_sysrebootreqd_path)):
			$retval |= updatenotify_process('websrvauth','websrvauth_process_updatenotification');
			config_lock();
			$retval |= rc_exec_service('websrv_htpasswd');
			$retval |= rc_update_service('websrv');
			config_unlock();
		endif;
		$savemsg = get_std_save_message($retval);
		if($retval == 0):
			updatenotify_delete('websrvauth');
		endif;
	endif;
endif;
if(isset($_GET['act']) && $_GET['act'] === 'del'):
	updatenotify_set('websrvauth',UPDATENOTIFY_MODE_DIRTY,$_GET['uuid']);
	header('Location: services_websrv.php');
	exit;
endif;
function websrvauth_process_updatenotification($mode,$data) {
	global $config;

	$retval = 0;
	switch ($mode):
		case UPDATENOTIFY_MODE_NEW:
		case UPDATENOTIFY_MODE_MODIFIED:
			break;
		case UPDATENOTIFY_MODE_DIRTY:
			$cnid = arr::search_ex($data,$config['websrv']['authentication']['url'],'uuid');
			if($cnid !== false):
				unset($config['websrv']['authentication']['url'][$cnid]);
				write_config();
			endif;
			break;
	endswitch;
	return $retval;
}
$pgtitle = [gtext('Services'),gtext('Webserver')];
include 'fbegin.inc';
?>
<script>
//<![CDATA[
$(window).on("load", function() {
	$("#protocol").click(function() { protocol_change(); });
	$("#authentication").click(function() { authentication_change(); });
});
$(document).ready(function() {
	protocol_change();
	authentication_change();
});
function protocol_change() {
	switch(document.iform.protocol.selectedIndex) {
		case 0:
			showElementById('privatekey_tr','hide');
			showElementById('certificate_tr','hide');
			break;
		default:
			showElementById('privatekey_tr','show');
			showElementById('certificate_tr','show');
			break;
	}
}
function authentication_change() {
	switch(document.iform.authentication.checked) {
		case false:
			showElementById('authdirs_tr','hide');
			break;
		case true:
			showElementById('authdirs_tr','show');
			break;
	}
}
//]]>
</script>
<?php
//	add tab navigation
$document = new document();
$document->
	add_area_tabnav()->
		add_tabnav_upper()->
			ins_tabnav_record('services_websrv.php',gettext('Webserver'),gettext('Reload page'),true)->
			ins_tabnav_record('services_websrv_webdav.php',gettext('WebDAV'));
$document->render();
?>
<form action="services_websrv.php" method="post" name="iform" id="iform" onsubmit="spinner()">
	<table id="area_data">
		<tbody>
			<tr>
				<td id="area_data_frame">
<?php
					if(!empty($input_errors)):
						print_input_errors($input_errors);
					endif;
					if(!empty($savemsg)):
						print_info_box($savemsg);
					endif;
					if(updatenotify_exists('websrvauth')):
						print_config_change_box();
					endif;
?>
					<table class="area_data_settings">
						<colgroup>
							<col class="area_data_settings_col_tag">
							<col class="area_data_settings_col_data">
						</colgroup>
						<thead>
<?php
							html_titleline_checkbox2('enable',gettext('Webserver'),!empty($pconfig['enable']),gettext('Enable'));
?>
						</thead>
						<tbody>
<?php
							$l_protocol = [
								'http' => gettext('HTTP'),
								'https' => gettext('HTTPS')
							];
							html_combobox2('protocol',gettext('Protocol'),$pconfig['protocol'],$l_protocol,'',true,false);
							html_inputbox2('port',gettext('Port'),$pconfig['port'],gettext('TCP port to bind the server to.'),true,5);
							$helpinghand = gettext('Select the permission for running this service. (www by default).')
								. '<br>'
								. sprintf('<b style="color: red;">%s</b><b>: %s</b>',gettext('NOTE'),gettext('Running this service as root is not recommended for security reasons, use at your own risk!'));
							$l_permission = [
								'server.username = "www"' => 'www',
								'' => 'root'
							];
							html_combobox2('runasuser',gettext('Permission'),$pconfig['runasuser'],$l_permission,$helpinghand,true);
							html_textarea2('certificate',gettext('Certificate'),$pconfig['certificate'],gettext('Paste a signed certificate in X.509 PEM format here.'),true,76,7,false,false);
							html_textarea2('privatekey',gettext('Private key'),$pconfig['privatekey'],gettext('Paste a private key in PEM format here.'),true,76,7,false,false);
							html_filechooser2('documentroot',gettext('Document Root'),$pconfig['documentroot'],gettext('Document root of the webserver. Home of the web page files.'),$g['media_path'],true,76);
							html_filechooser2('uploaddir',gettext('Upload Directory'),$pconfig['uploaddir'],sprintf(gettext('Upload directory of the webserver. The default is %s.'),$default_uploaddir),$default_uploaddir,true,76);
							html_checkbox2('authentication',gettext('Authentication'),!empty($pconfig['authentication']),gettext('Enable authentication.'),gettext('Give only local users access to the web page.'),false,false);
?>
							<tr id="authdirs_tr">
								<td class="celltag">&nbsp;</td>
								<td class="celldata">
									<table class="area_data_selection">
										<colgroup>
											<col style="width:45%">
											<col style="width:45%">
											<col style="width:10%">
										</colgroup>
										<thead>
											<tr>
												<th class="lhell"><?=gtext('URL');?></th>
												<th class="lhell"><?=gtext('Realm');?></th>
												<th class="lhebl"><?=gtext('Toolbox');?></th>
											</tr>
										</thead>
										<tbody>
<?php
											$url_grid = $config['websrv']['authentication']['url'] ?? [];
											foreach($url_grid as $urlv):
												$notificationmode = updatenotify_get_mode('websrvauth',$urlv['uuid']);
?>
												<tr>
													<td class="lcell"><?=htmlspecialchars($urlv['path']);?>&nbsp;</td>
													<td class="lcell"><?=htmlspecialchars($urlv['realm']);?>&nbsp;</td>
													<td class="lcebld">
														<table class="area_data_selection_toolbox">
															<colgroup>
																<col style="width:33%">
																<col style="width:34%">
																<col style="width:33%">
															</colgroup>
															<tbody>
																<tr>
<?php
																	if($notificationmode != UPDATENOTIFY_MODE_DIRTY):
?>
																		<td><a href="services_websrv_authurl.php?uuid=<?=$urlv['uuid'];?>" title="<?=gtext('Edit URL');?>"><?=$g_img['unicode.mod'];?></a></td>
																		<td><a href="services_websrv.php?act=del&amp;uuid=<?=$urlv['uuid'];?>" title="<?=gtext('Delete URL');?>" onclick="return confirm('<?=gtext('Do you really want to delete this URL?');?>')"><?=$g_img['unicode.del'];?></a></td>
																		<td></td>
<?php
																	else:
?>
																		<td><?=$g_img['unicode.del'];?></td>
																		<td></td>
																		<td></td>
<?php
																	endif;
?>
																</tr>
															</tbody>
														</table>
													</td>
												</tr>
<?php
											endforeach;
?>
										</tbody>
										<tfoot>
											<tr>
												<td class="lcenl" colspan="2"></td>
												<td class="lceadd">
													<a href="services_websrv_authurl.php" title="<?=gtext('Add URL');?>"><?=$g_img['unicode.add'];?></a>
												</td>
											</tr>
										</tfoot>
									</table>
									<span class="vexpl"><?=gtext("Define directories/URL's that require authentication.");?></span>
								</td>
							</tr>
<?php
							html_checkbox2("dirlisting",gettext('Directory listing'),!empty($pconfig['dirlisting']),gettext('Enable directory listing.'),gettext('A directory listing is generated when no index-files (index.php, index.html, index.htm or default.htm) are found in a directory.'),false);
							$helpinghand = '<a'
								. ' href="https://redmine.lighttpd.net/projects/lighttpd/wiki"'
								. ' target="_blank"'
								. ' rel="noreferrer"'
								. '>'
								. gettext('Please check the documentation')
								. '</a>.';
							html_textarea2('auxparam',gettext('Additional Parameters'),!empty($pconfig['auxparam']) ? $pconfig['auxparam'] : '',sprintf(gettext('These parameters will be added to %s.'),'websrv.conf')  . ' ' . $helpinghand,false,85,7,false,false);
?>
						</tbody>
					</table>
					<div id="submit">
						<input name="Submit" type="submit" class="formbtn" value="<?=gtext('Save & Restart');?>">
					</div>
<?php
					include 'formend.inc';
?>
				</td>
			</tr>
		</tbody>
	</table>
</form>
<?php
include 'fend.inc';
