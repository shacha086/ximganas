<?php
/*
	system_firmware.php

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

//	for guiconfig.inc, true means not to execute command header('system_firmware.php') when file_exists($d_firmwarelock_path);
$d_running_system_firmware_page = true;

require_once 'autoload.php';
require_once 'auth.inc';
require_once 'guiconfig.inc';

use common\arr;

/**
 *	Checks with /etc/firm.url to see if a newer firmware version is available online
 *	@global array $g
 *	@param string $locale
 *	@return ?string Any HTML message from the server
 */
function check_firmware_version($locale) {
	global $g;

	$post = "product=" . rawurlencode(get_product_name())
	      . "&platform=" . rawurlencode($g['fullplatform'])
	      . "&version=" . rawurlencode(get_product_version())
	      . "&revision=" . rawurlencode(get_product_revision());
	$url = trim(get_firm_url());
	if(preg_match('/^([^\/]+)(\/.*)/',$url,$m)):
		$host = $m[1];
		$path = $m[2];
	else:
		$host = $url;
		$path = '';
	endif;
	$rfd = @fsockopen(sprintf('ssl://%s',$host),443,$errno,$errstr,3);
	if($rfd):
		$hdr = "POST $path/checkversion.php?locale=$locale HTTP/1.0\r\n";
		$hdr .= "Content-Type: application/x-www-form-urlencoded\r\n";
		$hdr .= "User-Agent: " . get_product_name() . "-webGUI/1.0\r\n";
		$hdr .= "Host: " . $host . "\r\n";
		$hdr .= "Content-Length: " . strlen($post) . "\r\n\r\n";
		fwrite($rfd,$hdr);
		fwrite($rfd,$post);
		$inhdr = true;
		$resp = '';
		while(!feof($rfd)):
			$line = fgets($rfd);
			if($inhdr):
				if(trim($line) === ''):
					$inhdr = false;
				endif;
			else:
				$resp .= $line;
			endif;
		endwhile;
		fclose($rfd);
		return $resp;
	endif;
	return null;
}
/**
 *
 *	@param string $url
 *	@param int $timeout
 *	@return bool or string
 */
function simplexml_load_file_from_url($url,$timeout = 5) {
	global $config;

	$ch = curl_init($url);
	if($ch !== false):
		$curl_cfgopt = [
			CURLOPT_HEADER => false,
			CURLOPT_FOLLOWLOCATION => true, // follow location
			CURLOPT_RETURNTRANSFER => true, // return content
			CURLOPT_SSL_VERIFYPEER => true, // verify certificate of peer
			CURLOPT_CAPATH => '/etc/ssl', // certificate directory
			CURLOPT_CAINFO => '/etc/ssl/cert.pem', // root certificates from the Mozilla project
			CURLOPT_CONNECTTIMEOUT => (int)$timeout // set connection and read timeout
		];
		$proxy_cfg = arr::make_branch($config,'system','proxy','http');
		$test = $proxy_cfg['enable'] ?? false;
		$is_proxy_enabled = is_bool($test) ? $test : true;
		unset($test);
		if($is_proxy_enabled):
			$curl_cfgopt[CURLOPT_PROXY] = $proxy_cfg['address'] ?? '';
			$curl_cfgopt[CURLOPT_PROXYPORT] = $proxy_cfg['port'] ?? '';
			$test = $proxy_cfg['auth'] ?? false;
			$is_auth_enabled = is_bool($test) ? $test : true;
			unset($test);
			if($is_auth_enabled):
				$curl_cfgopt[CURLOPT_PROXYUSERPWD] = sprintf('%s:%s',$proxy_cfg['username'] ?? '',$proxy_cfg['password'] ?? '');
			endif;
		endif;
		if(curl_setopt_array($ch,$curl_cfgopt)):
			$data = curl_exec($ch);
			$curl_err_code = curl_errno($ch);
			if($curl_err_code):
//				write error to log
				write_log(sprintf('cURL error %d: %s',$curl_err_code,curl_strerror($curl_err_code) ?? 'Unknown error'));
				unset($ch);
			else:
				unset($ch);
				if($data !== false):
//					save error setting
					$previous_value = libxml_use_internal_errors(true);
//					get xml structure
					$xml_data = simplexml_load_string($data);
					libxml_clear_errors();
//					revert to previous error setting
					libxml_use_internal_errors($previous_value);
					return $xml_data;
				endif;
			endif;
		endif;
	endif;
	return false;
}
/**
 *	Read and scan rss feed for available updates, upgrades, betas and nightlies
 *	@param	string $locale
 *	@return	array
 */
