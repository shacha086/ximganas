<?php
/*
	grid_toolbox.php

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

namespace system\rc\sort;

use common\arr;
use common\properties as myp;
use common\rmo as myr;
use common\sphere as mys;
use common\toolbox as myt;
use system\rc as myparent;

use const PAGE_MODE_POST;
use const PAGE_MODE_VIEW;
use const UPDATENOTIFY_MODE_DIRTY;
use const UPDATENOTIFY_MODE_DIRTY_CONFIG;

use function get_std_save_message;
use function new_page;
use function updatenotify_exists;
use function updatenotify_get_mode;
use function write_config;

/**
 *	Wrapper class for autoloading functions
 */
class grid_toolbox extends myt\grid_toolbox {
/**
 *	Create the sphere object
 *	@global array $config
 *	@return mys\grid
 */
	public static function init_sphere() {
		global $config;

		$sphere = new mys\grid();
		shared_toolbox::init_sphere($sphere);
		$sphere->
			set_script('system_rc_sort')->
			set_parent('system_rc');
		return $sphere;
	}
/**
 *	Create the request method object
 *	@param myparent\grid_properties $cop
 *	@param mys\grid $sphere
 *	@return myr\rmo The request method object
 */
	public static function init_rmo(myparent\grid_properties $cop,mys\grid $sphere) {
		$rmo = new myr\rmo();
		$rmo->
			set_default('GET','view',PAGE_MODE_VIEW)->
			add('POST','apply',PAGE_MODE_POST);
		return $rmo;
	}
/**
 *	Create the properties object
 *	@return grid_properties The properties object
 */
	public static function init_properties() {
		$cop = new grid_properties();
		return $cop;
	}
/**
 *	Render the page
 *	@global array $input_errors
 *	@global string $errormsg
 *	@global string $savemsg
 *	@param myparent\grid_properties $cop
 *	@param mys\grid $sphere
 */
	public static function render(myparent\grid_properties $cop,mys\grid $sphere) {
		global $input_errors;
		global $errormsg;
		global $savemsg;
		global $g_img;

		$pgtitle = [gettext('System'),gettext('Advanced'),gettext('Command Scripts'),gettext('Sort')];
		$record_exists = count($sphere->grid) > 0;
		$a_col_width = ['5%','15%','35%','7%','18%','10%','10%'];
		$n_col_width = count($a_col_width);
		$document = new_page($pgtitle,$sphere->get_script()->get_scriptname());
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
		if(updatenotify_exists($sphere->get_notifier())):
			$content->ins_config_has_changed_box();
		endif;
//		add content
		$table = $content->add_table_data_selection();
		$table->ins_colgroup_with_styles('width',$a_col_width);
		$thead = $table->addTHEAD();
		$tbody = $table->addTBODY(['id' => 'system_rc_list']);
		$tfoot = $table->addTFOOT();
		$thead->ins_titleline(gettext('Overview'),$n_col_width);
		$tr = $thead->addTR();
		$tr->
			insTHwC('lhelc')->
			insTHwC('lhell',$cop->get_name()->get_title())->
			insTHwC('lhell',$cop->get_value()->get_title())->
			insTHwC('lhelc',gettext('Status'))->
			insTHwC('lhell',$cop->get_description()->get_title())->
			insTHwC('lhell',$cop->get_typeid()->get_title())->
			insTHwC('lhebl',$cop->get_toolbox()->get_title());
		if($record_exists):
			foreach($sphere->grid as $sphere->row_id => $sphere->row):
				$notificationmode = updatenotify_get_mode($sphere->get_notifier(),$sphere->get_row_identifier_value());
				$is_notdirty = ($notificationmode != UPDATENOTIFY_MODE_DIRTY) && ($notificationmode != UPDATENOTIFY_MODE_DIRTY_CONFIG);
				$is_enabled = $sphere->is_enadis_enabled() ? (is_bool($test = $sphere->row[$cop->get_enable()->get_name()] ?? false) ? $test : true) : true;
				$is_notprotected = $sphere->is_lock_enabled() ? !(is_bool($test = $sphere->row[$cop->get_protected()->get_name()] ?? false) ? $test : true) : true;
				$dc = $is_enabled ? '' : 'd';
				$typeid_name = $cop->get_typeid()->get_name();
				$typeid_options = $cop->get_typeid()->get_options();
				if(array_key_exists($sphere->row[$typeid_name],$typeid_options)):
					$typeid_value = $typeid_options[$sphere->row[$typeid_name]];
				else:
					$typeid_value = gettext('Unknown');
				endif;
				$tbody->
					addTR()->
						push()->
						addTDwC('lcelc' . $dc)->
							ins_input_hidden($sphere->get_cbm_name() . '[]',$sphere->get_row_identifier_value() ?? '')->
						pop()->
						insTDwC('lcell' . $dc,$sphere->row[$cop->get_name()->get_name()] ?? '')->
						insTDwC('lcell' . $dc,$sphere->row[$cop->get_value()->get_name()] ?? '')->
						ins_enadis_icon($is_enabled)->
						insTDwC('lcell' . $dc,$sphere->row[$cop->get_description()->get_name()] ?? '')->
						insTDwC('lcell' . $dc,$typeid_value)->
						add_toolbox_area(false)->
							ins_updownbox($sphere,true);
			endforeach;
		else:
			$tfoot->ins_no_records_found($n_col_width);
		endif;
		$document->
			add_area_buttons()->
				ins_button_apply();
//		additional javascript code
		$jol = <<<'EOJ'
$('#system_rc_list .move').click(function() {
	var row = $(this).closest('tr');
	if ($(this).hasClass('up')) row.prev().before(row);
	if ($(this).hasClass('down')) row.next().after(row);
});
EOJ;
		$body->ins_javascript();
		$body->add_js_on_load($jol);
		$body->add_js_document_ready($sphere->get_js_document_ready());
		$document->render();
	}
/**
 *	process request method
 *	@global string $d_sysrebootreqd_path
 *	@global array $input_errors
 *	@global string $errormsg
 *	@global string $savemsg
 *	@param myp\container $cop
 *	@param mys\root $sphere
 *	@param myr\rmo $rmo
 */
	public static function looper(myp\container $cop,mys\root $sphere,myr\rmo $rmo) {
		global $d_sysrebootreqd_path;
		global $input_errors;
		global $errormsg;
		global $savemsg;

//		preset $savemsg in case a reboot is pending
		if(file_exists($d_sysrebootreqd_path)):
			$savemsg = get_std_save_message(0);
		endif;
		[$page_method,$page_action,$page_mode] = $rmo->validate();
		switch($page_method):
			case 'POST':
				switch($page_action):
					case 'apply':
						if($_POST[$sphere->get_cbm_name()] && is_array($_POST[$sphere->get_cbm_name()])):
							$a_param = [];
							foreach($_POST[$sphere->get_cbm_name()] as $r_member):
								if(is_string($r_member)):
									$index = arr::search_ex($r_member,$sphere->grid,$sphere->get_row_identifier());
									if($index  !== false):
										$a_param[] = $sphere->grid[$index];
									endif;
								endif;
							endforeach;
							$sphere->grid = $a_param;
							write_config();
						endif;
						header($sphere->get_parent()->get_location());
						exit;
						break;
				endswitch;
				break;
		endswitch;
	}
}
