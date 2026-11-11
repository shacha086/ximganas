<?php
/*
	diag_infos_ups.php

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

use gui\document;

if($_POST):
	$upsc_enable = $_POST['raw_upsc_enable'];
else:
	$upsc_enable = false;
endif;

function tblrow($name,$value,$symbol = null,$id = null) {
	if(!$value):
		return;
	endif;
	if($symbol == '&deg;C'):
		$value = sprintf("%.1f",$value);
	endif;
	if($symbol == 'Hz'):
		$value = sprintf("%d",$value);
	endif;
	if($symbol == ' seconds' && $value > 60):
		$minutes = (int) ($value / 60);
		$seconds = $value % 60;
		if($minutes > 60):
			$hours = (int) ($minutes / 60);
			$minutes = $minutes % 60;
			$value = $hours;
			$symbol = ' hours ' . $minutes . ' minutes ' . $seconds . $symbol;
		else:
			$value = $minutes;
			$symbol = ' minutes ' . $seconds . $symbol;
		endif;
	endif;
	if($symbol == 'pre'):
		$value = '<pre class="cmdoutput">' . $value;
		$symbol = '</pre>';
	endif;
	print(<<<EOD
<tr id='{$id}'>
	<td class="celltag">{$name}</td>
	<td class="celldata">{$value}{$symbol}</td>
</tr>
EOD
	. "\n");
}
function tblrowbar($name,$value,$symbol,$red,$yellow,$green) {
	if(!$value):
		return;
	endif;
	$value = sprintf("%.1f",$value);
	$red = explode('-',$red);
	$yellow = explode('-',$yellow);
	$green = explode('-',$green);
	sort($red);
	sort($yellow);
	sort($green);

	if($value >= $red[0] && $value <= ($red[0]+9)):
		$color = 'black';
		$bgcolor = 'red';
	endif;
	if($value >= ($red[0]+10) && $value <= $red[1]):
		$color = 'white';
		$bgcolor = 'red';
	endif;
	if($value >= $yellow[0] && $value <= $yellow[1]):
		$color = 'black';
		$bgcolor = 'yellow';
	endif;
	if($value >= $green[0] && $value <= ($green[0]+9)):
		$color = 'black';
		$bgcolor = 'green';
	endif;
	if($value >= ($green[0]+10) && $value <= $green[1]):
		$color = 'white';
		$bgcolor = 'green';
	endif;
	print(<<<EOD
<tr>
	<td class="celltag">{$name}</td>
	<td class="celldata">
		<div style="width: 290px; height: 12px; border-top: thin solid gray; border-bottom: thin solid gray; border-left: thin solid gray; border-right: thin solid gray;">
			<div style="width: {$value}{$symbol}; height: 12px; background-color: {$bgcolor};">
				<div style="text-align: center; color: {$color}">{$value}{$symbol}</div>
			</div>
		</div>
	</td>
</tr>
EOD
	. "\n");
}
$pgtitle = [gtext('Diagnostics'),gtext('Information'),gtext('UPS')];
include 'fbegin.inc';
?>
<script>
//<![CDATA[
function upsc_enable_change() {
	var element = document.getElementById('raw_upsc_enable');
	if(element != null) {
		switch(element.checked) {
			case true:
				showElementById('upsc_raw_command','show');
				break;
			case false:
				showElementById('upsc_raw_command','hide');
				break;
		}
	}
}
//]]>
</script>
<?php
$document = new document();
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
			ins_tabnav_record('diag_infos_ipmi.php',gettext('IPMI Stats'))->
			ins_tabnav_record('diag_infos_ups.php',gettext('UPS'),gettext('Reload page'),true);
$document->render();
?>
<div class="area_data_top"></div>
<div id="area_data_frame">
<?php
	if(!isset($config['ups']['enable'])):
?>
		<table class="area_data_settings">
			<colgroup>
				<col class="area_data_settings_col_tag">
				<col class="area_data_settings_col_data">
			</colgroup>
			<thead>
<?php
				html_titleline2(gettext('UPS Information & Status'));
?>
			</thead>
			<tbody>
				<tr>
					<td class="celltag"><?=gtext('Information');?></td>
					<td class="celldata">
<?php
						echo '<pre class="cmdoutput">';
						echo gtext('UPS is disabled.');
						echo '</pre>';
?>
					</td>
				</tr>
			</tbody>
		</table>
<?php
	else:
?>
		<table class="area_data_settings">
			<colgroup>
				<col class="area_data_settings_col_tag">
				<col class="area_data_settings_col_data">
			</colgroup>
			<thead>
<?php
				html_titleline2(gettext('UPS Information & Status'));
?>
			</thead>
			<tbody>
<?php
				if(isset($config['ups']['ups2'])):
?>
					<tr>
						<td class="celltag"><?=gtext('Selected UPS');?></td>
						<td class="celldata">
							<form name="form2" action="diag_infos_ups.php" method="get">
								<select name="if" class="formfld" onchange="submit()">
<?php
									$curif = $config['ups']['upsname'];
									if(isset($_GET['if']) && $_GET['if']):
										$curif = $_GET['if'];
									endif;
									$ifnum = $curif;
									$ifdescrs = [$config['ups']['upsname'] => $config['ups']['upsname'],$config['ups']['ups2_upsname'] => $config['ups']['ups2_upsname']];
									foreach($ifdescrs as $ifn => $ifd):
										echo '<option value="',$ifn,'"';
										if($ifn == $curif):
											echo ' selected="selected"';
										endif;
										echo '>',htmlspecialchars($ifd),'</option>',"\n";
									endforeach;
?>
								</select>
							</form>
						</td>
					</tr>
<?php
				else:
					$ifnum = $config['ups']['upsname'];
				endif;
				$cmd = "/usr/local/bin/upsc {$ifnum}@{$config['ups']['ip']}";
				$handle = popen($cmd,'r');
				if($handle):
					$read = fread($handle,4096);
					pclose($handle);
					$lines = explode("\n",$read);
					$ups = [];
					foreach($lines as $line):
						$line = explode(':',$line);
						$ups[$line[0]] = trim($line[1] ?? '');
					endforeach;
					if(count($lines) == 1):
						tblrow(gtext('ERROR:'),'Data stale!');
					endif;
					tblrow(gtext('Manufacturer'),$ups['device.mfr'] ?? null);
					tblrow(gtext('Model'),$ups['device.model'] ?? null);
					tblrow(gtext('Type'),$ups['device.type'] ?? null);
					tblrow(gtext('Serial number'),$ups['device.serial'] ?? null);
					tblrow(gtext('Firmware version'),$ups['ups.firmware'] ?? null);
					$status = explode(' ',$ups['ups.status'] ?? null);
					$disp_status = '';
					foreach($status as $condition):
						if(strlen($disp_status)> 0):
							$disp_status .= ', ';
						endif;
						switch($condition):
							case 'WAIT':
								$disp_status .= gtext('UPS Waiting');
								break;
							case 'OFF':
								$disp_status .= gtext('UPS Off Line');
								break;
							case 'OL':
								$disp_status .= gtext('UPS On Line');
								break;
							case 'OB':
								$disp_status .= gtext('UPS On Battery');
								break;
							case 'TRIM':
								$disp_status .= gtext('SmartTrim');
								break;
							case 'BOOST':
								$disp_status .= gtext('SmartBoost');
								break;
							case 'OVER':
								$disp_status .= gtext('Overload');
								break;
							case 'LB':
								$disp_status .= gtext('Battery Low');
								break;
							case 'RB':
								$disp_status .= gtext('Replace Battery UPS');
								break;
							case 'CAL':
								$disp_status .= gtext('Calibration Battery');
								break;
							case 'CHRG':
								$disp_status .= gtext('Charging Battery');
								break;
							default:
								$disp_status .= $condition;
								break;
						endswitch;
					endforeach;
					tblrow(gtext('Status'),$disp_status);
					tblrowbar(gtext('Load'),$ups['ups.load'] ?? null,'%','100-80','79-60','59-0');
					tblrowbar(gtext('Battery level'),$ups['battery.charge'] ?? null,'%','0-29' ,'30-79','80-100');
//					status
					tblrow(gtext('Battery voltage'),$ups['battery.voltage'] ?? null,'V');
					tblrow(gtext('Input voltage'),$ups['input.voltage'] ?? null,'V');
					tblrow(gtext('Input frequency'),$ups['input.frequency'] ?? null,'Hz');
					tblrow(gtext('Output voltage'),$ups['output.voltage'] ?? null,'V');
					tblrow(gtext('Temperature'),$ups['ups.temperature'] ?? null,' &deg;C');
					tblrow(gtext('Remaining battery runtime'),$ups['battery.runtime'] ?? null,' seconds');
				endif;
?>
			</tbody>
		</table>
<?php
//		output
		if($handle):
?>
			<div class="gap"></div>
			<table class="area_data_settings">
				<colgroup>
					<col class="area_data_settings_col_tag">
					<col class="area_data_settings_col_data">
				</colgroup>
				<thead>
<?php
					html_titleline2(gettext('UPS Unit General Information'));
?>
				</thead>
				<tbody>
<?php
					tblrow(gtext('UPS status'),$ups['ups.status'] ?? null);
					tblrow(gtext('UPS alarms'),$ups['ups.alarm'] ?? null);
					tblrow(gtext('Internal UPS clock time'),$ups['ups.time'] ?? null);
					tblrow(gtext('Internal UPS clock date'),$ups['ups.date'] ?? null);
					tblrow(gtext('UPS model'),$ups['ups.model'] ?? null);
					tblrow(gtext('Manufacturer'),$ups['ups.mfr'] ?? null);
					tblrow(gtext('Manufacturing date'),$ups['ups.mfr.date'] ?? null);
					tblrow(gtext('Serial number'),$ups['ups.serial'] ?? null);
					tblrow(gtext('Vendor ID'),$ups['ups.vendorid'] ?? null);
					tblrow(gtext('Product ID'),$ups['ups.productid'] ?? null);
					tblrow(gtext('UPS firmware'),$ups['ups.firmware'] ?? null);
					tblrow(gtext('Auxiliary device firmware'),$ups['ups.firmware.aux'] ?? null);
					tblrow(gtext('UPS temperature'),$ups['ups.temperature'] ?? null,' &deg;C');
					tblrow(gtext('UPS load'),$ups['ups.load'] ?? null,'%');
					tblrow(gtext('Load when UPS switches to overload condition ("OVER")'),$ups['ups.load.high'] ?? null,'%');
					tblrow(gtext('UPS system identifier'),$ups['ups.id'] ?? null);
					tblrow(gtext('Interval to wait before restarting the load'),$ups['ups.delay.start'] ?? null,' seconds');
					tblrow(gtext('Interval to wait before rebooting the UPS)'),$ups['ups.delay.reboot'] ?? null,' seconds');
					tblrow(gtext('Interval to wait after shutdown with delay command'),$ups['ups.delay.shutdown'] ?? null,' seconds');
					tblrow(gtext('Time before the load will be started'),$ups['ups.timer.start'] ?? null,' seconds');
					tblrow(gtext('Time before the load will be rebooted'),$ups['ups.timer.reboot'] ?? null,' seconds');
					tblrow(gtext('Time before the load will be shutdown'),$ups['ups.timer.shutdown'] ?? null,' seconds');
					tblrow(gtext('Interval between self tests'),$ups['ups.test.interval'] ?? null,' seconds');
					tblrow(gtext('Results of last self test'),$ups['ups.test.result'] ?? null);
					tblrow(gtext('Language to use on front panel'),$ups['ups.display.language'] ?? null);
					tblrow(gtext('UPS external contact sensors'),$ups['ups.contacts'] ?? null);
					tblrow(gtext('Efficiency of the UPS (Ratio of the output current on the input current)'),$ups['ups.efficiency'] ?? null,'%');
					tblrow(gtext('Current value of apparent power (Volt-Amps)'),$ups['ups.power'] ?? null,'VA');
					tblrow(gtext('Nominal value of apparent power (Volt-Amps)'),$ups['ups.power.nominal'] ?? null,'VA');
					tblrow(gtext('Current value of real power (Watts)'),$ups['ups.realpower'] ?? null,'W');
					tblrow(gtext('Nominal value of real power (Watts)'),$ups['ups.realpower.nominal'] ?? null,'W');
					tblrow(gtext('Percentage of apparent power related to maximum load'),$ups['power.percent'] ?? null,'%');
					tblrow(gtext('UPS beeper status'),$ups['ups.beeper.status'] ?? null);
					tblrow(gtext('UPS type'),$ups['ups.type'] ?? null);
					tblrow(gtext('UPS watchdog status'),$ups['ups.watchdog.status'] ?? null);
					tblrow(gtext('UPS starts when mains is (re)applied'),$ups['ups.start.auto'] ?? null);
					tblrow(gtext('Allow to start UPS from battery'),$ups['ups.start.battery'] ?? null);
					tblrow(gtext('UPS coldstarts from battery'),$ups['ups.start.reboot'] ?? null);
?>
				</tbody>
			</table>
			<div class="gap"></div>
			<table class="area_data_settings">
				<colgroup>
					<col class="area_data_settings_col_tag">
					<col class="area_data_settings_col_data">
				</colgroup>
				<thead>
<?php
					html_titleline2(gettext('Incoming Line/Power Information'));
?>
				</thead>
				<tbody>
<?php
					tblrow(gtext('Input voltage'),$ups['input.voltage'] ?? null,'V');
					tblrow(gtext('Maximum incoming voltage seen'),$ups['input.voltage.maximum'] ?? null,'V');
					tblrow(gtext('Minimum incoming voltage seen'),$ups['input.voltage.minimum'] ?? null,'V');
					tblrow(gtext('Nominal input voltage'),$ups['input.voltage.nominal'] ?? null,'V');
					tblrow(gtext('Extended input voltage range'),$ups['input.voltage.extended'] ?? null);
					tblrow(gtext('Reason for last transfer to battery (* opaque)'),$ups['input.transfer.reason'] ?? null);
					tblrow(gtext('Low voltage transfer point'),$ups['input.transfer.low'] ?? null,'V');
					tblrow(gtext('High voltage transfer point'),$ups['input.transfer.high'] ?? null,'V');
					tblrow(gtext('smallest settable low voltage transfer point'),$ups['input.transfer.low.min'] ?? null,'V');
					tblrow(gtext('greatest settable low voltage transfer point'),$ups['input.transfer.low.max'] ?? null,'V');
					tblrow(gtext('smallest settable high voltage transfer point'),$ups['input.transfer.high.min'] ?? null,'V');
					tblrow(gtext('greatest settable high voltage transfer point'),$ups['input.transfer.high.max'] ?? null,'V');
					tblrow(gtext('Input power sensitivity'),$ups['input.sensitivity'] ?? null);
					tblrow(gtext('Input power quality (* opaque)'),$ups['input.quality'] ?? null);
					tblrow(gtext('Input current (A)'),$ups['input.current'] ?? null,'A');
					tblrow(gtext('Nominal input current (A)'),$ups['input.current.nominal'] ?? null,'A');
					tblrow(gtext('Input line frequency (Hz)'),$ups['input.frequency'] ?? null,'Hz');
					tblrow(gtext('Nominal input line frequency (Hz)'),$ups['input.frequency.nominal'] ?? null,'Hz');
					tblrow(gtext('Input line frequency low (Hz)'),$ups['input.frequency.low'] ?? null,'Hz');
					tblrow(gtext('Input line frequency high (Hz)'),$ups['input.frequency.high'] ?? null,'Hz');
					tblrow(gtext('Extended input frequency range'),$ups['input.frequency.extended'] ?? null);
					tblrow(gtext('Low voltage boosting transfer point'),$ups['input.transfer.boost.low'] ?? null,'V');
					tblrow(gtext('High voltage boosting transfer point'),$ups['input.transfer.boost.high'] ?? null,'V');
					tblrow(gtext('Low voltage trimming transfer point'),$ups['input.transfer.trim.low'] ?? null,'V');
					tblrow(gtext('High voltage trimming transfer point'),$ups['input.transfer.trim.high'] ?? null,'V');
?>
				</tbody>
			</table>
			<div class="gap"></div>
			<table class="area_data_settings">
				<colgroup>
					<col class="area_data_settings_col_tag">
					<col class="area_data_settings_col_data">
				</colgroup>
				<thead>
<?php
					html_titleline2(gettext('Outgoing Power/Inverter Information'));
?>
				</thead>
				<tbody>
<?php
					tblrow(gtext('Output voltage (V)'),$ups['output.voltage'] ?? null,'V');
					tblrow(gtext('Nominal output voltage (V)'),$ups['output.voltage.nominal'] ?? null,'V');
					tblrow(gtext('Output frequency (Hz)'),$ups['output.frequency'] ?? null,'Hz');
					tblrow(gtext('Nominal output frequency (Hz)'),$ups['output.frequency.nominal'] ?? null,'Hz');
					tblrow(gtext('Output current (A)'),$ups['output.current'] ?? null,'A');
					tblrow(gtext('Nominal output current (A)'),$ups['output.current.nominal'] ?? null,'A');
?>
				</tbody>
			</table>
			<div class="gap"></div>
			<table class="area_data_settings">
				<colgroup>
					<col class="area_data_settings_col_tag">
					<col class="area_data_settings_col_data">
				</colgroup>
				<thead>
<?php
					html_titleline2(gettext('Battery Details'));
?>
				</thead>
				<tbody>
<?php
					tblrow(gtext('Battery level'),$ups['battery.charge'] ?? null,'%');
					tblrow(gtext('Battery Remaining level when UPS switches to Shutdown mode (Low Battery)'),$ups['battery.charge.low'] ?? null,'%');
					tblrow(gtext('Minimum battery level for UPS restart after power-off'),$ups['battery.charge.restart'] ?? null,'%');
					tblrow(gtext('Battery level when UPS switches to "Warning" state'),$ups['battery.charge.warning'] ?? null,'%');
					tblrow(gtext('Battery voltage'),$ups['battery.voltage'] ?? null,'V');
					tblrow(gtext('Battery capacity'),$ups['battery.capacity'] ?? null,'Ah');
					tblrow(gtext('Battery current'),$ups['battery.current'] ?? null,'A');
					tblrow(gtext('Battery temperature'),$ups['battery.temperature'] ?? null,' &deg;C');
					tblrow(gtext('Nominal battery voltage'),$ups['battery.voltage.nominal'] ?? null,'V');
					tblrow(gtext('Remaining battery runtime'),$ups['battery.runtime'] ?? null,' seconds');
					tblrow(gtext('When UPS switches to Low Battery'),$ups['battery.runtime.low'] ?? null,' seconds');
					tblrow(gtext('Battery alarm threshold'),$ups['battery.alarm.threshold'] ?? null);
					tblrow(gtext('Battery change date'),$ups['battery.date'] ?? null);
					tblrow(gtext('Battery manufacturing date'),$ups['battery.mfr.date'] ?? null);
					tblrow(gtext('Number of battery packs'),$ups['battery.packs'] ?? null);
					tblrow(gtext('Number of bad battery packs'),$ups['battery.packs.bad'] ?? null);
					tblrow(gtext('Battery chemistry'),$ups['battery.type'] ?? null);
					tblrow(gtext('Prevent deep discharge of battery'),$ups['battery.protection'] ?? null);
					tblrow(gtext('Switch off when running on battery and no/low load'),$ups['battery.energysave'] ?? null);
?>
				</tbody>
			</table>
			<div class="gap"></div>
			<table class="area_data_settings">
				<colgroup>
					<col class="area_data_settings_col_tag">
					<col class="area_data_settings_col_data">
				</colgroup>
				<thead>
<?php
					html_titleline2(gettext('Ambient Conditions From External Probe Equipment'));
?>
				</thead>
				<tbody>
<?php
					tblrow(gtext('Ambient temperature (degrees C)'),$ups['ambient.temperature'] ?? null,' &deg;C');
					tblrow(gtext('Temperature alarm (enabled/disabled)'),$ups['ambient.temperature.alarm'] ?? null);
					tblrow(gtext('Temperature threshold high (degrees C)'),$ups['ambient.temperature.high'] ?? null,' &deg;C');
					tblrow(gtext('Temperature threshold low (degrees C)'),$ups['ambient.temperature.low'] ?? null,' &deg;C');
					tblrow(gtext('Maximum temperature seen (degrees C)'),$ups['ambient.temperature.maximum'] ?? null,' &deg;C');
					tblrow(gtext('Minimum temperature seen (degrees C)'),$ups['ambient.temperature.minimum'] ?? null,' &deg;C');
					tblrow(gtext('Ambient relative humidity (percent)'),$ups['ambient.humidity'] ?? null,'%');
					tblrow(gtext('Relative humidity alarm (enabled/disabled)'),$ups['ambient.humidity.alarm'] ?? null);
					tblrow(gtext('Relative humidity threshold high (percent)'),$ups['ambient.humidity.high'] ?? null,'%');
					tblrow(gtext('Relative humidity threshold high (percent)'),$ups['ambient.humidity.low'] ?? null,'%');
					tblrow(gtext('Maximum relative humidity seen (percent)'),$ups['ambient.humidity.maximum'] ?? null,'%');
					tblrow(gtext('Minimum relative humidity seen (percent)'),$ups['ambient.humidity.minimum'] ?? null,'%');
?>
				</tbody>
			</table>
			<div class="gap"></div>
			<table class="area_data_settings">
				<colgroup>
					<col class="area_data_settings_col_tag">
					<col class="area_data_settings_col_data">
				</colgroup>
				<thead>
<?php
					html_titleline2(gettext('Smart Outlet Management'));
?>
				</thead>
				<tbody>
<?php
					tblrow('[Main] Outlet system identifier',$ups['outlet.id'] ?? null);
					tblrow('[Main] Outlet description',$ups['outlet.desc'] ?? null);
					tblrow('[Main] Outlet switch control (on/off)',$ups['outlet.switch'] ?? null);
					tblrow('[Main] Outlet switch status (on/off)',$ups['outlet.status'] ?? null);
					tblrow('[Main] Outlet switch ability (yes/no)',$ups['outlet.switchable'] ?? null);
					tblrow('[Main] Remaining battery level to power off this outlet',$ups['outlet.autoswitch.charge.low'] ?? null,'%');
					tblrow('[Main] Interval to wait before shutting down this outlet',$ups['outlet.delay.shutdown'] ?? null,' seconds');
					tblrow('[Main] Interval to wait before restarting this outlet',$ups['outlet.delay.start'] ?? null,' seconds');
					tblrow('[Main] Current (A)',$ups['outlet.current'] ?? null,'A');
					tblrow('[Main] Maximum seen current (A)',$ups['outlet.current.maximum'] ?? null,'A');
					tblrow('[Main] Current value of real power (W)',$ups['outlet.realpower'] ?? null,'W');
					tblrow('[Main] Voltage (V)',$ups['outlet.voltage'] ?? null,'V');
					tblrow('[Main] Power Factor (dimensionless value between 0 and 1)',$ups['outlet.powerfactor'] ?? null);
					tblrow('[Main] Crest Factor (dimensionless, equal to or greater than 1)',$ups['outlet.crestfactor'] ?? null);
					tblrow('[Main] Apparent power (VA)',$ups['outlet.power'] ?? null,'VA');
					for($i = 1;array_key_exists('outlet.' . $i . '.id',$ups); $i++):
						$outleti = 'outlet.' .$i . '.';
						tblrow('['.$i.'] Outlet system identifier',$ups[$outleti . 'id'] ?? null);
						tblrow('['.$i.'] Outlet description',$ups[$outleti . 'desc'] ?? null);
						tblrow('['.$i.'] Outlet switch control (on/off)',$ups[$outleti . 'switch'] ?? null);
						tblrow('['.$i.'] Outlet switch status (on/off)',$ups[$outleti . 'status'] ?? null);
						tblrow('['.$i.'] Outlet switch ability (yes/no)',$ups[$outleti . 'switchable'] ?? null);
						tblrow('['.$i.'] Remaining battery level to power off this outlet',$ups[$outleti . 'autoswitch.charge.low'] ?? null,'%');
						tblrow('['.$i.'] Interval to wait before shutting down this outlet',$ups[$outleti . 'delay.shutdown'] ?? null,' seconds');
						tblrow('['.$i.'] Interval to wait before restarting this outlet',$ups[$outleti . 'delay.start'] ?? null,' seconds');
						tblrow('['.$i.'] Current (A)',$ups[$outleti . 'current'] ?? null,'A');
						tblrow('['.$i.'] Maximum seen current (A)',$ups[$outleti . 'current.maximum'] ?? null,'A');
						tblrow('['.$i.'] Current value of real power (W)',$ups[$outleti . 'realpower'] ?? null,'W');
						tblrow('['.$i.'] Voltage (V)',$ups[$outleti . 'voltage'] ?? null,'V');
						tblrow('['.$i.'] Power Factor (dimensionless value between 0 and 1)',$ups[$outleti . 'powerfactor'] ?? null);
						tblrow('['.$i.'] Crest Factor (dimensionless, equal to or greater than 1)',$ups[$outleti . 'crestfactor'] ?? null);
						tblrow('['.$i.'] Apparent power (VA)',$ups[$outleti . 'power'] ?? null,'VA');
					endfor;
?>
				</tbody>
			</table>
			<div class="gap"></div>
			<table class="area_data_settings">
				<colgroup>
					<col class="area_data_settings_col_tag">
					<col class="area_data_settings_col_data">
				</colgroup>
				<thead>
<?php
					html_titleline2(gettext('NUT Internal Driver Information'));
?>
				</thead>
				<tbody>
<?php
					tblrow(gtext('Driver used'),$ups['driver.name'] ?? null);
					tblrow(gtext('Driver version'),$ups['driver.version'] ?? null);
					tblrow(gtext('Driver version internal'),$ups['driver.version.internal'] ?? null);
					tblrow(gtext('Parameter xxx (ups.conf or cmdline -x) setting'),$ups['driver.parameter.xxx'] ?? null);
					tblrow(gtext('Flag xxx (ups.conf or cmdline -x) status'),$ups['driver.flag.xxx'] ?? null);
?>
				</tbody>
			</table>
			<div class="gap"></div>
			<table class="area_data_settings">
				<colgroup>
					<col class="area_data_settings_col_tag">
					<col class="area_data_settings_col_data">
				</colgroup>
				<thead>
<?php
					html_titleline2(gettext('Internal Server Information'));
?>
				</thead>
				<tbody>
<?php
					tblrow(gtext('Server information'),$ups['server.info'] ?? null);
					tblrow(gtext('Server version'),$ups['server.version'] ?? null);
?>
				</tbody>
			</table>
			<div class="gap"></div>
			<table class="area_data_settings">
				<colgroup>
					<col class="area_data_settings_col_tag">
					<col class="area_data_settings_col_data">
				</colgroup>
				<thead>
<?php
					html_titleline_checkbox2('raw_upsc_enable','NUT',$upsc_enable ? true : false,gettext('Show RAW UPS Info'),'upsc_enable_change()');
?>
				</thead>
				<tbody>
<?php
					tblrow(gtext('RAW info'),htmlspecialchars($read),'pre','upsc_raw_command');
?>
				</tbody>
			</table>
<?php
			unset($handle,$read,$lines,$status,$disp_status,$ups);
		endif;
		unset($cmd);
	endif;
?>
</div>
<div class="area_data_pot"></div>
<script>
//<![CDATA[
upsc_enable_change();
//]]>
</script>
<?php
include 'fend.inc';
