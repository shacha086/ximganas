<?php
/*
	services_hast.php

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

arr::make_branch($config,'hast','auxparam');
//	arr::make_branch($config,'hast','hastresource');
arr::make_branch($config,'vinterfaces','carp');
$pconfig['enable'] = isset($config['hast']['enable']);
//	$pconfig['role'] = $config['hast']['role'];
$pconfig['auxparam'] = implode("\n",$config['hast']['auxparam']);
$nodeid = @exec("/sbin/sysctl -q -n kern.hostuuid");
if(empty($nodeid)):
	$nodeid = 'unknown';
endif;
$nodename = system_get_hostname();
if(empty($nodename)):
	$nodename = 'unknown';
endif;
$a_carp = &$config['vinterfaces']['carp'];
arr::sort_key($a_carp,'if');
if(!sizeof($a_carp)):
	$errormsg = gtext('No configured CARP interfaces.')
		. ' '
		. '<a href="' . 'interfaces_carp.php' . '">'
		. gtext('Please add a new CARP interface first')
		. '</a>.';
endif;
if($_POST):
	unset($input_errors);
	unset($errormsg);
	$pconfig = $_POST;
	$preempt = @exec("/sbin/sysctl -q -n net.inet.carp.preempt");
	if(isset($_POST['switch_backup']) && $_POST['switch_backup']):
//		down all carp
		foreach($a_carp as $carp):
//			system("/sbin/ifconfig {$carp['if']} down");
			mwexec("/etc/rc.d/netif stop {$carp['if']}");
			if($carp['advskew'] <= 1):
				system("/sbin/ifconfig {$carp['if']} vhid {$carp['vhid']} state backup advskew 240");
			else:
				system("/sbin/ifconfig {$carp['if']} vhid {$carp['vhid']} state backup");
			endif;
//			system("/sbin/ifconfig {$carp['if']} up");
			mwexec("/etc/rc.d/netif start {$carp['if']}");
		endforeach;
//		wait for the primary disk to disappear
		$retry = 60;
		while($retry > 0):
			$result = mwexec("pgrep -lf 'hastd: .* \(primary\)' > /dev/null 2>&1");
			if($result != 0):
				break;
			endif;
			$retry--;
			sleep(1);
		endwhile;
		if($retry <= 0):
			write_log('error: still hasted primary exists!');
		endif;
//		up and set backup all carp
		if($preempt == 0 || (isset($a_carp[0]) && $a_carp[0]['advskew'] > 1)):
			foreach($a_carp as $carp):
//				system("/sbin/ifconfig {$carp['if']} up vhid {$carp['vhid']} state backup");
			endforeach;
		endif;
		header('Location: services_hast.php');
		exit;
	endif;
	if(isset($_POST['switch_master']) && $_POST['switch_master']):
//		up and set master all carp
		$role = get_hast_role();
		foreach($a_carp as $carp):
			$state = @exec("/sbin/ifconfig {$carp['if']} | grep  'carp:' | awk '{ print tolower($2) }'");
			if($carp['advskew'] <= 1):
				system("/sbin/ifconfig {$carp['if']} up vhid {$carp['vhid']} state master advskew {$carp['advskew']}");
			else:
				system("/sbin/ifconfig {$carp['if']} up vhid {$carp['vhid']} state master");
			endif;
//			if already master, use linkup action
			if($state == 'master' && $role != 'primary'):
				$action = $carp['linkup'];
				$result = mwexec($action);
			endif;
		endforeach;
//		wait for the secondary disk to disappear
		$retry = 60;
		while($retry > 0):
			$result = mwexec("pgrep -lf 'hastd: .* \(secondary\)' > /dev/null 2>&1");
			if($result != 0):
				break;
			endif;
			$retry--;
			sleep(1);
		endwhile;
		if($retry <= 0):
			write_log('error: still hasted secondary exists!');
		endif;
		header('Location: services_hast.php');
		exit;
	endif;
//	Input validation.
/*
	$reqdfields = ['role'];
	$reqdfieldsn = [gtext('HAST role')];
	$reqdfieldst = ['string'];
	do_input_validation($_POST,$reqdfields,$reqdfieldsn,$input_errors);
	do_input_validation_type($_POST,$reqdfields,$reqdfieldsn,$reqdfieldst,$input_errors);
*/
	if(empty($input_errors)):
		$old_enable = isset($config['hast']['enable']);
		$config['hast']['enable'] = isset($_POST['enable']);
