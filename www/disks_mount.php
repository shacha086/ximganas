<?php
/*
	disks_mount.php

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
use gui\document;

$pgtitle = [gtext('Disks'),gtext('Mount Point'),gtext('Management')];
if($_POST):
	$pconfig = $_POST;
	if(isset($_POST['apply']) && $_POST['apply']):
		$retval = 0;
		if(!file_exists($d_sysrebootreqd_path)):
//			process notifications
			updatenotify_process('mountpoint','mountmanagement_process_updatenotification');
//			restart services
			config_lock();
			$retval |= rc_update_service('samba');
			$retval |= rc_update_service('rsyncd');
			$retval |= rc_update_service('netatalk');
			$retval |= rc_update_service('rpcbind'); // !!! Do
			$retval |= rc_update_service('mountd');  // !!! not
			$retval |= rc_update_service('nfsd');    // !!! change
			$retval |= rc_update_service('statd');   // !!! this
			$retval |= rc_update_service('lockd');   // !!! order
			config_unlock();
		endif;
		$savemsg = get_std_save_message($retval);
		if($retval == 0):
			updatenotify_delete('mountpoint');
		endif;
		header('Location: disks_mount.php');
		exit;
	endif;
endif;
$a_mount = &arr::make_branch($config,'mounts','mount');
if(empty($a_mount)):
else:
	arr::sort_key($a_mount,'devicespecialfile');
endif;
if(isset($_GET['act']) && $_GET['act'] === 'del'):
	$index = arr::search_ex($_GET['uuid'],$config['mounts']['mount'],'uuid');
	if($index !== false) {
//		MUST check if mount point is used by swap.
		if((isset($config['system']['swap']['enable'])) && ($config['system']['swap']['type'] === 'file') && ($config['system']['swap']['mountpoint'] === $_GET['uuid'])):
			$errormsg[] = gtext(sprintf('A swap file is located on the mounted device %s.',$config['mounts']['mount'][$index]['devicespecialfile']));
		else:
			updatenotify_set('mountpoint',UPDATENOTIFY_MODE_DIRTY,$_GET['uuid']);
			header('Location: disks_mount.php');
			exit;
		endif;
	}
endif;
if(isset($_GET['act']) && $_GET['act'] === 'retry'):
	$index = arr::search_ex($_GET['uuid'],$config['mounts']['mount'],'uuid');
	if($index !== false):
		if(disks_mount($config['mounts']['mount'][$index]) == 0):
			rc_update_service('samba');
			rc_update_service('rsyncd');
			rc_update_service('netatalk');
			rc_update_service('rpcbind'); // !!! Do
			rc_update_service('mountd');  // !!! not
			rc_update_service('nfsd');    // !!! change
			rc_update_service('statd');   // !!! this
			rc_update_service('lockd');   // !!! order
		endif;
		header('Location: disks_mount.php');
		exit;
	endif;
endif;
function mountmanagement_process_updatenotification($mode,$data) {
	global $config;

	if (empty($config['mounts']['mount']) || !is_array($config['mounts']['mount'])):
		return 1;
	endif;
	$index = arr::search_ex($data,$config['mounts']['mount'],'uuid');
	if($index === false):
		return 1;
	endif;
	switch($mode):
		case UPDATENOTIFY_MODE_NEW:
			disks_mount($config['mounts']['mount'][$index]);
			break;
		case UPDATENOTIFY_MODE_MODIFIED:
			disks_umount_ex($config['mounts']['mount'][$index]);
			disks_mount($config['mounts']['mount'][$index]);
			break;
		case UPDATENOTIFY_MODE_DIRTY:
			disks_umount($config['mounts']['mount'][$index]);
			unset($config['mounts']['mount'][$index]);
			write_config();
			break;
	endswitch;
}
include 'fbegin.inc';
$document = new document();
$document->
	add_area_tabnav()->
		add_tabnav_upper()->
			ins_tabnav_record('disks_mount.php',gettext('Management'),gettext('Reload page'),true)->
			ins_tabnav_record('disks_mount_tools.php',gettext('Tools'))->
			ins_tabnav_record('disks_mount_fsck.php',gettext('Fsck'));
$document->render();
?>
<form action="disks_mount.php" method="post" id="iform" name="iform" class="pagecontent"><div class="area_data_top"></div><div id="area_data_frame">
<?php
	if(file_exists($d_sysrebootreqd_path)):
		print_info_box(get_std_save_message(0));
	endif;
	if(!empty($errormsg)):
		print_error_box($errormsg);
	endif;
	if(!empty($savemsg)):
		print_info_box($savemsg);
	endif;
	if(updatenotify_exists('mountpoint')):
		print_config_change_box();
	endif;
	if(!empty($input_errors)):
		print_input_errors($input_errors);
	endif;
?>
	<table class="area_data_selection">
		<colgroup>
			<col style="width:30%">
			<col style="width:15%">
			<col style="width:15%">
			<col style="width:20%">
			<col style="width:13%">
			<col style="width:7%">
		</colgroup>
		<thead>

<?php
			html_titleline2(gettext('Mount Point Management'),6);
?>
			<tr>
				<th class="lhell"><?=gtext('Disk');?></th>
				<th class="lhell"><?=gtext('File System');?></th>
				<th class="lhell"><?=gtext('Name');?></th>
				<th class="lhell"><?=gtext('Description');?></th>
				<th class="lhell"><?=gtext('Status');?></th>
				<th class="lhebl"></th>
			</tr>
		</thead>
		<tbody>

<?php
			foreach($a_mount as $mount):
				$notificationmode = updatenotify_get_mode('mountpoint',$mount['uuid']);
				switch($notificationmode):
					case UPDATENOTIFY_MODE_NEW:
						$status = gtext('Initializing');
						break;
					case UPDATENOTIFY_MODE_MODIFIED:
						$status = gtext('Modifying');
						break;
					case UPDATENOTIFY_MODE_DIRTY:
						$status = gtext('Deleting');
						break;
					default:
						if(disks_ismounted_ex($mount['sharename'],'sharename')):
							$status = gtext('OK');
						else:
							$status = gtext('Error') . " - <a href=\"disks_mount.php?act=retry&uuid={$mount['uuid']}\">" . gtext('Retry') . "</a>";
						endif;
						break;
				endswitch;
?>
				<tr>
<?php
					if($mount['type'] === 'disk'):
?>
						<td class="lcell"><?=htmlspecialchars($mount['devicespecialfile']);?>&nbsp;<?php if($mount['fstype'] == 'ufs' && preg_match('/^\/dev\/(.+)$/',$mount['mdisk'],$match)): echo "({$match[1]}{$mount['partition']})";endif;?></td>
<?php
					elseif($mount['type'] === 'hvol'):
?>
						<td class="lcell"><?=htmlspecialchars($mount['devicespecialfile']);?>&nbsp;<?php if($mount['fstype'] == 'ufs' && preg_match('/^\/dev\/(.+)$/',$mount['mdisk'],$match)): echo "({$match[1]}{$mount['partition']})";endif;?></td>
<?php
					elseif ($mount['type'] === 'custom'):
?>
						<td class="lcell"><?=htmlspecialchars($mount['devicespecialfile']);?>&nbsp;</td>
<?php
					else:
?>
						<td class="lcell"><?=htmlspecialchars($mount['filename']);?>&nbsp;</td>
<?php
					endif;
?>
					<td class="lcell"><?=htmlspecialchars($mount['fstype']);?>&nbsp;</td>
					<td class="lcell"><?=htmlspecialchars($mount['sharename']);?>&nbsp;</td>
					<td class="lcell"><?=htmlspecialchars($mount['desc']);?>&nbsp;</td>
					<td class="lcell"><?=$status;?>&nbsp;</td>
<?php
					if($notificationmode != UPDATENOTIFY_MODE_DIRTY):
?>
						<td class="lcebl">
							<a href="disks_mount_edit.php?uuid=<?=$mount['uuid'];?>"><img src="images/edit.png" title="<?=gtext('Edit mount point');?>" alt="<?=gtext('Edit mount point');?>"></a>&nbsp;
							<a href="disks_mount.php?act=del&amp;uuid=<?=$mount['uuid'];?>" onclick="return confirm('<?=gtext('Do you really want to delete this mount point? All elements that still use it will become invalid (e.g. share)!');?>')"><img src="images/delete.png" title="<?=gtext('Delete mount point');?>" alt="<?=gtext('Delete mount point');?>"></a>
						</td>
<?php
					else:
?>
						<td class="lcebl">
							<img src="images/delete.png" alt="">
						</td>
<?php
					endif;
?>
				</tr>
<?php
			endforeach;
?>
		</tbody>
		<tfoot>
			<tr>
				<th class="lcenl" colspan="5"></th>
				<th class="lceadd"><a href="disks_mount_edit.php"><img src="images/add.png" title="<?=gtext('Add mount point');?>" alt="<?=gtext('Add mount point');?>"></a></th>
			</tr>
		</tfoot>
	</table>
	<div id="remarks">
<?php
		html_remark2('Warning',gettext('Warning'),sprintf(gettext('UFS and ZFS are NATIVE filesystems of %s. Attempting to use other filesystems such as EXT2, EXT3, EXT4, FAT, FAT32, or NTFS can result in unpredictable results, file corruption and the loss of data!'),get_product_name()));
?>
	</div>
<?php
	include 'formend.inc';
?>
</div><div class="area_data_pot"></div></form>
<?php
include 'fend.inc';
