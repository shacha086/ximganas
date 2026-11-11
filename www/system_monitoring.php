<?php
/*
	system_monitoring.php

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
use gui\document;

$sphere_scriptname = basename(__FILE__);
$sphere_notifier = 'rrdgraphs';
arr::make_branch($config,'rrdgraphs');
$upsname = !empty($config['ups']['upsname']) ? $config['ups']['upsname'] : "identifier";
$upsip = !empty($config['ups']['ip']) ? $config['ups']['ip'] : "host-ip-address";

/* Check if the directory exists, the mountpoint has at least o=rx permissions and
 * set the permission to 775 for the last directory in the path.
 */
function rrdgraphs_change_perms($dir) {
	global $input_errors;

//	remove trailing slash
	$path = rtrim($dir,'/');
	if(strlen($path) > 1):
//		check if directory exists
		if(!is_dir($path)):
			$input_errors[] = sprintf(gtext("Directory %s doesn't exist!"),$path);
		else:
//			split path to get directory names
			$path_check = explode('/',$path);
//			get path depth
			$path_elements = count($path_check);
//			get mountpoint permissions for others
			$fp = substr(sprintf('%o',fileperms("/$path_check[1]/$path_check[2]")),-1);
//			need at least read & search permission at the origin
			if($fp >= 5):
//				set to the mountpoint
				$directory = "/$path_check[1]/$path_check[2]";
//				traverse the path and set permissions to rx
				for($i = 3;$i < $path_elements - 1;$i++):
//					add next level
					$directory = $directory . "/$path_check[$i]";
//					set permissions to o=+r+x
					exec("chmod o=+r+x \"$directory\"");
				endfor;
				$path_elements = $path_elements - 1;
				$directory = $directory . "/$path_check[$path_elements]";	// add last level
//				set permissions to 775
				chmod($directory,0775);
				if(array_key_exists('who',$_POST)):
					chown($directory,$_POST['who']);
				endif;
			else:
				$input_errors[] = sprintf(gtext("RRDGraphs needs at least read & execute permissions at the mount point for directory %s! Set the Read and Execute bits for Others (Access Restrictions | Mode) for the mount point %s (in <a href='disks_mount.php'>Disks | Mount Point | Management</a> or <a href='disks_zfs_dataset.php'>Disks | ZFS | Datasets</a>) and hit Save in order to take them effect."),$path,"/{$path_check[1]}/{$path_check[2]}");
			endif;
		endif;
	endif;
}
if(isset($_POST['save']) && $_POST['save']):
	unset($input_errors);
	$pconfig = $_POST;
	if(isset($_POST['ups']) && empty($_POST['ups_at'])):
		$input_errors[] = gtext('UPS Identifier and IP address')." ".sprintf(gtext('must be in the format: %s.'),"identifier@host-ip-address");
	endif;
	if(isset($_POST['latency']) && empty($_POST['latency_host'])):
		$input_errors[] = gtext('Network Latency') . ': ' . gtext('Destination host name or IP address.') . ' ' . gtext('Host') . ' ' . gtext('must be defined!');
	endif;
	if(isset($_POST['storage_path']) && (($_POST['storage_path'] == "") || ($_POST['storage_path'] == $g['media_path']))):
		$input_errors[] = gtext("The attribute 'Data directory' is required.");
	endif;
	if(empty($input_errors)):
		$retval = 0;
		if(isset($_POST['enable'])):
			$config['rrdgraphs']['enable'] = isset($_POST['enable']);
			if(empty($_POST['storage_path'])):
				$config['rrdgraphs']['storage_path'] = $g['media_path'];
			else:
				$_POST['storage_path'] = rtrim($_POST['storage_path'],'/');	// ensure to have no trailing slash
			endif;
			if(!is_dir("{$_POST['storage_path']}/rrd")):
				mkdir("{$_POST['storage_path']}/rrd",0775,true);	// new destination or first install
				rrdgraphs_change_perms("{$_POST['storage_path']}/rrd");	// check/set permissions
			endif;
			$config['rrdgraphs']['storage_path'] = $_POST['storage_path'];
			$_POST['graph_h'] = trim($_POST['graph_h']);
			$_POST['graph_w'] = trim($_POST['graph_w']);
			$config['rrdgraphs']['graph_h'] = !empty($_POST['graph_h']) ? $_POST['graph_h'] : 200;
			$config['rrdgraphs']['graph_w'] = !empty($_POST['graph_w']) ? $_POST['graph_w'] : 600;
			$config['rrdgraphs']['refresh_time'] = !empty($_POST['refresh_time']) ? $_POST['refresh_time'] : 300;
			$config['rrdgraphs']['autoscale'] = isset($_POST['autoscale']);
			$config['rrdgraphs']['background_white'] = isset($_POST['background_white']);
			$config['rrdgraphs']['bytes_per_second'] = isset($_POST['bytes_per_second']);
			$config['rrdgraphs']['logarithmic'] = isset($_POST['logarithmic']);
			$config['rrdgraphs']['axis'] = isset($_POST['axis']);
			if($config['rrdgraphs']['axis']):
				$config['rrdgraphs']['logarithmic'] = false;
			endif;
			$config['rrdgraphs']['load_averages'] = isset($_POST['load_averages']);
			$config['rrdgraphs']['cpu_frequency'] = isset($_POST['cpu_frequency']);
			$config['rrdgraphs']['cpu_temperature'] = isset($_POST['cpu_temperature']);
			$config['rrdgraphs']['disk_usage'] = isset($_POST['disk_usage']);
			$config['rrdgraphs']['lan_load'] = isset($_POST['lan_load']);
			$config['rrdgraphs']['lan_if'] = get_ifname($config['interfaces']['lan']['if']);	// for 'auto' if name
			$config['rrdgraphs']['no_processes'] = isset($_POST['no_processes']);
			$config['rrdgraphs']['cpu'] = isset($_POST['cpu']);
			$config['rrdgraphs']['memory_usage'] = isset($_POST['memory_usage']);
			$config['rrdgraphs']['arc_usage'] = isset($_POST['arc_usage']);
			$config['rrdgraphs']['l2arc_usage'] = isset($_POST['l2arc_usage']);
			$config['rrdgraphs']['arc_efficiency'] = isset($_POST['arc_efficiency']);
			$config['rrdgraphs']['latency'] = isset($_POST['latency']);
			$config['rrdgraphs']['latency_host'] = !empty($_POST['latency_host']) ? $_POST['latency_host'] : "127.0.0.1";
			$config['rrdgraphs']['latency_interface'] = $_POST['latency_interface'];
			$config['rrdgraphs']['latency_count'] = $_POST['latency_count'];
			$config['rrdgraphs']['latency_parameters'] = !empty($_POST['latency_parameters']) ? $_POST['latency_parameters'] : "";
			$config['rrdgraphs']['ups'] = isset($_POST['ups']);
			$config['rrdgraphs']['ups_at'] = !empty($_POST['ups_at']) ? $_POST['ups_at'] : "identifier@host-ip-address";
			$config['rrdgraphs']['uptime'] = isset($_POST['uptime']);
			$savemsg = get_std_save_message(write_config());
			if(!file_exists($d_sysrebootreqd_path)):
				config_lock();
				$retval |= rc_update_service("cron");
				config_unlock();
			endif;
			require '/usr/local/share/rrdgraphs/rrd-start.php';
		else:
			$config['rrdgraphs']['enable'] = isset($_POST['enable']);
			$savemsg = get_std_save_message(write_config());
			write_log('rrdgraphs service stopped');
			if(!file_exists($d_sysrebootreqd_path)):
				config_lock();
				$retval |= rc_update_service('cron');
				config_unlock();
			endif;
		endif;
	endif;
