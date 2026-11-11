<?php
/*
	status_disks.php

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
require_once 'co_sphere.php';

use common\arr;
use gui\document;

/**
 *	Create sphere
 *	@global array $config
 *	@return \co_sphere_row
 */
function get_sphere_status_disks() {
	global $config;

	$sphere = new co_sphere_row('status_disks','php');
	return $sphere;
}
/**
 *	Render details of this page
 *	@global array $config
 *	@param DOMDocument $root
 *	@return DOMDocument
 */
function status_disks_render($root = null) {
	global $config;

	if(is_null($root)):
		$is_DOM = false;
		$root = new document();
	else:
		$is_DOM = true;
	endif;
	$pconfig = [];
	$pconfig['temp_info'] = $config['smartd']['temp']['info'] ?? 0;
	$pconfig['temp_crit'] = $config['smartd']['temp']['crit'] ?? 0;
	$a_phy_hast = array_merge((array)get_hast_disks_list());
	$cfg_disks = arr::make_branch($config,'disks','disk');
	if(empty($cfg_disks)):
	else:
		arr::sort_key($cfg_disks,'name');
	endif;
	foreach($cfg_disks as $cfg_disk):
//		init loop values
		$lv = [];
		$lv['iostat_value'] = system_get_device_iostat($cfg_disk['name']);
		if($lv['iostat_value'] !== false):
			$lv['iostat'] = sprintf('%s KiB/t, %s tps, %s MiB/s',$lv['iostat_value']['kpt'],$lv['iostat_value']['tps'],$lv['iostat_value']['mps']);
		else:
			$lv['iostat'] = gettext('n/a');
		endif;
		$lv['temp_value'] = system_get_device_temp($cfg_disk['devicespecialfile']);
		$lv['name'] = $cfg_disk['name'];
		if($cfg_disk['type'] == 'HAST'):
			$role = $a_phy_hast[$cfg_disk['name']]['role'];
			$lv['size'] = $a_phy_hast[$cfg_disk['name']]['size'];
			$lv['status'] = sprintf('%s (%s)',(disks_exists($cfg_disk['devicespecialfile']) == 0) ? gettext('ONLINE') : gettext('MISSING'),$role);
		else:
			$lv['size'] = $cfg_disk['size'];
			$lv['status'] = (disks_exists($cfg_disk['devicespecialfile']) == 0) ? gettext('ONLINE') : gettext('MISSING');
		endif;
		$lv['model'] = $cfg_disk['model'];
		$lv['description'] = empty($cfg_disk['desc']) ? gettext('n/a') : $cfg_disk['desc'];
		$lv['serial'] = empty($cfg_disk['serial']) ? gettext('n/a') : $cfg_disk['serial'];
		$lv['fstype'] = empty($cfg_disk['fstype']) ? gettext('Unknown or unformatted') : get_fstype_shortdesc($cfg_disk['fstype']);
		$lv['temp'] = is_null($lv['temp_value']) ? gettext('n/a') : sprintf('%s °C',$lv['temp_value']);
		$tr = $root->addTR();
		$tr->
			insTDwC('lcell',$lv['name'])->
			insTDwC('lcell',$lv['size'])->
			insTDwC('lcell',$lv['model'])->
			insTDwC('lcell',$lv['description'])->
			insTDwC('lcell',$lv['serial'])->
			insTDwC('lcell',$lv['fstype'])->
			insTDwC('lcell',$lv['iostat']);
		if(is_null($lv['temp_value'])):
			$tr->insTDwC('lcell',$lv['temp']);
		elseif(!empty($pconfig['temp_crit']) && $lv['temp_value'] >= $pconfig['temp_crit']):
			$tr->addTDwC('lcell')->addDIV(['class'=> 'errortext'],$lv['temp']);
		elseif(!empty($pconfig['temp_info']) && $lv['temp_value'] >= $pconfig['temp_info']):
			$tr->addTDwC('lcell')->addDIV(['class'=> 'warningtext'],$lv['temp']);
		else:
			$tr->insTDwC('lcell',$lv['temp']);
		endif;
		$tr->insTDwC('lcebld',$lv['status']);
	endforeach;
	$geom_raid_disks = get_sraid_disks_list();
	foreach($geom_raid_disks as $geom_raid_disk_key => $geom_raid_disk):
//		init loop values
		$lv = [];
		$lv['iostat_value'] = system_get_device_iostat($geom_raid_disk_key);
		if($lv['iostat_value'] !== false):
			$lv['iostat'] = sprintf('%s KiB/t, %s tps, %s MiB/s',$lv['iostat_value']['kpt'],$lv['iostat_value']['tps'],$lv['iostat_value']['mps']);
		else:
			$lv['iostat'] = gettext('n/a');
		endif;
		$lv['temp_value'] = system_get_device_temp($geom_raid_disk_key);
		$lv['name'] = $geom_raid_disk_key;
		$lv['size'] = $geom_raid_disk['size'];
		$lv['model'] = gettext('n/a');
		$lv['description'] = gettext('Software RAID');
		$lv['serial'] = gettext('n/a');
		$lv['fstype'] = empty($geom_raid_disk['fstype']) ? gettext('UFS') : get_fstype_shortdesc($geom_raid_disk['fstype']);
		$lv['temp'] = is_null($lv['temp_value']) ? gettext('n/a') : sprintf('%s °C',$lv['temp_value']);
		$lv['status'] = $geom_raid_disk['state'];
		$tr = $root->addTR();
		$tr->
			insTDwC('lcell',$lv['name'])->
			insTDwC('lcell',$lv['size'])->
			insTDwC('lcell',$lv['model'])->
			insTDwC('lcell',$lv['description'])->
			insTDwC('lcell',$lv['serial'])->
			insTDwC('lcell',$lv['fstype'])->
			insTDwC('lcell',$lv['iostat']);
		if(is_null($lv['temp_value'])):
			$tr->insTDwC('lcell',$lv['temp']);
		elseif(!empty($pconfig['temp_crit']) && $lv['temp_value'] >= $pconfig['temp_crit']):
			$tr->addTDwC('lcell')->addDIV(['class'=> 'errortext'],$lv['temp']);
		elseif(!empty($pconfig['temp_info']) && $lv['temp_value'] >= $pconfig['temp_info']):
			$tr->addTDwC('lcell')->addDIV(['class'=> 'warningtext'],$lv['temp']);
		else:
			$tr->insTDwC('lcell',$lv['temp']);
		endif;
		$tr->insTDwC('lcebld',$lv['status']);
	endforeach;
	if($is_DOM):
		return $root;
	else:
		return $root->get_html();
	endif;
}
if(is_ajax()):
	$status = status_disks_render();
	render_ajax($status);
