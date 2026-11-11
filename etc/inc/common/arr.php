<?php
/*
	arr.php

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

namespace common;

/**
 *	Wrapper class for autoloading functions
 */
class arr {
/**
 *	Search in a multidimensional array.
 *	@param mixed $needle String or array of needles to search for.
 *	@param array $haystack The array to search.
 *	@param mixed $key String or array of keys to search.
 *	@return mixed Key on success or false on failure.
 */
	public static function search_ex($needle,$haystack,$key) {
		if(is_array($haystack) && $needle !== '' && $key !== ''):
			foreach($haystack as $haystackval => $value):
				$found = false;
				if(is_array($needle) && is_array($key)):
					foreach($needle as $n => $needlev):
						$found = ($value[$key[$n]] === $needlev);
						if(!$found):
							break;
						endif;
					endforeach;
				elseif(!isset($value[$key])):
//					$found = false;
				elseif(is_array($value[$key])):
					$found = in_array($needle,$value[$key]);
				else:
					$found = ($value[$key] === $needle);
				endif;
				if($found):
					return $haystackval;
				endif;
			endforeach;
		endif;
		return false;
	}
/**
 *	Sort an array by values using a user-defined key.
 *	@param array $array The array to sort.
 *	@param string $key The key used as sort criteria.
 *	@return boolean true on success or false on failure.
 */
	public static function sort_key(array &$array,$key) {
		if(empty($array) || ($key === '')):
			$result = true;
		else:
			$result = uasort($array,fn($a,$b) => isset($a[$key]) ? (isset($b[$key]) ? strnatcmp($a[$key],$b[$key]) : 1) : (isset($b[$key]) ? -1 : 0));
		endif;
		return $result;
	}
/**
 *	Create a chain of arrays in a given array if it doesn't exist.
 *	make_branch($config,'zfs','datasets','dataset') will create
 *	- $config['zfs'] = [] if zfs doesn't exist or is not an array.
 *	- $config['zfs']['datasets'] = [] if datasets doesn't exist or is not an array.
 *	- $config['zfs']['datasets']['dataset'] = [] if dataset doesn't exist or is not an array.
 *	- the last leaf of the chain is returned as reference.
 *	@param array $branch
 *	@param string $leaves
 *	@return array
 */
	public static function &make_branch(array &$branch,string ...$leaves) {
		$active_leaf = &$branch;
		foreach($leaves as $leaf):
			if(!(array_key_exists($leaf,$active_leaf) && is_array($active_leaf[$leaf]))):
				$active_leaf[$leaf] = [];
			endif;
			$next_leaf = &$active_leaf[$leaf];
			unset($active_leaf);
			$active_leaf = &$next_leaf;
			unset($next_leaf);
		endforeach;
		return $active_leaf;
	}
}
