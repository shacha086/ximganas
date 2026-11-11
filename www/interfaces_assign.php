<?php
/*
	interfaces_assign.php

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
require_once 'autoload.php';

use gui\document;
use common\arr;

/*
	In this file, "port" refers to the physical port name,
	while "interface" refers to LAN, WAN, or OPTn.
*/

arr::make_branch($config,'interfaces');
//	get list without VLAN interfaces
$portlist = get_interface_list();
foreach($portlist as $key => $val):
	$portlist[$key]['isvirtual'] = false;
endforeach;
unset($key,$val);
//	add WLAN interfaces
$cfg_wlan = arr::make_branch($config,'vinterfaces','wlan');
foreach($cfg_wlan as $wlanv):
	$portlist[$wlanv['if']] = $wlanv;
	$portlist[$wlanv['if']]['isvirtual'] = true;
endforeach;
unset($wlanv,$cfg_wlan);
//	add VLAN interfaces
$cfg_vlan = arr::make_branch($config,'vinterfaces','vlan');
foreach($cfg_vlan as $vlanv):
	$portlist[$vlanv['if']] = $vlanv;
	$portlist[$vlanv['if']]['isvirtual'] = true;
endforeach;
unset($vlanv,$cfg_vlan);
//	add LAGG interfaces
$cfg_lagg = arr::make_branch($config,'vinterfaces','lagg');
foreach($cfg_lagg as $laggv):
	$portlist[$laggv['if']] = $laggv;
	$portlist[$laggv['if']]['isvirtual'] = true;
endforeach;
unset($laggv,$cfg_lagg);
if($_POST):
	unset($input_errors);
//	build a list of the port names so we can see how the interfaces map
	$portifmap = [];
	foreach($portlist as $portname => $portinfo):
		$portifmap[$portname] = [];
	endforeach;
//	go through the list of ports selected by the user, build a list of port-to-interface mappings in portifmap
	foreach($_POST as $ifname => $ifport):
		if(preg_match('/^(lan$|opt)/',$ifname) === 1):
			$portifmap[$ifport][] = strtoupper($ifname);
		endif;
	endforeach;
//	deliver error message for any port with more than one assignment
	foreach($portifmap as $portname => $ifnames):
		if(count($ifnames) > 1):
			$errstr = sprintf('%s %s %s %u %s',gtext('Port'),$portname,gtext('was assigned to'),count($ifnames),gtext(' interfaces:'));
			foreach($portifmap[$portname] as $ifn):
				$errstr .= ' ' . $ifn;
			endforeach;
			$input_errors[] = $errstr;
		endif;
	endforeach;
	if(empty($input_errors)):
//		no errors detected, so update the config
		foreach($_POST as $ifname => $ifport):
			if(preg_match('/^(lan$|opt)/',$ifname) === 1):
				if(!is_array($ifport)):
					$config['interfaces'][$ifname]['if'] = $ifport;
//					check for wireless interfaces, set or clear ['wireless']
					if(preg_match($g['wireless_regex'],$ifport) === 1):
						arr::make_branch($config,'interfaces',$ifname,'wireless');
					else:
						unset($config['interfaces'][$ifname]['wireless']);
					endif;
//					make sure there is a name for OPTn
					if($ifname !== 'lan'):
						$config['interfaces'][$ifname]['descr'] ??= strtoupper($ifname);
					endif;
				endif;
			endif;
		endforeach;
		write_config();
		touch($d_sysrebootreqd_path);
	endif;
endif;
if(isset($_GET['act']) && $_GET['act'] == 'del'):
	$id = $_GET['id'];
	$ifn = $config['interfaces'][$id]['if'];
//	Stop interface.
	rc_exec_service("netif stop {$ifn}");
//	Remove ifconfig_xxx and ipv6_ifconfig_xxx entries.
	mwexec("/usr/local/sbin/rconf attribute remove 'ifconfig_{$ifn}'");
	mwexec("/usr/local/sbin/rconf attribute remove 'ipv6_ifconfig_{$ifn}'");
//	delete the specified OPTn
	unset($config['interfaces'][$id]);
//	shift down other OPTn interfaces to get rid of holes
//	the number of the OPTn port being deleted
	$i = substr($id,3);
	$i++;
//	look at the following OPTn ports
	while(isset($config['interfaces']['opt' . $i]) && is_array($config['interfaces']['opt' . $i])):
		$config['interfaces']['opt' . ($i - 1)] = $config['interfaces']['opt' . $i];
		if($config['interfaces']['opt' . ($i - 1)]['descr'] == 'OPT' . $i):
			$config['interfaces']['opt' . ($i - 1)]['descr'] = 'OPT' . ($i - 1);
		endif;
		unset($config['interfaces']['opt' . $i]);
		$i++;
	endwhile;
	write_config();
	touch($d_sysrebootreqd_path);
	$_SESSION['g']['headermenu'] = [];
	header('Location: interfaces_assign.php');
	exit;
endif;
if(isset($_GET['act']) && $_GET['act'] == 'add'):
//	find next free optional interface number
	$i = 1;
	while(isset($config['interfaces']['opt' . $i]) && is_array($config['interfaces']['opt' . $i])):
		$i++;
	endwhile;
	$newifname = 'opt' . $i;
	arr::make_branch($config,'interfaces',$newifname);
	$config['interfaces'][$newifname] = [];
	$config['interfaces'][$newifname]['descr'] = 'OPT' . $i;