function check_firmware_version_rss($locale) {
	$rss_url = 'https://sourceforge.net/projects/xigmanas/rss?path=/';
	$retval = [
		'osmajor' => [],
		'osminor' => [],
		'productmajor' => [],
		'productminor' => [],
		'productrevision' => [],
		'beta' => [],
		'nightly' => []
	];
//	load rss into xml
	$xml = simplexml_load_file_from_url($rss_url);
	unset($rss_url);
	if(empty($xml)):
		$retval['productrevision'][] = gettext('No online information available.');
		return $retval;
	endif;
	if(empty($xml->channel)):
		$retval['productrevision'][] = gettext('No online information available.');
		return $retval;
	endif;
//	gather installed product version
	$productversion = trim(get_product_version());
	if(preg_match('/^(?P<osmajor>[0-9]+)\.(?P<osminor>[0-9]+)\.(?P<productmajor>[0-9]+)\.(?P<productminor>[0-9]+)$/',$productversion,$productinfo)):
	else:
//		fallback if regex fails to validate product version
		$productinfo = [
			'osmajor' => 0,
			'osminor' => 0,
			'productmajor' => 0,
			'productminor' => 0
		];
	endif;
	unset($productversion);
//	gather installed product revision
	$productinfo['productrevision'] = get_product_revision();
//	prepare regex support for filtering
	$product_name_regex = preg_quote(get_product_name());
	$platform_type_regex = preg_quote(get_platform_type());
	$dir_release_regex = sprintf('%s\-(?P<osmajor_d1>[0-9]+)\.(?P<osminor_d1>[0-9]+)\.(?P<productmajor_d1>[0-9]+)\.(?P<productminor_d1>[0-9]+)',$product_name_regex);
	$dir_beta_regex = sprintf('%s\-Beta',$product_name_regex);
	$dir_nightly_regex = sprintf('%s\-Nightly_Builds',$product_name_regex);
	$dir_2_regex = '(?P<osmajor_d2>[0-9]+)\.(?P<osminor_d2>[0-9]+)\.(?P<productmajor_d2>[0-9]+)\.(?P<productminor_d2>[0-9]+)\.(?P<productrevision_d2>[0-9]+)';
	$file_1_regex = '(?P<osmajor>[0-9]+)\.(?P<osminor>[0-9]+)\.(?P<productmajor>[0-9]+)\.(?P<productminor>[0-9]+)\.(?P<productrevision>[0-9]+)';
	$file_regex = sprintf('%s\-%s\-%s.*',$product_name_regex,$platform_type_regex,$file_1_regex);
//	final regex for filtering
	$release_regex = '/^\/' . $dir_release_regex . '\/' . $dir_2_regex . '\/' . $file_regex . '/i';
	$beta_regex = '/^\/' . $dir_beta_regex . '\/' . $dir_2_regex . '\/' . $file_regex . '/i';
	$nightly_regex = '/^\/' . $dir_nightly_regex . '\/' . $dir_2_regex . '\/' . $file_regex . '/i';
	unset($dir_release_regex,$dir_beta_regex,$dir_nightly_regex,$dir_2_regex,$file_1_regex,$file_regex);
//	scan rss feed
	$tests = ['osmajor','osminor','productmajor','productminor','productrevision'];
	foreach($xml->channel->item as $item):
		unset($info_release,$info_beta,$info_nightly,$destination);
		if(preg_match($release_regex,$item->title,$info_release)):
//			release found
			foreach($tests as $test):
				switch($productinfo[$test] <=> $info_release[$test]):
					case -1:
						$destination = $test;
					case 1:
						break 2;
				endswitch;
			endforeach;
		elseif(preg_match($beta_regex,$item->title,$info_beta)):
//			beta found
			foreach($tests as $test):
				switch($productinfo[$test] <=> $info_beta[$test]):
					case -1:
						$destination = 'beta';
					case 1:
						break 2;
				endswitch;
			endforeach;
		elseif(preg_match($nightly_regex,$item->title,$info_nightly)):
//			nightly found
			foreach($tests as $test):
				switch($productinfo[$test] <=> $info_nightly[$test]):
					case -1:
						$destination = 'nightly';
					case 1:
						break 2;
				endswitch;
			endforeach;
		endif;
		if(isset($destination)):
			$fqfn = pathinfo($item->title);
			$fullname = $fqfn['basename'];
//			try to convert to local time
			$rss_date = preg_replace('/UT$/','GMT',$item->pubDate);
			$time = strtotime($rss_date);
			if($time === false):
				$date = $item->pubDate;
			else:
				$date = get_datetime_locale($time);
			endif;
			$release = sprintf('<a href="%s" title="%s" target="_blank">%s (%s)</a>',htmlspecialchars($item->link),htmlspecialchars($item->title),htmlspecialchars($fullname),htmlspecialchars($date));
			$retval[$destination][$fullname] = $release;
		endif;
	endforeach;
//	sort results
	foreach(['osmajor','osminor','productmajor','productminor','productrevision','beta','nightly'] as $index):
		krsort($retval[$index]);
	endforeach;
	return $retval;
}
$fwupplatforms = ['embedded','full']; // platforms that support firmware updating
$page_mode = 'default';
$input_errors = [];
$errormsg = '';
$savemsg = '';
$locale = $config['system']['language'] ?? 'en_US';
//	check boot partition
$part1size = $g_install['part1size_embedded'];
$cfdevice = trim(file_get_contents(sprintf('%s/cfdevice',$g['etc_path'])));
$diskinfo = disks_get_diskinfo($cfdevice);
$part1ok = true;
$part1min = $g_install['part1min_embedded'];
switch($page_mode):
	case 'default':
		if(in_array($g['platform'],$fwupplatforms)):
