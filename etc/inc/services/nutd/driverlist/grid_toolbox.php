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

namespace services\nutd\driverlist;

use common\sphere as mys;
use common\toolbox as myt;

use function new_page;

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
		$sphere->
			set_script('services_ups_drv');
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
 *	Returns an array of /usr/local/etc/nut/driver.list
 *	@return array
 */
	protected static function make_grid() {
		$grid = [];
//		read file
		$rows = @file('/usr/local/etc/nut/driver.list',FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
		if(is_array($rows)):
//			parse data
			foreach($rows as $row):
//				syntax should look like: '"<manufacturer>" "<device type>" "<support level>" "<model name>" "<model extra>" "<driver>"'.
				$regex = '/^"(?<manufacturer>.*)"\s*"(?<devicetype>.*)"\s*"(?<supportlevel>.*)"\s*"(?<modelname>.*)"\s*"(?<modelextra>.*)"\s*"(?<driver>.*)"/';
				unset($matches);
				if(preg_match($regex,$row,$matches) === 1):
					$driverinfo = [];
					$driverinfo['manufacturer'] = $matches['manufacturer'] ?? '';
					$driverinfo['devicetype'] = $matches['devicetype'] ?? '';
					$driverinfo['supportlevel'] = $matches['supportlevel'] ?? '';
					$driverinfo['modelname'] = $matches['modelname'] ?? '';
					$driverinfo['modelextra'] = $matches['modelextra'] ?? '';
					$driverinfo['driver'] = $matches['driver'] ?? '';
					$grid[] = $driverinfo;
				endif;
			endforeach;
		endif;
		return $grid;
	}
/**
 *	Render the page
 *	@global array $input_errors
 *	@global string $errormsg
 *	@global string $savemsg
 *	@param grid_properties $cop
 *	@param mys\grid $sphere
 */
	public static function render(grid_properties $cop,mys\grid $sphere) {
		$pgtitle = [gettext('Services'),gettext('UPS'),gettext('Driver List')];
		$sphere->grid = self::make_grid();
		$row_count = count($sphere->grid);
		$row_exists = ($row_count > 0);
		$use_tablesort = ($row_count > 1);
		$a_col_width = ['25%','25%','25%','25%'];
		$n_col_width = count($a_col_width);
		if($use_tablesort):
			$document = new_page($pgtitle,$sphere->get_script()->get_scriptname(),'notabnav','tablesort');
		else:
			$document = new_page($pgtitle,$sphere->get_script()->get_scriptname(),'notabnav');
		endif;
//		get areas
		$pagecontent = $document->getElementById('pagecontent');
//		create data area
		$content = $pagecontent->add_area_data();
//		add content
		$table = $content->add_table_data_selection();
		$table->ins_colgroup_with_styles('width',$a_col_width);
		$thead = $table->addTHEAD();
		$tbody = $table->addTBODY();
		$tfoot = $table->addTFOOT();
		$thead->ins_titleline(gettext('UPS Driver List'),$n_col_width);
		$thead->
			addTR()->
				insTHwC('lhell',$cop->get_manufacturer()->get_title())->
				insTHwC('lhell',$cop->get_modelname()->get_title())->
				insTHwC('lhell',$cop->get_modelextra()->get_title())->
				insTHwC('lhebl',$cop->get_driver()->get_title());
		if($row_exists):
			$key_manufacturer = $cop->get_manufacturer()->get_name();
			$key_modelname = $cop->get_modelname()->get_name();
			$key_modelextra = $cop->get_modelextra()->get_name();
			$key_driver = $cop->get_driver()->get_name();
			foreach($sphere->grid as $sphere->row_id => $sphere->row):
				$tbody->
					addTR()->
						insTDwC('lcell',$sphere->row[$key_manufacturer])->
						insTDwC('lcell',$sphere->row[$key_modelname])->
						insTDwC('lcell',$sphere->row[$key_modelextra])->
						insTDwC('lcebl',$sphere->row[$key_driver]);
			endforeach;
		else:
			$tfoot->ins_no_records_found($n_col_width);
		endif;
		$document->render();
	}
}