endif;
if(isset($_POST['reset_graphs']) && $_POST['reset_graphs']):
	write_log('rrdgraphs service execute delete statistical data ...');
	$savemsg = gtext('All data from the following statistics have been deleted:');
	$format = sprintf('%s/rrd/%%s.rrd',$config['rrdgraphs']['storage_path']);
	$processing_table = [
		['postid' => 'cpu_frequency','fnpattern' => 'cpu_freq','log' => 'cpu frequency','msg' => gtext('CPU Frequency')],
		['postid' => 'cpu_temperature','fnpattern' => 'cpu_temp','log' => 'cpu temperature','msg' => gtext('CPU Temperature')],
		['postid' => 'cpu','fnpattern' => 'cpu','log' => 'cpu','msg' => gtext('CPU Usage')],
		['postid' => 'load_averages','fnpattern' => 'load_averages','log' => 'load averages','msg' => gtext('Load Averages')],
		['postid' => 'disk_usage','fnpattern' => 'mnt_*','log' => 'disk usage','msg' => gtext('Disk Usage')],
		['postid' => 'memory_usage','fnpattern' => 'memory','log' => 'memory usage','msg' => gtext('Memory Usage')],
		['postid' => 'latency','fnpattern' => 'latency','log' => 'network latency','msg' => gtext('Network Latency')],
		['postid' => 'lan_load','fnpattern' => $config['rrdgraphs']['lan_if'],'log' => 'network traffic','msg' => gtext('Network Traffic')],
		['postid' => 'no_processes','fnpattern' => 'processes','log' => 'system processes','msg' => gtext('System Processes')],
		['postid' => 'ups','fnpattern' => 'ups','log' => 'ups','msg' => gtext('UPS Statistics')],
		['postid' => 'uptime','fnpattern' => 'uptime','log' => 'uptime','msg' => gtext('Uptime Statistics')],
		['postid' => 'arc_usage','fnpattern' => 'zfs_arc','log' => 'zfs arc usage','msg' => gtext('ZFS ARC Usage')],
		['postid' => 'l2arc_usage','fnpattern' => 'zfs_l2arc','log' => 'zfs l2arc usage','msg' => gtext('ZFS L2ARC Usage')],
		['postid' => 'arc_efficiency','fnpattern' => 'zfs_arceff','log' => 'zfs cache efficiency','msg' => gtext('ZFS Cache Efficiency')]
	];
	foreach($processing_table as $processing_task):
		if(isset($_POST[$processing_task['postid']])):
			$rrd_pattern = sprintf($format,$processing_task['fnpattern']);
			$rrd_fqfn_collection = glob($rrd_pattern,GLOB_NOSORT);
			if(is_array($rrd_fqfn_collection) && (count($rrd_fqfn_collection) > 0)):
				array_walk($rrd_fqfn_collection,function($rrd_fqfn) {
					if(is_file($rrd_fqfn)):
						unlink($rrd_fqfn);
					endif;
				});
				write_log(sprintf('rrdgraphs service deleted %s statistics',$processing_task['log']));
				$savemsg .= '<br />- ' . $processing_task['msg'];
			endif;
		endif;
	endforeach;
	require '/usr/local/share/rrdgraphs/rrd-start.php';