//			check boot partition size except for zfs platforms
			if(!$g['zroot'] && $diskinfo['mediasize_mbytes'] < $part1min):
				$part1ok = false;
				$errormsg = sprintf(gtext("Boot partition is too small. You need to reinstall from LiveCD/LiveUSB or resize boot partition of %s.\n"),$cfdevice);
				$page_mode = 'info';
			endif;
		else:
			$errormsg = gtext('Firmware uploading is not supported on this platform.');
			$page_mode = 'info';
		endif;
		break;
endswitch;
switch($page_mode):
	case 'default':
		if(file_exists($d_firmwarelock_path)):
			$errormsg = gtext('A firmware upgrade is in progress.');
			$page_mode = 'info';
		endif;
		break;
endswitch;
switch($page_mode):
	case 'default':
		if(file_exists($d_sysrebootreqd_path)):
			$page_mode = 'info';
		endif;
		break;
endswitch;
switch($page_mode):
	case 'default':
		if($_POST && isset($_POST['submit'])):
			switch($_POST['submit']):
				case 'enable':
				case 'disable':
				case 'upgrade':
					$page_mode = $_POST['submit'];
					break;
			endswitch;
		endif;
		break;
endswitch;
switch($page_mode):
	case 'enable':
		$retval = rc_exec_script('/etc/rc.firmware enable');
		if($retval == 0):
			touch($d_fwupenabled_path);
//			delete all kinds of firmware files from ftmp folder
			unlink_if_exists(sprintf('%s/firmware.img',$g['ftmp_path']));
			unlink_if_exists(sprintf('%s/firmware.tgz',$g['ftmp_path']));
			unlink_if_exists(sprintf('%s/firmware.txz',$g['ftmp_path']));
		else:
			$input_errors[] = gtext('Failed to access in-memory file system.');
			$page_mode = 'disable';
		endif;
		break;
	case 'upgrade':
		if(!isset($_FILES['ulfile'])):
			$page_mode = 'disable';
		else:
			if(is_uploaded_file($_FILES['ulfile']['tmp_name'])):
//				dynamically rename firmware file extension regarding the platform.
//				also handle some errors here before firmware page locks.
				$prd_name = get_product_name();
				$firmware_file = null;
				$ul_file = $_FILES['ulfile']['name'];
				if($g['zroot']):
					if(preg_match(sprintf('/^%s\S*%s$/',preg_quote($prd_name),preg_quote('.tgz')),$ul_file)):
						$firmware_file = sprintf('%s/firmware.tgz',$g['ftmp_path']);
					elseif(preg_match(sprintf('/^%s\S*%s$/',preg_quote($prd_name),preg_quote('.txz')),$ul_file)):
						$firmware_file = sprintf('%s/firmware.txz',$g['ftmp_path']);
					else:
						$input_errors[] = gtext('Invalid firmware file.');
					endif;
				elseif(preg_match(sprintf('/^%s\S*%s$/',preg_quote($prd_name),preg_quote('.img.xz')),$ul_file)):
					$firmware_file = sprintf('%s/firmware.img',$g['ftmp_path']);
				else:
					$input_errors[] = gtext('Invalid firmware file.');
				endif;