//	Set IPv4 to 'DHCP' and IPv6 to 'Auto' per default.
	$config['interfaces'][$newifname]['ipaddr'] = 'dhcp';
	$config['interfaces'][$newifname]['ipv6addr'] = 'auto';
//	Find an unused port for this interface
	foreach($portlist as $portname => $portinfo):
		$portused = false;
		foreach($config['interfaces'] as $ifname => $ifdata):
			if(isset($ifdata['if']) && $ifdata['if'] == $portname):
				$portused = true;
				break;
			endif;
		endforeach;
		if(!$portused):
			$config['interfaces'][$newifname]['if'] = $portname;
			if(preg_match($g['wireless_regex'], $portname)):
				$config['interfaces'][$newifname]['wireless'] = [];
			endif;
			break;
		endif;
	endforeach;
	write_config();
	touch($d_sysrebootreqd_path);
	$_SESSION['g']['headermenu'] = [];
	header('Location: interfaces_assign.php');
	exit;
endif;
$pgtitle = [gtext('Network'),gtext('Interface Management')];
include 'fbegin.inc';
$document = new document();
$document->
	add_area_tabnav()->
		add_tabnav_upper()->
			ins_tabnav_record('interfaces_assign.php',gettext('Management'),gettext('Reload page'),true)->
			ins_tabnav_record('interfaces_wlan.php',gettext('WLAN'))->
			ins_tabnav_record('interfaces_vlan.php',gettext('VLAN'))->
			ins_tabnav_record('interfaces_lagg.php',gtext('LAGG'))->
			ins_tabnav_record('interfaces_bridge.php',gettext('Bridge'))->
			ins_tabnav_record('interfaces_carp.php',gettext('CARP'));
$document->render();
?>
<form action="interfaces_assign.php" method="post" name="iform" id="iform" onsubmit="spinner()"><table id="area_data"><tbody><tr><td id="area_data_frame">
<?php
	if(!empty($input_errors)):
		print_input_errors($input_errors);
	endif;
	if(file_exists($d_sysrebootreqd_path)):
		print_info_box(get_std_save_message(0));
	endif;
?>
	<table class="area_data_selection">
		<colgroup>
			<col style="width:45%">
			<col style="width:45%">
			<col style="width:10%">
		</colgroup>
		<thead>
<?php
			html_titleline2(gettext('Overview'),3);
?>
			<tr>
				<td class="lhell"><?=gtext('Interface');?></td>
				<td class="lhell"><?=gtext('Network Port');?></td>
				<td class="lhebl">&nbsp;</td>
			</tr>
		</thead>
		<tbody>
<?php
			foreach($config['interfaces'] as $ifname => $iface):
				if(isset($iface['descr']) && $iface['descr']):
					$ifdescr = $iface['descr'];
				else:
					$ifdescr = strtoupper($ifname);
				endif;
?>
				<tr>
					<td class="lcell"><strong><?=$ifdescr;?></strong></td>
					<td class="lcell">
						<select name="<?=$ifname;?>" class="formfld" id="<?=$ifname;?>">
<?php
							foreach($portlist as $portname => $portinfo):
?>
								<option value="<?=$portname;?>" <?php if($portname == $iface['if']) echo 'selected="selected"';?>>
<?php
									if($portinfo['isvirtual']):
										$descr = $portinfo['if'];
										if($portinfo['desc']):
											$descr .= sprintf(' (%s)',$portinfo['desc']);
										endif;
										echo htmlspecialchars($descr);
									else:
										echo htmlspecialchars(sprintf('%s (%s)',$portname,$portinfo['mac']));
									endif;
?>
								</option>
<?php
							endforeach;
?>
						</select>
					</td>
					<td class="lcebl">
<?php
						if(($ifname != 'lan') && ($ifname != 'wan')):
?>
							<a href="interfaces_assign.php?act=del&amp;id=<?=$ifname;?>"><img src="images/delete.png" title="<?=gtext('Delete interface');?>" border="0" alt="<?=gtext('Delete interface');?>" /></a>
<?php
						endif;
?>
					</td>
				</tr>
<?php
			endforeach;
?>
		</tbody>
<?php
		if(count($config['interfaces']) < count($portlist)):
?>
		<tfoot>
			<tr>
				<th class="lcenl" colspan="2"></th>
				<th class="lceadd">
					<a href="interfaces_assign.php?act=add">
						<img src="images/add.png" title="<?=gtext('Add interface');?>" alt="<?=gtext('Add interface');?>" class="spin oneemhigh"/>
					</a>
				</th>
			</tr>
		</tfoot>
<?php
		endif;
?>
	</table>
	<div id="submit">
		<input name="Submit" type="submit" class="formbtn" value="<?=gtext('Save');?>" />
	</div>
	<div id="remarks">
<?php
		$helpinghand = gettext('After you click "Save" you must reboot the server to make the changes take effect.')
		. ' '
		. gettext('You may also have to do one or more of the following steps before you can access your server again:')
		. '<ul>'
		. '<li><span class="vexpl">' . gettext('Change the IP address of your server') . '</span></li>'
		. '<li><span class="vexpl">' . gettext('Access the webGUI with the new IP address') . '</span></li>'
		. '</ul>';
		html_remark2('warning',gettext('Warning'),$helpinghand);
?>
	</div>
<?php
	include 'formend.inc';
?>
</td></tr></tbody></table></form>
<?php
include 'fend.inc';
