<?php
/*
	status_graph_memory.php

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
$cops_element = new myp\property_list();
$cops_element->
	set_options(['memory' => gettext('Standard'),'memory-detailed' => gettext('Detailed')])->
	set_defaultvalue('memory')->
	set_id('statusgraphmemorylevel')->
	set_name('statusgraphmemorylevel')->
	set_input_type(myp\property::INPUT_TYPE_SELECT)->
	set_title(gettext('Level'));
//	populate $statusgraphmemorylevel from $_POST or $_SESSION or default value
$statusgraphmemorylevel = $_POST['statusgraphmemorylevel'] ?? $_SESSION['statusgraphmemorylevel'] ?? $cops_element->get_defaultvalue();
if(is_string($statusgraphmemorylevel) && array_key_exists($statusgraphmemorylevel,$cops_element->get_options())):
else:
	$statusgraphmemorylevel = $cops_element->get_defaultvalue();
endif;
$_SESSION['statusgraphmemorylevel'] = $statusgraphmemorylevel;
mwexec('/usr/local/share/rrdgraphs/rrd-graph.sh memory',true);
$document = new_page([gettext('Status'),gettext('Monitoring'),gettext('Memory Usage')],'status_graph_memory.php');
//	get areas
$head = $document->getElementById('head');
$pagecontent = $document->getElementById('pagecontent');
$head->insElement('meta',['http-equiv' => 'refresh','content' => $refresh]);
//	add tab navigation
myst::add_tabnav($document,myst::RRD_MEMORY_USAGE);
//	create data area
$content = $pagecontent->add_area_data();
//	display information, warnings and errors
if(file_exists($d_sysrebootreqd_path)):
	$content->ins_info_box(get_std_save_message(0));
endif;
$content->
	add_table_data_settings()->
		ins_colgroup_data_settings()->
		push()->
		addTHEAD()->
			c2_titleline(gettext('Reporting Level'))->
		pop()->
		addTBODY(['class' => 'donothighlight'])->
			c2($cops_element,$statusgraphmemorylevel);
$content->
	add_table_data_settings()->
		push()->
		addTHEAD()->
			ins_titleline(gettext('Memory Usage') . sprintf(' (%s)',sprintf(gettext('Graph updates every %d seconds.'),$refresh)))->
		pop()->
		addTBODY(['class' => 'donothighlight'])->
			addTR()->
				addTD()->
					addDIV(['class' => 'rrdgraphs'])->
						insIMG(['class' => 'rrdgraphs','src' => sprintf('/images/rrd/rrd-%s_daily.png?rand=%s',$statusgraphmemorylevel,$now),'alt' => gettext('RRDGraphs Daily Memory Graph')])->
						insIMG(['class' => 'rrdgraphs','src' => sprintf('/images/rrd/rrd-%s_weekly.png?rand=%s',$statusgraphmemorylevel,$now),'alt' => gettext('RRDGraphs Weekly Memory Graph')])->
						insIMG(['class' => 'rrdgraphs','src' => sprintf('/images/rrd/rrd-%s_monthly.png?rand=%s',$statusgraphmemorylevel,$now),'alt' => gettext('RRDGraphs Monthly Memory Graph')])->
						insIMG(['class' => 'rrdgraphs','src' => sprintf('/images/rrd/rrd-%s_yearly.png?rand=%s',$statusgraphmemorylevel,$now),'alt' => gettext('RRDGraphs Yearly Memory Graph')]);
$content->
	add_area_remarks()->
		ins_remark('info','',sprintf(gettext('Graph updates every %d seconds.'),$refresh));
$document->
	add_area_buttons(true,true)->
		ins_button_save();
$document->
	add_js_document_ready('document.getElementById("statusgraphmemorylevel").setAttribute("onchange","submit()");');
$document->render();