endif;
$pconfig['enable'] = isset($config['rrdgraphs']['enable']);
$pconfig['storage_path'] = !empty($config['rrdgraphs']['storage_path']) ? $config['rrdgraphs']['storage_path'] : $g['media_path'];
$pconfig['graph_h'] = !empty($config['rrdgraphs']['graph_h']) ? $config['rrdgraphs']['graph_h'] : 200;
$pconfig['graph_w'] = !empty($config['rrdgraphs']['graph_w']) ? $config['rrdgraphs']['graph_w'] : 600;
$pconfig['refresh_time'] = !empty($config['rrdgraphs']['refresh_time']) ? $config['rrdgraphs']['refresh_time'] : 300;
$pconfig['autoscale'] = isset($config['rrdgraphs']['autoscale']);
$pconfig['background_white'] = isset($config['rrdgraphs']['background_white']);
// available graphs
$pconfig['cpu_frequency'] = isset($config['rrdgraphs']['cpu_frequency']);
$pconfig['cpu_temperature'] = isset($config['rrdgraphs']['cpu_temperature']);
$pconfig['cpu'] = isset($config['rrdgraphs']['cpu']);
$pconfig['load_averages'] = isset($config['rrdgraphs']['load_averages']);
$pconfig['disk_usage'] = isset($config['rrdgraphs']['disk_usage']);
$pconfig['memory_usage'] = isset($config['rrdgraphs']['memory_usage']);
$pconfig['latency'] = isset($config['rrdgraphs']['latency']);
$pconfig['latency_host'] = !empty($config['rrdgraphs']['latency_host']) ? $config['rrdgraphs']['latency_host'] : "127.0.0.1";
$pconfig['latency_interface'] = !empty($config['rrdgraphs']['latency_interface']) ? $config['rrdgraphs']['latency_interface'] : "identifier@host-ip-address";
$pconfig['latency_count'] = !empty($config['rrdgraphs']['latency_count']) ? $config['rrdgraphs']['latency_count'] : "3";
$pconfig['latency_parameters'] = !empty($config['rrdgraphs']['latency_parameters']) ? $config['rrdgraphs']['latency_parameters'] : "";
$pconfig['lan_load'] = isset($config['rrdgraphs']['lan_load']);
$pconfig['bytes_per_second'] = isset($config['rrdgraphs']['bytes_per_second']);
$pconfig['logarithmic'] = isset($config['rrdgraphs']['logarithmic']);
$pconfig['axis'] = isset($config['rrdgraphs']['axis']);
$pconfig['no_processes'] = isset($config['rrdgraphs']['no_processes']);
$pconfig['ups'] = isset($config['rrdgraphs']['ups']);
$pconfig['ups_at'] = !empty($config['rrdgraphs']['ups_at']) ? $config['rrdgraphs']['ups_at'] : "identifier@host-ip-address";
$pconfig['uptime'] = isset($config['rrdgraphs']['uptime']);
$pconfig['arc_usage'] = isset($config['rrdgraphs']['arc_usage']);
$pconfig['l2arc_usage'] = isset($config['rrdgraphs']['l2arc_usage']);
$pconfig['arc_efficiency'] = isset($config['rrdgraphs']['arc_efficiency']);
$a_interface = get_interface_list();
// Add VLAN interfaces
arr::make_branch($config,'vinterfaces','vlan');
if(!empty($config['vinterfaces']['vlan'])):
	foreach($config['vinterfaces']['vlan'] as $vlanv):
		$a_interface[$vlanv['if']] = $vlanv;
		$a_interface[$vlanv['if']]['isvirtual'] = true;
	endforeach;
