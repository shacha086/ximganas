<?php
/*
	cli_toolbox.php

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

namespace disks\zfs\zpool;

use function mwexec2;

/**
 *	Wrapper class for autoloading functions
 */
class cli_toolbox {
/**
 *	Returns basic properties of a single zfs zpool or all zfs zpools.
 *	@param string $entity_name If provided, only basic information of this specific zfs zpool is returned.
 *	@return string An unescaped string.
 */
	public static function get_list(?string $entity_name = null): string {
		$a_cmd = ['zpool','list','-o','name,size,alloc,free,expandsz,frag,cap,dedup,health,altroot,guid'];
		if(isset($entity_name)):
			$a_cmd[] = escapeshellarg($entity_name);
		endif;
		$a_cmd[] = '2>&1';
		$cmd = implode(' ',$a_cmd);
		mwexec2($cmd,$output);
		return implode("\n",$output);
	}
/**
 *	Returns all properties of a single zfs zpool or all zfs zpools.
 *	@param string $entity_name If provided, only the properties of this specific zfs zpool are returned.
 *	@return string An unescaped string.
 */
	public static function get_properties(?string $entity_name = null): string {
		$a_cmd = ['zpool','list','-H','-o','name'];
		if(isset($entity_name)):
			$a_cmd[] = escapeshellarg($entity_name);
		endif;
		$a_cmd[] = '2>&1';
		$cmd = implode(' ',$a_cmd);
		mwexec2($cmd,$a_names,$exitstatus);
		if($exitstatus === 0 && is_array($a_names) && count($a_names) > 0):
			$names = implode(' ',array_map('escapeshellarg',$a_names));
			$cmd = sprintf('zpool get all %s 2>&1',$names);
			mwexec2($cmd,$output);
		else:
			$output = [gettext('No ZFS zpool information available.')];
		endif;
		return implode("\n",$output);
	}
/**
 *	Returns the status of a single zfs zpool or all zfs zpools.
 *	@return string An unescaped string.
 */
	public static function get_status(?string $pool = null): string {
		$a_cmd = ['zpool','status','-v','-T','d'];
		if(isset($pool)):
			$a_cmd[] = escapeshellarg($pool);
		endif;
		$a_cmd[] = '2>&1';
		$cmd = implode(' ',$a_cmd);
		mwexec2($cmd,$output);
		return implode("\n",$output);
	}
/**
 *	Returns a list of pool names
 *	@return array
 */
	public static function get_list_of_pools(): array {
		$cmd_parameters = ['zpool','list','-H','-o','name'];
		$cmd = implode(' ',$cmd_parameters);
		mwexec2($cmd,$poolnames,$exitstatus);
		if($exitstatus === 0):
			return $poolnames;
		endif;
		return [];
	}
	public static function get_pool_info() {
		$sphere = [];
		$whatineed = 'name,size,allocated,expandsize,fragmentation,dedupratio,health,altroot,guid';
		$pools_info = static::info_zpool(null,$whatineed);
		if(!empty($pools_info)):
			foreach($pools_info as $pool_info):
	//			map names of zpool list -o options to local options
				$pool_name = $pool_info['name'];
				$row = [];
				$row['name'] = $pool_name;
				$row['size'] = is_numeric($pool_info['size']) ? $pool_info['size'] : 0;
				$row['alloc'] = is_numeric($pool_info['allocated']) ? $pool_info['allocated'] : 0;
				$row['free'] = $row['size'] - $row['alloc'];
				$row['expandsz'] = $pool_info['expandsize'];
				$row['frag'] = $pool_info['fragmentation'];
				$row['cap'] = $row['size'] > 0 ? $row['alloc'] / $row['size'] * 100 : 0;
				$row['dedup'] = $pool_info['dedupratio'];
				$row['health'] = $pool_info['health'];
				$row['altroot'] = $pool_info['altroot'];
				$row['guid'] = $pool_info['guid'];
				$a_zfs = static::info_zfs($pool_name,'name,used,available');
				if(empty($a_zfs)):
					$row['used'] = -1;
					$row['avail'] = -1;
				else:
					foreach($a_zfs as $r_zfs):
	//					there should be only one row
						$row['used'] = $r_zfs['used'];
						$row['avail'] = $r_zfs['available'];
					endforeach;
				endif;
				$sphere[$pool_name] = $row;
			endforeach;
		endif;
		return $sphere;
	}
/**
 *	Get zpool information from cli zpool command.
 *	If you don't specify zpool properties, the preset zpool properties will be returned.
 *	If you specify zpool properties, they must be separated with a comma.
 *	Property names returned are lowercase.
 *	The function returns an empty array on error or when the zpool is unknown to zfs.
 *	@param string $poolname The name of the pool or null.
 *	@param string $properties A comma separated list of zpool properties.
 *	@return array Array with zpool properties of all zpools or the given zpool.
 */
	public static function info_zpool(?string $pool = null,?string $properties = null) {
//		initialize return value
		$sphere = [];
		$cmd_parameters = ['zpool','get','-Hp'];
		if(is_null($properties)):
			$cmd_parameters[] = 'all';
		else:
			$cmd_parameters[] = escapeshellarg($properties);
		endif;
		if(!is_null($pool)):
			$cmd_parameters[] = escapeshellarg($pool);
		endif;
		$cmd = implode(' ',$cmd_parameters);
		$cmd_output = [];
		$exitstatus = 0;
		mwexec2($cmd,$cmd_output,$exitstatus);
		if($exitstatus === 0):
			foreach($cmd_output as $cmd_output_row):
//				each row contains 4 values
				[$name,$property,$value,$source] = explode("\t",$cmd_output_row);
				$sphere[$name][$property] = $value;
			endforeach;
		endif;
		return $sphere;
	}
/**
 *	Get zfs properties information of a pool from cli
 *	If you don't specify properties, the default properties will be returned.
 *	If you specify properties they must be separated with a comma.
 *	Property keys are returned in lowercase.
 *	The function returns an empty array on error or when the zpool is unknown to zfs.
 *	@param string $poolname
 *	@param string $properties
 *	@return array Array with zfs properties of all zpools
 */
	public static function info_zfs(?string $dataset = null,?string $properties = null) {
		$sphere = [];
		$cmd_parameters = ['zfs','get','-Hp'];
		if(is_null($properties)):
			$cmd_parameters[] = 'all';
		else:
			$cmd_parameters[] = escapeshellarg($properties);
		endif;
		if(!is_null($dataset)):
			$cmd_parameters[] = escapeshellarg($dataset);
		endif;
		$cmd = implode(' ',$cmd_parameters);
		$cmd_output = [];
		$exitstatus = 0;
		mwexec2($cmd,$cmd_output,$exitstatus);
		if($exitstatus === 0):
			foreach($cmd_output as $cmd_output_row):
//				each row contains 4 values
				[$name,$property,$value,$source] = explode("\t",$cmd_output_row);
				$sphere[$name][$property] = $value;
			endforeach;
		endif;
		return $sphere;
	}
}
