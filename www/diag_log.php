<?php
/*
	diag_log.php

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
require_once 'diag_log.inc';

use gui\document;

$sphere_scriptname = basename(__FILE__);
$sphere_header = 'Location: '.$sphere_scriptname;

$log = filter_input(INPUT_GET,'log',FILTER_VALIDATE_INT,['flags' => FILTER_REQUIRE_SCALAR,'options' => ['default' => null,'min_range' => 0]]);
if(is_null($log)):
	$log = filter_input(INPUT_POST,'log',FILTER_VALIDATE_INT,['flags' => FILTER_REQUIRE_SCALAR,'options' => ['default' => 0,'min_range' => 0]]);
endif;
$searchlog = filter_input(INPUT_POST,'searchlog',FILTER_VALIDATE_REGEXP,['flags' => FILTER_REQUIRE_SCALAR,'options' => ['default' => '','regexp' => '/\S/']]);
$action = filter_input(INPUT_POST,'submit',FILTER_VALIDATE_REGEXP,['flags' => FILTER_REQUIRE_SCALAR,'options' => ['default' => 'none','regexp' => '/^(clear|download|refresh|search)$/']]);
switch($action):
	case 'clear':
		log_clear($loginfo[$log]);
		header(sprintf('Location: diag_log.php?log=%s',$log));
		exit;
		break;
	case 'download':
		log_download($loginfo[$log]);
		exit;
		break;
	case 'refresh':
		header(sprintf('Location: diag_log.php?log=%s',$log));
		exit;
		break;
	case 'search':
		break;
//		case 'none':
	default:
		break;
endswitch;
$pgtitle = [gtext('Diagnostics'),gtext('Log')];
include 'fbegin.inc';
?>
<script>
//<![CDATA[
var allowspinner = true;
$(window).on("load",function() {
<?php
	//	Determine if spinner can run based on button id.
?>
	$("button").on("click",function () { allowspinner = ($(this).attr("id") !== "button_download"); });
<?php
	//	Init spinner on submit for id iform.
?>
	$("#iform").on("submit",function() { if(allowspinner) spinner(); });
<?php
	//	Init spinner on click for class spin.
?>
	$(".spin").on("click",function() { spinner(); });
<?php
	//	Load page again when log selection changes.
?>
	$("#log").on("change",function() {
		spinner();
		window.document.location.href = 'diag_log.php?log=' + document.iform.log.value;
	});
});
//]]>
</script>
<?php
$document = new document();
$document->
	add_area_tabnav()->
		add_tabnav_upper()->
			ins_tabnav_record('diag_log.php',gettext('Log'),gettext('Reload page'),true)->
			ins_tabnav_record('diag_log_settings.php',gettext('Settings'))->
			ins_tabnav_record('diag_log_settings_remote.php',gettext('Remote Syslog Server'));
$document->render();
?>
<form action="<?=$sphere_scriptname;?>" method="post" name="iform" id="iform" class="pagecontent"><div class="area_data_top"></div><div id="area_data_frame">
	<table class="area_data_selection">
		<colgroup>
			<col style="width:100%">
		</colgroup>
		<thead>
<?php
			html_titleline2(gettext('Log Filter'),1);
?>
		</thead>
		<tbody class="donothighlight"><tr><td>
			<select id="log" class="formfld" name="log">
<?php
				foreach($loginfo as $loginfo_key => $loginfo_val):
					if($loginfo_val['visible'] !== false):
						echo '<option value="',$loginfo_key,'"';
						if($loginfo_key == $log):
							echo ' selected="selected"';
						endif;
						echo '>',$loginfo_val['desc'],'</option>';
					endif;
				endforeach;
?>
			</select>
<?php
			echo html_button('clear',gettext('Clear'));
			echo html_button('download',gettext('Download'));
			echo html_button('refresh',gettext('Refresh'));
?>
			<span class="label">&nbsp;&nbsp;&nbsp;<?=gtext('Search event');?></span>
			<input type="text" class="formfld" size="30" id="searchlog" name="searchlog" value="<?=htmlspecialchars($searchlog);?>"/>
<?php
			echo html_button('search',gettext('Search'));
?>
		</td></tr></tbody>
	</table>
	<table class="area_data_selection">
<?php
		if(is_array($loginfo[$log])):
			echo '<colgroup>';
			foreach($loginfo[$log]['columns'] as $column_key => $column_val):
				echo '<col style="width:',$column_val['width'],'">';
			endforeach;
			echo '</colgroup>';
		endif;
?>
		<thead>
<?php
			$columns = 0;
			$column_header = [];
			if(is_array($loginfo[$log])):
				$columns = count($loginfo[$log]['columns']);
				html_titleline2(gettext('Log'),$columns);
				echo '<tr>',"\n";
				foreach($loginfo[$log]['columns'] as $column_key => $column_val):
					echo sprintf('<th class="%1$s" %2$s>%3$s</th>',$column_val['hdrclass'],$column_val['param'],$column_val['title']),"\n";
				endforeach;
				echo '</tr>',"\n";
			else:
				html_titleline2(gettext('Log'),1);
			endif;
?>
		</thead>
		<tbody>
<?php
			$content_array = log_get_contents($loginfo[$log]['logfile'],$loginfo[$log]['type']);
			if(!empty($content_array)):
//				regex to identify encapsulated RFC5424 compliant messages
				$regex_rfc5424 = '/^(?<version>[1-9][0-9]{0,2}) (?<timestamp>\S+) (?<hostname>\S+) (?<appname>\S+) (?<procid>\S+) (?<msgid>\S+) (?<sd>-|\[(?:\\[\]\[]|[^\[\]])*\])($| )(?<msg>.*)/';
				$test_rfc5424 = $loginfo[$log]['test.rfc5424'];
//				Create table data
				foreach($content_array as $content_record):
//					Skip invalid pattern matches
					if(preg_match($loginfo[$log]['pattern'],$content_record,$matches) !== 1):
						continue;
					endif;
//					Skip empty lines
					if((count($loginfo[$log]['columns']) == 1) && empty($matches[1])):
						continue;
					endif;
//					check if msg is rfc5424 compliant.
					if($test_rfc5424):
						$content_record_rfc5424 = $matches['msg'] ?? '';
						if(preg_match($regex_rfc5424,$content_record_rfc5424,$matches_rfc5424) === 1):
//							adjust message
							$matches['msg'] = sprintf('%s: %s',$matches_rfc5424['appname'] ?? '-',$matches_rfc5424['msg'] ?? '-');
						endif;
					endif;
					echo '<tr>',"\n";
						foreach($loginfo[$log]['columns'] as $column_key => $column_val):
							echo sprintf('<td class="%1$s" %2$s>%3$s</td>',$column_val['class'],$column_val['param'],htmlspecialchars($matches[$column_val['pmid']]));
						endforeach;
					echo '</tr>',"\n";
				endforeach;
			endif;
?>
		</tbody>
	</table>
<?php
	include 'formend.inc';
?>
</div><div class="area_data_pot"></div></form>
<?php
include 'fend.inc';
