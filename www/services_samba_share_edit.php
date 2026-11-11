<?php
/*
	services_samba_share_edit.php

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
use services\samba\share\row_toolbox as toolbox;
use services\samba\share\shared_toolbox;

// get mount info specified path
function get_mount_info($path){
	if(file_exists($path) === false):
		return false;
	endif;
//	get all mount points
	$a_mounts = [];
	mwexec2('/sbin/mount -p',$rawdata);
	foreach($rawdata as $line):
		[$dev,$dir,$fs,$opt,$dump,$pass] = preg_split('/\s+/',$line);
		$a_mounts[] = [
			'dev' => $dev,
			'dir' => $dir,
			'fs' => $fs,
			'opt' => $opt,
			'dump' => $dump,
			'pass' => $pass,
		];
	endforeach;
	if(empty($a_mounts)):
		return false;
	endif;
// check path with mount list
	do {
		foreach($a_mounts as $mountv):
			if(strcmp($mountv['dir'],$path) == 0):
//				found mount point
				return $mountv;
			endif;
		endforeach;
//		path don't have parent?
		if(strpos($path,'/') === false):
			break;
		endif;
//		retry with parent
		$pathinfo = pathinfo($path);
		$path = $pathinfo['dirname'];
	} while (1);
	return false;
}
//	init indicators
$input_errors = [];
$prerequisites_ok = true;
//	preset $savemsg when a reboot is pending
if(file_exists($d_sysrebootreqd_path)):
	$savemsg = get_std_save_message(0);
endif;
//	init properties and sphere
$cop = toolbox::init_properties();
$sphere = toolbox::init_sphere();
$rmo = toolbox::init_rmo();
[$page_method,$page_action,$page_mode] = $rmo->validate();
//	determine page mode and validate resource id
switch($page_method):
	case 'GET':
		switch($page_action):
			case 'add':
//				bring up a form with default values and let the user modify it
				$sphere->row[$sphere->get_row_identifier()] = $cop->get_row_identifier()->get_defaultvalue();
				break;
			case 'edit':
//				modify the data of the provided resource id and let the user modify it
				$sphere->row[$sphere->get_row_identifier()] = $cop->get_row_identifier()->validate_input(INPUT_GET);
				break;
		endswitch;
		break;
	case 'POST':
		switch($page_action):
			case 'add':
//				bring up a form with default values and let the user modify it
				$sphere->row[$sphere->get_row_identifier()] =  $cop->get_row_identifier()->get_defaultvalue();
				break;
			case 'cancel':
//				cancel - nothing to do
				$sphere->row[$sphere->get_row_identifier()] = null;
				break;
			case 'clone':
				$sphere->row[$sphere->get_row_identifier()] = $cop->get_row_identifier()->get_defaultvalue();
				break;
			case 'edit':
//				edit requires a resource id, get it from input and validate
				$sphere->row[$sphere->get_row_identifier()] = $cop->get_row_identifier()->validate_input();
				break;
			case 'save':
//				modify requires a resource id, get it from input and validate
				$sphere->row[$sphere->get_row_identifier()] = $cop->get_row_identifier()->validate_input();
				break;
		endswitch;
		break;
endswitch;
//	exit if $sphere->row[$sphere->row_identifier()] is null
if(is_null($sphere->get_row_identifier_value())):
	header($sphere->get_parent()->get_location());
	exit;
endif;
//	search resource id in sphere
$sphere->row_id = arr::search_ex($sphere->get_row_identifier_value(),$sphere->grid,$sphere->get_row_identifier());
//	start determine record update mode
$updatenotify_mode = updatenotify_get_mode($sphere->get_notifier(),$sphere->get_row_identifier_value()); // get updatenotify mode
$record_mode = RECORD_ERROR;
if($sphere->row_id === false):
//	record does not exist in config
	if(in_array($page_mode,[PAGE_MODE_ADD,PAGE_MODE_CLONE,PAGE_MODE_POST],true)):
//		ADD or CLONDE or POST
		switch($updatenotify_mode):
			case UPDATENOTIFY_MODE_UNKNOWN:
				$record_mode = RECORD_NEW;
				break;
		endswitch;
	endif;
else:
//	record found in configuration
	if(in_array($page_mode,[PAGE_MODE_EDIT,PAGE_MODE_POST,PAGE_MODE_VIEW],true)):
//		EDIT or POST or VIEW
		switch($updatenotify_mode):
			case UPDATENOTIFY_MODE_NEW:
				$record_mode = RECORD_NEW_MODIFY;
				break;
			case UPDATENOTIFY_MODE_MODIFIED:
				$record_mode = RECORD_MODIFY;
				break;
			case UPDATENOTIFY_MODE_UNKNOWN:
				$record_mode = RECORD_MODIFY;
				break;
		endswitch;
	endif;
endif;
if($record_mode === RECORD_ERROR):
//	something went wrong
	header($sphere->get_parent()->get_location());
	exit;
endif;
$isrecordnew = ($record_mode === RECORD_NEW);
$isrecordnewmodify = ($record_mode === RECORD_NEW_MODIFY);
$isrecordmodify = ($record_mode === RECORD_MODIFY);
$isrecordnewornewmodify = ($isrecordnew || $isrecordnewmodify);
//	end determine record update mode
$cops = [
	$cop->get_name(),
	$cop->get_path(),
	$cop->get_comment(),
	$cop->get_readonly(),
	$cop->get_browseable(),
	$cop->get_guest(),
	$cop->get_guestonly(),
	$cop->get_inheritpermissions(),
	$cop->get_inheritacls(),
	$cop->get_recyclebin(),
	$cop->get_hidedotfiles(),
	$cop->get_shadowcopy(),
	$cop->get_shadowformat(),
	$cop->get_zfsacl(),
	$cop->get_storealternatedatastreams(),
	$cop->get_storentfsacls(),
	$cop->get_afpcompat(),
	$cop->get_vfs_fruit_resource(),
	$cop->get_vfs_fruit_time_machine(),
	$cop->get_vfs_fruit_metadata(),
	$cop->get_vfs_fruit_locking(),
	$cop->get_vfs_fruit_encoding(),
	$cop->get_hostsallow(),
	$cop->get_hostsdeny(),
	$cop->get_createmask(),
	$cop->get_directorymask(),
	$cop->get_forcegroup(),
	$cop->get_forceuser(),
	$cop->get_auxparam()
];
$a_user = [];
$a_user[''] = gettext('Default');
foreach(system_get_user_list() as $key => $val):
	$a_user[strtolower($key)] = $key;
endforeach;
ksort($a_user);
$cop->get_forceuser()->set_options($a_user);
unset($val,$key,$a_user);
$a_group = [];
$a_group[''] = gettext('Default');
foreach(system_get_group_list() as $key => $val):
	$a_group[$key] = $key;
endforeach;
$cop->get_forcegroup()->set_options($a_group);
unset($val,$key,$a_group);
switch($page_mode):
	case PAGE_MODE_ADD:
		foreach($cops as $cops_element):
			$sphere->row[$cops_element->get_name()] = $cops_element->get_defaultvalue();
		endforeach;
		break;
	case PAGE_MODE_CLONE:
		foreach($cops as $cops_element):
			$name = $cops_element->get_name();
			$sphere->row[$name] = $cops_element->validate_input() ?? $cops_element->get_defaultvalue();
		endforeach;
//		adjust page mode
		$page_mode = PAGE_MODE_ADD;
		break;
	case PAGE_MODE_EDIT:
		$source = $sphere->grid[$sphere->row_id];
		foreach($cops as $cops_element):
			$name = $cops_element->get_name();
			switch($cops_element->get_input_type()):
				case $cops_element::INPUT_TYPE_TEXTAREA:
					if(array_key_exists($name,$source) && is_array($source[$name])):
						$source[$name] = implode("\n",$source[$name]);
					endif;
					break;
			endswitch;
			$sphere->row[$name] = $cops_element->validate_config($source);
		endforeach;
		break;
	case PAGE_MODE_POST:
		foreach($cops as $cops_element):
			$name = $cops_element->get_name();
			$sphere->row[$name] = $cops_element->validate_input();
			if(!isset($sphere->row[$name])):
				$sphere->row[$name] = $_POST[$name] ?? '';
				$input_errors[] = $cops_element->get_message_error();
			endif;
		endforeach;
//		check for duplicate name.
		$name = $cop->get_name()->get_name();
		$index = arr::search_ex($sphere->row[$name],$sphere->grid,$name);
		if($index !== false):
			if($isrecordnew):
				$input_errors[] = gettext('The share name is already used.');
			elseif($index !== $sphere->row_id):
				$input_errors[] = gettext('The share name is already used.');
			endif;
		endif;
//		enable ZFS ACL on ZFS mount point
		$mntinfo = get_mount_info($sphere->row[$cop->get_path()->get_name()]);
		if($mntinfo !== false && $mntinfo['fs'] === 'zfs'):
			if($isrecordnew):
//				first time creation
				$sphere->row[$cop->get_zfsacl()->get_name()] = true;
			endif;
		endif;
		if(empty($input_errors)):
			foreach($cops as $cops_element):
				switch($cops_element->get_input_type()):
					case $cops_element::INPUT_TYPE_TEXTAREA:
						$name = $cops_element->get_name();
						$sphere->row[$name] = array_map(fn($element) => trim($element,"\n\r\t"),explode("\n",$sphere->row[$name]));
						break;
				endswitch;
			endforeach;
			$sphere->upsert();
			if($isrecordnew):
				updatenotify_set($sphere->get_notifier(),UPDATENOTIFY_MODE_NEW,$sphere->get_row_identifier_value(),$sphere->get_notifier_processor());
			elseif($updatenotify_mode === UPDATENOTIFY_MODE_UNKNOWN):
				updatenotify_set($sphere->get_notifier(),UPDATENOTIFY_MODE_MODIFIED,$sphere->get_row_identifier_value(),$sphere->get_notifier_processor());
			endif;
			write_config();
			header($sphere->get_parent()->get_location());
			exit;
		endif;
		break;
endswitch;
$sphere->add_page_title($isrecordnew ? gettext('Add') : gettext('Edit'));
if(is_bool($test = $config['system']['showdisabledsections'] ?? false) ? $test : true):
	$jdr = '';
else:
	$jdr = <<<EOJ
	$('#{$cop->get_afpcompat()->get_id()}').change(function() {
		switch(this.checked) {
			case true:
				$('#{$cop->get_vfs_fruit_resource()->get_id()}_tr').show();
				$('#{$cop->get_vfs_fruit_time_machine()->get_id()}_tr').show();
				$('#{$cop->get_vfs_fruit_metadata()->get_id()}_tr').show();
				$('#{$cop->get_vfs_fruit_locking()->get_id()}_tr').show();
				$('#{$cop->get_vfs_fruit_encoding()->get_id()}_tr').show();
				break;
			case false:
				$('#{$cop->get_vfs_fruit_encoding()->get_id()}_tr').hide();
				$('#{$cop->get_vfs_fruit_locking()->get_id()}_tr').hide();
				$('#{$cop->get_vfs_fruit_metadata()->get_id()}_tr').hide();
				$('#{$cop->get_vfs_fruit_time_machine()->get_id()}_tr').hide();
				$('#{$cop->get_vfs_fruit_resource()->get_id()}_tr').hide();
				break;
        }
    });
	$('#{$cop->get_afpcompat()->get_id()}').change();
	$('#{$cop->get_shadowcopy()->get_id()}').change(function() {
		switch(this.checked) {
			case true:
				$('#{$cop->get_shadowformat()->get_id()}_tr').show();
				break;
			case false:
				$('#{$cop->get_shadowformat()->get_id()}_tr').hide();
				break;
        }
	});
	$('#{$cop->get_shadowcopy()->get_id()}').change();
EOJ;
endif;
$document = new_page($sphere->get_page_title(),$sphere->get_script()->get_scriptname());
//	add tab navigation
shared_toolbox::add_tabnav($document);
//	get areas
$body = $document->getElementById('main');
$pagecontent = $document->getElementById('pagecontent');
//	create data area
$content = $pagecontent->add_area_data();
//	display information, warnings and errors
$content->
	ins_input_errors($input_errors)->
	ins_info_box($savemsg)->
	ins_error_box($errormsg);
$tbody = $content->
	add_table_data_settings()->
		ins_colgroup_data_settings()->
		push()->
		addTHEAD()->
			c2_titleline(gettext('Share Settings'))->
		pop()->
		addTBODY();
$tbody->
	c2($cop->get_name(),$sphere,true)->
	c2($cop->get_comment(),$sphere,true)->
	c2($cop->get_path(),$sphere,true)->
	c2($cop->get_readonly(),$sphere)->
	c2($cop->get_browseable(),$sphere)->
	c2($cop->get_guest(),$sphere);
$guestok_hooks = $document->get_hooks();
foreach($guestok_hooks as $hook_key => $hook_obj):
	switch($hook_key):
		case 'guest':
			$hook_obj->addDIV(['class' => 'showifchecked'])->cr($cop->get_guestonly(),$sphere,false,false);
			break;
	endswitch;
endforeach;
unset($guestok_hooks);
$tbody->
	c2($cop->get_forceuser(),$sphere)->
	c2($cop->get_forcegroup(),$sphere)->
	c2($cop->get_inheritpermissions(),$sphere)->
	c2($cop->get_recyclebin(),$sphere)->
	c2($cop->get_hidedotfiles(),$sphere)->
	c2($cop->get_shadowcopy(),$sphere)->
	c2($cop->get_shadowformat(),$sphere)->
	c2($cop->get_zfsacl(),$sphere)->
	c2($cop->get_inheritacls(),$sphere)->
	c2($cop->get_storealternatedatastreams(),$sphere)->
	c2($cop->get_storentfsacls(),$sphere)->
	c2($cop->get_createmask(),$sphere)->
	c2($cop->get_directorymask(),$sphere)->
	c2($cop->get_hostsallow(),$sphere)->
	c2($cop->get_hostsdeny(),$sphere);
$content->
	add_table_data_settings()->
		push()->
		ins_colgroup_data_settings()->
		addTHEAD()->
			c2($cop->get_afpcompat(),$sphere,false,false,gettext('Apple Filing Protocol (AFP) Compatibility Settings'))->
		pop()->
		addTBODY()->
			c2($cop->get_vfs_fruit_resource(),$sphere)->
			c2($cop->get_vfs_fruit_time_machine(),$sphere)->
			c2($cop->get_vfs_fruit_metadata(),$sphere)->
			c2($cop->get_vfs_fruit_locking(),$sphere)->
			c2($cop->get_vfs_fruit_encoding(),$sphere);
$content->
	add_table_data_settings()->
		push()->
		ins_colgroup_data_settings()->
		addTHEAD()->
			c2_titleline(gettext('Additional Parameters'))->
		pop()->
		addTBODY()->
			c2($cop->get_auxparam(),$sphere,false,false);
$buttons = $document->add_area_buttons();
if($isrecordnew):
	$buttons->ins_button_add();
else:
	$buttons->ins_button_save();
	if($prerequisites_ok && empty($input_errors)):
		$buttons->ins_button_clone();
	endif;
endif;
$buttons->ins_button_cancel();
$buttons->ins_input_hidden($sphere->get_row_identifier(),$sphere->get_row_identifier_value());
//	additional javascript code
$body->ins_javascript($sphere->get_js());
$body->add_js_on_load($sphere->get_js_on_load());
$body->add_js_document_ready($jdr);
$document->render();
