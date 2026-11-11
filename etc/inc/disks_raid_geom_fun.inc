<?php
/*
	disks_raid_geom_fun.inc

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

use common\arr;

/**
 *	returns an array with additional geom raid information
 *	@return array
 */
function geomraid_processinfo_get() {
	$a_return = [
		'1' => [
			'type' =>  '1',
			'x-devdir' => '/dev/mirror',
			'x-notifier' => 'raid_gmirror',
			'x-processor' => 'gmirror_process_updatenotification',
			'x-page-maintenance' => 'disks_raid_gmirror_tools.php',
			'x-page-information' => 'disks_raid_gmirror_info.php',
			'x-button' => 'button_raid1',
			'gt-type' => gtext('RAID-1'),
			'show-create-button' => true
		],
		'0' => [
			'type' =>  '0',
			'x-devdir' => '/dev/stripe',
			'x-notifier' => 'raid_gstripe',
			'x-processor' => 'gstripe_process_updatenotification',
			'x-page-maintenance' => 'disks_raid_gstripe_tools.php',
			'x-page-information' => 'disks_raid_gstripe_info.php',
			'x-button' => 'button_raid0',
			'gt-type' => gtext('RAID-0'),
			'show-create-button' => true
		],
		'JBOD' => [
			'type' => 'JBOD',
			'x-devdir' => '/dev/concat',
			'x-notifier' => 'raid_gconcat',
			'x-processor' => 'gconcat_process_updatenotification',
			'x-page-maintenance' => 'disks_raid_gconcat_tools.php',
			'x-page-information' => 'disks_raid_gconcat_info.php',
			'x-button' => 'button_jbod',
			'gt-type' => gtext('JBOD'),
			'show-create-button' => true
		]
	];
	return $a_return;
}
/**
 *	Compiles an array containing all GEOM RAID volumes listed in config.xml
 *	Amends processing data
 *	@global array $config
 *	@param array $a_record
 */
function geomraid_config_get(&$a_record) {
	global $config;

	$a_record = [];
	arr::make_branch($config,'gconcat','vdisk');
	arr::make_branch($config,'gmirror','vdisk');
	arr::make_branch($config,'gstripe','vdisk');
	foreach($config['gconcat']['vdisk'] as $r_record):
		$r_record['balance'] = '';
		$a_record[] = $r_record;
	endforeach;
	foreach($config['gmirror']['vdisk'] as $r_record):
		$a_record[] = $r_record;
	endforeach;
	foreach($config['gstripe']['vdisk'] as $r_record):
		$r_record['balance'] = '';
		$a_record[] = $r_record;
	endforeach;
}
/**
 *	Writes all array information into the appropriate section in config.xml
 *	Strips processing data
 *	@global array $config
 *	@param array $a_record
 */
function geomraid_config_set(&$a_record) {
	global $config;

	$a_gconcat = [];
	$a_gconcat['vdisk'] = [];
	$a_gmirror = [];
	$a_gmirror['vdisk'] = [];
	$a_gstripe = [];
	$a_gstripe['vdisk'] = [];
	foreach($a_record as $r_record):
		$r_config = $r_record; // copy array information
		//	strip options
		switch($r_config['type']):
			case 'JBOD':
				unset($r_config['balance']);
				$a_gconcat['vdisk'][] = $r_config;
				break;
			case '1':
				$a_gmirror['vdisk'][] = $r_config;
				break;
			case '0':
				unset($r_config['balance']);
				$a_gstripe['vdisk'][] = $r_config;
				break;
		endswitch;
	endforeach;
	unset($config['gconcat']);
	$config['gconcat'] = $a_gconcat;
	unset($config['gmirror']);
	$config['gmirror'] = $a_gmirror;
	unset($config['gstripe']);
	$config['gstripe'] = $a_gstripe;
}
function is_geomraid_mounted($devicespecialfile,&$a_mount) {
	return (false !== arr::search_ex($devicespecialfile,$a_mount,'mdisk'));
}
/**
 *	GEOM CONCAT process update notifications
 *	@global array $config
 *	@param const $mode
 *	@param uuid $data
 *	@return int
 */
function gconcat_process_updatenotification($mode,$data) {
	global $config;

	$retval = 0;
	arr::make_branch($config,'gconcat','vdisk');
	switch($mode):
		case UPDATENOTIFY_MODE_NEW:
			$retval |= rc_exec_service('geom load concat');
			$retval |= disks_raid_gconcat_configure($data);
			break;
		case UPDATENOTIFY_MODE_MODIFIED:
			$retval |= rc_exec_service('geom start concat');
			break;
		case UPDATENOTIFY_MODE_DIRTY:
			$retval |= disks_raid_gconcat_delete($data);
		case UPDATENOTIFY_MODE_DIRTY_CONFIG:
			$index = arr::search_ex($data,$config['gconcat']['vdisk'],'uuid');
			if(false !== $index):
				unset($config['gconcat']['vdisk'][$index]);
				write_config();
			endif;
			break;
	endswitch;
	return $retval;
}
/**
 *	GEOM MIRROR process update notifications
 *	@global array $config
 *	@param const $mode
 *	@param uuid $data
 *	@return int
 */
function gmirror_process_updatenotification($mode,$data) {
	global $config;

	$retval = 0;
	arr::make_branch($config,'gmirror','vdisk');
	switch($mode):
		case UPDATENOTIFY_MODE_NEW:
			$retval |= rc_exec_service('geom load mirror');
			$retval |= disks_raid_gmirror_configure($data);
			break;
		case UPDATENOTIFY_MODE_MODIFIED:
			$retval |= rc_exec_service('geom start mirror');
			break;
		case UPDATENOTIFY_MODE_DIRTY:
			$retval |= disks_raid_gmirror_delete($data);
		case UPDATENOTIFY_MODE_DIRTY_CONFIG:
			$index = arr::search_ex($data, $config['gmirror']['vdisk'], 'uuid');
			if(false !== $index):
				unset($config['gmirror']['vdisk'][$index]);
				write_config();
			endif;
			break;
	endswitch;
	return $retval;
}
/**
 *	GEOM STRIPE process update notifications
 *	@global array $config
 *	@param const $mode
 *	@param uuid $data
 *	@return int
 */
function gstripe_process_updatenotification($mode,$data) {
	global $config;

	$retval = 0;
	arr::make_branch($config,'gstripe','vdisk');
	switch($mode):
		case UPDATENOTIFY_MODE_NEW:
			$retval |= rc_exec_service('geom load stripe');
			$retval |= disks_raid_gstripe_configure($data);
			break;
		case UPDATENOTIFY_MODE_MODIFIED:
			$retval |= rc_exec_service('geom start stripe');
			break;
		case UPDATENOTIFY_MODE_DIRTY:
			$retval |= disks_raid_gstripe_delete($data);
		case UPDATENOTIFY_MODE_DIRTY_CONFIG:
			$index = arr::search_ex($data,$config['gstripe']['vdisk'],'uuid');
			if(false !== $index):
				unset($config['gstripe']['vdisk'][$index]);
				write_config();
			endif;
			break;
	endswitch;
	return $retval;
}
