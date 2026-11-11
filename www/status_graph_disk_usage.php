<?php
/*
	status_graph_disk_usage.php

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

use common\arr;
use common\properties as myp;
use status\monitor\shared_toolbox as myst;

$grid = arr::make_branch($config,'rrdgraphs');
$refresh = empty($grid['refresh_time']) ? 300 : $grid['refresh_time'];
$now = time();
$disk_types = (empty($grid['mounts']) ? 0 : 1) + (empty($grid['pools']) ? 0 : 2);
switch($disk_types):
	case 1:
		$options = $grid['mounts'];
		break;
	case 2:
		$options = $grid['pools'];
		break;
	case 3:
		$options = array_merge($grid['mounts'],$grid['pools']);
		break;
	default:
		$options = [];
		break;
endswitch;
if(!empty($options)):
	asort($options);
endif;
$cops_element = new myp\property_list();
$cops_element->
	set_defaultvalue('')->
	set_id('statusgraphdukey')->
	set_input_type(myp\property::INPUT_TYPE_SELECT)->
	set_name('statusgraphdukey')->
	set_options($options)->
	set_title(gettext('Disk'));
$record_exists = count($options) > 0;
if($record_exists):
	$statusgraphdukey = $_POST['statusgraphdukey'] ?? $_SESSION['statusgraphdukey'] ?? null;
	if(is_string($statusgraphdukey) && array_key_exists($statusgraphdukey,$options)):
	else:
		$statusgraphdukey = array_key_first($options);
	endif;
	$_SESSION['statusgraphdukey'] = $statusgraphdukey;
	$current_filesystem = $options[$statusgraphdukey];
	$current_filesystem_enc = strtr(base64_encode($current_filesystem),'+/=','-_~');
	mwexec(sprintf('/usr/local/share/rrdgraphs/rrd-graph.sh disk_usage %s',escapeshellarg($current_filesystem)),true);
else:
	$statusgraphdukey = null;
	$current_filesystem_enc = null;
endif;
$document = new_page([gettext('Status'),gettext('Monitoring'),gettext('Disk Usage')],'status_graph_disk_usage.php');
//	get areas
$head = $document->getElementById('head');
$pagecontent = $document->getElementById('pagecontent');
$head->insElement('meta',['http-equiv' => 'refresh','content' => $refresh]);
//	add tab navigation
myst::add_tabnav($document,myst::RRD_DISK_USAGE);
//	create data area
$content = $pagecontent->add_area_data();
//	display information, warnings and errors
if(file_exists($d_sysrebootreqd_path)):
	$content->ins_info_box(get_std_save_message(0));
endif;
if($record_exists):
	$content->
		add_table_data_settings()->
			ins_colgroup_data_settings()->
			push()->
			addTHEAD()->
				c2_titleline(gettext('Reporting Disk'))->
			pop()->
			addTBODY(['class' => 'donothighlight'])->
				c2($cops_element,$statusgraphdukey);
	$content->
		add_table_data_settings()->
			push()->
			addTHEAD()->
				ins_titleline(gettext('Disk Usage') . sprintf(' (%s)',sprintf(gettext('Graph updates every %d seconds.'),$refresh)))->
			pop()->
			addTBODY(['class' => 'donothighlight'])->
				addTR()->
					addTD()->
						addDIV(['class' => 'rrdgraphs'])->
							insIMG(['class' => 'rrdgraphs','src' => sprintf('/images/rrd/rrd-mnt_%s_daily.png?rand=%s',$current_filesystem_enc,$now),'alt' => gettext('RRDGraphs Daily Disk Usage Graph')])->
							insIMG(['class' => 'rrdgraphs','src' => sprintf('/images/rrd/rrd-mnt_%s_weekly.png?rand=%s',$current_filesystem_enc,$now),'alt' => gettext('RRDGraphs Weekly Disk Usage Graph')])->
							insIMG(['class' => 'rrdgraphs','src' => sprintf('/images/rrd/rrd-mnt_%s_monthly.png?rand=%s',$current_filesystem_enc,$now),'alt' => gettext('RRDGraphs Monthly Disk Usage Graph')])->
							insIMG(['class' => 'rrdgraphs','src' => sprintf('/images/rrd/rrd-mnt_%s_yearly.png?rand=%s',$current_filesystem_enc,$now),'alt' => gettext('RRDGraphs Yearly Disk Usage Graph')]);
	$content->
		add_area_remarks()->
			ins_remark('info','',sprintf(gettext('Graph updates every %d seconds.'),$refresh));
	$document->
		add_area_buttons(true,true)->
			ins_button_save();
	$document->
		add_js_document_ready('document.getElementById("statusgraphdukey").setAttribute("onchange","submit()");');
else:
	$content->
		add_table_data_settings()->
			ins_colgroup_data_settings()->
			push()->
			addTHEAD()->
				c2_titleline(gettext('Reporting Disk'))->
			pop()->
			addTFOOT()->
				ins_no_records_found(2);
endif;
$document->render();
