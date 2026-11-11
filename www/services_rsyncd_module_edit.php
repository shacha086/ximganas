<?php
/*
	services_rsyncd_module_edit.php

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
use	common\uuid;
use gui\document;

$uuid = $_POST['uuid'] ?? $_GET['uuid'] ?? null;
$a_mount = &arr::make_branch($config,'mounts','mount');
arr::sort_key($a_mount,'devicespecialfile');
$a_module = &arr::make_branch($config,'rsyncd','module');
arr::sort_key($a_module,'name');
if(isset($uuid) && (false !== ($cnid = arr::search_ex($uuid,$a_module,'uuid')))):
	$pconfig['uuid'] = $a_module[$cnid]['uuid'];
	$pconfig['name'] = $a_module[$cnid]['name'];
	$pconfig['path'] = $a_module[$cnid]['path'];
	$pconfig['comment'] = $a_module[$cnid]['comment'];
	$pconfig['list'] = isset($a_module[$cnid]['list']);
	$pconfig['rwmode'] = $a_module[$cnid]['rwmode'];
	$pconfig['maxconnections'] = $a_module[$cnid]['maxconnections'];
	$pconfig['hostsallow'] = $a_module[$cnid]['hostsallow'];
	$pconfig['hostsdeny'] = $a_module[$cnid]['hostsdeny'];
	$pconfig['uid'] = $a_module[$cnid]['uid'];
	$pconfig['gid'] = $a_module[$cnid]['gid'];
	if(isset($a_module[$cnid]['auxparam']) && is_array($a_module[$cnid]['auxparam'])):
		$pconfig['auxparam'] = implode("\n",$a_module[$cnid]['auxparam']);
	endif;
else:
	$pconfig['uuid'] = uuid::create_v4();
	$pconfig['name'] = '';
	$pconfig['path'] = '';
	$pconfig['comment'] = '';
	$pconfig['list'] = true;
	$pconfig['rwmode'] = 'rw';
	$pconfig['maxconnections'] = '0';
	$pconfig['hostsallow'] = '';
	$pconfig['hostsdeny'] = '';
	$pconfig['uid'] = '';
	$pconfig['gid'] = '';
	$pconfig['auxparam'] = '';
endif;
if($_POST):
	unset($input_errors);
	$pconfig = $_POST;
	if(isset($_POST['Cancel']) && $_POST['Cancel']):
		header("Location: services_rsyncd_module.php");
		exit;
	endif;
//	Input validation.
	$reqdfields = ['name','comment'];
	$reqdfieldsn = [gtext('Name'),gtext('Comment')];
	do_input_validation($_POST,$reqdfields,$reqdfieldsn,$input_errors);
	$reqdfieldst = ['string','string'];
	do_input_validation_type($_POST,$reqdfields,$reqdfieldsn,$reqdfieldst,$input_errors);
	if(empty($input_errors)):
		$module = [];
		$module['uuid'] = $_POST['uuid'];
		$module['name'] = $_POST['name'];
		$module['path'] = $_POST['path'];
		$module['comment'] = $_POST['comment'];
		$module['list'] = isset($_POST['list']);
		$module['rwmode'] = $_POST['rwmode'];
		$module['maxconnections'] = $_POST['maxconnections'];
		$module['hostsallow'] = $_POST['hostsallow'];
		$module['hostsdeny'] = $_POST['hostsdeny'];
		$module['uid'] = $_POST['uid'];
		$module['gid'] = $_POST['gid'];
//		Write additional parameters.
		unset($module['auxparam']);
		$a_auxparam = explode("\n",$_POST['auxparam']);
		foreach($a_auxparam as $auxparam):
			$auxparam = trim($auxparam,"\t\n\r");
			if(!empty($auxparam)):
				$module['auxparam'][] = $auxparam;
			endif;
		endforeach;
		if(isset($uuid) && ($cnid !== false)):
			$a_module[$cnid] = $module;
			$mode = UPDATENOTIFY_MODE_MODIFIED;
		else:
			$a_module[] = $module;
			$mode = UPDATENOTIFY_MODE_NEW;
		endif;
		updatenotify_set('rsyncd',$mode,$module['uuid']);
		write_config();
		header('Location: services_rsyncd_module.php');
		exit;
	endif;
endif;
$pgtitle = [gtext('Services'),gtext('Rsync'),gtext('Server'),gtext('Module'),isset($uuid) ? gtext('Edit') : gtext('Add')];
include 'fbegin.inc';
$document = new document();
$document->
	add_area_tabnav()->
		push()->
		add_tabnav_upper()->
			ins_tabnav_record(href: 'services_rsyncd.php',value: gettext('Server'),title: gettext('Reload page'),active: true)->
			ins_tabnav_record(href: 'services_rsyncd_client.php',value: gettext('Client'))->
			ins_tabnav_record(href: 'services_rsyncd_local.php',value: gettext('Local'))->
		pop()->
		add_tabnav_lower()->
			ins_tabnav_record(href: 'services_rsyncd.php',value: gettext('Settings'))->
			ins_tabnav_record(href: 'services_rsyncd_module.php',value: gettext('Modules'),title: gettext('Reload page'),active: true);
$document->render();
?>
<form action="services_rsyncd_module_edit.php" method="post" name="iform" id="iform" class="pagecontent"><div class="area_data_top"></div><div id="area_data_frame">
<?php
	if(!empty($input_errors)):
		print_input_errors($input_errors);
	endif;
?>
	<table class="area_data_settings">
		<colgroup>
			<col class="area_data_settings_col_tag">
			<col class="area_data_settings_col_data">
		</colgroup>
		<thead>
<?php
			html_titleline2(gettext('Module Settings'));
?>
		</thead>
		<tbody>
<?php
			html_inputbox2(ctrlname: 'name',title: gettext('Name'),value: $pconfig['name'] ?? '',desc: '',required: true,size: 30,readonly: false,altpadding: false,maxlength: 0,placeholder: '');
			html_inputbox2(ctrlname: 'comment',title: gettext('Comment'),value: $pconfig['comment'] ?? '',desc: '',required: true,size: 30,readonly: false,altpadding: false,maxlength: 0,placeholder: '');
			html_filechooser2(ctrlname: 'path',title: gettext('Path'),value: $pconfig['path'] ?? '',desc: gettext('Path to be shared.'),path: $g['media_path'],required: true,size: 60,readonly: false);
			html_checkbox2(ctrlname: 'list',title: gettext('List'),checked: !empty($pconfig['list']),caption: gettext('Enable module listing.'),desc: gettext('This option determines if this module should be listed when the client asks for a listing of available modules. By setting this to false you can create hidden modules.'),required: false,readonly: false,onclick: '',altpadding: false);
			$options = [
				"ro" => gettext('Read only'),
				"rw" => gettext('Read/Write'),
				"wo" => gettext('Write only')
			];
			html_combobox2(ctrlname: 'rwmode',title: gettext('Access Mode'),value: $pconfig['rwmode'] ?? '',options: $options,desc: gettext('This controls the access a remote host has to this module.'),required: false,readonly: false,onclick: '');
			html_inputbox2(ctrlname: 'maxconnections',title: gettext('Max. Connections'),value: $pconfig['maxconnections'] ?? '',desc: gettext('Maximum number of simultaneous connections. Default is 0 (unlimited).'),required: false,size: 5,readonly: false,altpadding: false,maxlength: 0,placeholder: '');
			html_inputbox2(ctrlname: 'uid',title: gettext('User ID'),value: $pconfig['uid'] ?? '',desc: sprintf(gettext("This option specifies the user name or user ID that file transfers to and from that module should take place. In combination with the '%s' option this determines what file permissions are available. Leave this field empty to use default settings."),gettext('Group ID')),required: false,size: 60,readonly: false,altpadding: false,maxlength: 0,placeholder: '');
			html_inputbox2(ctrlname: 'gid',title: gettext('Group ID'),value: $pconfig['gid'] ?? '',desc: gettext('This option specifies the group name or group ID that file transfers to and from that module should take place. Leave this field empty to use default settings.'),required: false,size: 60,readonly: false,altpadding: false,maxlength: 0,placeholder: '');
			html_inputbox2(ctrlname: 'hostsallow',title: gettext('Hosts Allow'),value: $pconfig['hostsallow'] ?? '',desc: gettext('This option is a comma, space, or tab delimited set of hosts which are permitted to access this module. You can specify the hosts by name or IP number. Leave this field empty to use default settings.'),required: false,size: 60,readonly: false,altpadding: false,maxlength: 0,placeholder: '');
			html_inputbox2(ctrlname: 'hostsdeny',title: gettext('Hosts Deny'),value: $pconfig['hostsdeny'] ?? '',desc: gettext('This option is a comma, space, or tab delimited set of hosts which are NOT permitted to access this module. Where the lists conflict, the allow list takes precedence. In the event that it is necessary to deny all by default, use the keyword ALL (or the netmask 0.0.0.0/0) and then explicitly specify to the hosts allow parameter those hosts that should be permitted access. Leave this field empty to use default settings.'),required: false,size: 60,readonly: false,altpadding: false,maxlength: 0,placeholder: '');
			$helpinghand = '<a href="'
			. 'http://rsync.samba.org/ftp/rsync/rsync.html'
			. '" target="_blank">'
			. gettext('Please check the documentation')
			. '</a>.';
			html_textarea2(ctrlname: 'auxparam',title: gettext('Additional Parameters'),value: $pconfig['auxparam'] ?? '',desc: gettext('These parameters will be added to the module configuration in rsyncd.conf.') . ' ' . $helpinghand,required: false,columns: 65,rows: 5,readonly: false,wrap: false);
?>
		</tbody>
	</table>
	<div id="submit">
		<input name="Submit" type="submit" class="formbtn" value="<?=(isset($uuid) && ($cnid !== false)) ? gtext('Save') : gtext('Add')?>" />
		<input name="Cancel" type="submit" class="formbtn" value="<?=gtext('Cancel');?>" />
		<input name="uuid" type="hidden" value="<?=$pconfig['uuid'];?>" />
	</div>
<?php
	include 'formend.inc';
?>
</div><div class="area_data_pot"></div></form>
<?php
include 'fend.inc';