//				verify firmware image(s)
				if(!stristr($ul_file,$g['fullplatform'])):
					$input_errors[] = gtext('The file you try to flash is not for this platform') . ' (' . $g['fullplatform'] . ').';
				elseif(!file_exists($_FILES['ulfile']['tmp_name'])):
//					probably out of memory for the MFS
					$input_errors[] = gtext('Firmware upload failed (out of memory?)');
				elseif(empty($input_errors) && !is_null($firmware_file)):
//					move the image so PHP won't delete it
					move_uploaded_file($_FILES['ulfile']['tmp_name'],$firmware_file);
					if($g['zroot']):
//						skip firmware verify on full, this is performed by the rc.firmware for tgz/txz file.
					elseif(!verify_xz_file($firmware_file)):
						$input_errors[] = gtext('The firmware file is corrupt.');
					endif;
				endif;
			else:
				$input_errors[] = gtext('Firmware upload failed with error message:') . sprintf(' %s',$g_file_upload_error[$_FILES['ulfile']['error']]);
			endif;
//			Upgrade firmware if there were no errors.
			if(empty($input_errors)):
				touch($d_firmwarelock_path);
				switch($g['platform']):
					case 'embedded':
						rc_exec_script_async(sprintf('/etc/rc.firmware upgrade %s',$firmware_file));
						break;
					case 'full':
						rc_exec_script_async(sprintf('/etc/rc.firmware fullupgrade %s',$firmware_file));
						break;
				endswitch;
				$savemsg = gtext('The firmware is now being installed. The server will reboot automatically.');
//				clean firmwarelock: Permit to force all pages to be redirect on the firmware page.
//				unlink_if_exists($d_firmwarelock_path);
//				clean fwupenabled: Permit to know if the ram drive /ftmp is created.
				unlink_if_exists($d_fwupenabled_path);
			else:
				$page_mode = 'disable';
			endif;
		endif;
		break;
endswitch;
//	must be seperate from enable and upgrade, mode could have been updated to disable on error
switch($page_mode):
	case 'disable':
//		delete all kinds of firmware files from ftmp folder
		unlink_if_exists(sprintf('%s/firmware.img',$g['ftmp_path']));
		unlink_if_exists(sprintf('%s/firmware.tgz',$g['ftmp_path']));
		unlink_if_exists(sprintf('%s/firmware.txz',$g['ftmp_path']));
		rc_exec_script('/etc/rc.firmware disable');
		unlink_if_exists($d_fwupenabled_path);
		$page_mode = 'default';
		break;
endswitch;
//	valid modes at this point: info, default, enable, upgrade
$fw_info_current_osver = '';
switch($page_mode):
	case 'info':
	case 'default':
	case 'enable':
		if(!isset($config['system']['disablefirmwarecheck'])):
			if(file_exists(sprintf('%s/firm.url',$g['etc_path']))):
				$fw_info_current_osver = check_firmware_version($locale);
			else:
				$fw_info_current_osver = check_firmware_version_rss($locale);
			endif;
		endif;
		break;
endswitch;
$pgtitle = [gtext('System'),gtext('Firmware Update')];
include 'fbegin.inc';
?>
<div class="area_data_top"></div>
<div id="area_data_frame">
<?php
	if(!empty($errormsg)):
		print_error_box($errormsg);
	endif;
	if(file_exists($d_sysrebootreqd_path)):
		print_info_box(get_std_save_message(0));
	endif;
	if(!empty($input_errors)):
		print_input_errors($input_errors);
	endif;
	if(!empty($savemsg)):
		print_info_box($savemsg);
	endif;
	switch($page_mode):
		case 'info':
		case 'default':
		case 'enable':
?>
			<table class="area_data_settings">
				<colgroup>
					<col class="area_data_settings_col_tag">
					<col class="area_data_settings_col_data">
				</colgroup>
				<thead>
<?php
					html_titleline2(gettext('Firmware Information'));
?>
				</thead>
				<tbody>
<?php
					html_text2('currentversion',gettext('Current Version'),sprintf('%s %s (%s)',get_product_name(),get_product_version(),get_product_revision()));