endif;
// Add LAGG interfaces
arr::make_branch($config,'vfinterfaces','lagg');
if(!empty($config['vinterfaces']['lagg'])):
	foreach($config['vinterfaces']['lagg'] as $laggv):
		$a_interface[$laggv['if']] = $laggv;
		$a_interface[$laggv['if']]['isvirtual'] = true;
	endforeach;
endif;
// Use first interface as default if it is not set.
if(empty($pconfig['latency_interface']) && is_array($a_interface)):
	$pconfig['latency_interface'] = key($a_interface);
endif;
$pgtitle = [gtext('System'),gtext('Advanced'),gtext('Monitoring Setup')];
include 'fbegin.inc';
?>
<script>
//<![CDATA[
$(window).on("load", function() {
<?php // Init spinner.?>
	$("#iform").submit(function() { spinner(); });
	$(".spin").click(function() { spinner(); });
});
function axis_change() {
	switch(document.iform.axis.checked) {
		case true:
			document.getElementById('logarithmic').checked = false;
			showElementById('logarithmic_tr','hide');
			break;
		case false:
			showElementById('logarithmic_tr','show');
			break;
	}
}
function logarithmic_change() {
	switch(document.iform.logarithmic.checked) {
		case true:
			document.getElementById('axis').checked = false;
			showElementById('axis_tr','hide');
			break;
		case false:
			showElementById('axis_tr','show');
			break;
	}
}
function lan_change() {
	switch(document.iform.lan_load.checked) {
		case true:
			showElementById('bytes_per_second_tr','show');
			showElementById('axis_tr','show');
			axis_change();
			logarithmic_change();
			break;
		case false:
			showElementById('bytes_per_second_tr','hide');
			showElementById('logarithmic_tr','hide');
			showElementById('axis_tr','hide');
			break;
	}
}
function latency_change() {
	switch(document.iform.latency.checked) {
		case true:
			showElementById('latency_host_tr','show');
			showElementById('latency_interface_tr','show');
			showElementById('latency_count_tr','show');
			showElementById('latency_parameters_tr','show');
			showElementById('latency_interface_cell','show');
			showElementById('latency_interface_table','show');
			break;
		case false:
			showElementById('latency_host_tr','hide');
			showElementById('latency_interface_tr','hide');
			showElementById('latency_count_tr','hide');
			showElementById('latency_parameters_tr','hide');
			showElementById('latency_interface_cell','hide');
			showElementById('latency_interface_table','hide');
			break;
	}
}
function ups_change() {
	switch(document.iform.ups.checked) {
		case true:
			showElementById('ups_at_tr','show');
			break;
		case false:
			showElementById('ups_at_tr','hide');
			break;
	}
}
function enable_change(enable_change) {
	var endis = !(document.iform.enable.checked || enable_change);
	document.iform.storage_path.disabled = endis;
	document.iform.storage_pathbrowsebtn.disabled = endis;
	document.iform.graph_h.disabled = endis;
	document.iform.graph_w.disabled = endis;
	document.iform.refresh_time.disabled = endis;
	document.iform.autoscale.disabled = endis;
	document.iform.background_white.disabled = endis;
	document.iform.bytes_per_second.disabled = endis;
	document.iform.logarithmic.disabled = endis;
	document.iform.axis.disabled = endis;
	document.iform.reset_graphs.disabled = endis;
	document.iform.uptime.disabled = endis;
	document.iform.load_averages.disabled = endis;
	document.iform.no_processes.disabled = endis;
	document.iform.cpu.disabled = endis;
	document.iform.cpu_frequency.disabled = endis;
	document.iform.cpu_temperature.disabled = endis;
	document.iform.memory_usage.disabled = endis;
	document.iform.arc_usage.disabled = endis;
	document.iform.l2arc_usage.disabled = endis;
	document.iform.arc_efficiency.disabled = endis;
	document.iform.disk_usage.disabled = endis;
	document.iform.lan_load.disabled = endis;
	document.iform.latency.disabled = endis;
	document.iform.latency_host.disabled = endis;
	document.iform.latency_interface.disabled = endis;
	document.iform.latency_count.disabled = endis;
	document.iform.latency_parameters.disabled = endis;
	document.iform.ups.disabled = endis;
	document.iform.ups_at.disabled = endis;
}
//]]>
</script>
<?php
$document = new document();
$document->
	add_area_tabnav()->
		add_tabnav_upper()->
			ins_tabnav_record('system_advanced.php',gettext('Advanced'))->
			ins_tabnav_record('system_email.php',gettext('Email'))->
			ins_tabnav_record('system_email_reports.php',gettext('Email Reports'))->
			ins_tabnav_record('system_monitoring.php',gettext('Monitoring'),gettext('Reload page'),true)->
			ins_tabnav_record('system_swap.php',gettext('Swap'))->
			ins_tabnav_record('system_rc.php',gettext('Command Scripts'))->
			ins_tabnav_record('system_cron.php',gettext('Cron'))->
			ins_tabnav_record('system_loaderconf.php',gettext('loader.conf'))->
			ins_tabnav_record('system_rcconf.php',gettext('rc.conf'))->
			ins_tabnav_record('system_sysctl.php',gettext('sysctl.conf'))->
			ins_tabnav_record('system_syslogconf.php',gettext('syslog.conf'));
