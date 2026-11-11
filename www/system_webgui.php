<?php
/*
	system_webgui.php

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

use system\webgui\setting_toolbox as toolbox;
use system\webgui\shared_toolbox;

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
	$cop->get_adddivsubmittodataframe(),
	$cop->get_enabletogglemode(),
	$cop->get_cssfcfile(),
	$cop->get_cssfcfilemode(),
	$cop->get_cssguifile(),
	$cop->get_cssguifilemode(),
	$cop->get_cssnavbarfile(),
	$cop->get_cssnavbarfilemode(),
	$cop->get_csstabsfile(),
	$cop->get_csstabsfilemode(),
	$cop->get_navbartoplevelstyle(),
	$cop->get_nonsidisksizevalues(),
	$cop->get_showcolorfulmeter(),
	$cop->get_skipviewmode(),
	$cop->get_showmaxcpus(),
	$cop->get_lp_diag_infos()
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
				if(array_key_exists('g',$_SESSION) && array_key_exists('headermenu',$_SESSION['g'])):
					$_SESSION['g']['headermenu'] = [];
				endif;
				$_SESSION['submit'] = $sphere->get_script()->get_basename();
				$_SESSION[$sphere->get_script()->get_basename()] = $retval;
				header($sphere->get_script()->get_location());
				exit;
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
$input_errors_found = count($input_errors) > 0;
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
if($pending_changes && !$input_errors_found):
	$content->ins_config_has_changed_box();
endif;
//	add content
$content->
	add_table_data_settings()->
		push()->
		ins_colgroup_data_settings()->
		addTHEAD()->
			c2_titleline(gettext('Navigation Menu'))->
		pop()->
		addTBODY()->
			c2($cop->get_navbartoplevelstyle(),$sphere,false,$is_readonly);
$content->
	add_table_data_settings()->
		push()->
		ins_colgroup_data_settings()->
		addTHEAD()->
			c2_titleline(gettext('Display Options'))->
		pop()->
		addTBODY()->
			c2($cop->get_nonsidisksizevalues(),$sphere,false,$is_readonly)->
			c2($cop->get_showcolorfulmeter(),$sphere,false,$is_readonly)->
			c2($cop->get_showmaxcpus(),$sphere,false,$is_readonly);
$content->
	add_table_data_settings()->
		push()->
		ins_colgroup_data_settings()->
		addTHEAD()->
			c2_titleline(gettext('Button Settings'))->
		pop()->
		addTBODY()->
			c2($cop->get_enabletogglemode(),$sphere,false,$is_readonly)->
			c2($cop->get_skipviewmode(),$sphere,false,$is_readonly)->
			c2($cop->get_adddivsubmittodataframe(),$sphere,false,$is_readonly);
$css_settings = $content->add_table_data_settings();
$css_settings->
	ins_colgroup_data_settings()->
	addTHEAD()->
		c2_titleline(gettext('CSS Settings'));
$tbody = $css_settings->addTBODY();
$hooks_cssguifile = $tbody->c2($cop->get_cssguifile(),$sphere,false,$is_readonly)->get_hooks();
$hooks_cssguifile['fc']->cr($cop->get_cssguifilemode(),$sphere,false,$is_readonly);
$hooks_cssnavbarfile = $tbody->c2($cop->get_cssnavbarfile(),$sphere,false,$is_readonly)->get_hooks();
$hooks_cssnavbarfile['fc']->cr($cop->get_cssnavbarfilemode(),$sphere,false,$is_readonly);
$hooks_csstabsfile = $tbody->c2($cop->get_csstabsfile(),$sphere,false,$is_readonly)->get_hooks();
$hooks_csstabsfile['fc']->cr($cop->get_csstabsfilemode(),$sphere,false,$is_readonly);
$hooks_cssfcfile = $tbody->c2($cop->get_cssfcfile(),$sphere,false,$is_readonly)->get_hooks();
$hooks_cssfcfile['fc']->cr($cop->get_cssfcfilemode(),$sphere,false,$is_readonly);
$content->
	add_table_data_settings()->
		push()->
		ins_colgroup_data_settings()->
		addTHEAD()->
			c2_titleline(gettext('Landing Pages'))->
		pop()->
		addTBODY()->
			c2($cop->get_lp_diag_infos(),$sphere,false,$is_readonly);
//	add buttons
$buttons = $document->add_area_buttons();
switch($page_mode):
	case PAGE_MODE_VIEW:
		$buttons->ins_button_edit();
		break;
	case PAGE_MODE_EDIT:
		$buttons->ins_button_save();
		$buttons->ins_button_cancel();
		break;
endswitch;
//	showtime
$document->render();
