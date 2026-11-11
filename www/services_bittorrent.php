<?php
/*
	services_bittorrent.php

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

use services\torrent\setting_toolbox as toolbox;
use services\torrent\shared_toolbox;

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
	$cop->get_authrequired(),
	$cop->get_configdir(),
	$cop->get_dht(),
	$cop->get_downlimit(),
	$cop->get_downloaddir(),
	$cop->get_enable(),
	$cop->get_encryption(),
	$cop->get_extraoptions(),
	$cop->get_incompletedir(),
	$cop->get_lpd(),
	$cop->get_messagelevel(),
	$cop->get_password(),
	$cop->get_peerport(),
	$cop->get_pex(),
	$cop->get_port(),
	$cop->get_portforwarding(),
	$cop->get_preallocation(),
	$cop->get_rpchostwhitelistenabled(),
	$cop->get_rpchostwhitelist(),
	$cop->get_startafterstart(),
	$cop->get_stopbeforestop(),
	$cop->get_umask(),
	$cop->get_uplimit(),
	$cop->get_username(),
	$cop->get_utp(),
	$cop->get_watchdir()
];
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
				$retval |= rc_update_service('transmission');
				$retval |= rc_update_service('mdnsresponder');
				config_unlock();
				$_SESSION['submit'] = $sphere->get_script()->get_basename();
				$_SESSION[$sphere->get_script()->get_basename()] = $retval;
				header($sphere->get_script()->get_location());
				exit;
				break;
			case 'disable':
				$retval = 0;
				$name = $cop->get_enable()->get_name();
				$sphere->grid[$name] ??= false;
				if($sphere->grid[$name]):
					$sphere->grid[$name] = false;
					write_config();
					config_lock();
					$retval |= rc_update_service('transmission');
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
					$retval |= rc_update_service('transmission');
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
$is_running = (rc_is_service_running('transmission') === 0);
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
//	add content section 1
$tds1 = $content->add_table_data_settings();
$tds1->ins_colgroup_data_settings();
$thead1 = $tds1->addTHEAD();
$tbody1 = $tds1->addTBODY();
switch($page_mode):
	case PAGE_MODE_VIEW:
		$thead1->c2_titleline(gettext('BitTorrent Settings'));
		break;
	case PAGE_MODE_EDIT:
		$thead1->c2($cop->get_enable(),$sphere,false,$is_readonly,gettext('BitTorrent Settings'));
		break;
endswitch;
$tbody1->
	c2_textinfo('running',gettext('Service Active'),$is_running_message);
$tbody1->
	c2($cop->get_peerport(),$sphere,false,$is_readonly)->
	c2($cop->get_downloaddir(),$sphere,true,$is_readonly)->
	c2($cop->get_configdir(),$sphere,false,$is_readonly)->
	c2($cop->get_portforwarding(),$sphere,false,$is_readonly)->
	c2($cop->get_pex(),$sphere,false,$is_readonly)->
	c2($cop->get_dht(),$sphere,false,$is_readonly)->
	c2($cop->get_lpd(),$sphere,false,$is_readonly)->
	c2($cop->get_utp(),$sphere,false,$is_readonly)->
	c2($cop->get_preallocation(),$sphere,false,$is_readonly)->
	c2($cop->get_encryption(),$sphere,false,$is_readonly)->
	c2($cop->get_uplimit(),$sphere,false,$is_readonly)->
	c2($cop->get_downlimit(),$sphere,false,$is_readonly)->
	c2($cop->get_watchdir(),$sphere,false,$is_readonly)->
	c2($cop->get_incompletedir(),$sphere,false,$is_readonly)->
	c2($cop->get_umask(),$sphere,false,$is_readonly)->
	c2($cop->get_messagelevel(),$sphere,false,$is_readonly)->
	c2($cop->get_startafterstart(),$sphere,false,$is_readonly)->
	c2($cop->get_stopbeforestop(),$sphere,false,$is_readonly)->
	c2($cop->get_extraoptions(),$sphere,false,$is_readonly);
//	add content section 2
$tds2 = $content->add_table_data_settings();
$tds2->ins_colgroup_data_settings();
$tds2->addTHEAD()->
	c2_titleline(gettext('Transmission Web Interface'));
$tbody2 = $tds2->addTBODY();
$tbody2->
	c2($cop->get_port(),$sphere,false,$is_readonly)->
	c2($cop->get_authrequired(),$sphere,false,$is_readonly);
$authrequired_hooks = $document->get_hooks();
foreach($authrequired_hooks as $hook_key => $hook_obj):
	$hook_obj->addDIV(['class' => 'showifchecked'])->cr($cop->get_username(),$sphere,false,$is_readonly);
	$hook_obj->addDIV(['class' => 'showifchecked'])->cr($cop->get_password(),$sphere,false,$is_readonly);
endforeach;
$tbody2->
	c2($cop->get_rpchostwhitelistenabled(),$sphere,false,$is_readonly);
$rpchostwhitelistenabled_hooks = $document->get_hooks();
foreach($rpchostwhitelistenabled_hooks as $hook_key => $hook_obj):
	switch($hook_key):
		case 'true':
			$hook_obj->addDIV(['class' => 'showifchecked'])->cr($cop->get_rpchostwhitelist(),$sphere,false,$is_readonly);
			break;
	endswitch;
endforeach;
if($is_running):
	$if = get_ifname($config['interfaces']['lan']['if']);
	$ipaddr = get_ipaddr($if);
	$value = $cop->get_port()->validate_config($sphere->grid);
	if($value == ''):
		$value = $cop->get_port()->get_defaultvalue();
	endif;
	if($value == ''):
		$url = sprintf('http://%s',$ipaddr);
	else:
		$url = sprintf('http://%s:%s',$ipaddr,$value);
	endif;
	$text = sprintf('<a href="%1$s" target="_blank">%1$s</a>',$url);
	$tbody2->c2_textinfo('url',gettext('URL'),$text);
endif;
//	add buttons
$buttons = $document->add_area_buttons();
switch($page_mode):
	case PAGE_MODE_VIEW:
		$buttons->ins_button_edit();
		if($pending_changes && $is_enabled):
			$buttons->ins_button_enadis(!$is_enabled);
		elseif(!$pending_changes):
			$buttons->ins_button_enadis(!$is_enabled);
		endif;
		break;
	case PAGE_MODE_EDIT:
		$buttons->ins_button_save();
		$buttons->ins_button_cancel();
		break;
endswitch;
$document->render();