$document->render();
?>
<form action="<?=$sphere_scriptname;?>" method="post" id="iform" name="iform" class="pagecontent"><div class="area_data_top"></div><div id="area_data_frame">
<?php
	if(!empty($savemsg)):
		print_info_box($savemsg);
	endif;
	if(!empty($errormsg)):
		print_error_box($errormsg);
	endif;
	if(!empty($input_errors)):
		print_input_errors($input_errors);
	endif;
	if(file_exists($d_sysrebootreqd_path)):
		print_info_box(get_std_save_message(0));
	endif;
?>
	<table class="area_data_settings">
		<colgroup>
			<col class="area_data_settings_col_tag">
			<col class="area_data_settings_col_data">
		</colgroup>
		<thead>
<?php
			html_titleline_checkbox2('enable',gettext('System Monitoring Settings'),$pconfig['enable'],gettext('Enable'),'enable_change(false)');
?>
		</thead>
		<tbody>
<?php
			html_filechooser2('storage_path',gettext('Home Directory'),$pconfig['storage_path'],gettext('Enter the path to the home directory. This directory will store the statistical data and gets updated every 5 minutes!'),$g['media_path'],true,60);
			html_inputbox2('refresh_time',gettext('Page Refresh'),$pconfig['refresh_time'],gettext('Auto page refresh.') . ' ' . sprintf(gettext('(default %s %s'),300,gettext('seconds)')),false,5);
			html_inputbox2('graph_h',gettext('Graphs Height'),$pconfig['graph_h'],sprintf(gettext('Height of the graphs. (default %s pixels)'),200),false,5);
			html_inputbox2('graph_w',gettext('Graphs Width'),$pconfig['graph_w'],sprintf(gettext('Width of the graphs. (default %s pixels)'),600),false,5);
			html_checkbox2('autoscale',gettext('Autoscale'),$pconfig['autoscale'],gettext('Autoscale for graphs.'),'',false);
			html_checkbox2('background_white',gettext('Background'),$pconfig['background_white'],gettext('Enable white background graphs. (black as default)'),'',false);
