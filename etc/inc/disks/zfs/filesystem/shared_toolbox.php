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

namespace disks\zfs\filesystem;

use common\arr;
use common\sphere as mys;
use DOMDocument;

use const UPDATENOTIFY_MODE_DIRTY;
use const UPDATENOTIFY_MODE_DIRTY_CONFIG;
use const UPDATENOTIFY_MODE_MODIFIED;
use const UPDATENOTIFY_MODE_NEW;

use function mwexec;
use function updatenotify_clear;

/**
 *	Wrapper class for autoloading functions
 */
class shared_toolbox {
	private const NOTIFICATION_NAME = __NAMESPACE__;
	private const NOTIFICATION_PROCESSOR = 'process_notification';
	private const ROW_IDENTIFIER = 'guid';
/**
 *	Process notifications
 *	@param int $mode
 *	@param string $data
 *	@return int
 */
	public static function process_notification(int $mode,string $data) {
		global $config;

		$retval = 0;
		$sphere = grid_toolbox::init_sphere();
		$cop = grid_toolbox::init_properties();
		$sphere->row_id = arr::search_ex($data,$sphere->grid,$sphere->get_row_identifier());
		if($sphere->row_id !== false):
			switch($mode):
				case UPDATENOTIFY_MODE_NEW:
				case UPDATENOTIFY_MODE_MODIFIED:
					break;
				case UPDATENOTIFY_MODE_DIRTY_CONFIG:
					break;
				case UPDATENOTIFY_MODE_DIRTY:
//					destroy dataset
					$sphere->row = $sphere->grid[$sphere->row_id];
					$pool_name = $sphere->row[$cop->get_pool_name()->get_name()];
					$dataset_name = $sphere->row[$cop->get_dataset_name()->get_name()];
					$fq_filesystem_name = sprintf('%s/%s',$pool_name,$dataset_name);
//					check if extended destroy is enabled
					$test = $config['disks']['zfs']['settings']['destroy_dependents'] ?? false;
					$extended_destroy = is_bool($test) ? $test : true;
					if($extended_destroy):
//						destroy dataset and its dependents
						$a_command = [
							'zfs',
							'destroy',
							'-fr',
							escapeshellarg($fq_filesystem_name)
						];
/*
//						destroy dataset and its dependents
						$fqfn_lua_script = '/etc/inc/disks/zfs/api/destroy_branch.lua';
						$a_command = [
							'zfs',
							'program',
							escapeshellarg($pool_name),
							escapeshellarg($fqfn_lua_script),
							escapeshellarg($fq_filesystem_name)
						];
 */
					else:
//						destroy dataset
						$a_command = [
							'zfs',
							'destroy',
							'-f',
							escapeshellarg($fq_filesystem_name)
						];
					endif;
					$command = implode(' ',$a_command);
					$retval |= mwexec($command,true);
					break;
			endswitch;
		endif;
		updatenotify_clear($sphere->get_notifier(),$data);
		return $retval;
	}
/**
 *	Configure shared sphere settings
 *	@global array $config
 *	@param mys\root $sphere
 */
	public static function init_sphere(mys\root $sphere) {
		$sphere->
			set_notifier(self::NOTIFICATION_NAME)->
			set_notifier_processor(sprintf('%s::%s',self::class,self::NOTIFICATION_PROCESSOR))->
			set_row_identifier(self::ROW_IDENTIFIER)->
			set_enadis(false)->
			set_lock(false)->
			add_page_title(gettext('Disks'),gettext('ZFS'),gettext('Filesystem'));
/*
		zfs get -Hp -d 0 -t filesystem -o all all [dataset]
 */
	}
/**
 *	Add the tab navigation menu of this sphere
 *	@param DOMDocument $document
 *	@return int
 */
	public static function add_tabnav(DOMDocument $document) {
		$retval = 0;
		$document->
			add_area_tabnav()->
				push()->
				add_tabnav_upper()->
					ins_tabnav_record('disks_zfs_zpool.php',gettext('Pools'))->
					ins_tabnav_record('disks_zfs_filesystem.php',gettext('Filesystems'),gettext('Reload page'),true)->
					ins_tabnav_record('disks_zfs_volume.php',gettext('Volumes'))->
					ins_tabnav_record('disks_zfs_snapshot.php',gettext('Snapshots'))->
					ins_tabnav_record('disks_zfs_scheduler_snapshot_create.php',gettext('Scheduler'))->
					ins_tabnav_record('disks_zfs_config.php',gettext('Configuration'))->
					ins_tabnav_record('disks_zfs_settings.php',gettext('Settings'))->
				pop()->
				add_tabnav_lower()->
					ins_tabnav_record('disks_zfs_filesystem.php',gettext('Filesystem'),gettext('Reload page'),true)->
					ins_tabnav_record('disks_zfs_dataset_info.php',gettext('Information'));
		return $retval;
	}
}
