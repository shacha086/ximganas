<?php
/*
	interfaces_opt.php

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

require_once 'auth.inc';
require_once 'guiconfig.inc';

unset($index);
if(isset($_GET['index']) && $_GET['index']):
	$index = $_GET['index'];
elseif(isset($_POST['index']) && $_POST['index']):
	$index = $_POST['index'];
endif;
if(!$index):
	exit;
endif;
$optcfg = &$config['interfaces']['opt' . $index];
//	get interface information.
$ifinfo = get_interface_info(get_ifname($optcfg['if']));
if($config['interfaces']['opt' . $index]['ipaddr'] == 'dhcp'):
	$pconfig['type'] = 'DHCP';
	$pconfig['ipaddr'] = get_ipaddr($optcfg['if']);
	$pconfig['subnet'] = get_subnet_bits($optcfg['if']);
else:
	$pconfig['type'] = 'Static';
	$pconfig['ipaddr'] = $optcfg['ipaddr'];
  $pconfig['subnet'] = $optcfg['subnet'];
endif;
$pconfig['ipv6_enable'] = isset($optcfg['ipv6_enable']);
if($config['interfaces']['opt' . $index]['ipv6addr'] == 'auto'):
	$pconfig['ipv6type'] = 'Auto';
	$pconfig['ipv6addr'] = get_ipv6addr($optcfg['if']);
else:
	$pconfig['ipv6type'] = 'Static';
	$pconfig['ipv6addr'] = $optcfg['ipv6addr'];
	$pconfig['ipv6subnet'] = $optcfg['ipv6subnet'];
endif;
$pconfig['descr'] = $optcfg['descr'];
$pconfig['enable'] = isset($optcfg['enable']);
$pconfig['mtu'] = !empty($optcfg['mtu']) ? $optcfg['mtu'] : '';
$pconfig['polling'] = isset($optcfg['polling']);
$pconfig['media'] = !empty($optcfg['media']) ? $optcfg['media'] : 'autoselect';
$pconfig['mediaopt'] = !empty($optcfg['mediaopt']) ? $optcfg['mediaopt'] : '';
$pconfig['extraoptions'] = !empty($optcfg['extraoptions']) ? $optcfg['extraoptions'] : '';
if(!empty($ifinfo['wolevents'])):
	$pconfig['wakeon'] = $optcfg['wakeon'] ?? 'off';
endif;
//	Wireless interface?
if(isset($optcfg['wireless'])):
	require 'interfaces_wlan.inc';
	wireless_config_init();
endif;
if($_POST):
	unset($input_errors);
	$pconfig = $_POST;
//	input validation.
	if(isset($_POST['enable']) && $_POST['enable']):
//		description unique?
		for($i = 1; isset($config['interfaces']['opt' . $i]); $i++):
			if($i != $index):
				if($config['interfaces']['opt' . $i]['descr'] == $_POST['descr']):
					$input_errors[] = gtext('An interface with the specified description already exists.');
				endif;
			endif;
		endfor;
		if ($_POST['type'] === 'Static'):
			$reqdfields = ['descr','ipaddr','subnet'];
			$reqdfieldsn = [gtext('Description'),gtext('IP Address'),gtext('Subnet Bit Count')];
			do_input_validation($_POST,$reqdfields,$reqdfieldsn,$input_errors);
			if(($_POST['ipaddr'] && !is_ipv4addr($_POST['ipaddr']))):
				$input_errors[] = gtext('A valid IP address must be specified.');
			endif;
			if($_POST['subnet'] && !filter_var($_POST['subnet'],FILTER_VALIDATE_INT,['options' => ['min_range' => 1,'max_range' => 32]])):
				$input_errors[] = gtext('A valid network bit count (1-32) must be specified.');
			endif;
		endif;
		if(isset($_POST['ipv6_enable']) && $_POST['ipv6_enable'] && ($_POST['ipv6type'] === 'Static')):
			$reqdfields = ['ipv6addr','ipv6subnet'];
			$reqdfieldsn = [gtext('IPv6 address'),gtext('Prefix')];
			do_input_validation($_POST,$reqdfields,$reqdfieldsn,$input_errors);
			if(($_POST['ipv6addr'] && !is_ipv6addr($_POST['ipv6addr']))):
				$input_errors[] = gtext('A valid IPv6 address must be specified.');
			endif;
			if($_POST['ipv6subnet'] && !filter_var($_POST['ipv6subnet'],FILTER_VALIDATE_INT,['options' => ['min_range' => 1,'max_range' => 128]])):
				$input_errors[] = gtext('A valid prefix (1-128) must be specified.');
			endif;
			if(($_POST['mtu'] && !is_mtu($_POST['mtu']))):
				$input_errors[] = gtext('A valid mtu size must be specified.');
			endif;
		endif;
	endif;
//	wireless interface?
	if(isset($optcfg['wireless'])):
		$wi_input_errors = wireless_config_post();
		if($wi_input_errors):
			if(is_array($input_errors)):
				$input_errors = array_merge($input_errors,$wi_input_errors);
			else:
				$input_errors = $wi_input_errors;
			endif;
		endif;
	endif;
	if(empty($input_errors)):
		if(strcmp($_POST['type'],'Static') == 0):
			$optcfg['ipaddr'] = $_POST['ipaddr'];
			$optcfg['subnet'] = $_POST['subnet'];
		elseif(strcmp($_POST['type'],'DHCP') == 0):
			$optcfg['ipaddr'] = 'dhcp';
		endif;
		$optcfg['ipv6_enable'] = isset($_POST['ipv6_enable']) ? true : false;
		if(strcmp($_POST['ipv6type'],'Static') == 0):
			$optcfg['ipv6addr'] = $_POST['ipv6addr'];
			$optcfg['ipv6subnet'] = $_POST['ipv6subnet'];
		elseif(strcmp($_POST['ipv6type'],'Auto') == 0):
			$optcfg['ipv6addr'] = 'auto';
		endif;
		$optcfg['descr'] = $_POST['descr'];
		$optcfg['mtu'] = $_POST['mtu'];
		$optcfg['enable'] = isset($_POST['enable']) ? true : false;
		$optcfg['polling'] = isset($_POST['polling']) ? true : false;
		$optcfg['media'] = $_POST['media'];
		$optcfg['mediaopt'] = $_POST['mediaopt'];
		$optcfg['extraoptions'] = $_POST['extraoptions'];
		if (!empty($ifinfo['wolevents'])):
			$optcfg['wakeon'] = $_POST['wakeon'];
		endif;
		write_config();
		touch($d_sysrebootreqd_path);
	endif;
endif;
$desc = filter_var($optcfg['descr'],FILTER_VALIDATE_REGEXP,['flags' => FILTER_REQUIRE_SCALAR,'options' => ['default' => sprintf('OPT%d',$index),'regexp' => '/\S/']]);
$pgtitle = [gtext('Network'),sprintf('%s (%s)',gtext('Optional'),htmlspecialchars($desc))];
include 'fbegin.inc';
?>
<script>
//<![CDATA[
function enable_change(enable_change) {
	var endis = !(document.iform.enable.checked || enable_change);

	if (enable_change.name == "ipv6_enable") {
		endis = !enable_change.checked;

		document.iform.ipv6type.disabled = endis;
		document.iform.ipv6addr.disabled = endis;
		document.iform.ipv6subnet.disabled = endis;
	} else {
		document.iform.type.disabled = endis;
		document.iform.descr.disabled = endis;
		document.iform.mtu.disabled = endis;
		//document.iform.polling.disabled = endis;
		document.iform.media.disabled = endis;
		document.iform.mediaopt.disabled = endis;
<?php if (!empty($ifinfo['wolevents'])):?>
		document.iform.wakeon.disabled = endis;
<?php endif;?>
		document.iform.extraoptions.disabled = endis;
		document.iform.ipv6_enable.disabled = endis;
<?php if (isset($optcfg['wireless'])):?>
		document.iform.standard.disabled = endis;
		document.iform.ssid.disabled = endis;
		document.iform.scan_ssid.disabled = endis;
		document.iform.channel.disabled = endis;
		document.iform.encryption.disabled = endis;
		document.iform.wep_key.disabled = endis;
		document.iform.wpa_keymgmt.disabled = endis;
		document.iform.wpa_pairwise.disabled = endis;
		document.iform.wpa_psk.disabled = endis;
<?php endif;?>

		if (document.iform.enable.checked == true) {
			endis = !(document.iform.ipv6_enable.checked || enable_change);
		}

		document.iform.ipv6type.disabled = endis;
		document.iform.ipv6addr.disabled = endis;
		document.iform.ipv6subnet.disabled = endis;
	}

	type_change();
	ipv6_type_change();
	media_change();
<?php if (isset($optcfg['wireless'])):?>
	encryption_change();
<?php endif;?>
}

function type_change() {
	switch (document.iform.type.selectedIndex) {
		case 0: /* Static */
			var endis = !(document.iform.enable.checked);
			document.iform.ipaddr.disabled = endis;
			document.iform.subnet.disabled = endis;
			break;
		case 1: /* DHCP */
			document.iform.ipaddr.disabled = 1;
			document.iform.subnet.disabled = 1;
			break;
	}
}

