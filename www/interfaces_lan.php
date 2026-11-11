<?php
/*
	interfaces_lan.php

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

$sphere_scriptname = basename(__FILE__);
$lancfg = &$config['interfaces']['lan'];
$optcfg = &$config['interfaces']['lan']; // Required for WLAN.

// Get interface informations.
$ifinfo = get_interface_info(get_ifname($lancfg['if']));
$pconfig['enable'] = isset($lancfg['enable']);
if(strcmp($lancfg['ipaddr'],'dhcp') == 0):
	$pconfig['type'] = 'DHCP';
	$pconfig['ipaddr'] = get_ipaddr($lancfg['if']);
	$pconfig['subnet'] = get_subnet_bits($lancfg['if']);
else:
	$pconfig['type'] = 'Static';
	$pconfig['ipaddr'] = $lancfg['ipaddr'];
	$pconfig['subnet'] = $lancfg['subnet'];
endif;
$pconfig['ipv6_enable'] = isset($lancfg['ipv6_enable']);
if(strcmp($lancfg['ipv6addr'],'auto') == 0):
	$pconfig['ipv6type'] = 'Auto';
	$pconfig['ipv6addr'] = get_ipv6addr($lancfg['if']);
else:
	$pconfig['ipv6type'] = 'Static';
	$pconfig['ipv6addr'] = $lancfg['ipv6addr'];
	$pconfig['ipv6subnet'] = $lancfg['ipv6subnet'];
endif;
$pconfig['gateway'] = get_ipv4defaultgateway();
$pconfig['ipv6gateway'] = get_ipv6defaultgateway();
$pconfig['ipv6privacy'] = isset($lancfg['ipv6privacy']);
$pconfig['mtu'] = !empty($lancfg['mtu']) ? $lancfg['mtu'] : '';
$pconfig['media'] = !empty($lancfg['media']) ? $lancfg['media'] : 'autoselect';
$pconfig['mediaopt'] = !empty($lancfg['mediaopt']) ? $lancfg['mediaopt'] : '';
$pconfig['polling'] = isset($lancfg['polling']);
$pconfig['extraoptions'] = !empty($lancfg['extraoptions']) ? $lancfg['extraoptions'] : '';
if(!empty($ifinfo['wolevents'])):
	$pconfig['wakeon'] = $lancfg['wakeon'] ?? '';
endif;
//	Wireless interface?
if(isset($lancfg['wireless'])):
	require 'interfaces_wlan.inc';
	wireless_config_init();
endif;
if($_POST):
	unset($input_errors);
	$pconfig = $_POST;
//	Input validation.
	$reqdfields = [];
	$reqdfieldsn = [];
	$reqdfieldst = [];
	if($_POST['type'] === 'Static'):
		$reqdfields = ['ipaddr','subnet'];
		$reqdfieldsn = [gtext('IP Address'),gtext('Subnet Bit Count')];
		do_input_validation($_POST,$reqdfields,$reqdfieldsn,$input_errors);
		if(($_POST['ipaddr'] && !is_ipv4addr($_POST['ipaddr']))):
			$input_errors[] = gtext('A valid IPv4 address must be specified.');
		endif;
		if($_POST['subnet'] && !filter_var($_POST['subnet'],FILTER_VALIDATE_INT,['options' => ['min_range' => 1,'max_range' => 32]])):
			$input_errors[] = gtext('A valid network bit count (1-32) must be specified.');
		endif;
	endif;
	if(isset($_POST['ipv6_enable']) && $_POST['ipv6_enable'] && ($_POST['ipv6type'] === 'Static')):
		$reqdfields = ['ipv6addr','ipv6subnet'];
		$reqdfieldsn = [gtext('IPv6 Address'),gtext('Prefix')];
		do_input_validation($_POST,$reqdfields,$reqdfieldsn,$input_errors);
		if(($_POST['ipv6addr'] && !is_ipv6addr($_POST['ipv6addr']))):
			$input_errors[] = gtext('A valid IPv6 address must be specified.');
		endif;
		if($_POST['ipv6subnet'] && !filter_var($_POST['ipv6subnet'],FILTER_VALIDATE_INT,['options' => ['min_range' => 1,'max_range' => 128]])):
			$input_errors[] = gtext('A valid prefix (1-128) must be specified.');
		endif;
		if(($_POST['ipv6gatewayr'] && !is_ipv6addr($_POST['ipv6gateway']))):
			$input_errors[] = gtext('A valid IPv6 Gateway address must be specified.');
		endif;
	endif;
//	Wireless interface?
	if(isset($lancfg['wireless'])):
		$wi_input_errors = wireless_config_post();
		if($wi_input_errors):
			if(is_array($input_errors)):
				$input_errors = array_merge($input_errors,$wi_input_errors);
			else:
				$input_errors = $wi_input_errors;
			endif;
		endif;
	endif;
	if(!$input_errors):
		if(strcmp($_POST['type'],'Static') == 0):
			$lancfg['ipaddr'] = $_POST['ipaddr'];
			$lancfg['subnet'] = $_POST['subnet'];
			$lancfg['gateway'] = $_POST['gateway'];
		elseif(strcmp($_POST['type'],'DHCP') == 0):
			$lancfg['ipaddr'] = 'dhcp';
		endif;
		$lancfg['ipv6_enable'] = isset($_POST['ipv6_enable']);
		if(strcmp($_POST['ipv6type'],'Static') == 0):
			$lancfg['ipv6addr'] = $_POST['ipv6addr'];
			$lancfg['ipv6subnet'] = $_POST['ipv6subnet'];
			$lancfg['ipv6gateway'] = $_POST['ipv6gateway'];
		elseif(strcmp($_POST['ipv6type'],'Auto') == 0):
			$lancfg['ipv6addr'] = 'auto';
			$lancfg['ipv6privacy'] = isset($_POST['ipv6privacy']);
		endif;
		$lancfg['mtu'] = $_POST['mtu'];
		$lancfg['media'] = $_POST['media'];
		$lancfg['mediaopt'] = $_POST['mediaopt'];
		$lancfg['polling'] = isset($_POST['polling']);
		$lancfg['extraoptions'] = $_POST['extraoptions'];
		if(!empty($ifinfo['wolevents'])):
			$lancfg['wakeon'] = $_POST['wakeon'];
		endif;
		write_config();
		touch($d_sysrebootreqd_path);
	endif;
endif;
$pgtitle = [gtext('Network'),gtext('LAN Management')];
include 'fbegin.inc';
?>
<script>
//<![CDATA[
	function enable_change(enable_change) {
		var endis = !(document.iform.ipv6_enable.checked || enable_change);

		if (enable_change.name == "ipv6_enable") {
			endis = !enable_change.checked;
			document.iform.ipv6type.disabled = endis;
			document.iform.ipv6addr.disabled = endis;
			document.iform.ipv6subnet.disabled = endis;
			document.iform.ipv6gateway.disabled = endis;
		} else {
			document.iform.ipv6type.disabled = endis;
			document.iform.ipv6addr.disabled = endis;
			document.iform.ipv6subnet.disabled = endis;
			document.iform.ipv6gateway.disabled = endis;
		}
		ipv6_type_change();
	}
	function type_change() {
		switch (document.iform.type.selectedIndex) {
			case 0: /* Static */
				document.iform.ipaddr.disabled = 0;
				document.iform.subnet.disabled = 0;
				document.iform.gateway.disabled = 0;
				break;
			case 1: /* DHCP */
				document.iform.ipaddr.disabled = 1;
				document.iform.subnet.disabled = 1;
				document.iform.gateway.disabled = 1;
				break;
		}
	}
	function ipv6_type_change() {
		switch (document.iform.ipv6type.selectedIndex) {
			case 0: /* Static */
				var endis = !(document.iform.ipv6_enable.checked);

				document.iform.ipv6addr.disabled = endis;
				document.iform.ipv6subnet.disabled = endis;
				document.iform.ipv6gateway.disabled = endis;
				document.iform.ipv6privacy.disabled = 1;
				break;
			case 1: /* Autoconfigure */
				document.iform.ipv6addr.disabled = 1;
				document.iform.ipv6subnet.disabled = 1;
				document.iform.ipv6gateway.disabled = 1;
				document.iform.ipv6privacy.disabled = endis;
				break;
		}
	}
	function media_change() {
		switch (document.iform.media.value) {
			case "autoselect":
				showElementById('mediaopt_tr','hide');
				break;
			default:
				showElementById('mediaopt_tr','show');
				break;
		}
	}
