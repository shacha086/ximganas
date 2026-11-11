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

namespace system\sysctl\info;

use common\sphere as mys;
use common\toolbox as myt;

use function new_page;
use function updatenotify_exists;

/**
 *	Wrapper class for autoloading functions
 */
class grid_toolbox extends myt\grid_toolbox {
/**
 *	Create the sphere object
 *	@return mys\grid
 */
	public static function init_sphere() {
		$sphere = new mys\grid();
		shared_toolbox::init_sphere($sphere);
		$sphere->
			set_script('system_sysctl_info');
		return $sphere;
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
 *	@param grid_properties $cop
 *	@param mys\info\grid $sphere
 */
	public static function render(grid_properties $cop,mys\grid $sphere) {
		global $input_errors;
		global $errormsg;
		global $savemsg;

		$record_exists = count($sphere->grid) > 0;
		$use_tablesort = count($sphere->grid) > 1;
		$a_col_width = ['30%','15%','30%','25%'];
		$n_col_width = count($a_col_width);
		if($use_tablesort):
			$document = new_page($sphere->get_page_title(),$sphere->get_script()->get_scriptname(),'tablesort');
		else:
			$document = new_page($sphere->get_page_title(),$sphere->get_script()->get_scriptname());
		endif;
//		get areas
		$body = $document->getElementById('main');
		$pagecontent = $document->getElementById('pagecontent');
//		add tab navigation
		shared_toolbox::add_tabnav($document);
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
		$tbody = $table->addTBODY();
		$tfoot = $table->addTFOOT();
		$thead->
			ins_titleline(gettext('Overview'),$n_col_width)->
			addTR()->
				insTHwC('lhell',$cop->get_name()->get_title())->
				insTHwC('lhell',$cop->get_sysctltype()->get_title())->
				insTHwC('lhell',$cop->get_sysctlinfo()->get_title())->
				insTHwC('lhebl',$cop->get_value()->get_title());
		if($record_exists):
			$key_sysctltype = $cop->get_sysctltype()->get_name();
			$key_sysctlinfo = $cop->get_sysctlinfo()->get_name();
			$key_value = $cop->get_value()->get_name();
			foreach($sphere->grid as $sphere->row_id => $sphere->row):
				$tbody->
					addTR()->
						insTDwC('lcell',$sphere->row_id ?? '')->
						insTDwC('lcell',$sphere->row[$key_sysctltype] ?? '')->
						insTDwC('lcell',$sphere->row[$key_sysctlinfo] ?? '')->
						insTDwC('lcebl',$sphere->row[$key_value] ?? '');
			endforeach;
		else:
			$tfoot->ins_no_records_found($n_col_width);
		endif;
		$document->render();
	}
}
