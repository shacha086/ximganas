<?php
/*
	fm_permissions.php

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

namespace filemanager;

use common\arr;
use common\session;

trait fm_permissions {
/**
 *	This function decides wether a specific action is allowed or not,
 *	depending the rights of the current user.
 *	@global array $config
 *	@param string|null $dir Directory in which the action should happen.
 *	@param string|null $file File on which the action should happen.
 *	@param string $action The name of the action which should be executed.
 *	@return boolean true if the action is granted, false otherwise
 *	@remarks Until now, the permission engine does not support directory or file
 *  based actions, so only the global actions are treated. The parameters
 *	$dir and $file are ignored. This is for later use. However, if possible,
 *	provide the $dir and $file parameters so the code does not have to be chaned
 *	if the permission engine will support this features in the future.
 */
	public function permissions_grant(?string $dir = null,?string $file = null,string $action = '') {
		global $config;

		$has_permission = false;
		if(session::is_admin()):
			$has_permission = true;
		else:
			$user = session::get_user_name();
			if($user !== false):
				$sphere = arr::make_branch($config,'access','user');
//				lookup user
				$sphere_rowid = arr::search_ex($user,$sphere,'login');
				if($sphere_rowid !== false):
					$sphere_row = $sphere[$sphere_rowid];
//					verify that user is permitted to use file manager
					$test = $sphere_row['fm_enable'] ?? false;
					$is_enabled = is_bool($test) ? $test : true;
					if($is_enabled):
						switch($action):
							case 'read':
								$test = $sphere_row['fmp_read'] ?? false;
								$has_permission = is_bool($test) ? $test : true;
								break;
							case 'create':
								$test = $sphere_row['fmp_create'] ?? false;
								$has_permission = is_bool($test) ? $test : true;
								break;
							case 'change':
								$test = $sphere_row['fmp_change'] ?? false;
								$has_permission = is_bool($test) ? $test : true;
								break;
							case 'delete':
								$test = $sphere_row['fmp_delete'] ?? false;
								$has_permission = is_bool($test) ? $test : true;
								break;
							case 'copy':
								$test = $sphere_row['fmp_read'] ?? false;
								$fmp_read = is_bool($test) ? $test : true;
								$test = $sphere_row['fmp_create'] ?? false;
								$fmp_create = is_bool($test) ? $test : true;
								$has_permission = $fmp_read && $fmp_create;
								break;
							case 'move':
								$test = $sphere_row['fmp_change'] ?? false;
								$has_permission = is_bool($test) ? $test : true;
								break;
						endswitch;
					endif;
				endif;
			endif;
		endif;
		return $has_permission;
	}
}