<?php
if(isset($lancfg['wireless'])):
?>
	function encryption_change() {
		switch (document.iform.encryption.value) {
			case "none":
				showElementById('wep_key_tr','hide');
				showElementById('wpa_keymgmt_tr','hide');
				showElementById('wpa_pairwise_tr','hide');
				showElementById('wpa_psk_tr','hide');
				break;
			case "wep":
				showElementById('wep_key_tr','show');
				showElementById('wpa_keymgmt_tr','hide');
				showElementById('wpa_pairwise_tr','hide');
				showElementById('wpa_psk_tr','hide');
				break;
			case "wpa":
				showElementById('wep_key_tr','hide');
				showElementById('wpa_keymgmt_tr','show');
				showElementById('wpa_pairwise_tr','show');
				showElementById('wpa_psk_tr','show');
				break;
		}
	}
<?php
endif;
?>
//]]>
</script>
<form action="<?=$sphere_scriptname;?>" method="post" name="iform" id="iform" onsubmit="spinner()" class="pagecontent">
	<div class="area_data_top"></div>
	<div id="area_data_frame">
<?php
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
				html_titleline2(gettext('IPv4 Settings'));
?>
			</thead>
			<tbody>
<?php
				html_combobox2('type',gettext('Type'),$pconfig['type'] ?? '',['Static' => gettext('Static'),'DHCP' => 'DHCP'],'',true,false,'type_change()');
				html_ipv4addrbox2('ipaddr','subnet',gettext('IP Address'),$pconfig['ipaddr'] ?? '',$pconfig['subnet'] ?? '','',true);
				html_inputbox2('gateway',gettext('Gateway'),$pconfig['gateway'] ?? '','',true,20);
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
				html_titleline_checkbox2('ipv6_enable',gettext('IPv6 Settings'),!empty($pconfig['ipv6_enable']),gettext('Activate'),'enable_change(this)');
