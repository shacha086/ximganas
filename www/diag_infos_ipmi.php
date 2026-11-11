<?php
/*
	diag_infos_ipmi.php

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

function get_ipmi_sensor() {
	$a_sensor = [];
	$a_output = [];
	mwexec2('ipmitool sensor',$a_output);
	foreach($a_output as $r_output):
		$r_sensor = explode('|',$r_output);
		$c_sensor = count($r_sensor);
		for($i = 0;$i < $c_sensor;$i++):
			$r_sensor[$i] = trim($r_sensor[$i]);
		endfor;
		$a_sensor[] = $r_sensor;
	endforeach;
	unset($a_output);
	return $a_sensor;
}
function get_ipmi_fru() {
	$a_fru = [];
	$a_output = [];
	mwexec2('ipmitool fru',$a_output);
	foreach($a_output as $r_output):
		if(strpos($r_output,':') !== false):
//			we need 2 columns only, tag and value
			$r_fru = explode(': ',$r_output,2);
			$c_fru = count($r_fru);
			for($i = 0;$i < $c_fru;$i++):
				$r_fru[$i] = trim($r_fru[$i]);
			endfor;
			$a_fru[] = $r_fru;
		endif;
	endforeach;
	unset($a_output);
	return $a_fru;
}
function diag_infos_ipmi_ajax() {
	$a_ipmi_sensor = get_ipmi_sensor();
	$body_output = '';
	foreach($a_ipmi_sensor as $r_ipmi_sensor):
		$body_output .= '<tr>' . "\n";
		$body_output .= '<td class="lcell">' . htmlspecialchars($r_ipmi_sensor[0]) . '</td>' . "\n";
		$body_output .= '<td class="lcell">' . htmlspecialchars($r_ipmi_sensor[1] . ' ' . $r_ipmi_sensor[2]) . '</td>' . "\n";
		$body_output .= '<td class="lcell">' . htmlspecialchars($r_ipmi_sensor[3]) . '</td>' . "\n";
		$body_output .= '<td class="lcelr">' . htmlspecialchars($r_ipmi_sensor[4]) . '</td>' . "\n";
		$body_output .= '<td class="lcelr">' . htmlspecialchars($r_ipmi_sensor[9]) . '</td>' . "\n";
		$body_output .= '<td class="lcelr">' . htmlspecialchars($r_ipmi_sensor[6]) . '</td>' . "\n";
		$body_output .= '<td class="lcelr">' . htmlspecialchars($r_ipmi_sensor[7]) . '</td>' . "\n";
		$body_output .= '<td class="lcelr">' . htmlspecialchars($r_ipmi_sensor[5]) . '</td>' . "\n";
		$body_output .= '<td class="lcebr">' . htmlspecialchars($r_ipmi_sensor[8]) . '</td>' . "\n";
		$body_output .= '</tr>' . "\n";
	endforeach;
	return $body_output;
}
$a_ipmi_sensor = get_ipmi_sensor();
if(is_ajax()):
	$status['area_refresh'] = diag_infos_ipmi_ajax();
	render_ajax($status);
endif;
$a_ipmi_fru = get_ipmi_fru();
$pgtitle = [gettext('Diagnostics'),gettext('Information'),gettext('IPMI Statistics')];
$document = new_page($pgtitle);
//	get areas
$body = $document->getElementById('main');
$pagecontent = $document->getElementById('pagecontent');
//	add tab navigation
$document->
	add_area_tabnav()->
		add_tabnav_upper()->
			ins_tabnav_record('diag_infos_disks.php',gettext('Disks'))->
			ins_tabnav_record('diag_infos_disks_info.php',gettext('Disks (Info)'))->
			ins_tabnav_record('diag_infos_part.php',gettext('Partitions'))->
			ins_tabnav_record('diag_infos_smart.php',gettext('S.M.A.R.T.'))->
			ins_tabnav_record('diag_infos_space.php',gettext('Space Used'))->
			ins_tabnav_record('diag_infos_swap.php',gettext('Swap'))->
			ins_tabnav_record('diag_infos_mount.php',gettext('Mounts'))->
			ins_tabnav_record('diag_infos_raid.php',gettext('Software RAID'))->
			ins_tabnav_record('diag_infos_iscsi.php',gettext('iSCSI Initiator'))->
			ins_tabnav_record('diag_infos_ad.php',gettext('MS Domain'))->
			ins_tabnav_record('diag_infos_samba.php',gettext('SMB'))->
			ins_tabnav_record('diag_infos_testparm.php',gettext('testparm'))->
			ins_tabnav_record('diag_infos_ftpd.php',gettext('FTP'))->
			ins_tabnav_record('diag_infos_rsync_client.php',gettext('RSYNC Client'))->
			ins_tabnav_record('diag_infos_netstat.php',gettext('Netstat'))->
			ins_tabnav_record('diag_infos_sockets.php',gettext('Sockets'))->
			ins_tabnav_record('diag_infos_ipmi.php',gettext('IPMI Stats'),gettext('Reload page'),true)->
			ins_tabnav_record('diag_infos_ups.php',gettext('UPS'));
$record_exists = count($a_ipmi_sensor) > 0;
$use_tablesort = count($a_ipmi_sensor) > 1;
$a_col_width = ['11%','12%','5%','12%','12%','12%','12%','12%','12%'];
$n_col_width = count($a_col_width);
$content = $pagecontent->add_area_data();
$table = $content->add_table_data_selection();
$table->ins_colgroup_with_styles('width',$a_col_width);
$thead = $table->addTHEAD();
if($record_exists):
	$tbody = $table->addTBODY(['id' => 'area_refresh']);
endif;
$tfoot = $table->addTFOOT();
$thead->ins_titleline(gettext('Sensor Information'),$n_col_width);
$tr = $thead->addTR();
$tr->
	insTH(['class' => 'lhelc','colspan' => 3],gettext('Sensor List'))->
	insTH(['class' => 'lhelc','colspan' => 2],gettext('Non-Recoverable'))->
	insTH(['class' => 'lhelc','colspan' => 2],gettext('Non-Critical'))->
	insTH(['class' => 'lhebc','colspan' => 2],gettext('Critical'));
$tr = $thead->addTR();
$tr->
	insTHwC('lhell',gettext('Sensor'))->
	insTHwC('lhell',gettext('Reading'))->
	insTHwC('lhell',gettext('Status'))->
	insTHwC('lhell',gettext('Lower'))->
	insTHwC('lhell',gettext('Upper'))->
	insTHwC('lhell',gettext('Lower'))->
	insTHwC('lhell',gettext('Upper'))->
	insTHwC('lhell',gettext('Lower'))->
	insTHwC('lhebl',gettext('Upper'));
if($record_exists):
	$tbody->import_soup(diag_infos_ipmi_ajax(),true);
//	add additional javascript code
	$js_document_ready = <<<'EOJ'
		var gui = new GUI;
		gui.recall(5000,5000,'diag_infos_ipmi.php',null,function(data) {
			if($('#area_refresh').length > 0) {
				$('#area_refresh').html(data.area_refresh);
			}
		});
	EOJ;
	$body->add_js_document_ready($js_document_ready);
else:
	$tfoot->ins_no_records_found($n_col_width,gettext('No IPMI Sensor data available.'));
endif;
$record_exists = count($a_ipmi_fru) > 0;
$use_tablesort = count($a_ipmi_fru) > 1;
$table = $content->add_table_data_settings();
$table->ins_colgroup_data_settings();
$thead = $table->addTHEAD();
if($record_exists):
	$tbody = $table->addTBODY();
endif;
$tfoot = $table->addTFOOT();
$thead->c2_titleline(gettext('FRU Information'));
if($record_exists):
	foreach($a_ipmi_fru as $r_ipmi_fru):
		$tbody->c2_textinfo(id: sprintf('fru_%s',uuid::create_v4()),title: $r_ipmi_fru[0],value: $r_ipmi_fru[1] ?? '');
	endforeach;
else:
	$tfoot->ins_no_records_found($n_col_width,gettext('No IPMI FRU data available.'));
endif;
$document->render();