//		$config['hast']['role'] = $_POST['role'];
		unset($config['hast']['auxparam']);
		foreach(explode("\n",$_POST['auxparam']) as $auxparam):
			$auxparam = trim($auxparam,"\t\n\r");
			if(!empty($auxparam)):
				$config['hast']['auxparam'][] = $auxparam;
			endif;
		endforeach;
		$retval = 0;
		if($old_enable == false && $config['hast']['enable'] == true):
//			disable services
			arr::make_branch($config,'samba');
			$config['samba']['enable'] = false;
			arr::make_branch($config,'ftpd');
			$config['ftpd']['enable'] = false;
			arr::make_branch($config,'tftpd');
			$config['tftpd']['enable'] = false;
//			arr::make_branch($config,'sshd');
//			$config['sshd']['enable'] = false;
			arr::make_branch($config,'nfsd');
			$config['nfsd']['enable'] = false;
			arr::make_branch($config,'afp');
			$config['afp']['enable'] = false;
			arr::make_branch($config,'rsyncd');
			$config['rsyncd']['enable'] = false;
			arr::make_branch($config,'unison');
			$config['unison']['enable'] = false;
			arr::make_branch($config,'iscsitarget');
			$config['iscsitarget']['enable'] = false;
			arr::make_branch($config,'daap');
			$config['daap']['enable'] = false;
			arr::make_branch($config,'dynamicdns');
			$config['dynamicdns']['enable'] = false;
			arr::make_branch($config,'snmpd');
			$config['snmpd']['enable'] = false;
			arr::make_branch($config,'ups');
			$config['ups']['enable'] = false;
			arr::make_branch($config,'websrv');
			$config['websrv']['enable'] = false;
			arr::make_branch($config,'bittorrent');
			$config['bittorrent']['enable'] = false;
			arr::make_branch($config,'lcdproc');
			$config['lcdproc']['enable'] = false;
//			update config
			write_config();
//			stop services
			config_lock();
			$retval |= rc_update_service('samba');
			$retval |= rc_update_service('proftpd');
			$retval |= rc_update_service('tftpd');
//			$retval |= rc_update_service('sshd');
			$retval |= rc_update_service('rpcbind');
			$retval |= rc_update_service('mountd');
			$retval |= rc_update_service('nfsd');
			$retval |= rc_update_service('statd');
			$retval |= rc_update_service('lockd');
			$retval |= rc_update_service('netatalk');
			$retval |= rc_update_service('rsyncd');
			$retval |= rc_update_service('unison');
			$retval |= rc_update_service('iscsi_target');
			$retval |= rc_update_service('mt-daapd');
			$retval |= rc_update_service('inadyn');
			$retval |= rc_update_service('bsnmpd');
			$retval |= rc_update_service('nut');
			$retval |= rc_update_service('nut_upslog');
			$retval |= rc_update_service('nut_upsmon');
			$retval |= rc_exec_service('websrv_htpasswd');
			$retval |= rc_update_service('websrv');
			$retval |= rc_update_service('transmission');
			$retval |= rc_update_service('mdnsresponder');
			config_unlock();
		else:
			write_config();
		endif;
		if(!file_exists($d_sysrebootreqd_path)):
			config_lock();
			$retval |= rc_update_service("hastd");
			config_unlock();
		endif;
		$savemsg = get_std_save_message($retval);
	endif;
