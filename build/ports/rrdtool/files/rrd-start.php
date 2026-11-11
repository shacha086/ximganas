<?php
/*
	rrd-start.php

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

use common\arr;

$runtime_dir = '/usr/local/share/rrdgraphs';
if(isset($config['rrdgraphs']['enable'])):
	write_log('rrdgraphs service started');
//	create config file
	$rrdconfig = fopen("{$config['rrdgraphs']['storage_path']}/rrd_config",'w');
	fwrite($rrdconfig,sprintf("STORAGE_PATH=%s\n",escapeshellarg($config['rrdgraphs']['storage_path'] ?? '')));
	fwrite($rrdconfig,sprintf("GRAPH_H=%u\n",$config['rrdgraphs']['graph_h'] ?? 200));
	fwrite($rrdconfig,sprintf("GRAPH_W=%u\n",$config['rrdgraphs']['graph_w'] ?? 600));
	fwrite($rrdconfig,sprintf("REFRESH_TIME=%u\n",$config['rrdgraphs']['refresh_time']));
	fwrite($rrdconfig,sprintf("AUTOSCALE=%s\n",isset($config['rrdgraphs']['autoscale']) ? '--alt-autoscale' : ''));
	fwrite($rrdconfig,sprintf("BACKGROUND_WHITE=%u\n",isset($config['rrdgraphs']['background_white']) ? 1 : 0));
	fwrite($rrdconfig,sprintf("BYTE_SWITCH=%u\n",isset($config['rrdgraphs']['bytes_per_second']) ? 1 : 0));
	fwrite($rrdconfig,sprintf("LOGARITHMIC=%u\n",isset($config['rrdgraphs']['logarithmic']) ? 1 : 0));
	fwrite($rrdconfig,sprintf("AXIS=%u\n",isset($config['rrdgraphs']['axis']) ? 1 : 0));
	fwrite($rrdconfig,sprintf("INTERFACE0=%s\n",escapeshellarg($config['rrdgraphs']['lan_if'] ?? '')));
	fwrite($rrdconfig,sprintf("LATENCY_HOST=%s\n",escapeshellarg($config['rrdgraphs']['latency_host'] ?? '')));
	fwrite($rrdconfig,sprintf("LATENCY_INTERFACE=%s\n",escapeshellarg($config['rrdgraphs']['latency_interface'] ?? '')));
	fwrite($rrdconfig,sprintf("LATENCY_INTERFACE_IP=%s\n",escapeshellarg(get_ipaddr($config['rrdgraphs']['lan_if']) ?? '')));
	fwrite($rrdconfig,sprintf("LATENCY_COUNT=%s\n",escapeshellarg($config['rrdgraphs']['latency_count'] ?? '')));
	fwrite($rrdconfig,sprintf("LATENCY_PARAMETERS=%s\n",escapeshellarg($config['rrdgraphs']['latency_parameters'] ?? '')));
	fwrite($rrdconfig,sprintf("UPS_AT=%s\n",escapeshellarg($config['rrdgraphs']['ups_at'] ?? '')));
	fwrite($rrdconfig,sprintf("RUN_FRQ=%u\n",isset($config['rrdgraphs']['cpu_frequency']) ? 1 : 0));
	fwrite($rrdconfig,sprintf("RUN_TMP=%u\n",isset($config['rrdgraphs']['cpu_temperature']) ? 1 : 0));
	fwrite($rrdconfig,sprintf("RUN_CPU=%u\n",isset($config['rrdgraphs']['cpu']) ? 1 : 0));
	fwrite($rrdconfig,sprintf("RUN_DUS=%u\n",isset($config['rrdgraphs']['disk_usage']) ? 1 : 0));
	fwrite($rrdconfig,sprintf("RUN_AVG=%u\n",isset($config['rrdgraphs']['load_averages']) ? 1 : 0));
	fwrite($rrdconfig,sprintf("RUN_MEM=%u\n",isset($config['rrdgraphs']['memory_usage']) ? 1 : 0));
	fwrite($rrdconfig,sprintf("RUN_LAT=%u\n",isset($config['rrdgraphs']['latency']) ? 1 : 0));
	fwrite($rrdconfig,sprintf("RUN_LAN=%u\n",isset($config['rrdgraphs']['lan_load']) ? 1 : 0));
	fwrite($rrdconfig,sprintf("RUN_PRO=%u\n",isset($config['rrdgraphs']['no_processes']) ? 1 : 0));
	fwrite($rrdconfig,sprintf("RUN_UPS=%u\n",isset($config['rrdgraphs']['ups']) ? 1 : 0));
	fwrite($rrdconfig,sprintf("RUN_UPT=%u\n",isset($config['rrdgraphs']['uptime']) ? 1 : 0));
	fwrite($rrdconfig,sprintf("RUN_ARC=%u\n",isset($config['rrdgraphs']['arc_usage']) ? 1 : 0));
	fwrite($rrdconfig,sprintf("RUN_L2ARC=%u\n",isset($config['rrdgraphs']['l2arc_usage']) ? 1 : 0));
	fwrite($rrdconfig,sprintf("RUN_ARCEFF=%u\n",isset($config['rrdgraphs']['arc_efficiency']) ? 1 : 0));
	if(isset($config['rrdgraphs']['disk_usage'])):
		if(isset($config['rrdgraphs']['mounts'])):
			unset($config['rrdgraphs']['mounts']);
		endif;
		arr::make_branch($config,'rrdgraphs','mounts');
		if(isset($config['rrdgraphs']['pools'])):
			unset($config['rrdgraphs']['pools']);
		endif;
		arr::make_branch($config,'rrdgraphs','pools');
		if(is_array($config['mounts']) && is_array($config['mounts']['mount'])):
			for($i = 0; $i < count($config['mounts']['mount']); ++$i):
				$config['rrdgraphs']['mounts']["mount{$i}"] = $config['mounts']['mount'][$i]['sharename'];
				fwrite($rrdconfig,sprintf("MOUNT%u=%s\n",$i,escapeshellarg($config['mounts']['mount'][$i]['sharename'])));
			endfor;
		endif;
		if(is_array($config['zfs']['pools']) && is_array($config['zfs']['pools']['pool'])):
			unset($pools);
//			get ZFS pools and datasets
			exec('zfs list -H -t filesystem -o name',$pools,$retval);
			for($i = 0;$i < count($pools);++$i):
				$config['rrdgraphs']['pools']["pool{$i}"] = $pools[$i];
				fwrite($rrdconfig,sprintf("POOL%u=%s\n",$i,escapeshellarg($pools[$i])));
			endfor;
		endif;
	endif;
	fclose($rrdconfig);
//	create new .rrds if necessary
	if(isset($config['rrdgraphs']['cpu_frequency'])):
		$rrd_name = 'cpu_freq.rrd';
		$rrd_fqfn = sprintf('%s/rrd/%s',$config['rrdgraphs']['storage_path'],$rrd_name);
		if(!is_file($rrd_fqfn)):
			$ret_val = mwexec("/usr/local/bin/rrdtool create " . escapeshellarg($rrd_fqfn) .
				" -s 300" .
				" 'DS:core0:GAUGE:600:0:U'" .
				" 'DS:core1:GAUGE:600:0:U'" .
				" 'RRA:AVERAGE:0.5:1:576'" .
				" 'RRA:AVERAGE:0.5:6:672'" .
				" 'RRA:AVERAGE:0.5:24:732'" .
				" 'RRA:AVERAGE:0.5:144:1460'",
				true);
			write_log(sprintf('rrdgraphs service is collecting cpu frequency statistics (%s)',$rrd_name));
		endif;
	endif;
	if(isset($config['rrdgraphs']['cpu_temperature'])):
		$rrd_name = 'cpu_temp.rrd';
		$rrd_fqfn = sprintf('%s/rrd/%s',$config['rrdgraphs']['storage_path'],$rrd_name);
		if(!is_file($rrd_fqfn)):
			$ret_val = mwexec("/usr/local/bin/rrdtool create " . escapeshellarg($rrd_fqfn) .
				" -s 300" .
				" 'DS:core0:GAUGE:600:0:U'" .
				" 'DS:core1:GAUGE:600:0:U'" .
				" 'RRA:AVERAGE:0.5:1:576'" .
				" 'RRA:AVERAGE:0.5:6:672'" .
				" 'RRA:AVERAGE:0.5:24:732'" .
				" 'RRA:AVERAGE:0.5:144:1460'",
				true);
			write_log(sprintf('rrdgraphs service is collecting cpu temperature statistics (%s)',$rrd_name));
		endif;
	endif;
	if(isset($config['rrdgraphs']['cpu'])):
		$rrd_name = 'cpu.rrd';
		$rrd_fqfn = sprintf('%s/rrd/%s',$config['rrdgraphs']['storage_path'],$rrd_name);
		if(!is_file($rrd_fqfn)):
			$ret_val = mwexec("/usr/local/bin/rrdtool create " . escapeshellarg($rrd_fqfn) .
				" -s 300" .
				" 'DS:user:GAUGE:600:U:U'" .
				" 'DS:nice:GAUGE:600:U:U'" .
				" 'DS:system:GAUGE:600:U:U'" .
				" 'DS:interrupt:GAUGE:600:U:U'" .
				" 'DS:idle:GAUGE:600:U:U'" .
				" 'RRA:AVERAGE:0.5:1:576'" .
				" 'RRA:AVERAGE:0.5:6:672'" .
				" 'RRA:AVERAGE:0.5:24:732'" .
				" 'RRA:AVERAGE:0.5:144:1460'",
				true);
			write_log(sprintf('rrdgraphs service is collecting cpu usage statistics (%s)',$rrd_name));
		endif;
	endif;
	if(isset($config['rrdgraphs']['load_averages'])):
		$rrd_name = 'load_averages.rrd';
		$rrd_fqfn = sprintf('%s/rrd/%s',$config['rrdgraphs']['storage_path'],$rrd_name);
		if(!is_file($rrd_fqfn)):
			$ret_val = mwexec("/usr/local/bin/rrdtool create " . escapeshellarg($rrd_fqfn) .
				" -s 300" .
				" 'DS:CPU:GAUGE:600:0:100'" .
				" 'DS:CPU5:GAUGE:600:0:100'" .
				" 'DS:CPU15:GAUGE:600:0:100'" .
				" 'RRA:AVERAGE:0.5:1:576'" .
				" 'RRA:AVERAGE:0.5:6:672'" .
				" 'RRA:AVERAGE:0.5:24:732'" .
				" 'RRA:AVERAGE:0.5:144:1460'",
				true);
			write_log(sprintf('rrdgraphs service is collecting load averages statistics (%s)',$rrd_name));
		endif;
	endif;
	if(isset($config['rrdgraphs']['disk_usage'])):
		$temp_array = array_merge($config['rrdgraphs']['mounts'],$config['rrdgraphs']['pools']);
		foreach($temp_array as $retval):
			$rrd_name = sprintf('mnt_%s.rrd',strtr(base64_encode($retval),'+/=','-_~'));
			$rrd_fqfn = sprintf('%s/rrd/%s',$config['rrdgraphs']['storage_path'],$rrd_name);
			if(!is_file($rrd_fqfn)):
				$ret_val = mwexec("/usr/local/bin/rrdtool create " . escapeshellarg($rrd_fqfn) .
					" -s 300" .
					" 'DS:Used:GAUGE:600:U:U'" .
					" 'DS:Free:GAUGE:600:U:U'" .
					" 'RRA:AVERAGE:0.5:1:576'" .
					" 'RRA:AVERAGE:0.5:6:672'" .
					" 'RRA:AVERAGE:0.5:24:732'" .
					" 'RRA:AVERAGE:0.5:144:1460'",
					true);
				write_log(sprintf('rrdgraphs service is collecting disk usage statistics (%s)',$rrd_name));
			endif;
		endforeach;
	endif;
	if(isset($config['rrdgraphs']['memory_usage'])):
		$rrd_name = 'memory.rrd';
		$rrd_fqfn = sprintf('%s/rrd/%s',$config['rrdgraphs']['storage_path'],$rrd_name);
		if(!is_file($rrd_fqfn)):
			$ret_val = mwexec("/usr/local/bin/rrdtool create " . escapeshellarg($rrd_fqfn) .
				" -s 300" .
				" 'DS:active:GAUGE:600:U:U'" .
				" 'DS:inact:GAUGE:600:U:U'" .
				" 'DS:wired:GAUGE:600:U:U'" .
				" 'DS:cache:GAUGE:600:U:U'" .
				" 'DS:buf:GAUGE:600:U:U'" .
				" 'DS:free:GAUGE:600:U:U'" .
				" 'DS:total:GAUGE:600:U:U'" .
				" 'DS:used:GAUGE:600:U:U'" .
				" 'RRA:AVERAGE:0.5:1:576'" .
				" 'RRA:AVERAGE:0.5:6:672'" .
				" 'RRA:AVERAGE:0.5:24:732'" .
				" 'RRA:AVERAGE:0.5:144:1460'",
				true);
			write_log(sprintf('rrdgraphs service is collecting memory usage statistics (%s)',$rrd_name));
		endif;
	endif;
	if(isset($config['rrdgraphs']['latency'])):
		$rrd_name = 'latency.rrd';
		$rrd_fqfn = sprintf('%s/rrd/%s',$config['rrdgraphs']['storage_path'],$rrd_name);
		if(!is_file($rrd_fqfn)):
			$ret_val = mwexec("/usr/local/bin/rrdtool create " . escapeshellarg($rrd_fqfn) .
				" -s 300" .
				" 'DS:min:GAUGE:600:U:U'" .
				" 'DS:avg:GAUGE:600:U:U'" .
				" 'DS:max:GAUGE:600:U:U'" .
				" 'DS:stddev:GAUGE:600:U:U'" .
				" 'RRA:AVERAGE:0.5:1:576'" .
				" 'RRA:AVERAGE:0.5:6:672'" .
				" 'RRA:AVERAGE:0.5:24:732'" .
				" 'RRA:AVERAGE:0.5:144:1460'",
				true);
			write_log(sprintf('rrdgraphs service is collecting network latency statistics (%s)',$rrd_name));
		endif;
	endif;
	if(isset($config['rrdgraphs']['lan_load'])):
		$rrd_name = sprintf('%s.rrd',$config['rrdgraphs']['lan_if']);
		$rrd_fqfn = sprintf('%s/rrd/%s',$config['rrdgraphs']['storage_path'],$rrd_name);
		if(!is_file($rrd_fqfn)):
			$ret_val = mwexec("/usr/local/bin/rrdtool create " . escapeshellarg($rrd_fqfn) .
				" -s 300" .
				" 'DS:in:COUNTER:600:0:U'" .
				" 'DS:out:COUNTER:600:0:U'" .
				" 'RRA:AVERAGE:0.5:1:576'" .
				" 'RRA:AVERAGE:0.5:6:672'" .
				" 'RRA:AVERAGE:0.5:24:732'" .
				" 'RRA:AVERAGE:0.5:144:1460'",
				true);
			write_log(sprintf('rrdgraphs service is collecting network traffic statistics (%s)',$rrd_name));
		endif;
		for($j = 1;isset($config['interfaces']['opt' . $j]);$j++):
			$rrd_name = sprintf('opt-%s.rrd',$config['interfaces']['opt' . $j]['if']);
			$rrd_fqfn = sprintf('%s/rrd/%s',$config['rrdgraphs']['storage_path'],$rrd_name);
			if(!is_file($rrd_fqfn)):
				$ret_val = mwexec("/usr/local/bin/rrdtool create " . escapeshellarg($rrd_fqfn) .
					" -s 300" .
					" 'DS:in:COUNTER:600:0:U'" .
					" 'DS:out:COUNTER:600:0:U'" .
					" 'RRA:AVERAGE:0.5:1:576'" .
					" 'RRA:AVERAGE:0.5:6:672'" .
					" 'RRA:AVERAGE:0.5:24:732'" .
					" 'RRA:AVERAGE:0.5:144:1460'",
					true);
				write_log(sprintf('rrdgraphs service is collecting network traffic statistics (%s)',$rrd_name));
			endif;
		endfor;
	endif;
	if(isset($config['rrdgraphs']['no_processes'])):
		$rrd_name = 'processes.rrd';
		$rrd_fqfn = sprintf('%s/rrd/%s',$config['rrdgraphs']['storage_path'],$rrd_name);
		if(!is_file($rrd_fqfn)):
			$ret_val = mwexec("/usr/local/bin/rrdtool create " . escapeshellarg($rrd_fqfn) .
				" -s 300" .
				" 'DS:total:GAUGE:600:U:U'" .
				" 'DS:running:GAUGE:600:U:U'" .
				" 'DS:sleeping:GAUGE:600:U:U'" .
				" 'DS:waiting:GAUGE:600:U:U'" .
				" 'DS:starting:GAUGE:600:U:U'" .
				" 'DS:stopped:GAUGE:600:U:U'" .
				" 'DS:zombie:GAUGE:600:U:U'" .
				" 'RRA:AVERAGE:0.5:1:576'" .
				" 'RRA:AVERAGE:0.5:6:672'" .
				" 'RRA:AVERAGE:0.5:24:732'" .
				" 'RRA:AVERAGE:0.5:144:1460'",
				true);
			write_log(sprintf('rrdgraphs service is collecting system processes statistics (%s)',$rrd_name));
		endif;
	endif;
	if(isset($config['rrdgraphs']['ups'])):
		$rrd_name = 'ups.rrd';
		$rrd_fqfn = sprintf('%s/rrd/%s',$config['rrdgraphs']['storage_path'],$rrd_name);
		if(!is_file($rrd_fqfn)):
			$ret_val = mwexec("/usr/local/bin/rrdtool create " . escapeshellarg($rrd_fqfn) .
				" -s 300" .
				" 'DS:charge:GAUGE:600:U:U'" .
				" 'DS:load:GAUGE:600:U:U'" .
				" 'DS:bvoltage:GAUGE:600:U:U'" .
				" 'DS:ovoltage:GAUGE:600:U:U'" .
				" 'DS:ivoltage:GAUGE:600:U:U'" .
				" 'DS:runtime:GAUGE:600:U:U'" .
				" 'DS:OL:GAUGE:600:U:U'" .
				" 'DS:OF:GAUGE:600:U:U'" .
				" 'DS:OB:GAUGE:600:U:U'" .
				" 'DS:CG:GAUGE:600:U:U'" .
				" 'RRA:AVERAGE:0.5:1:576'" .
				" 'RRA:AVERAGE:0.5:6:672'" .
				" 'RRA:AVERAGE:0.5:24:732'" .
				" 'RRA:AVERAGE:0.5:144:1460'",
				true);
			write_log(sprintf('rrdgraphs service is collecting ups statistics (%s)',$rrd_name));
		endif;
	endif;
	if(isset($config['rrdgraphs']['uptime'])):
		$rrd_name = 'uptime.rrd';
		$rrd_fqfn = sprintf('%s/rrd/%s',$config['rrdgraphs']['storage_path'],$rrd_name);
		if(!is_file($rrd_fqfn)):
			$ret_val = mwexec("/usr/local/bin/rrdtool create " . escapeshellarg($rrd_fqfn) .
				" -s 300" .
				" 'DS:uptime:GAUGE:600:U:U'" .
				" 'RRA:AVERAGE:0.5:1:576'" .
				" 'RRA:AVERAGE:0.5:6:672'".
				" 'RRA:AVERAGE:0.5:24:732'" .
				" 'RRA:AVERAGE:0.5:144:1460'",
				true);
			write_log(sprintf('rrdgraphs service is collecting uptime statistics (%s)',$rrd_name));
		endif;
	endif;
	if(isset($config['rrdgraphs']['arc_usage'])):
		$rrd_name = 'zfs_arc.rrd';
		$rrd_fqfn = sprintf('%s/rrd/%s',$config['rrdgraphs']['storage_path'],$rrd_name);
		if(!is_file($rrd_fqfn)):
			$ret_val = mwexec("/usr/local/bin/rrdtool create " . escapeshellarg($rrd_fqfn) .
				" -s 300" .
				" 'DS:Total:GAUGE:600:U:U'" .
				" 'DS:MFU:GAUGE:600:U:U'" .
				" 'DS:MRU:GAUGE:600:U:U'" .
				" 'DS:Anon:GAUGE:600:U:U'" .
				" 'DS:Header:GAUGE:600:U:U'" .
				" 'DS:Other:GAUGE:600:U:U'" .
				" 'RRA:AVERAGE:0.5:1:576'" .
				" 'RRA:AVERAGE:0.5:6:672'" .
				" 'RRA:AVERAGE:0.5:24:732'" .
				" 'RRA:AVERAGE:0.5:144:1460'",
				true);
			write_log(sprintf('rrdgraphs service is collecting zfs arc statistics (%s)',$rrd_name));
		endif;
	endif;
	if(isset($config['rrdgraphs']['l2arc_usage'])):
		$rrd_name = 'zfs_l2arc.rrd';
		$rrd_fqfn = sprintf('%s/rrd/%s',$config['rrdgraphs']['storage_path'],$rrd_name);
		if(!is_file($rrd_fqfn)):
			$ret_val = mwexec("/usr/local/bin/rrdtool create " . escapeshellarg($rrd_fqfn) .
				" -s 300" .
				" 'DS:L2_SIZE:GAUGE:600:U:U'" .
				" 'DS:L2_ASIZE:GAUGE:600:U:U'" .
				" 'RRA:AVERAGE:0.5:1:576'" .
				" 'RRA:AVERAGE:0.5:6:672'" .
				" 'RRA:AVERAGE:0.5:24:732'" .
				" 'RRA:AVERAGE:0.5:144:1460'",
				true);
			write_log(sprintf('rrdgraphs service is collecting zfs l2arc statistics (%s)',$rrd_name));
		endif;
	endif;
	if(isset($config['rrdgraphs']['arc_efficiency'])):
		$rrd_name = 'zfs_arceff.rrd';
		$rrd_fqfn = sprintf('%s/rrd/%s',$config['rrdgraphs']['storage_path'],$rrd_name);
		if(!is_file($rrd_fqfn)):
			$ret_val = mwexec("/usr/local/bin/rrdtool create " . escapeshellarg($rrd_fqfn) .
				" -s 300" .
				" 'DS:EFF_ARC:GAUGE:600:0:U'" .
				" 'DS:EFF_DEMAND:GAUGE:600:0:U'" .
				" 'DS:EFF_PREFETCH:GAUGE:600:0:U'" .
				" 'DS:EFF_METADATA:GAUGE:600:0:U'" .
				" 'DS:EFF_L2ARC:GAUGE:600:0:U'" .
				" 'RRA:AVERAGE:0.5:1:576'" .
				" 'RRA:AVERAGE:0.5:6:672'" .
				" 'RRA:AVERAGE:0.5:24:732'" .
				" 'RRA:AVERAGE:0.5:144:1460'",
				true);
			write_log(sprintf('rrdgraphs service is collecting zfs cache efficiency statistics (%s)',$rrd_name));
		endif;
	endif;
//	create graphs
	$ret_val = mwexec("{$runtime_dir}/rrd-graph.sh",true);
endif;
write_config();
