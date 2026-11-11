<?php
/*
	status_graph.php

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
use status\monitor\shared_toolbox as myst;

$grid = arr::make_branch($config,'interfaces');
$graph_width = 400;
$graph_height = 225;
$a_object = [
	'type' => 'image/svg+xml',
	'class' => 'rrdgraphs',
	'width' => $graph_width,
	'height' => $graph_height
];
$a_param = [
	'name' => 'src'
];
$curif = 'lan';
if(isset($_GET['if']) && $_GET['if']):
	$curif = $_GET['if'];
endif;
$ifnum = get_ifname($grid[$curif]['if']);
$ifdescrs = ['lan' => 'LAN'];
for($j = 1;isset($grid['opt' . $j]);$j++):
	$ifdescrs['opt' . $j] = $grid['opt' . $j]['descr'];
endfor;
$gt_notsupported = gettext('Your browser does not support this svg object type.');
$document = new_page([gettext('Status'),gettext('Monitoring'),gettext('System Load')]);
//	get areas
$pagecontent = $document->getElementById('pagecontent');
//	add tab navigation
myst::add_tabnav($document,myst::RRD_SYSTEM_LOAD);
//	create data area
$content = $pagecontent->add_area_data();
//	display information, warnings and errors
if(file_exists($d_sysrebootreqd_path)):
	$content->ins_info_box(get_std_save_message(0));
endif;
$div = $content->
	add_table_data_settings()->
		push()->
		addTHEAD()->
			ins_titleline(gettext('System Load'))->
		pop()->
		addTBODY(['class' => 'donothighlight'])->
			addTR()->
				addTD()->
					addDIV(['class' => 'rrdgraphs']);
$a_object['id'] = 'graph';
$a_object['data'] = sprintf('status_graph2.php?ifnum=%s&ifname=%s',$ifnum,rawurlencode($ifdescrs[$curif]));
$a_param['value'] = sprintf('status_graph2.php?ifnum=%s&ifname=%s',$ifnum,rawurlencode($ifdescrs[$curif]));
$div->
	addElement('object',$a_object)->
		insElement('param',$a_param)->
		insSPAN([],$gt_notsupported);
for($j = 1;isset($grid['opt' . $j]);$j++):
	$ifdescrs = $grid['opt' . $j]['descr'];
	$ifnum = $grid['opt' . $j]['if'];
	$a_object['id'] = sprintf('graph%d',$j);
	$a_object['data'] = sprintf('status_graph2.php?ifnum=%s&ifname=%s',$ifnum,rawurlencode($ifdescrs));
	$a_param['value'] = sprintf('status_graph2.php?ifnum=%s&ifname=%s',$ifnum,rawurlencode($ifdescrs));
	$div->
		addElement('object',$a_object)->
			insElement('param',$a_param)->
			insSPAN([],$gt_notsupported);
endfor;
$a_object['id'] = 'graph0';
$a_object['data'] = 'status_graph_cpu2.php';
$a_param['value'] = 'status_graph_cpu2.php';
$div->
	addElement('object',$a_object)->
		insElement('param',$a_param)->
		insSPAN([],$gt_notsupported);
$content->
	add_area_remarks()->
		ins_remark('info','',gettext('Graph shows recent 120 seconds.'));
$document->render();
