<?php
/*
	row_toolbox.php

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

namespace networks\vlan;

use common\rmo as myr;
use common\sphere as mys;
use common\toolbox as myt;

use const RECORD_MODIFY;
use const RECORD_NEW;

use function new_page;

/**
 *	Wrapper class for autoloading functions
 */
class row_toolbox extends myt\row_toolbox {
/**
 *	Create the sphere object
 *	@return mys\row
 */
	public static function init_sphere() {
		global $config;

		$sphere = new mys\row();
		shared_toolbox::init_sphere($sphere);
		$sphere->
			set_script('interfaces_vlan_edit')->
			set_parent('interfaces_vlan');
		return $sphere;
	}
/**
 *	Create the request method object
 *	@return myr\rmo The request method object
 */
	public static function init_rmo() {
		return myr\rmo_row_templates::rmo_with_clone();
	}
/**
 *	Create the properties object
 *	@return row_properties The properties object
 */
	public static function init_properties() {
		$cop = new row_properties();
		return $cop;
	}
/**
 *	Render the page
 *	@global array $input_errors
 *	@global string|array $errormsg
 *	@global string|array $savemsg
 *	@param row_properties $cop
 *	@param mys\row $sphere
 *	@param int $record_mode
 *	@param bool $prerequisites_ok
 */
	public static function render(row_properties $cop,mys\row $sphere,int $record_mode,bool $prerequisites_ok) {
		global $input_errors;
		global $errormsg;
		global $savemsg;

		$isrecordnew = ($record_mode === RECORD_NEW);
//		$isrecordnewmodify = ($record_mode === RECORD_NEW_MODIFY);
		$isrecordmodify = ($record_mode === RECORD_MODIFY);
//		$isrecordnewornewmodify = ($isrecordnew || $isrecordnewmodify);
		$sphere->add_page_title($isrecordnew ? gettext('Add') : gettext('Edit'));
		$document = new_page($sphere->get_page_title(),$sphere->get_script()->get_scriptname(),'tablesort','sorter-checkbox');
//		add tab navigation
		shared_toolbox::add_tabnav($document);
//		get areas
		$body = $document->getElementById('main');
		$pagecontent = $document->getElementById('pagecontent');
//		create data area
		$content = $pagecontent->add_area_data();
//		display information, warnings and errors
		$content->
			ins_input_errors($input_errors)->
			ins_info_box($savemsg)->
			ins_error_box($errormsg);
		$content->add_table_data_settings()->
			ins_colgroup_data_settings()->
			push()->
			addTHEAD()->
				c2($cop->get_enable(),$sphere,false,false,gettext('Configuration'))->
			pop()->
			addTBODY()->
				c2($cop->get_if(),$sphere,true,false)->
				c2($cop->get_tag(),$sphere,true,false)->
				c2($cop->get_vlandev(),$sphere,true,false)->
				c2($cop->get_vlanpcp(),$sphere,false,false)->
				c2($cop->get_vlanproto(),$sphere,false,false)->
				c2($cop->get_description(),$sphere,false,false);
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
//		additional javascript code
		$body->ins_javascript($sphere->get_js());
		$body->add_js_on_load($sphere->get_js_on_load());
		$body->add_js_document_ready($sphere->get_js_document_ready());
		$document->render();
	}
}
