<?php
/*
	shared_toolbox.php

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

namespace status\monitor;

use common\arr;
use DOMDocument;

class shared_toolbox {
	public const RRD_SYSTEM_LOAD = 1;
	public const RRD_CPU_LOAD = 2;
	public const RRD_CPU_FREQUENCY = 3;
	public const RRD_CPU_TEMPERATURE = 4;
	public const RRD_CPU_USAGE = 5;
	public const RRD_LOAD_AVERAGES = 6;
	public const RRD_DISK_USAGE = 7;
	public const RRD_MEMORY_USAGE = 8;
	public const RRD_NETWORK_LATENCY = 9;
	public const RRD_NETWORK_LOAD = 10;
	public const RRD_NO_PROCESSES = 11;
	public const RRD_UPS = 12;
	public const RRD_UPTIME = 13;
	public const RRD_ARC_USAGE = 14;
	public const RRD_L2ARC_USAGE = 15;
	public const RRD_ARC_EFFICIENCY = 16;

	/**
 *	Add tab navigation menu
 *	@global array $config
 *	@param DOMDocument $document
 *	@param int $selected
 *	@return int
 */
	public static function add_tabnav(DOMDocument $document,int $selected) {
		global $config;

		$retval = 0;
		$gt_reload_page = gettext('Reload Page');
		$tabnav = $document->
			add_area_tabnav()->
				add_tabnav_upper();
		$itsme = $selected === static::RRD_SYSTEM_LOAD;
		$tabnav->ins_tabnav_record('status_graph.php',gettext('System Load'),$itsme ? $gt_reload_page : '',$itsme);
		$itsme = $selected === static::RRD_CPU_LOAD;
		$tabnav->ins_tabnav_record('status_graph_cpu.php',gettext('CPU Load'),$itsme ? $gt_reload_page : '',$itsme);
		$grid = arr::make_branch($config,'rrdgraphs');
		if(isset($grid['enable'])):
			if(isset($grid['cpu_frequency'])):
				$itsme = $selected === static::RRD_CPU_FREQUENCY;
				$tabnav->ins_tabnav_record('status_graph_cpu_freq.php',gettext('CPU Frequency'),$itsme ? $gt_reload_page : '',$itsme);
			endif;
			if(isset($grid['cpu_temperature'])):
				$itsme = $selected === static::RRD_CPU_TEMPERATURE;
				$tabnav->ins_tabnav_record('status_graph_cpu_temp.php',gettext('CPU Temperature'),$itsme ? $gt_reload_page : '',$itsme);
			endif;
			if(isset($grid['cpu'])):
				$itsme = $selected === static::RRD_CPU_USAGE;
				$tabnav->ins_tabnav_record('status_graph_cpu_usage.php',gettext('CPU Usage'),$itsme ? $gt_reload_page : '',$itsme);
			endif;
			if(isset($grid['load_averages'])):
				$itsme = $selected === static::RRD_LOAD_AVERAGES;
				$tabnav->ins_tabnav_record('status_graph_load_averages.php',gettext('Load Averages'),$itsme ? $gt_reload_page : '',$itsme);
			endif;
			if(isset($grid['disk_usage'])):
				$itsme = $selected === static::RRD_DISK_USAGE;
				$tabnav->ins_tabnav_record('status_graph_disk_usage.php',gettext('Disk Usage'),$itsme ? $gt_reload_page : '',$itsme);
			endif;
			if(isset($grid['memory_usage'])):
				$itsme = $selected === static::RRD_MEMORY_USAGE;
				$tabnav->ins_tabnav_record('status_graph_memory.php',gettext('Memory Usage'),$itsme ? $gt_reload_page : '',$itsme);
			endif;
			if(isset($grid['latency'])):
				$itsme = $selected === static::RRD_NETWORK_LATENCY;
				$tabnav->ins_tabnav_record('status_graph_latency.php',gettext('Network Latency'),$itsme ? $gt_reload_page : '',$itsme);
			endif;
			if(isset($grid['lan_load'])):
				$itsme = $selected === static::RRD_NETWORK_LOAD;
				$tabnav->ins_tabnav_record('status_graph_network.php',gettext('Network Traffic'),$itsme ? $gt_reload_page : '',$itsme);
			endif;
			if(isset($grid['no_processes'])):
				$itsme = $selected === static::RRD_NO_PROCESSES;
				$tabnav->ins_tabnav_record('status_graph_processes.php',gettext('Processes'),$itsme ? $gt_reload_page : '',$itsme);
			endif;
			if(isset($grid['ups'])):
				$itsme = $selected === static::RRD_UPS;
				$tabnav->ins_tabnav_record('status_graph_ups.php',gettext('UPS'),$itsme ? $gt_reload_page : '',$itsme);
			endif;
			if(isset($grid['uptime'])):
				$itsme = $selected === static::RRD_UPTIME;
				$tabnav->ins_tabnav_record('status_graph_uptime.php',gettext('Uptime'),$itsme ? $gt_reload_page : '',$itsme);
			endif;
			if(isset($grid['arc_usage'])):
				$itsme = $selected === static::RRD_ARC_USAGE;
				$tabnav->ins_tabnav_record('status_graph_zfs_arc.php',gettext('ZFS ARC'),$itsme ? $gt_reload_page : '',$itsme);
			endif;
			if(isset($grid['l2arc_usage'])):
				$itsme = $selected === static::RRD_L2ARC_USAGE;
				$tabnav->ins_tabnav_record('status_graph_zfs_l2arc.php',gettext('ZFS L2ARC'),$itsme ? $gt_reload_page : '',$itsme);
			endif;
			if(isset($grid['arc_efficiency'])):
				$itsme = $selected === static::RRD_ARC_EFFICIENCY;
				$tabnav->ins_tabnav_record('status_graph_zfs_arceff.php',gettext('ZFS Cache Efficiency'),$itsme ? $gt_reload_page : '',$itsme);
			endif;
		endif;
		return $retval;
	}
}