?>
				</tbody>
			</table>
			<table class="area_data_settings">
				<colgroup>
					<col class="area_data_settings_col_tag">
					<col class="area_data_settings_col_data">
				</colgroup>
				<thead>
<?php
					html_titleline2(gettext('Online Information'));
?>
				</thead>
				<tbody>
<?php
					if(isset($config['system']['disablefirmwarecheck'])):
						html_text2('onlineversion',gettext('Online Information'),gettext('Firmware upgrade check has been disabled.'));
					elseif(is_string($fw_info_current_osver) && preg_match('/\S/',$fw_info_current_osver)):
						html_text2('onlineversion',gettext('Online Information'),$fw_info_current_osver);
					elseif(is_array($fw_info_current_osver)):
						if(empty($fw_info_current_osver['productrevision'])):
							html_text2('onlineversion',gettext('Regular Product Updates'),gettext('There are no regular product updates available.'));
						else:
							html_text2('onlineversion',gettext('Regular Product Updates'),implode('<br />',$fw_info_current_osver['productrevision']));
						endif;
						if(!empty($fw_info_current_osver['productminor'])):
							html_text2('productminor',gettext('Product Version Updates'),implode('<br />',$fw_info_current_osver['productminor']));
						endif;
						if(!empty($fw_info_current_osver['productmajor'])):
							html_text2('productmajor',gettext('Major Product Version Updates'),implode('<br />',$fw_info_current_osver['productmajor']));
						endif;
						if(!empty($fw_info_current_osver['osminor'])):
							html_text2('osminor',gettext('OS Version Upgrades'),implode('<br />',$fw_info_current_osver['osminor']));
						endif;
						if(!empty($fw_info_current_osver['osmajor'])):
							html_text2('osmajor',gettext('Major OS Version Upgrades'),implode('<br />',$fw_info_current_osver['osmajor']));
						endif;
						if(!empty($fw_info_current_osver['beta'])):
							html_text2('beta',gettext('Beta Versions'),implode('<br />',$fw_info_current_osver['beta']));
						endif;
						if(!empty($fw_info_current_osver['nightly'])):
							html_text2('nightly',gettext('Nightly Builds'),implode('<br />',$fw_info_current_osver['nightly']));
						endif;
					else:
						html_text2('onlineversion',gettext('Online Information'),gettext('No online information available.'));
					endif;
?>
				</tbody>
			</table>
<?php
			break;
	endswitch;
	switch($page_mode):
		case 'default':
?>
			<form action="system_firmware.php" method="post" enctype="multipart/form-data" onsubmit="spinner()">
				<div id="submit">
					<button type="submit" name="submit" class="formbtn" id="button_enable" value="enable"><?=gtext('Enable Firmware Upgrade');?></button>
				</div>
<?php
				include 'formend.inc';
?>
			</form>
<?php
			break;
		case 'enable':
?>
			<form action="system_firmware.php" method="post" enctype="multipart/form-data" onsubmit="spinner()">
				<div id="submit">
					<table class="area_data_settings">
						<colgroup>
							<col class="area_data_settings_col_tag">
							<col class="area_data_settings_col_data">
						</colgroup>
						<thead>
<?php
							html_titleline2(gettext('Firmware Selection'));
?>
						</thead>
						<tbody><tr>
							<td class="celltagreq"><?=gtext('Choose File');?></td>
							<td class="celldatareq"><input name="ulfile" type="file" style="width:100%"/></td>
						</tr></tbody>
					</table>
				</div>
				<div id="submit">
					<button type="submit" name="submit" class="formbtn" id="button_disable" value="disable"><?=gtext('Disable Firmware Upgrade');?></button>
					<button type="submit" name="submit" class="formbtn" id="button_upgrade" value="upgrade"><?=gtext('Upgrade Firmware');?></button>
				</div>
				<br />
				<div id="remarks">
<?php
					$helpinghand = gettext('Do not abort the firmware upgrade process once it has started.')
						. '<br />'
						. '<a href="' . 'system_backup.php' . '">'
						. gettext('It is recommended that you backup the server configuration before you upgrade')
						. '</a>.';
					html_remark2('warning',gettext('Warning'),$helpinghand);
?>
				</div>
<?php
				include 'formend.inc';
?>
			</form>
<?php
			break;
	endswitch;
?>
</div>
<div class="area_data_pot"></div>
<?php
include 'fend.inc';
