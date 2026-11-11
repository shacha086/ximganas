<?php
/*
	disks_zfs_volume_info.php

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
use disks\zfs\volume\cfg_toolbox as cfg;
use disks\zfs\volume\cli_toolbox as cli;

if(isset($_GET['uuid']) && is_string($_GET['uuid']) && uuid::is_v4($_GET['uuid'])):
//	collect information from a single zfs volume
	$uuid = $_GET['uuid'];
	$entity_name = cfg::name_of_uuid($uuid);
	if(isset($entity_name)):
		$status = ['arl' => cli::get_list($entity_name),'arp' => cli::get_properties($entity_name)];
	else:
		$status = ['arl' => gettext('ZFS volume not found.'),'arp' => gettext('ZFS volume properties not available.')];
	endif;
	$json_string = json_encode(['submit' => 'inform','uuid' => $uuid]);
else:
//	collect information from all zfs volumes
	$entity_name = null;
	$status = ['arl' => cli::get_list(),'arp' => cli::get_properties()];
	$json_string = 'null';
endif;
if(is_ajax()):
	render_ajax($status);
endif;
$pgtitle = [gettext('Disks'),gettext('ZFS'),gettext('Volumes'),gettext('Information')];
if(isset($entity_name)):
	$pgtitle[] = $entity_name;
endif;
$document = new_page($pgtitle);
//	add tab navigation
$document->
	add_area_tabnav()->
		push()->
		add_tabnav_upper()->
			ins_tabnav_record('disks_zfs_zpool.php',gettext('Pools'))->
			ins_tabnav_record('disks_zfs_dataset.php',gettext('Datasets'))->
			ins_tabnav_record('disks_zfs_volume.php',gettext('Volumes'),gettext('Reload page'),true)->
			ins_tabnav_record('disks_zfs_snapshot.php',gettext('Snapshots'))->
			ins_tabnav_record('disks_zfs_scheduler_snapshot_create.php',gettext('Scheduler'))->
			ins_tabnav_record('disks_zfs_config.php',gettext('Configuration'))->
			ins_tabnav_record('disks_zfs_settings.php',gettext('Settings'))->
		pop()->
		add_tabnav_lower()->
			ins_tabnav_record('disks_zfs_volume.php',gettext('Volume'))->
			ins_tabnav_record('disks_zfs_volume_info.php',gettext('Information'),gettext('Reload page'),true);
//	get areas
$body = $document->getElementById('main');
$pagecontent = $document->getElementById('pagecontent');
//	create data area
$content = $pagecontent->add_area_data();
$content->
	add_table_data_settings()->
		ins_colgroup_data_settings()->
		push()->
		addTHEAD()->
			c2_titleline(gettext('ZFS Volume Information & Status'))->
		pop()->
		addTBODY()->
			addTR()->
				insTDwC('celltag',gettext('Information & Status'))->
				addTDwC('celldata')->
					addElement('pre',['class' => 'cmdoutput'])->
						insSPAN(['id' => 'arl'],$status['arl']);
$content->
	add_table_data_settings()->
		ins_colgroup_data_settings()->
		push()->
		addTHEAD()->
			c2_titleline(gettext('ZFS Volume Properties'))->
		pop()->
		addTBODY()->
			addTR()->
				insTDwC('celltag',gettext('Properties'))->
				addTDwC('celldata')->
					addElement('pre',['class' => 'cmdoutput'])->
						insSPAN(['id' => 'arp'],$status['arp']);
//	add additional javascript code
$js_document_ready = <<<EOJ
	var gui = new GUI;
	gui.recall(30000,30000,'disks_zfs_volume_info.php',$json_string,function(data) {
		if($('#arl').length > 0) { $('#arl').text(data.arl); }
		if($('#arp').length > 0) { $('#arp').text(data.arp); }
	});
EOJ;
$body->add_js_document_ready($js_document_ready);
$document->render();