endif;
$sphere = get_sphere_status_disks();
$jcode = <<<EOJ
$(document).ready(function(){
	var gui = new GUI;
	gui.recall(15000, 15000, 'status_disks.php', null, function(data) {
		if ($('#area_refresh').length > 0) {
			$('#area_refresh').html(data.data);
			$('.area_data_selection').trigger('update');
		}
	});
});
EOJ;
$a_colwidth = ['5%','7%','15%','17%','13%','10%','18%','8%','7%'];
$n_colwidth = count($a_colwidth);
$document = new_page([gettext('Status'),gettext('Disks')],null,'tablesort');
$body = $document->getElementById('main');
$pagecontent = $document->getElementById('pagecontent');
$body->ins_javascript($jcode);
$content = $pagecontent->add_area_data();
$tbody_inner = $content->
	add_table_data_selection()->
		ins_colgroup_with_styles('width',$a_colwidth)->
		push()->addTHEAD()->
			ins_titleline(gettext('Status & Information'),$n_colwidth)->
			addTR()->
				insTHwC('lhell',gettext('Device'))->
				insTHwC('lhell',gettext('Size'))->
				insTHwC('lhell',gettext('Device Model'))->
				insTHwC('lhell',gettext('Description'))->
				insTHwC('lhell',gettext('Serial Number'))->
				insTHwC('lhell',gettext('Filesystem'))->
				insTHwC('lhell sorter-false parser-false',gettext('I/O Statistics'))->
				insTHwC('lhell',gettext('Temperature'))->
				insTHwC('lhebl',gettext('Status'))->
		pop()->addTBODY(['id' => 'area_refresh']);
status_disks_render($tbody_inner);
$document->render();
