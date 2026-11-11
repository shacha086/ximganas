<?php
/*
	services_samba.php

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

use services\samba\setting_toolbox as toolbox;
use services\samba\shared_toolbox;

//	init indicators
$input_errors = [];
//	preset $savemsg when a reboot is pending
if(file_exists($d_sysrebootreqd_path)):
	$savemsg = get_std_save_message(0);
endif;
//	init properties, sphere and rmo
$cop = toolbox::init_properties();
$sphere = toolbox::init_sphere();
$rmo = toolbox::init_rmo($cop,$sphere);
$cops = [
	$cop->get_aio(),
	$cop->get_aiorsize(),
	$cop->get_aiowsize(),
	$cop->get_auxparam(),
	$cop->get_clientmaxprotocol(),
	$cop->get_clientminprotocol(),
	$cop->get_serversigning(),
	$cop->get_serversmbencrypt(),
	$cop->get_clientsmbencrypt(),
	$cop->get_createmask(),
	$cop->get_directorymask(),
	$cop->get_doscharset(),
	$cop->get_easupport(),
	$cop->get_enable(),
	$cop->get_forcegroup(),
	$cop->get_forceuser(),
	$cop->get_guestaccount(),
	$cop->get_if(),
	$cop->get_largereadwrite(),
	$cop->get_localmaster(),
	$cop->get_loglevel(),
	$cop->get_mapdosattributes(),
	$cop->get_maptoguest(),
	$cop->get_maxprotocol(),
	$cop->get_minprotocol(),
	$cop->get_netbiosname(),
	$cop->get_pwdsrv(),
	$cop->get_rcvbuf(),
	$cop->get_security(),
	$cop->get_serverdesc(),
	$cop->get_sndbuf(),
	$cop->get_storedosattributes(),
	$cop->get_timesrv(),
	$cop->get_trusteddomains(),
	$cop->get_unixcharset(),
	$cop->get_winssrv(),
	$cop->get_workgroup()
];
$a_user = [];
$a_user[''] = gettext('Default');
foreach(system_get_user_list() as $key => $val):
	$a_user[strtolower($key)] = $key;
endforeach;
ksort($a_user);
$cop->get_forceuser()->set_options($a_user);
$cop->get_guestaccount()->set_options($a_user);
unset($val,$key,$a_user);
$a_group = [];
$a_group[''] = gettext('Default');
foreach(system_get_group_list() as $key => $val):
	$a_group[$key] = $key;
endforeach;
$cop->get_forcegroup()->set_options($a_group);
unset($val,$key,$a_group);
$pending_changes = updatenotify_exists($sphere->get_notifier());
[$page_method,$page_action,$page_mode] = $rmo->validate();
switch($page_method):
	case 'SESSION':
		switch($page_action):
			case $sphere->get_script()->get_basename():
				$retval = filter_var($_SESSION[$sphere->get_script()->get_basename()],FILTER_VALIDATE_INT,['options' => ['default' => 0]]);
				unset($_SESSION['submit'],$_SESSION[$sphere->get_script()->get_basename()]);
				$savemsg = get_std_save_message($retval);
				if($retval !== 0):
					$page_action = 'edit';
					$page_mode = PAGE_MODE_EDIT;
				else:
					$page_action = 'view';
					$page_mode = PAGE_MODE_VIEW;
				endif;
				break;
		endswitch;
		break;
	case 'POST':
		switch($page_action):
			case 'apply':
				$retval = 0;
				$retval |= updatenotify_process($sphere->get_notifier(),$sphere->get_notifier_processor());
				config_lock();
				$retval |= rc_update_service('samba');
				$retval |= rc_update_service('mdnsresponder');
				config_unlock();
				$_SESSION['submit'] = $sphere->get_script()->get_basename();
				$_SESSION[$sphere->get_script()->get_basename()] = $retval;
				header($sphere->get_script()->get_location());
				exit;
				break;
			case 'reload':
				$retval = 0;
				$name = $cop->get_enable()->get_name();
				$sphere->grid[$name] ??= false;
				if($sphere->grid[$name] && !$pending_changes):
					config_lock();
					$retval |= rc_update_service_ex('samba',true);
					config_unlock();
					$_SESSION['submit'] = $sphere->get_script()->get_basename();
					$_SESSION[$sphere->get_script()->get_basename()] = $retval;
					header($sphere->get_script()->get_location());
					exit;
				else:
					$page_action = 'view';
					$page_mode = PAGE_MODE_VIEW;
				endif;
				break;
			case 'disable':
				$retval = 0;
				$name = $cop->get_enable()->get_name();
				$sphere->grid[$name] ??= false;
				if($sphere->grid[$name]):
					$sphere->grid[$name] = false;
					write_config();
					config_lock();
					$retval |= rc_update_service('samba');
					$retval |= rc_update_service('mdnsresponder');
					config_unlock();
					$_SESSION['submit'] = $sphere->get_script()->get_basename();
					$_SESSION[$sphere->get_script()->get_basename()] = $retval;
					header($sphere->get_script()->get_location());
					exit;
				else:
					$page_action = 'view';
					$page_mode = PAGE_MODE_VIEW;
				endif;
				break;
			case 'enable':
				$retval = 0;
				$name = $cop->get_enable()->get_name();
				$sphere->grid[$name] ??= false;
				if($sphere->grid[$name] || $pending_changes):
					$page_action = 'view';
					$page_mode = PAGE_MODE_VIEW;
				else:
					$sphere->grid[$name] = true;
					write_config();
					config_lock();
					$retval |= rc_update_service('samba');
					$retval |= rc_update_service('mdnsresponder');
					config_unlock();
					$_SESSION['submit'] = $sphere->get_script()->get_basename();
					$_SESSION[$sphere->get_script()->get_basename()] = $retval;
					header($sphere->get_script()->get_location());
					exit;
				endif;
				break;
		endswitch;
		break;
endswitch;
//	validate
switch($page_action):
	case 'edit':
	case 'view':
		$source = $sphere->grid;
		foreach($cops as $cops_element):
			$name = $cops_element->get_name();
			switch($cops_element->get_input_type()):
				case $cops_element::INPUT_TYPE_TEXTAREA:
					if(array_key_exists($name,$source) && is_array($source[$name])):
						$source[$name] = implode("\n",$source[$name]);
					endif;
					break;
			endswitch;
			$sphere->row[$name] = $cops_element->validate_array_element($source);
			if(is_null($sphere->row[$name])):
				if(array_key_exists($name,$source) && is_scalar($source[$name])):
					$sphere->row[$name] = $source[$name];
				else:
					$sphere->row[$name] = $cops_element->get_defaultvalue();
				endif;
			endif;
		endforeach;
		break;
	case 'save':
		$source = $_POST;
		foreach($cops as $cops_element):
			$name = $cops_element->get_name();
			$sphere->row[$name] = $cops_element->validate_input();
			if(is_null($sphere->row[$name])):
				$input_errors[] = $cops_element->get_message_error();
				if(array_key_exists($name,$source) && is_scalar($source[$name])):
					$sphere->row[$name] = $source[$name];
				else:
					$sphere->row[$name] = $cops_element->get_defaultvalue();
				endif;
			endif;
		endforeach;
		if(empty($input_errors)):
			foreach($cops as $cops_element):
				$name = $cops_element->get_name();
				switch($cops_element->get_input_type()):
					case $cops_element::INPUT_TYPE_TEXTAREA:
						$sphere->row[$name] = array_map(fn($element) => trim($element,"\n\r\t"),explode("\n",$sphere->row[$name]));
						break;
				endswitch;
				$sphere->grid[$name] = $sphere->row[$name];
			endforeach;
			write_config();
			updatenotify_set($sphere->get_notifier(),UPDATENOTIFY_MODE_MODIFIED,'SERVICE',$sphere->get_notifier_processor());
			header($sphere->get_script()->get_location());
			exit;
		else:
			$page_mode = PAGE_MODE_EDIT;
		endif;
		break;
endswitch;
//	determine final page mode and calculate readonly flag
[$page_mode,$is_readonly] = calc_skipviewmode($page_mode);
$is_enabled = $sphere->row[$cop->get_enable()->get_name()];
$is_running = (rc_is_service_running('samba') === 0);
$is_running_message = $is_running ? gettext('Yes') : gettext('No');
//	create document
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
if($pending_changes):
	$content->ins_config_has_changed_box();
endif;
//	add content
$tds1 = $content->add_table_data_settings();
$tds1->ins_colgroup_data_settings();
$thead1 = $tds1->addTHEAD();
$tbody1 = $tds1->addTBODY();
switch($page_mode):
	case PAGE_MODE_VIEW:
		$thead1->c2_titleline(gettext('SMB Settings'));
		break;
	case PAGE_MODE_EDIT:
		$thead1->c2($cop->get_enable(),$sphere,false,$is_readonly,gettext('SMB Settings'));
		break;
endswitch;
$tbody1->
	c2_textinfo('running',gettext('Service Active'),$is_running_message);
$tbody1->
	c2($cop->get_security(),$sphere,true,$is_readonly);
$security_hooks = $document->get_hooks();
foreach($security_hooks as $hook_key => $hook_obj):
	switch($hook_key):
		case 'ads':
			$hook_obj->addDIV(['class' => 'showifchecked'])->cr($cop->get_trusteddomains(),$sphere,false,$is_readonly);
			$hook_obj->addDIV(['class' => 'showifchecked'])->cr($cop->get_pwdsrv(),$sphere,false,$is_readonly);
			$hook_obj->addDIV(['class' => 'showifchecked'])->cr($cop->get_winssrv(),$sphere,false,$is_readonly);
			break;
	endswitch;
endforeach;
unset($hook_obj,$hook_key,$security_hooks);
$tbody1->
	c2($cop->get_netbiosname(),$sphere,true,$is_readonly)->
	c2($cop->get_workgroup(),$sphere,true,$is_readonly)->
	c2($cop->get_if(),$sphere,false,$is_readonly)->
	c2($cop->get_serverdesc(),$sphere,false,$is_readonly)->
	c2($cop->get_doscharset(),$sphere,false,$is_readonly)->
	c2($cop->get_unixcharset(),$sphere,false,$is_readonly)->
	c2($cop->get_loglevel(),$sphere,false,$is_readonly)->
	c2($cop->get_localmaster(),$sphere,false,$is_readonly)->
	c2($cop->get_timesrv(),$sphere,false,$is_readonly);
$tds2 = $content->add_table_data_settings();
$tds2->ins_colgroup_data_settings();
$tds2->addTHEAD()->
	c2_titleline(gettext('Advanced SMB Settings'));
$tbody2 = $tds2->addTBODY();
$tbody2->
	c2($cop->get_guestaccount(),$sphere,false,$is_readonly)->
	c2($cop->get_maptoguest(),$sphere,false,$is_readonly)->
	c2($cop->get_forceuser(),$sphere,false,$is_readonly)->
	c2($cop->get_forcegroup(),$sphere,false,$is_readonly)->
	c2($cop->get_sndbuf(),$sphere,false,$is_readonly)->
	c2($cop->get_rcvbuf(),$sphere,false,$is_readonly)->
	c2($cop->get_easupport(),$sphere,false,$is_readonly)->
	c2($cop->get_storedosattributes(),$sphere,false,$is_readonly)->
	c2($cop->get_mapdosattributes(),$sphere,false,$is_readonly);
$tbody2->
	c2($cop->get_aio(),$sphere,false,$is_readonly);
$aio_hooks = $document->get_hooks();
foreach($aio_hooks as $hook_key => $hook_obj):
	$hook_obj->addDIV(['class' => 'showifchecked'])->cr($cop->get_aiorsize(),$sphere,false,$is_readonly);
	$hook_obj->addDIV(['class' => 'showifchecked'])->cr($cop->get_aiowsize(),$sphere,false,$is_readonly);
endforeach;
unset($hook_obj,$hook_key,$aio_hooks);
$tbody2->
	c2($cop->get_createmask(),$sphere,false,$is_readonly)->
	c2($cop->get_directorymask(),$sphere,false,$is_readonly)->
	c2($cop->get_maxprotocol(),$sphere,false,$is_readonly)->
	c2($cop->get_minprotocol(),$sphere,false,$is_readonly)->
	c2($cop->get_serversigning(),$sphere,false,$is_readonly)->
	c2($cop->get_serversmbencrypt(),$sphere,false,$is_readonly)->
	c2($cop->get_clientsmbencrypt(),$sphere,false,$is_readonly)->
	c2($cop->get_clientmaxprotocol(),$sphere,false,$is_readonly)->
	c2($cop->get_clientminprotocol(),$sphere,false,$is_readonly)->
	c2($cop->get_auxparam(),$sphere,false,$is_readonly);
//	add buttons
$buttons = $document->add_area_buttons();
switch($page_mode):
	case PAGE_MODE_VIEW:
		$buttons->ins_button_edit();
		if($pending_changes && $is_enabled):
			$buttons->ins_button_enadis(!$is_enabled);
		elseif(!$pending_changes):
			$buttons->ins_button_enadis(!$is_enabled);
			$buttons->ins_button_reload($is_enabled);
		endif;
		break;
	case PAGE_MODE_EDIT:
		$buttons->ins_button_save();
		$buttons->ins_button_cancel();
		break;
endswitch;
$document->render();