?>
			</thead>
			<tbody>
<?php
				html_combobox2('ipv6type',gettext('Type'),$pconfig['ipv6type'] ?? '',['Static' => gettext('Static'),'Auto' => gettext('Auto')],'',true,false,'ipv6_type_change()');
				html_ipv6addrbox2('ipv6addr','ipv6subnet',gettext('IP Address'),$pconfig['ipv6addr'] ?? '',$pconfig['ipv6subnet'] ?? '','',true);
				html_inputbox2('ipv6gateway',gettext('Gateway'),$pconfig['ipv6gateway'] ?? '','',true,20);
				html_checkbox2('ipv6privacy',gettext('Privacy Extension'),!empty($pconfig['ipv6privacy']),gettext('Enable IPv6 privacy extensions.'),'',true);
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
				html_titleline2(gettext('Advanced Settings'));
?>
			</thead>
			<tbody>
<?php
				html_inputbox2('mtu',gettext('MTU'),$pconfig['mtu'] ?? '',gettext('Set the maximum transmission unit of the interface to n, default is interface specific. The MTU is used to limit the size of packets that are transmitted on an interface. Not all interfaces support setting the MTU, and some interfaces have range restrictions.'),false,5);
//				html_checkbox2('polling',gettext('Device Polling'),!empty($pconfig['polling']),gettext('Enable device polling'),gettext('Device polling is a technique that lets the system periodically poll network devices for new data instead of relying on interrupts. This can reduce CPU load and therefore increase throughput, at the expense of a slightly higher forwarding delay (the devices are polled 1000 times per second). Not all NICs support polling.'),false);
				html_combobox2('media',gettext('Media'),$pconfig['media'] ?? '',['autoselect' => gettext('Autoselect'),'10baseT/UTP' => '10baseT/UTP','100baseTX' => '100baseTX','1000baseTX' => '1000baseTX','1000baseSX' => '1000baseSX',],'',false,false,'media_change()');
				html_combobox2('mediaopt',gettext('Duplex'),$pconfig['mediaopt'] ?? '',['half-duplex' => 'half-duplex','full-duplex' => 'full-duplex'],'',false);
				if(!empty($ifinfo['wolevents'])):
					$wakeonoptions = [
						'off' => gettext('Off'),
						'wol' => gettext('On')];
					foreach ($ifinfo['wolevents'] as $woleventv):
						$wakeonoptions[$woleventv] = $woleventv;
					endforeach;
					html_combobox2('wakeon',gettext('Wake On LAN'),$pconfig['wakeon'] ?? '',$wakeonoptions,'',false);
				else:
					html_text2('wakeon',gettext('Wake On LAN'),gettext('Not available'));
				endif;
				html_inputbox2('extraoptions',gettext('Extra Options'),$pconfig['extraoptions'] ?? '',gettext('Extra options to ifconfig (usually empty).'),false,40);
				if(isset($lancfg['wireless'])):
					wireless_config_print();
				endif;
?>
			</tbody>
		</table>
		<div id="submit">
			<input name="Submit" type="submit" class="formbtn" value="<?=gtext('Save');?>" onclick="enable_change(true)">
		</div>
		<div id="remarks">
<?php
			$helpinghand = gettext('After you click "Save" you may also have to do one or more of the following steps before you can access this server again:')
				. '<ul>'
				. '<li>' . gettext('Change the IP address of your computer') . '</li>'
				. '<li>' . gettext('Access the webGUI with the new IP address') . '</li>'
				. '</ul>';
			html_remark2('warning',gettext('Warning'),$helpinghand);
?>
		</div>
	</div>
	<div class="area_data_pot"></div>
<?php
	include 'formend.inc';
?>
</form>
<script>
//<![CDATA[
type_change();
ipv6_type_change();
media_change();
enable_change(false);
<?php
if(isset($lancfg['wireless'])):
?>
encryption_change();
<?php
endif;
?>
//]]>
</script>
<?php
include 'fend.inc';