function ipv6_type_change() {
	switch (document.iform.ipv6type.selectedIndex) {
		case 0: /* Static */
			var endis = !(document.iform.enable.checked && document.iform.ipv6_enable.checked);
			document.iform.ipv6addr.disabled = endis;
			document.iform.ipv6subnet.disabled = endis;
			break;
		case 1: /* Autoconfigure */
			document.iform.ipv6addr.disabled = 1;
			document.iform.ipv6subnet.disabled = 1;
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
if(isset($optcfg['wireless'])):
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
<?php
if($optcfg['if']):
?>
	<form action="interfaces_opt.php" method="post" name="iform" id="iform" onsubmit="spinner()">
		<table id="area_data"><tbody><tr><td id="area_data_frame">
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
					html_titleline_checkbox2('enable',gettext('IPv4 Settings'),!empty($pconfig['enable']) ? true : false,gettext('Activate'),'enable_change(false)');
?>
				</thead>
				<tbody>
<?php
					html_combobox2('type',gettext('Type'),$pconfig['type'],['Static' => gettext('Static'),'DHCP' => gettext('DHCP')],'',true,false,'type_change()');
					html_inputbox2('descr',gettext('Description'),$pconfig['descr'],gettext('You may enter a description here for your reference.'),true,20);
					html_ipv4addrbox2('ipaddr','subnet',gettext('IP Address'),!empty($pconfig['ipaddr']) ? $pconfig['ipaddr'] : '',!empty($pconfig['subnet']) ? $pconfig['subnet'] : '','',true);
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
					html_separator2();
					html_titleline_checkbox2('ipv6_enable',gettext('IPv6 Settings'),!empty($pconfig['ipv6_enable']) ? true : false,gettext('Activate'),'enable_change(this)');
?>
				</thead>
				<tbody>
<?php
					html_combobox2('ipv6type',gettext('Type'),$pconfig['ipv6type'],['Static' => gettext('Static'),'Auto' => gettext('Auto')],'',true,false,'ipv6_type_change()');
					html_ipv6addrbox2('ipv6addr','ipv6subnet',gettext('IP Address'),!empty($pconfig['ipv6addr']) ? $pconfig['ipv6addr'] : '',!empty($pconfig['ipv6subnet']) ? $pconfig['ipv6subnet'] : '','',true);
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
					html_separator2();
					html_titleline2(gettext('Advanced Settings'));
?>
				</thead>
				<tbody>
<?php
					html_inputbox2('mtu',gettext('MTU'),$pconfig['mtu'],gettext('Set the maximum transmission unit of the interface to n, default is interface specific. The MTU is used to limit the size of packets that are transmitted on an interface. Not all interfaces support setting the MTU, and some interfaces have range restrictions.'),false,5);
//					html_checkbox2('polling',gettext('Device polling'),$pconfig['polling'] ? true : false,gettext('Enable device polling'),gettext('Device polling is a technique that lets the system periodically poll network devices for new data instead of relying on interrupts. This can reduce CPU load and therefore increase throughput, at the expense of a slightly higher forwarding delay (the devices are polled 1000 times per second). Not all NICs support polling.'),false);
					html_combobox2('media',gettext('Media'),$pconfig['media'],['autoselect' => gettext('Autoselect'),'10baseT/UTP' => '10baseT/UTP','100baseTX' => '100baseTX','1000baseTX' => '1000baseTX','1000baseSX' => '1000baseSX',],'',false,false,'media_change()');
					html_combobox2('mediaopt',gettext('Duplex'),$pconfig['mediaopt'],['half-duplex' => 'half-duplex','full-duplex' => 'full-duplex'],'',false);
					if(!empty($ifinfo['wolevents'])):
						$wakeonoptions = ['off' => gettext('Off'),'wol' => gettext('On')]; foreach ($ifinfo['wolevents'] as $woleventv) { $wakeonoptions[$woleventv] = $woleventv; };
						html_combobox2('wakeon',gettext('Wake On LAN'),$pconfig['wakeon'],$wakeonoptions,'',false);
					else:
						html_text2('wakeon',gettext('Wake On LAN'),gettext('Not available'));
					endif;
					html_inputbox2('extraoptions',gettext('Extra Options'),$pconfig['extraoptions'],gettext('Extra options to ifconfig (usually empty).'),false,40);
					if(isset($optcfg['wireless'])):
						wireless_config_print();
					endif;
?>
				</tbody>
			</table>
			<div id="submit">
				<input name="index" type="hidden" value="<?=$index;?>" />
				<input name="Submit" type="submit" class="formbtn" value="<?=gtext('Save');?>" onclick="enable_change(true)" />
			</div>
		</td></tr></tbody></table>
<?php
		include 'formend.inc';
?>
	</form>
<script>
//<![CDATA[
enable_change(false);
//]]>
</script>
<?php
else:
?>
	<strong>Optional <?=$index;?> has been disabled because there is no OPT<?=$index;?> interface.</strong>
<?php
endif;
include 'fend.inc';
