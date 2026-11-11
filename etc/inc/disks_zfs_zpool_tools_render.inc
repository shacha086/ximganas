<?php
/*
	disks_zfs_zpool_tools_render.inc

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
require_once 'config.inc';
require_once 'util.inc';

use common\arr;
use common\uuid;

function render_set_start() {
	$title = gettext('ZFS Pool Tools');
	echo '<table class="area_data_settings">',"\n";
		echo '<colgroup>',"\n";
			echo '<col class="area_data_settings_col_tag">',"\n";
			echo '<col class="area_data_settings_col_data">',"\n";
		echo '</colgroup>',"\n";
		echo '<thead>',html_titleline2($title),'</thead>',"\n";
		echo '<tbody>',"\n";
	return true;
}
function render_command_and_execute($subcommand,$a_param = [],$b_exec = true) {
	$param = implode(' ',$a_param);
	$output = sprintf('%1$s %2$s %3$s','zpool',$subcommand,$param);
	html_text2('syscommand',gettext('Command'),$output);
	echo '<tr>',"\n",'<td class="celltag">',gtext('Output'),'</td><td class="celldata"><pre class="cmdoutput">';
	if($b_exec):
		$result = zfs_zpool_cmd($subcommand,$param,true);
	else:
		$result = 7;
	endif;
	echo '</pre></td></tr>';
	return $result;
}
function render_zpool_status($param,$b_exec = true) {
	echo '<tr>',"\n",'<td class="celltag">',gtext('Pool Status'),'</td><td class="celldata"><pre class="cmdoutput">';
	if($b_exec):
		$result = zfs_zpool_cmd('status',escapeshellarg($param),true);
	else:
		$result = 7;
	endif;
	echo '</pre></td></tr>';
	return $result;
}
function render_command_result($result = 0,$render_success = true,$render_error = true) {
	switch($result):
		case 0:
			if($render_success):
				html_text2('success',gettext('Success'),gettext('Command execution was successful'));
			endif;
			break;
		case 2:
			if($render_error):
				html_text2('error',gettext('Error'),gettext('Invalid command line option'));
			endif;
			break;
		case 7: // test mode
			if($render_success):
				html_text2('success',gettext('Success'),gettext('Test mode, no command execution'));
			endif;
			break;
		case 15: // rendering problem, rendering function reported no items
			if($render_error):
				html_text2('error',gettext('Error'),gettext('Missing parameter'));
			endif;
			break;
//		case 1:
		default:
			if($render_error):
				html_text2('error',gettext('Error'),gettext('An error occured.'));
			endif;
			break;
	endswitch;
}
function render_set_end() {
/*
 * End of setting table
 */
		echo '</tbody>',"\n";
	echo '</table>',"\n";
	return true;
}
function render_submit($pageindex = 1,$activity = '',$option = '',$a_pool = [],$a_flag = []) {
/*
 * Displays the submit area of the page
 */
	echo '<div id="submit">',"\n";
	if($pageindex === 1):
		echo '<input name="reload" type="submit" value="',gtext('Return to Command Page'),'" class="formbtn"/>',"\n";
	else:
		echo '<input type="submit" name="submit" value="',gtext('Next'),'" class="formbtn"/>',"\n";
	endif;
	echo '<input type="hidden" name="pageindex" value="',$pageindex,'"/>',"\n";
	if(!empty($activity)):
		echo '<input type="hidden" name="activity[]" value="',$activity,'"/>',"\n";
	endif;
	if(!empty($option)):
		echo '<input type="hidden" name="option[]" value="',$option,'"/>',"\n";
	endif;
	if(is_array($a_flag) && (count($a_flag) > 0)):
		foreach($a_flag as $r_flag):
			echo '<input type="hidden" name="flag[]" value="',$r_flag,'"/>',"\n";
		endforeach;
	endif;
	if(is_array($a_pool) && (count($a_pool) > 0)):
		foreach($a_pool as $r_pool):
			echo '<input type="hidden" name="pool[]" value="',$r_pool,'"/>',"\n";
		endforeach;
	endif;
	echo '</div>',"\n";
	return true;
}
function render_activity_view($activity_longname) {
/*
 * Displays the activity
 * Embedded in the body of a table with 2 columns
 */
	html_text2('sysactivity',gettext('Activity'),$activity_longname);
	return true;
}
function render_option_view($option_name) {
/*
 * Displays the name of the selected option
 * Embedded in the body of a table with 2 columns
 */
	html_text2('sysoption',gettext('Option'),$option_name);
	return true;
}
function render_pool_edit($a_items = [],$format = '0N',$a_selected_items = []) {
/*
 * Displays a list of radio or checkbox input fields for available pools
 * Embedded in the body of a table with 2 columns
 */
	$b_ret = (0 < count($a_items));
	$attribute = [
		'name' => 'name="pool[]"',
		'id' => null,
		'value' => null,
		'checked' => '',
		'required' => ''
	];
	switch($format):
		case '1':
			$attribute['required'] = 'required="required"';
//			no break;
		case '0':
			$attribute['type'] = 'type="radio"';
			$is_checkbox = false;
			break;
		case '1N':
//			no break;
		case '0N':
			$attribute['type'] = 'type="checkbox"';
			$is_checkbox = true;
			break;
	endswitch;
	echo '<tr>';
		echo '<td class="celltag">',gtext('Pools'),'</td>'; // left column
		echo '<td class="celldata">'; // right column start
			echo '<table class="area_data_selection">',"\n";
				echo '<colgroup>',"\n";
					echo '<col style="width:5%">',"\n"; // Checkbox or Radio
					echo '<col style="width:35%">',"\n"; // Name
					echo '<col style="width:30%">',"\n"; // Version
					echo '<col style="width:35%">',"\n"; // Pool Status Name
				echo '</colgroup>',"\n";
				echo '<thead>',"\n";
					echo '<tr>',"\n";
						if($is_checkbox):
							echo '<th class="lhelc"><input type="checkbox" id="togglepool" name="togglepool" title="',gtext('Invert Selection'),'"/></th>',"\n";
						else:
							echo '<th class="lhelc"></th>',"\n";
						endif;
						echo '<th class="lhell">',gtext('Pool'),'</th>',"\n";
						echo '<th class="lhell">',gtext('Pool Version'),'</th>',"\n";
						echo '<th class="lhebl">',gtext('Pool State'),'</th>',"\n";
					echo '</tr>',"\n";
				echo '</thead>',"\n";
				echo '<tbody>',"\n";
					if($b_ret):
						foreach($a_items as $r_item):
							$id = sprintf($is_checkbox ? 'checkbox_%s' : 'radio_%s',uuid::create_v4());
							$attribute['id'] = sprintf('id="%s"',$id);
							$value = $r_item['pool.name'];
							$attribute['value'] = sprintf('value="%s"',$value);
							if(count($a_selected_items) > 0):
								if(array_search($value,$a_selected_items) === false):
									$attribute['checked'] =  '';
								else:
									$attribute['checked'] =  'checked="checked"';
								endif;
							endif;
							echo '<tr>';
								echo '<td class="lcelc">';
									echo '<input ',implode(' ',$attribute),'/>';
								echo '</td>',"\n";
								echo '<td class="lcell">';
									echo sprintf('<label for="%s">',$id),htmlspecialchars($value,ENT_HTML5),'</label>';
								echo '</td>',"\n";
								echo '<td class="lcell">',htmlspecialchars($r_item['pool.version'],ENT_HTML5),'</td>',"\n";
								echo '<td class="lcebl">',htmlspecialchars($r_item['pool.state_name'],ENT_HTML5),'</td>',"\n";
							echo '</tr>';
						endforeach;
					else:
						$value = gettext('No suitable pools found.');
						echo '<tr>';
							echo '<td class="lcelc"></td>',"\n";
							echo '<td class="lcell">',htmlspecialchars($value,ENT_HTML5),'</td>',"\n";
							echo '<td class="lcell"></td>',"\n";
							echo '<td class="lcebl"></td>',"\n";
						echo '</tr>';
					endif;
				echo '</tbody>',"\n";
			echo '</table>',"\n";
		echo '</td>',"\n"; // right hand column end
	echo '</tr>',"\n";
	return $b_ret;
}
function render_pool_view($selected_items = []) { // $item can be an array or a string
/*
 * Displays the items in a comma seperated list
 * Embedded in the body of a table with 2 columns
 */
	$b_ret = true;
	if(is_array($selected_items)):
		if(count($selected_items) > 1):
			$last = array_pop($selected_items);
			$content = sprintf('%s %s %s',implode(', ',$selected_items),gettext('and'),$last);
		else:
			$content = implode(' ',$selected_items);
		endif;
	elseif(is_string($selected_items)):
		$content = $selected_items;
	else:
		$content = '';
	endif;
	if(preg_match('/\S/',$content)):
		$output = $content;
	else:
		$b_ret = false;
		$output = gettext('No pools have been selected');
	endif;
	html_text2('syspool',gettext('Pools'),$output);
	return $b_ret;
}
function render_pooldev_edit($a_items = [],$format = '1',$a_selected_items = [],bool $check_not_present = false) {
/*
 * Displays a list of radio or checkbox input fields for pool devices
 * Embedded in the body of a table with 2 columns
 */
	$b_ret = (0 < count($a_items));
	$attribute = [
		'name' => 'name="pooldev[]"',
		'id' => null,
		'value' => null,
		'checked' => '',
		'required' => ''
	];
	switch($format):
		case '1':
			$attribute['required'] = 'required="required"';
//			no break;
		case '0':
			$attribute['type'] = 'type="radio"';
			$is_checkbox = false;
			break;
		case '1N':
//			no break;
		case '0N':
			$attribute['type'] = 'type="checkbox"';
			$is_checkbox = true;
			break;
	endswitch;
	echo '<tr>';
		echo '<td class="celltag">';  // left hand column start
		echo gtext('Pool Devices');
		echo '</td>'; // left hand column end
		echo '<td class="celldata">'; // right hand column start
			echo '<table class="area_data_selection">',"\n";
				echo '<colgroup>',"\n";
					echo '<col style="width:5%">',"\n"; // Checkbox or Radio
					echo '<col style="width:45%">',"\n"; // Device Name
					echo '<col style="width:10%">',"\n"; // Device Type
					echo '<col style="width:20%">',"\n"; // Virtual Device
					echo '<col style="width:20%">',"\n"; // Pool
				echo '</colgroup>',"\n";
				echo '<thead>',"\n";
					echo '<tr>',"\n";
						if($is_checkbox):
							echo '<th class="lhelc"><input type="checkbox" id="togglepooldev" name="togglepooldev" title="',gtext('Invert Selection'),'"/></th>',"\n";
						else:
							echo '<th class="lhelc"></th>',"\n";
						endif;
						echo '<th class="lhell">',gtext('Device'),'</th>',"\n";
						echo '<th class="lhell">',gtext('Type'),'</th>',"\n";
						echo '<th class="lhell">',gtext('Virtual Device'),'</th>',"\n";
						echo '<th class="lhebl">',gtext('Pool'),'</th>',"\n";
					echo '</tr>',"\n";
				echo '</thead>',"\n";
				echo '<tbody>',"\n";
					if($b_ret):
						foreach($a_items as $r_item):
							$id = sprintf($is_checkbox ? 'checkbox_%s' : 'radio_%s',uuid::create_v4());
							$attribute['id'] = sprintf('id="%s"',$id);
							if($check_not_present):
								$value = $r_item['device.guid'];
							else:
								$value = $r_item['device.path'];
							endif;
							if($check_not_present && $r_item['device.not_present']):
								$display_value = sprintf('%s (%s) %s',$r_item['device.guid'],$r_item['device.path'],gettext('missing'));
							else:
								$display_value = $r_item['device.path'];
							endif;
							$attribute['value'] = sprintf('value="%s"',$value);
							if(count($a_selected_items) > 0):
								if(array_search($value,$a_selected_items) === false):
									$attribute['checked'] =  '';
								else:
									$attribute['checked'] =  'checked="checked"';
								endif;
							endif;
							$vdevice = '';
							if(preg_match('/\S/',$r_item['vdev.type'])):
								$vdevice = $r_item['vdev.type'];
								if(preg_match('/\S/',$r_item['vdev.id'])):
									$vdevice .= '-' . $r_item['vdev.id'];
								endif;
							endif;
							echo '<tr>';
								echo '<td class="lcelc">';
									echo '<input ',implode(' ',$attribute),'/>';
								echo '</td>',"\n";
								echo '<td class="lcell">';
									echo sprintf('<label for="%s">',$id),htmlspecialchars($display_value,ENT_HTML5),'</label>';
								echo '</td>',"\n";
								echo '<td class="lcell">',htmlspecialchars($r_item['device.type'],ENT_HTML5),'</td>',"\n";
								echo '<td class="lcell">',htmlspecialchars($vdevice,ENT_HTML5),'</td>',"\n";
								echo '<td class="lcebl">',htmlspecialchars($r_item['pool.name'],ENT_HTML5),'</td>',"\n";
							echo '</tr>';
						endforeach;
					else:
						$value = gettext('No suitable devices found.');
						echo '<tr>';
							echo '<td class="lcelc"></td>',"\n";
							echo '<td class="lcell">',htmlspecialchars($value,ENT_HTML5),'</td>',"\n";
							echo '<td class="lcell"></td>',"\n";
							echo '<td class="lcell"></td>',"\n";
							echo '<td class="lcebl"></td>',"\n";
						echo '</tr>';
					endif;
				echo '</tbody>',"\n";
			echo '</table>',"\n";
		echo '</td>',"\n"; // right hand column end
	echo '</tr>',"\n";
	return $b_ret;
}
function clc_device_path_from_guid($guid,$data_devices) {
	if(array_key_exists($guid,$data_devices)):
		if($data_devices[$guid]['device.not_present']):
			$retval = sprintf('%s (%s) %s',$guid,$data_devices[$guid]['device.path'],gettext('missing'));
		else:
			$retval = $data_devices[$guid]['device.path'];
		endif;
	else:
		$retval = sprintf('%s %s',$guid,gettext('unknown'));
	endif;
	return $retval;
}
function render_pooldev_view($items = [],$data_devices = []) { // $items can be an array or a string
/*
 *	Displays the items in a comma seperated list
 *	Embedded in the body of a table with 2 columns
 */
	$b_ret = true;
	$grid = [];
	if(is_array($items)):
		foreach($items as $item):
			$grid[] = clc_device_path_from_guid($item,$data_devices);
		endforeach;
	elseif(is_string($items)):
		$grid[] = clc_device_path_from_guid($items,$data_devices);
	else:
		$grid[] = '';
	endif;
	if(count($grid) > 1):
		$last = array_pop($grid);
		$content = sprintf('%s %s %s',implode(', ',$grid),gettext('and'),$last);
	else:
		$content = implode(' ',$grid);
	endif;
	if(preg_match('/\S/',$content)):
		$output = $content;
	else:
		$b_ret = false;
		$output = gettext('No pool devices have been selected');
	endif;
	html_text2('syspooldev',gettext('Pool Devices'),$output);
	return $b_ret;
}
function render_newvdev_edit($a_items = [],$format = '0N',$a_selected_items = []) {
/*
 * Displays a list of radio checkbox input fields for available virtual devices
 * Embedded in the body of a table with 2 columns
 */
	$b_ret = (0 < count($a_items));
	$attribute = [
		'name' => 'name="newvdev[]"',
		'id' => null,
		'value' => null,
		'checked' => '',
		'required' => ''
	];
	switch($format):
		case '1':
			$attribute['required'] = 'required="required"';
//			no break;
		case '0':
			$attribute['type'] = 'type="radio"';
			$is_checkbox = false;
			break;
		case '1N':
//			no break;
		case '0N':
			$attribute['type'] = 'type="checkbox"';
			$is_checkbox = true;
			break;
	endswitch;
	echo '<tr>';
		echo '<td class="celltag">';  // left hand column start
		echo gtext('Virtual Devices');
		echo '</td>'; // left hand column end
		echo '<td class="celldata">'; // right hand column start
			echo '<table class="area_data_selection">',"\n";
				echo '<colgroup>',"\n";
					echo '<col style="width:5%">',"\n"; // Checkbox or Radio
					echo '<col style="width:95%">',"\n"; // Name
				echo '</colgroup>',"\n";
				echo '<thead>',"\n";
					echo '<tr>',"\n";
						if($is_checkbox):
							echo '<th class="lhelc"><input type="checkbox" id="togglenewvdev" name="togglenewvdev" title="',gtext('Invert Selection'),'"/></th>',"\n";
						else:
							echo '<th class="lhelc"></th>',"\n";
						endif;
						echo '<th class="lhebl">',gtext('Virtual Device'),'</th>',"\n";
					echo '</tr>',"\n";
				echo '</thead>',"\n";
				echo '<tbody>',"\n";
					if($b_ret):
						foreach($a_items as $r_item):
							$id = sprintf($is_checkbox ? 'checkbox_%s' : 'radio_%s',uuid::create_v4());
							$attribute['id'] = sprintf('id="%s"',$id);
							$value = $r_item['name'];
							$attribute['value'] = sprintf('value="%s"',$value);
							if(count($a_selected_items) > 0):
								if(array_search($value,$a_selected_items) === false):
									$attribute['checked'] = '';
								else:
									$attribute['checked'] = 'checked="checked"';
								endif;
							endif;
							echo '<tr>';
								echo '<td class="lcelc">';
									echo '<input ',implode(' ',$attribute),'/>';
								echo '</td>',"\n";
								echo '<td class="lcebl">';
									echo sprintf('<label for="%s">',$id),htmlspecialchars($value,ENT_HTML5),'</label>';
								echo '</td>',"\n";
							echo '</tr>';
						endforeach;
					else:
						$value = gettext('No suitable virtual devices found.');
						echo '<tr>';
							echo '<td class="lcelc"></td>',"\n";
							echo '<td class="lcebl">',htmlspecialchars($value,ENT_HTML5),'</td>',"\n";
						echo '</tr>';
					endif;
				echo '</tbody>',"\n";
			echo '</table>',"\n";
		echo '</td>',"\n"; // right hand column end
	echo '</tr>',"\n";
	return $b_ret;
}
function render_newvdev_view($items = []) { // $items can be an array or a string
/*
 * Displays the items in a comma seperated list
 * Embedded in the body of a table with 2 columns
 */
	$b_ret = true;
	if(is_array($items)):
		if(count($items) > 1):
			$last = array_pop($items);
			$content = sprintf('%s %s %s',implode(', ',$items),gettext('and'),$last);
		else:
			$content = implode(' ',$items);
		endif;
	elseif(is_string($items)):
		$content = $items;
	else:
		$content = '';
	endif;
	if(preg_match('/\S/',$content)):
		$output = $content;
	else:
		$b_ret = false;
		$output = gettext('No virtual devices have been selected');
	endif;
	html_text2('sysvdev',gettext('Virtual Devices'),$output);
	return $b_ret;
}
function render_newdev_edit($a_items = [],$format = '1',$a_selected_items = []) {
/*
 * Displays a list of radio or checkbox input fields for available devices
 * Embedded in the body of a table with 2 columns
 */
	$use_si = is_sidisksizevalues();
	$b_ret = (0 < count($a_items));
	$attribute = [
		'name' => 'name="newdev[]"',
		'id' => null,
		'value' => null,
		'checked' => '',
		'required' => ''
	];
	switch($format):
		case '1':
			$attribute['required'] = 'required="required"';
//			no break;
		case '0':
			$attribute['type'] = 'type="radio"';
			$is_checkbox = false;
			break;
		case '1N':
//			no break;
		case '0N':
			$attribute['type'] = 'type="checkbox"';
			$is_checkbox = true;
			break;
	endswitch;
	echo '<tr>';
		echo '<td class="celltag">';  // left hand column start
		echo gtext('Devices');
		echo '</td>'; // left hand column end
		echo '<td class="celldata">'; // right hand column start
			echo '<table class="area_data_selection">',"\n";
				echo '<colgroup>',"\n";
					echo '<col style="width:5%">',"\n"; // Checkbox or Radio
					echo '<col style="width:45%">',"\n"; // Device Name
					echo '<col style="width:50%">',"\n"; // Mediasize
				echo '</colgroup>',"\n";
				echo '<thead>',"\n";
					echo '<tr>',"\n";
						if($is_checkbox):
							echo '<th class="lhelc"><input type="checkbox" id="togglenewdev" name="togglenewdev" title="',gtext('Invert Selection'),'"/></th>',"\n";
						else:
							echo '<th class="lhelc"></th>',"\n";
						endif;
						echo '<th class="lhell">',gtext('Device'),'</th>',"\n";
						echo '<th class="lhebl">',gtext('Media Size'),'</th>',"\n";
					echo '</tr>',"\n";
				echo '</thead>',"\n";
				echo '<tbody>',"\n";
					if($b_ret):
						foreach($a_items as $r_item):
							$id = sprintf($is_checkbox ? 'checkbox_%s' : 'radio_%s',uuid::create_v4());
							$attribute['id'] = sprintf('id="%s"',$id);
							$value = $r_item['name'];
							$mediasize = $r_item['mediasize'] ?? 0;
							$attribute['value'] = sprintf('value="%s"',$value);
							if(count($a_selected_items) > 0):
								if(array_search($value,$a_selected_items) === false):
									$attribute['checked'] = '';
								else:
									$attribute['checked'] = 'checked="checked"';
								endif;
							endif;
							echo '<tr>';
								echo '<td class="lcelc">';
									echo '<input ',implode(' ',$attribute),'/>';
								echo '</td>',"\n";
								echo '<td class="lcell">';
									echo sprintf('<label for="%s">',$id),htmlspecialchars($value,ENT_HTML5),'</label>';
								echo '</td>',"\n";
								echo '<td class="lcebl">',"\n";
									echo htmlspecialchars(format_bytes($mediasize,2,false,$use_si),ENT_HTML5);
								echo '</td>',"\n";
							echo '</tr>';
						endforeach;
					else:
						$value = gettext('No suitable devices found.');
						echo '<tr>';
							echo '<td class="lcelc"></td>',"\n";
							echo '<td class="lcebl">',htmlspecialchars($value,ENT_HTML5),'</td>',"\n";
						echo '</tr>';
					endif;
				echo '</tbody>',"\n";
			echo '</table>',"\n";
		echo '</td>',"\n"; // right hand column end
	echo '</tr>',"\n";
	return $b_ret;
}
function render_newdev_view($items = []) { // $items can be an array or a string
/*
 * Displays the items in a comma seperated list
 * Embedded in the body of a table with 2 columns
 */
	$b_ret = true;
	if(is_array($items)):
		if(count($items) > 1):
			$last = array_pop($items);
			$content = sprintf('%s %s %s',implode(', ',$items),gettext('and'),$last);
		else:
			$content = implode(' ',$items);
		endif;
	elseif(is_string($items)):
		$content = $items;
	else:
		$content = '';
	endif;
	if(preg_match('/\S/',$content)):
		$output = $content;
	else:
		$b_ret = false;
		$output = gettext('No devices have been selected');
	endif;
	html_text2('sysvdevice',gettext('Devices'),$output);
	return $b_ret;
}
function render_selector_radio($title,$a_option,$r_option_selected = '') {
/*
 * Displays a list of radio input fields for available options
 * Embedded in the body of a table with 2 columns
 * fields:
 *	name - text - the name of the radio button
 *	value - text - the return value of the radio button
 *	show - - bool - the radio button won't be shown if set to false
 *	longtext - text - Explanation of the option
 *
 * An option gets preselected when it matches $r_option_selected
 */
	$b_check_default = arr::search_ex($r_option_selected,$a_option,'value') === false;
	if(count($a_option) > 0):
		echo '<tr>';
			echo '<td class="celltag">';  // left hand column start
				echo htmlspecialchars($title,ENT_HTML5);
			echo '</td>'; // left hand column end
			echo '<td class="celldata">'; // right hand column start
				echo '<table class="area_data_selection">',"\n";
					echo '<colgroup>',"\n";
						echo '<col style="width:5%">',"\n"; // Radio
						echo '<col style="width:95%">',"\n"; // Name
					echo '</colgroup>',"\n";
					echo '<thead>',"\n";
//						html_titleline2($gt_title,2);
						echo '<tr>',"\n";
							echo '<th class="lhelc"></th>',"\n";
							echo '<th class="lhebl">',htmlspecialchars($title,ENT_HTML5),'</th>',"\n";
						echo '</tr>',"\n";
					echo '</thead>',"\n";
					echo '<tbody>',"\n";
						foreach($a_option as $r_option):
							if($r_option['show']):
								$id = sprintf('radio_%s',uuid::create_v4());
								$attribute = [
									'name' => sprintf(' name="%s[]"',$r_option['name']),
									'value' => sprintf(' value="%s"',$r_option['value']),
									'type' => ' type="radio"',
									'id' => sprintf(' id="%s"',$id),
									'checked' => '',
									'longname' => $r_option['longname']
								];
								if($b_check_default):
									if($r_option['default']):
										$attribute['checked'] = ' checked="checked"';
									endif;
								else:
									if($r_option_selected === $r_option['value']):
										$attribute['checked'] = ' checked="checked"';
									endif;
								endif;
								echo '<tr>';
									echo '<td class="lcelc">';
										echo '<input',$attribute['type'],$attribute['name'],$attribute['id'],$attribute['value'],$attribute['checked'],'/>';
									echo '</td>',"\n";
									echo '<td class="lcebl">','<label for="',$id,'">',htmlspecialchars($attribute['longname'],ENT_HTML5),'</label>','</td>',"\n";
								echo '</tr>';
							endif;
						endforeach;
					echo '</tbody>',"\n";
				echo '</table>',"\n";
			echo '</td>',"\n"; // right hand column end
		echo '</tr>',"\n";
	endif;
	return true;
}
class co_zpool_flag_base {
	public $a_pool_keys = [];
	public function __construct() {
		$this->init_pool_keys();
		return true;
	}
	public function init_pool_keys() {
		$this->a_pool_keys = [];
		$this->load_pool_keys();
	}
	public function load_pool_keys() {
		$this->a_pool_keys['expand'] = ['name' => 'flag','value' => 'expand','show' => true,'default' => false,'longname' => gettext('Expand device to use all available space.')];
		$this->a_pool_keys['force'] = ['name' => 'flag','value' => 'force','show' => true,'default' => false,'longname' => gettext('Force Operation')];
		$this->a_pool_keys['gptlabel'] = ['name' => 'flag','value' => 'gptlabel','show' => true,'default' => false,'longname' => gettext('Include GPT labels into scan/import')];
		$this->a_pool_keys['gptid'] = ['name' => 'flag','value' => 'gptid','show' => true,'default' => false,'longname' => gettext('Include GPT ID into scan/import')];
		$this->a_pool_keys['ufslabel'] = ['name' => 'flag','value' => 'ufslabel','show' => true,'default' => false,'longname' => gettext('Include UFS labels into scan/import')];
		$this->a_pool_keys['ufsid'] = ['name' => 'flag','value' => 'ufsid','show' => true,'default' => false,'longname' => gettext('Include UFS ID into scan/import')];
		$this->a_pool_keys['diskid'] = ['name' => 'flag','value' => 'diskid','show' => true,'default' => false,'longname' => gettext('Include Disk ID into scan/import')];
		$this->a_pool_keys['devlabel'] = ['name' => 'flag','value' => 'devlabel','show' => true,'default' => false,'longname' => gettext('Include /dev into scan/import')];
		$this->a_pool_keys['import.readonly'] = ['name' => 'flag','value' => 'import.readonly','show' => true,'default' => false,'longname' => gettext('Import pool in read-only mode')];
		$this->a_pool_keys['import.autoexpand'] = ['name' => 'flag','value' => 'import.autoexpand','show' => true,'default' => false,'longname' => gettext('Resize pool to the maximum possible size')];
		$this->a_pool_keys['destroyed.pools'] = ['name' => 'flag','value' => 'destroyed.pools','show' => true,'default' => false,'longname' => gettext('Scan/Import destroyed pools only')];
		$this->a_pool_keys['test'] = ['name' => 'flag','value' => 'test','show' => true,'default' => false,'longname' => gettext('Test Mode')];
		$this->a_pool_keys['resilver.wait'] = ['name' => 'flag','value' => 'resilver.wait','show' => true,'default' => false,'longname' => gettext('Wait until resilvering has finished before returning.')];
		$this->a_pool_keys['resilver.sequential'] = ['name' => 'flag','value' => 'resilver.sequential','show' => true,'default' => false,'longname' => gettext('Reconstruct the new device sequentially to restore redundancy as quickly as possible.')];
		return true;
	}
}
class co_zpool_flags extends co_zpool_flag_base {
	public $a_available_keys = [];
	public $a_selected_keys = [];
	public $c_tag_header;
	public $c_val_header_name;
	public $c_val_none;
	public function __construct($a_available_keys = [],$a_selected_keys = []) {
		parent::__construct();
		$this->c_tag_header = gettext('Parameter');
		$this->c_val_header_name = gettext('Parameter');
		$this->c_val_none = gettext('None');
		$this->init_available_keys($a_available_keys);
		$this->init_selected_keys($a_selected_keys);
	}
	public function init_available_keys($a_keynames = []) {
/*
 *	Resets the list of available keynames and loads a new list
 */
		$this->a_available_keys = [];
		$this->load_available_keys($a_keynames);
		return true;
	}
	public function load_available_keys($a_keynames = []) {
/*
 *	loads an array of keynames into the list of available keynames. The provided keynames must be valid pool keys
 */
		$a_temp = array_combine($a_keynames,$a_keynames); // create keys
		$a_intersect = array_intersect_key($a_temp,$this->a_pool_keys); // calculate matching keynames
		$this->a_available_keys = $this->a_available_keys + $a_intersect; // add keys to available optons
		return true;
	}
	public function show_available_keys($a_keynames = []) {
/*
 *	render the given keynames
 */
		foreach($a_keynames as $r_keyname):
			if(key_exists($r_keyname,$this->a_pool_keys)):
				$this->a_pool_keys[$r_keyname]['show'] = true;
			endif;
		endforeach;
		return true;
	}
	public function hide_available_keys($a_keynames = []) {
/*
 *	do not render the given keynames
 */
		foreach($a_keynames as $r_keyname):
			if(key_exists($r_keyname,$this->a_pool_keys)):
				$this->a_pool_keys[$r_keyname]['show'] = false;
			endif;
		endforeach;
		return true;
	}
	public function set_default_available_keys($a_keynames = []) {
/*
 *	checks the given keynames by default if no options have been selected before
 */
		foreach($a_keynames as $r_keyname):
			if(key_exists($r_keyname,$this->a_pool_keys)):
				$this->a_pool_keys[$r_keyname]['dafault'] = true;
			endif;
		endforeach;
		return true;
	}
	public function clr_default_available_keys($a_keynames = []) {
/*
 *	unchecks the given keynames by default if no options have been selected before
 */
		foreach($a_keynames as $r_keyname):
			if(key_exists($r_keyname,$this->a_pool_keys)):
				$this->a_pool_keys[$r_keyname]['default'] = false;
			endif;
		endforeach;
		return true;
	}
	public function render_available_keys() {
/*
 *	Displays a list of checkbox input fields for available keys
 *	Embedded in the body of a table with 2 columns
 *
 *	A key is checked by default when it is in the list of selected keys
 *	Default option of keys are used when no key has been previously selected
 */
		if(count($this->a_available_keys) === 0):
			return true; // nothing to render
		endif;
		$b_use_default = (0 === count(array_intersect_key($this->a_available_keys,$this->a_selected_keys)));
		$attribute = [
			'name' => '',
			'id' => null,
			'value' => '',
			'type' => 'type="checkbox"',
			'checked' => ''
		];
		echo '<tr>';
			echo '<td class="celltag">';  // left hand column start
				echo htmlspecialchars($this->c_tag_header,ENT_HTML5);
			echo '</td>',"\n"; // left hand column end
			echo '<td class="celldata">',"\n"; // right hand column start
				echo '<table class="area_data_selection">',"\n";
					echo '<colgroup>',"\n";
						echo '<col style="width:5%">',"\n"; // Checkbox
						echo '<col style="width:95%">',"\n"; // Name
					echo '</colgroup>',"\n";
					echo '<thead>',"\n";
						echo '<tr>',"\n";
							echo '<th class="lhelc"></th>',"\n";
							echo '<th class="lhebl">',htmlspecialchars($this->c_val_header_name,ENT_HTML5),'</th>',"\n";
						echo '</tr>',"\n";
					echo '</thead>',"\n";
					echo '<tbody>',"\n";
						foreach($this->a_available_keys as $key):
							if($this->a_pool_keys[$key]['show']):
								$id = sprintf('checkbox_%s',uuid::create_v4());
								$attribute['id'] = sprintf('id="%s"',$id);
								$value = $this->a_pool_keys[$key]['longname'];
								$attribute['name'] = sprintf('name="%s[]"',$this->a_pool_keys[$key]['name']);
								$attribute['value'] = sprintf('value="%s"',$this->a_pool_keys[$key]['value']);
								$attribute['checked'] = '';
								if($b_use_default):
									if($this->a_pool_keys[$key]['default']):
										$attribute['checked'] = 'checked="checked"';
									endif;
								else:
									if(isset($this->a_selected_keys[$key])):
										$attribute['checked'] = 'checked="checked"';
									endif;
								endif;
								echo '<tr>',"\n";
									echo '<td class="lcelc">';
										echo '<input ',implode(' ',$attribute),'/>';
									echo '</td>',"\n";
									echo '<td class="lcebl">';
										echo sprintf('<label for="%s">',$id),htmlspecialchars($value,ENT_HTML5),'</label>';
									echo '</td>',"\n";
								echo '</tr>',"\n";
							endif;
						endforeach;
					echo '</tbody>',"\n";
				echo '</table>',"\n";
			echo '</td>',"\n"; // right hand column end
		echo '</tr>',"\n";
		return true;
	}
	public function init_selected_keys($a_keynames = []) {
/*
 *	Resets the list of selected keynames and loads a new list
 */
		$this->a_selected_keys = [];
		$this->load_selected_keys($a_keynames);
		return true;
	}
	public function load_selected_keys($a_keynames = []) {
/*
 *	loads an array of keynames into the list of selected options
 *	the provided keynames must be valid available keys
 */
		foreach($a_keynames as $r_keyname):
			if(key_exists($r_keyname,$this->a_available_keys)):
				$this->a_selected_keys[$r_keyname] = $r_keyname;
			endif;
		endforeach;
		return true;
	}
	public function render_selected_keys() {
/*
 *	Displays the long name of each selected flag
 *	Embedded in the body of a table with 2 columns
 */
		$a_output = [];
		if(empty($this->a_selected_keys) || empty($this->a_available_keys)):
			$a_output[] = $this->c_val_none;
		else:
			foreach($this->a_selected_keys as $r_selected_option):
				if($this->a_pool_keys[$r_selected_option]['show']):
					$a_output[] = $this->a_pool_keys[$r_selected_option]['longname'];
				endif;
			endforeach;
		endif;
		if(count($a_output) > 1):
			$last = array_pop($a_output);
			$output = sprintf('%s %s %s',implode(', ',$a_output),gettext('and'),$last);
		else:
			$output = implode(', ',$a_output);
		endif;
		html_text2('sysflag',$this->c_tag_header,$output);
		return true;
	}
}
