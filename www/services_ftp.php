<?php
/*
	services_ftp.php

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

use gui\document;
use common\arr;

$l_sysloglevel = [
	'emerg' => gettext('Emergency Level'),
	'alert' => gettext('Alert Level'),
	'crit' => gettext('Critical Error Level'),
	'error' => gettext('Error Level'),
	'warn' => gettext('Warning Level'),
	'notice' => gettext('Notice Level'),
	'info' => gettext('Info Level'),
	'debug' => gettext('Debug Level')
];
arr::make_branch($config,'ftpd');
$pconfig['enable'] = isset($config['ftpd']['enable']);
$pconfig['port'] = $config['ftpd']['port'];
$pconfig['numberclients'] = $config['ftpd']['numberclients'];
$pconfig['maxconperip'] = $config['ftpd']['maxconperip'];
$pconfig['maxloginattempts'] = $config['ftpd']['maxloginattempts'];
$pconfig['timeout'] = $config['ftpd']['timeout'];
$pconfig['anonymousonly'] = isset($config['ftpd']['anonymousonly']);
$pconfig['localusersonly'] = isset($config['ftpd']['localusersonly']);
$pconfig['allowgroup'] = !empty($config['ftpd']['allowgroup']) ? $config['ftpd']['allowgroup'] : '';
$pconfig['pasv_max_port'] = $config['ftpd']['pasv_max_port'];
$pconfig['pasv_min_port'] = $config['ftpd']['pasv_min_port'];
$pconfig['pasv_address'] = $config['ftpd']['pasv_address'];
$pconfig['userbandwidthup'] = $config['ftpd']['userbandwidth']['up'];
$pconfig['userbandwidthdown'] = $config['ftpd']['userbandwidth']['down'];
$pconfig['anonymousbandwidthup'] = $config['ftpd']['anonymousbandwidth']['up'];
$pconfig['anonymousbandwidthdown'] = $config['ftpd']['anonymousbandwidth']['down'];
if(!empty($config['ftpd']['filemask'])):
	$pconfig['filemask'] = $config['ftpd']['filemask'];
else:
	$pconfig['filemask'] = '077';
endif;
if(!empty($config['ftpd']['directorymask'])):
	$pconfig['directorymask'] = $config['ftpd']['directorymask'];
else:
	$pconfig['directorymask'] = '022';
endif;
$pconfig['banner'] = str_replace(chr(27),'&#27;',base64_decode($config['ftpd']['banner'] ?? ''));
$pconfig['fxp'] = isset($config['ftpd']['fxp']);
$pconfig['allowrestart'] = isset($config['ftpd']['allowrestart']);
$pconfig['permitrootlogin'] = isset($config['ftpd']['permitrootlogin']);
$pconfig['chrooteveryone'] = isset($config['ftpd']['chrooteveryone']);
$pconfig['identlookups'] = isset($config['ftpd']['identlookups']);
$pconfig['usereversedns'] = isset($config['ftpd']['usereversedns']);
$pconfig['disabletcpwrapper'] = isset($config['ftpd']['disabletcpwrapper']);
$pconfig['tls'] = isset($config['ftpd']['tls']);
$pconfig['tlsrequired'] = isset($config['ftpd']['tlsrequired']);
$pconfig['privatekey'] = base64_decode($config['ftpd']['privatekey']);
$pconfig['certificate'] = base64_decode($config['ftpd']['certificate']);
$pconfig['sysloglevel'] = isset($config['ftpd']['sysloglevel']) ? $config['ftpd']['sysloglevel'] : 'notice';
if(isset($config['ftpd']['auxparam']) && is_array($config['ftpd']['auxparam'])):
	$pconfig['auxparam'] = implode("\n",$config['ftpd']['auxparam']);
endif;
if($_POST):
	unset($input_errors);
	$pconfig = $_POST;
	if(isset($_POST['enable']) && $_POST['enable']):
		// Input validation.
		$reqdfields = ['port','numberclients','maxconperip','maxloginattempts','timeout'];
		$reqdfieldsn = [gtext('TCP Port'),gtext('Max. Clients'),gtext('Max. Connections Per Host'),gtext('Max. Login Attempts'),gtext('Timeout')];
		$reqdfieldst = ['numeric','numeric','numeric','numeric','numeric'];
		if(!empty($_POST['tls'])):
			$reqdfields = array_merge($reqdfields,['certificate','privatekey']);
			$reqdfieldsn = array_merge($reqdfieldsn,[gtext('Certificate'),gtext('Private key')]);
			$reqdfieldst = array_merge($reqdfieldst,['certificate','privatekey']);
		endif;
		do_input_validation($_POST,$reqdfields,$reqdfieldsn,$input_errors);
		do_input_validation_type($_POST,$reqdfields,$reqdfieldsn,$reqdfieldst,$input_errors);
		if(!is_port($_POST['port'])):
			$input_errors[] = gtext('The TCP Port must be a valid port number.');
		endif;
		if(($_POST['numberclients'] < 1) || ($_POST['numberclients'] > 50)):
			$input_errors[] = gtext('The max. number of clients must be in the range from 1 to 50.');
		endif;
		if($_POST['maxconperip'] < 0):
			$input_errors[] = gtext('The max. connections per IP address must be either 0 (unlimited) or greater.');
		endif;
		if(!is_numericint($_POST['timeout'])):
			$input_errors[] = gtext('The maximum idle time must be a number.');
		endif;
		if(($_POST['pasv_min_port'] !== '0') && (($_POST['pasv_min_port'] < 1024) || ($_POST['pasv_min_port'] > 65535))):
			$input_errors[] = sprintf(gtext('The attribute %s must be in the range from %d to %d.'),gtext('Min. Passive Port'),1024,65535);
		endif;
		if(($_POST['pasv_max_port'] !== '0') && (($_POST['pasv_max_port'] < 1024) || ($_POST['pasv_max_port'] > 65535))):
			$input_errors[] = sprintf(gtext('The attribute %s must be in the range from %d to %d.'),gtext('Max. Passive Port'),1024,65535);
		endif;
		if(($_POST['pasv_min_port'] !== '0') && ($_POST['pasv_max_port'] !== '0')):
			if($_POST['pasv_min_port'] >= $_POST['pasv_max_port']):
				$input_errors[] = sprintf(gtext('The attribute %s must be less than %s.'),gtext('Min. Passive Port'),gtext('Max. Passive Port'));
			endif;
		endif;
		if(!empty($_POST['anonymousonly']) && !empty($_POST['localusersonly'])):
			$input_errors[] = gtext("It is impossible to enable 'Anonymous Users' and 'Authenticated Users' both at the same time.");
		endif;
	endif;
	if(empty($input_errors)):
		$config['ftpd']['enable'] = isset($_POST['enable']);
		$config['ftpd']['numberclients'] = $_POST['numberclients'];
		$config['ftpd']['maxconperip'] = $_POST['maxconperip'];
		$config['ftpd']['maxloginattempts'] = $_POST['maxloginattempts'];
		$config['ftpd']['timeout'] = $_POST['timeout'];
		$config['ftpd']['port'] = $_POST['port'];
		$config['ftpd']['anonymousonly'] = isset($_POST['anonymousonly']);
		$config['ftpd']['localusersonly'] = isset($_POST['localusersonly']);
		$config['ftpd']['allowgroup'] = $_POST['allowgroup'];
		$config['ftpd']['pasv_max_port'] = $_POST['pasv_max_port'];
		$config['ftpd']['pasv_min_port'] = $_POST['pasv_min_port'];
		$config['ftpd']['pasv_address'] = $_POST['pasv_address'];
		$config['ftpd']['banner'] = base64_encode(str_replace('&#27;',chr(27),$_POST['banner'] ?? '')); // Encode string, otherwise line breaks will get lost
		$config['ftpd']['filemask'] = $_POST['filemask'];
		$config['ftpd']['directorymask'] = $_POST['directorymask'];
		$config['ftpd']['fxp'] = isset($_POST['fxp']);
		$config['ftpd']['allowrestart'] = isset($_POST['allowrestart']);
		$config['ftpd']['permitrootlogin'] = isset($_POST['permitrootlogin']);
		$config['ftpd']['chrooteveryone'] = isset($_POST['chrooteveryone']);
		$config['ftpd']['identlookups'] = isset($_POST['identlookups']);
		$config['ftpd']['usereversedns'] = isset($_POST['usereversedns']);
		$config['ftpd']['disabletcpwrapper'] = isset($_POST['disabletcpwrapper']);
		$config['ftpd']['tls'] = isset($_POST['tls']);
		$config['ftpd']['tlsrequired'] = isset($_POST['tlsrequired']);
		$config['ftpd']['privatekey'] = base64_encode($_POST['privatekey']);
		$config['ftpd']['certificate'] = base64_encode($_POST['certificate']);
		$config['ftpd']['userbandwidth']['up'] = $pconfig['userbandwidthup'];
		$config['ftpd']['userbandwidth']['down'] = $pconfig['userbandwidthdown'];
		$config['ftpd']['anonymousbandwidth']['up'] = $pconfig['anonymousbandwidthup'];
		$config['ftpd']['anonymousbandwidth']['down'] = $pconfig['anonymousbandwidthdown'];
		$config['ftpd']['sysloglevel'] = $pconfig['sysloglevel'];
//		Write additional parameters.
		unset($config['ftpd']['auxparam']);
		foreach(explode("\n",$_POST['auxparam']) as $auxparam):
			$auxparam = trim($auxparam,"\t\n\r");
			if(!empty($auxparam)):
				$config['ftpd']['auxparam'][] = $auxparam;
			endif;
		endforeach;
		write_config();
		$retval = 0;
		if(!file_exists($d_sysrebootreqd_path)):
			config_lock();
			$retval |= rc_update_service('proftpd');
			$retval |= rc_update_service('mdnsresponder');
			config_unlock();
		endif;
		$savemsg = get_std_save_message($retval);
	endif;
endif;
$pgtitle = [gtext('Services'),gtext('FTP')];
include 'fbegin.inc';
?>
<script>
//<![CDATA[
function enable_change(enable_change) {
	var endis = !(document.iform.enable.checked || enable_change);
	document.iform.port.disabled = endis;
	document.iform.timeout.disabled = endis;
	document.iform.permitrootlogin.disabled = endis;
	document.iform.numberclients.disabled = endis;
	document.iform.maxconperip.disabled = endis;
	document.iform.maxloginattempts.disabled = endis;
	document.iform.anonymousonly.disabled = endis;
	document.iform.localusersonly.disabled = endis;
	document.iform.allowgroup.disabled = endis;
	document.iform.banner.disabled = endis;
	document.iform.fxp.disabled = endis;
	document.iform.allowrestart.disabled = endis;
	document.iform.pasv_max_port.disabled = endis;
	document.iform.pasv_min_port.disabled = endis;
	document.iform.pasv_address.disabled = endis;
	document.iform.filemask.disabled = endis;
	document.iform.directorymask.disabled = endis;
	document.iform.chrooteveryone.disabled = endis;
	document.iform.identlookups.disabled = endis;
	document.iform.usereversedns.disabled = endis;
	document.iform.disabletcpwrapper.disabled = endis;
	document.iform.tls.disabled = endis;
	document.iform.tlsrequired.disabled = endis;
	document.iform.privatekey.disabled = endis;
	document.iform.certificate.disabled = endis;
	document.iform.userbandwidthup.disabled = endis;
	document.iform.userbandwidthdown.disabled = endis;
	document.iform.anonymousbandwidthup.disabled = endis;
	document.iform.anonymousbandwidthdown.disabled = endis;
	document.iform.sysloglevel.disabled = endis;
	document.iform.auxparam.disabled = endis;
}

function tls_change() {
	switch (document.iform.tls.checked) {
		case true:
			showElementById('tlsrequired_tr','show');
			showElementById('privatekey_tr','show');
			showElementById('certificate_tr','show');
			break;

		case false:
			showElementById('tlsrequired_tr','hide');
			showElementById('privatekey_tr','hide');
			showElementById('certificate_tr','hide');
			break;
	}
}

function localusersonly_change() {
	switch (document.iform.localusersonly.checked) {
		case true:
			showElementById('allowgroup_tr','show');
			showElementById('anonymousbandwidthup_tr','hide');
			showElementById('anonymousbandwidthdown_tr','hide');
			break;

		case false:
			showElementById('allowgroup_tr','hide');
			showElementById('anonymousbandwidthup_tr','show');
			showElementById('anonymousbandwidthdown_tr','show');
			break;
	}
}

function anonymousonly_change() {
	switch (document.iform.anonymousonly.checked) {
		case true:
			showElementById('userbandwidthup_tr','hide');
			showElementById('userbandwidthdown_tr','hide');
			break;

		case false:
			showElementById('userbandwidthup_tr','show');
			showElementById('userbandwidthdown_tr','show');
			break;
	}
}
//]]>
</script>
<?php
$document = new document();
$document->
	add_area_tabnav()->
		add_tabnav_upper()->
			ins_tabnav_record('services_ftp.php',gettext('Settings'),gettext('Reload page'),true)->
			ins_tabnav_record('services_ftp_mod.php',gettext('Modules'));
$document->render();
?>
<form action="services_ftp.php" method="post" name="iform" id="iform" onsubmit="spinner()">
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
?>
					<table class="area_data_settings">
						<colgroup>
							<col class="area_data_settings_col_tag">
							<col class="area_data_settings_col_data">
						</colgroup>
						<thead>
<?php
							html_titleline_checkbox2('enable',gettext('File Transfer Protocol'),!empty($pconfig['enable']),gettext('Enable'),'enable_change(false)');
?>
						</thead>
						<tbody>
<?php
							html_inputbox2('port',gettext('TCP Port'),$pconfig['port'],sprintf(gettext('Enter a custom port number if you want to override the default port. (Default is %s).'),'21'),true,4);
							html_inputbox2('numberclients',gettext('Max. Clients'),$pconfig['numberclients'],gettext('Maximum number of users that can connect to the server.'),true,3);
							html_inputbox2('maxconperip',gettext('Max. Connections Per Host'),$pconfig['maxconperip'],gettext('Maximum number of connections allowed per IP address. (0 = unlimited).'),true,3);
							html_inputbox2('maxloginattempts',gettext('Max. Login Attempts'),$pconfig['maxloginattempts'],gettext('Maximum number of allowed password attempts before disconnection.'),true,3);
							html_inputbox2('timeout',gettext('Timeout'),$pconfig['timeout'],gettext('Maximum idle time in seconds.'),true,5);
							html_checkbox2('permitrootlogin',gettext('Permit Root Login'),!empty($pconfig['permitrootlogin']),gettext('Specifies whether it is allowed to login as superuser (root) directly.'),'',false);
							html_checkbox2('anonymousonly',gettext('Anonymous Users'),!empty($pconfig['anonymousonly']),gettext('Only allow anonymous users. Use this on a public FTP site with no remote FTP access to real accounts.'),'',false,false,'anonymousonly_change()',false);
							html_checkbox2('localusersonly',gettext('Authenticated Users'),!empty($pconfig['localusersonly']),gettext('Only allow authenticated users. Anonymous logins are prohibited.'),'',false,false,'localusersonly_change()',false);
							html_inputbox2('allowgroup',gettext('Group Allow'),$pconfig['allowgroup'],gettext('Comma-separated list of group names that are permitted to login to the FTP server. (empty = ftp group).'),false,40);
							$n_rows = min(64,max(8,1 + substr_count($pconfig['banner'],"\n")));
							html_textarea2('banner',gettext('Banner'),$pconfig['banner'],gettext('Greeting banner displayed to client when first connection comes in.'),false,65,$n_rows,false,false);
							html_separator2();
							html_titleline2(gettext('Advanced Settings'));
							html_inputbox2('filemask',gettext('File Mask'),$pconfig['filemask'],gettext('Use this option to override the file creation mask (077 by default).'),false,3);
							html_inputbox2('directorymask',gettext('Directory Mask'),$pconfig['directorymask'],gettext('Use this option to override the directory creation mask (022 by default).'),false,3);
							html_checkbox2('fxp',gettext('FXP'),!empty($pconfig['fxp']),gettext('Enable FXP protocol.'),gettext('FXP allows transfers between two remote servers without any file data going to the client asking for the transfer (insecure!).'),false);
							html_checkbox2('allowrestart',gettext('Resume'),!empty($pconfig['allowrestart']),gettext('Allow clients to resume interrupted uploads and downloads.'),'',false);
							html_checkbox2('chrooteveryone',gettext('Default Root'),!empty($pconfig['chrooteveryone']),gettext('chroot() everyone, but root.'),gettext('If default root is enabled, a chroot operation is performed immediately after a client authenticates. This can be used to effectively isolate the client from a portion of the host system filespace.'),false);
							html_checkbox2('identlookups',gettext('IdentLookups'),!empty($pconfig['identlookups']),gettext('Enable the ident protocol (RFC1413).'),gettext('When a client initially connects to the server the ident protocol is used to attempt to identify the remote username.'),false);
							html_checkbox2('usereversedns',gettext('Reverse DNS lookup'),!empty($pconfig['usereversedns']),gettext('Enable reverse DNS lookup.'),gettext("Enable reverse DNS lookup performed on the remote host's IP address for incoming active mode data connections and outgoing passive mode data connections."),false);
							html_checkbox2('disabletcpwrapper',gettext('TCP Wrapper'),!empty($pconfig['disabletcpwrapper']),gettext('Disable TCP wrapper (mod_wrap module).'),'',false);
							html_inputbox2('pasv_address',gettext('Masquerade Address'),$pconfig['pasv_address'],gettext('Causes the server to display the network information for the specified IP address or DNS hostname to the client, on the assumption that that IP address or DNS host is acting as a NAT gateway or port forwarder for the server.'),false,20);
							html_inputbox2('pasv_min_port',gettext('Passive Port Min.'),$pconfig['pasv_min_port'],gettext('The minimum port to allocate for PASV style data connections (0 = use any port).'),false,20);
							html_inputbox2('pasv_max_port',gettext('Passive Port Max.'),$pconfig['pasv_max_port'],gettext('The maximum port to allocate for PASV style data connections (0 = use any port).') . '<br /><br />' . gettext('Passive ports restricts the range of ports from which the server will select when sent the PASV command from a client. The server will randomly choose a number from within the specified range until an open port is found. The port range selected must be in the non-privileged range (eg. greater than or equal to 1024). It is strongly recommended that the chosen range be large enough to handle many simultaneous passive connections (for example, 49152-65534, the IANA-registered ephemeral port range).'),false,20);
							html_inputbox2('userbandwidthup',gettext('Upload Bandwidth'),$pconfig['userbandwidthup'],gettext('Authenticated user upload bandwith in KB/s. An empty field means infinity.'),false,5);
							html_inputbox2('userbandwidthdown',gettext('Download Bandwidth'),$pconfig['userbandwidthdown'],gettext('Authenticated user download bandwith in KB/s. An empty field means infinity.'),false,5);
							html_inputbox2('anonymousbandwidthup',gettext('Upload Bandwidth'),$pconfig['anonymousbandwidthup'],gettext('Anonymous user upload bandwith in KB/s. An empty field means infinity.'),false,5);
							html_inputbox2('anonymousbandwidthdown',gettext('Download Bandwidth'),$pconfig['anonymousbandwidthdown'],gettext('Anonymous user download bandwith in KB/s. An empty field means infinity.'),false,5);
							html_checkbox2('tls',gettext('TLS'),!empty($pconfig['tls']),gettext('Enable TLS connections.'),'',false,false,'tls_change()',false);
							html_textarea2('certificate',gettext('Certificate'),$pconfig['certificate'],gettext('Paste a signed certificate in X.509 PEM format here.'),true,65,7,false,false);
							html_textarea2('privatekey',gettext('Private Key'),$pconfig['privatekey'],gettext('Paste an private key in PEM format here.'),true,65,7,false,false);
							html_checkbox2('tlsrequired',gettext('TLS Only'),!empty($pconfig['tlsrequired']),gettext('Allow TLS connections only.'),'',false);
							html_combobox2('sysloglevel',gettext('Syslog Level'),$pconfig['sysloglevel'],$l_sysloglevel,'');
							$helpinghand = '<a'
								. ' href="http://www.proftpd.org/docs/directives/index.html"'
								. ' target="_blank"'
								. ' rel="noreferrer">'
								. gettext('Please check the documentation')
								. '</a>.';
							html_textarea2('auxparam',gettext('Additional Parameters'),!empty($pconfig['auxparam']) ? $pconfig['auxparam'] : '',sprintf(gettext('These parameters are added to %s.'),"proftpd.conf") . ' ' . $helpinghand,false,65,5,false,false);
?>
						</tbody>
					</table>
					<div id="submit">
						<input name="Submit" type="submit" class="formbtn" value="<?=gtext("Save & Restart");?>" onclick="enable_change(true)" />
					</div>
<?php
					include 'formend.inc';
?>
				</td>
			</tr>
		</tbody>
	</table>
</form>
<script>
//<![CDATA[
enable_change(false);
anonymousonly_change();
localusersonly_change();
tls_change();
//]]>
</script>
<?php
include 'fend.inc';