endif;
$pgtitle = [gtext('Services'),gtext('HAST')];
include 'fbegin.inc';
?>
<script>
//<![CDATA[
$(document).ready(function(){
	function enable_change(enable_change) {
		var val = !($('#enable').prop('checked') || enable_change);
		$('#auxparam').prop('disabled', val);
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
<table width="100%" border="0" cellpadding="0" cellspacing="0">
	<tr>
		<td class="tabnavtbl">
			<ul id="tabnav">
				<li class="tabact"><a href="services_hast.php" title="<?=gtext('Reload page');?>"><span><?=gtext("Settings");?></span></a></li>
				<li class="tabinact"><a href="services_hast_resource.php"><span><?=gtext("Resources");?></span></a></li>
				<li class="tabinact"><a href="services_hast_info.php"><span><?=gtext("Information");?></span></a></li>
			</ul>
		</td>
	</tr>
	<tr>
		<td class="tabcont">
			<form action="services_hast.php" method="post" name="iform" id="iform" onsubmit="spinner()">
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
?>
				<table width="100%" border="0" cellpadding="6" cellspacing="0">
<?php
					html_titleline_checkbox2('enable',gettext('HAST (Highly Available Storage)'),!empty($pconfig['enable']),gettext('Enable'),'');
					echo html_text2('nodeid',gettext('Node ID'),htmlspecialchars($nodeid));
					echo html_text2('nodename',gettext('Node Name'),htmlspecialchars($nodename));
					$a_vipaddrs = [];
					foreach ($a_carp as $carp):
						$ifinfo = get_carp_info($carp['if']);
//						$a_vipaddrs[] = $carp['vipaddr']." ({$ifinfo['state']},{$ifinfo['advskew']})";
						$a_vipaddrs[] = $carp['vipaddr']." ({$ifinfo['state']})";
					endforeach;
					echo html_text2('vipaddr',gettext('Virtual IP Address'),(!empty($a_vipaddrs) ? htmlspecialchars(join(', ',$a_vipaddrs)) : sprintf("<span class='red'>%s</span>",gettext('No configured CARP interfaces.'))));
//					html_combobox2('role',gettext('HAST role'),$pconfig['role'],['primary' => gettext('Primary'),'secondary' => gettext('Secondary')],'',true);
?>
					<tr id="control_btn">
						<td colspan="2">
							<input id="switch_backup" name="switch_backup" type="submit" class="formbtn" value="<?php echo gtext("Switch VIP to BACKUP"); ?>" />
<?php
							if(isset($a_carp[0]) && $a_carp[0]['advskew'] <= 1):
?>
								&nbsp;<input id="switch_master" name="switch_master" type="submit" class="formbtn" value="<?php echo gtext("Switch VIP to MASTER"); ?>" />
<?php
							endif;
?>
						</td>
					</tr>
<?php
					html_separator2();
					html_titleline2(gettext('Advanced Settings'));
					$helpinghand = '<a'
						. ' href="http://www.freebsd.org/doc/en_US.ISO8859-1/books/handbook/disks-hast.html"'
						. ' target="_blank"'
						. ' rel="noreferrer">'
						. gettext('Please check the documentation')
						. '</a>.';
					html_textarea2('auxparam',gettext('Additional Parameters'),$pconfig['auxparam'],sprintf(gettext("These parameters are added to %s."),'hast.conf') . ' ' . $helpinghand,false,65,5,false,false);
?>
				</table>
				<div id="submit">
					<input name="Submit" type="submit" class="formbtn" value="<?=gtext("Save & Restart");?>" />
				</div>
				<div id="remarks">
<?php
					html_remark2('note',gettext('Note'),sprintf("<div id='enumeration'><ul><li>%s</li><li>%s</li><li>%s</li></ul></div>",gettext('When HAST is enabled, the local devices, the local services and the additional packages which do not support HAST volume cannot be used.'), gettext('The HAST volumes can not be accessed until HAST node becomes Primary.'),gettext('Dynamic IP (DHCP) can not be used for HAST resources.')));
?>
				</div>
<?php
				include 'formend.inc';
?>
			</form>
		</td>
	</tr>
</table>
<?php
include 'fend.inc';