?>
		</tbody>
	</table>
	<table class="area_data_settings">
		<colgroup>
			<col class="area_data_settings_col_tag">
			<col class="area_data_settings_col_data">
		</colgroup>
		<thead>
<?php
			html_titleline2(gettext('Available Graphs'));
?>
		</thead>
		<tbody>
<?php
			html_checkbox2('cpu_frequency',gettext('CPU Frequency'),$pconfig['cpu_frequency'],gettext('Enable collecting CPU frequency statistics.'),'',false);
			html_checkbox2('cpu_temperature',gettext('CPU Temperature'),$pconfig['cpu_temperature'],gettext('Enable collecting CPU temperature statistics.'),'',false);
			html_checkbox2('cpu',gettext('CPU Usage'),$pconfig['cpu'],gettext('Enable collecting CPU usage statistics.'),'',false);
			html_checkbox2('disk_usage',gettext('Disk Usage'),$pconfig['disk_usage'],gettext('Enable collecting disk space usage statistics.'),'',false);
			html_checkbox2('load_averages',gettext('Load Averages'),$pconfig['load_averages'],gettext('Enable collecting average system load statistics.'),'',false);
			html_checkbox2('memory_usage',gettext('Memory Usage'),$pconfig['memory_usage'],gettext('Enable collecting memory usage statistics.'),'',false);
			html_checkbox2('latency',gettext('Network Latency'),$pconfig['latency'],gettext('Enable collecting network latency statistics.'),'',false,false,'latency_change()');
			html_inputbox2('latency_host',gettext('Host'),$pconfig['latency_host'],gettext('Destination host name or IP address.'),false,20);
			$a_option = [];
			$s_option = '';
			foreach($a_interface as $if => $ifinfo):
				$ifinfo = get_interface_info($if);
				if(('up' == $ifinfo['status']) || ('associated' == $ifinfo['status'])):
					$a_option[] = $if;
					if($if == $pconfig['latency_interface']):
						$s_option = $if;
					endif;
				endif;
			endforeach;
			html_combobox2('latency_interface',gettext('Interface Selection'),$s_option,$a_option,gettext('Select the interface (only selectable if your server has more than one) to use for the source IP address in outgoing packets.'));
			$latency_a_count = [];
			for($i = 1;$i <= 20;$i++):
				$latency_a_count[$i] = $i;
			endfor;
			html_combobox2('latency_count',gettext('Count'),$pconfig['latency_count'],$latency_a_count,gettext('Stop after sending (and receiving) N packets.'),false);
			$helpinghand = gettext('These parameters will be added to the ping command.')
				. ' '
				. sprintf(gettext('Please check the %sdocumentation%s.'),'<a href="http://www.freebsd.org/cgi/man.cgi?query=ping&amp;apropos=0&amp;sektion=0&amp;format=html" target="_blank">','</a>');
			html_inputbox2('latency_parameters',gettext('Additional Parameters'),$pconfig['latency_parameters'],$helpinghand,false,60);
			html_checkbox2('lan_load',gettext('Network Traffic'),$pconfig['lan_load'],gettext('Enable collecting network traffic statistics.'),'',false,false,'lan_change()');
			html_checkbox2('bytes_per_second',gettext('Bytes/sec'),$pconfig['bytes_per_second'],gettext('Use Bytes/sec instead of Bits/sec for network throughput display.'),'',false);
			html_checkbox2('logarithmic',gettext('Logarithmic Scaling'),$pconfig['logarithmic'],sprintf(gettext('Use logarithmic y-axis scaling for %s graphs. (can not be used together with positive/negative y-axis range)'),gettext('network traffic')),'',false,false,'logarithmic_change()');
			html_checkbox2('axis',gettext('Y-axis Range'),$pconfig['axis'],sprintf(gettext('Show positive/negative values for %s graphs. (can not be used together with logarithmic scaling)'),gettext('network traffic')),'',false,false,'axis_change()');
			html_checkbox2('no_processes',gettext('System Processes'),$pconfig['no_processes'],gettext('Enable collecting system process statistics.'),'',false);
			html_checkbox2('ups',gettext('UPS Statistics'),$pconfig['ups'],gettext('Enable collecting UPS statistics.'),'',false,false,'ups_change()');
			$helpinghand = gettext('Enter the UPS identifier and host IP address of the machine where the UPS is connected to. (this also can be a remote host)')
				. '<br /> '
				. gettext('The UPS identifier and IP address')
				.	' '
				. sprintf(gettext('must be in the format: %s.'),'identifier@host-ip-address or identifier@localhost');
			html_inputbox2('ups_at',gettext('UPS Identifier'),$pconfig['ups_at'],$helpinghand,false,60);
			html_checkbox2('uptime',gettext('Uptime Statistics'),$pconfig['uptime'],gettext('Enable collecting uptime statistics.'),'',false);
			html_checkbox2('arc_usage',gettext('ZFS ARC Usage'),$pconfig['arc_usage'],gettext('Enable collecting ZFS ARC usage statistics.'),'',false);
			html_checkbox2('l2arc_usage',gettext('ZFS L2ARC Usage'),$pconfig['l2arc_usage'],gettext('Enable collecting ZFS L2ARC usage statistics.'),'',false);
			html_checkbox2('arc_efficiency',gettext('ZFS Cache Efficiency'),$pconfig['arc_efficiency'],gettext('Enable collecting ZFS cache efficiency statistics.'),'',false);
?>
		</tbody>
	</table>
	<div id="submit">
		<input id="save" name="save" type="submit" class="formbtn" value="<?=gtext('Save & Restart');?>"/>
		<input id="reset_graphs" name="reset_graphs" type="submit" class="formbtn" value="<?=gtext('Reset Graphs');?>" onclick="return confirm('<?=gtext('Do you really want to delete all data from the selected statistics?');?>')" />
	</div>
	<div id="remarks">
<?php
		$helpinghand = sprintf(gettext("'%s' deletes all statistical data from the graphs!"),gettext('Reset Graphs'))
			. '<div id="enumeration"><ul>'
			. '<li>' . sprintf(gettext("If only specific statistics needs to be reset, clear all other check boxes before performing '%s'."),gettext('Reset Graphs')) . '</li>'
			. '</ul></div>';
		html_remark2('warning',gettext('Warning'),$helpinghand);
?>
	</div>
<?php
	include 'formend.inc';
?>
</div><div class="area_data_pot"></div></form>
<script>
//<![CDATA[
lan_change();
latency_change();
ups_change();
enable_change(false);
//]]>
</script>
<?php
include 'fend.inc';
