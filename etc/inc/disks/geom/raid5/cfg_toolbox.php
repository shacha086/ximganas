<?php
/*
	cfg_toolbox.php

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

namespace disks\geom\raid5;

use common\arr;

use function is_string;

/**
 *	Wrapper class for autoloading functions
 */
class cfg_toolbox {
/**
 *	Returns the graid5 name of $uuid or null.
 *	@global array $config The global config file.
 *	@param string $uuid UUID of the graid5.
 *	@return string|null graid5 name.
 */
	public static function name_of_uuid(string $uuid): ?string {
		global $config;

		$entity_name = null;
		$sphere_array = &arr::make_branch($config,'graid5','vdisk');
		$sphere_rowid = arr::search_ex($uuid,$sphere_array,'uuid');
		if($sphere_rowid !== false):
			$sphere_record = $sphere_array[$sphere_rowid];
			$sr_name = $sphere_record['name'] ?? null;
			if(isset($sr_name) && is_string($sr_name)):
				$entity_name = $sr_name;
			endif;
		endif;
		return $entity_name;
	}
}
