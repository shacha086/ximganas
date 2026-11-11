<?php
/*
	disks_raid_graid5_info.php

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

use common\uuid;
use disks\geom\raid5\cfg_toolbox as cfg;
use disks\geom\raid5\cli_toolbox as cli;

if(isset($_GET['uuid']) && is_string($_GET['uuid']) && uuid::is_v4($_GET['uuid'])):
//	collect information from a single geom (via uuid)
	$uuid = $_GET['uuid'];
	$entity_name = cfg::name_of_uuid($uuid);
	if(isset($entity_name)):
		$status = ['ars' => cli::get_status($entity_name),'arl' => cli::get_list($entity_name)];
	else:
		$status = ['ars' => gettext('GEOM not found.'),'arl' => gettext('GEOM details not available.')];
	endif;
	$json_string = json_encode(['submit' => 'inform','uuid' => $uuid]);
elseif(isset($_GET['name']) && is_string($_GET['name'])):
//	collect information from a single geom (via geom name)
	$entity_name = $_GET['name'];
	$status = ['ars' => cli::get_status($entity_name),'arl' => cli::get_list($entity_name)];
	$json_string = json_encode(['submit' => 'inform','name' => $entity_name]);
else:
//	collect information from all graid5s
	$entity_name = null;
	$status = ['ars' => cli::get_status()];
	$json_string = 'null';
endif;
if(is_ajax()):
	render_ajax($status);
endif;
$pgtitle = [gettext('Disks'),gettext('Software RAID'),gettext('RAID5'),gettext('Information')];
$document = new_page($pgtitle);
//	get areas
$body = $document->getElementById('main');
$pagecontent = $document->getElementById('pagecontent');
//	add tab navigation
$document->
	add_area_tabnav()->
		push()->
		add_tabnav_upper()->
			ins_tabnav_record('disks_raid_geom.php',gettext('GEOM'),gettext('Reload page'),true)->
			ins_tabnav_record('disks_raid_gvinum.php',gettext('RAID 0/1/5'))->
		pop()->
		add_tabnav_lower()->
			ins_tabnav_record('disks_raid_geom.php',gettext('Management'))->
			ins_tabnav_record('disks_raid_graid5_tools.php',gettext('Maintenance'))->
			ins_tabnav_record('disks_raid_graid5_info.php',gettext('Information'),gettext('Reload page'),true);
$content = $pagecontent->add_area_data();
$content->
	add_table_data_settings()->
		push()->
		ins_colgroup_data_settings()->
		addTHEAD()->
			c2_titleline(gettext('RAID5 Status'))->
		pop()->
		addTBODY()->
			addTR()->
				insTDwC('celltag',gettext('Status'))->
				addTDwC('celldata')->
					addElement('pre',['class' => 'cmdoutput'])->
						insSPAN(['id' => 'ars'],$status['ars']);
if(isset($entity_name)):
$content->
	add_table_data_settings()->
		ins_colgroup_data_settings()->
		push()->
		addTHEAD()->
			c2_titleline(gettext('RAID5 Details'))->
		pop()->
		addTBODY()->
			addTR()->
				insTDwC('celltag',gettext('Details'))->
				addTDwC('celldata')->
					addElement('pre',['class' => 'cmdoutput'])->
						insSPAN(['id' => 'arl'],$status['arl']);
endif;
//	add additional javascript code
$js_document_ready = <<<EOJ
	var gui = new GUI;
	gui.recall(10000,10000,'disks_raid_graid5_info.php',$json_string,function(data) {
		if($('#ars').length > 0) { $('#ars').text(data.ars); }
		if($('#arl').length > 0) { $('#arl').text(data.arl); }
	});
EOJ;
$body->add_js_document_ready($js_document_ready);
$document->render();
