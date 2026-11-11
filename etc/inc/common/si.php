<?php
/*
	si.php

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

class si {
/**
 *	Determines the use of binary prefixes instead of decimal prefixes.
 *	@global array $config
 *	@return bool Returns true when SI (base 1000) should be used, false if not.
 */
	public static function is_sidisksizevalues(): bool {
		global $config;

		$test = $config['system']['webgui']['nonsidisksizevalues'] ?? false;
		return is_bool($test) ? !$test : false;
	}
/**
 *	Converts an integer or an integer string into a human readable format for unit byte.
 *	@param int/string $value Value to be converted into a human readable format.
 *	@param int $precision The number of decimal digits. Default is 2.
 *	@param bool $fixedformat Flag to format the returned string with the number of decimals given by $precision. Default is true.
 *	@param bool $use_si Use SI divisor (1000) or binary divisor (1024). Default is true.
 *	@return string Human readable version of bytes.
 */
	public static function format_bytes($number,int $precision = 2,bool $fixedformat = true,bool $use_si = true) {
		if($number < 0):
			$number = 0;
		endif;
		$decimals = filter_var($precision,FILTER_VALIDATE_INT,['options' => ['default' => 2,'min_range' => 0]]);
		if($use_si):
//			si, 1000
			$unit_grid = ['B','kB','MB','GB','TB','PB','EB','ZB','YB'];
			$unit_overflow = '#B';
			$divisor = 1000;
		else:
//			non-si, 1024
			$unit_grid = ['B','KiB','MiB','GiB','TiB','PiB','EiB','ZiB','YiB'];
			$unit_overflow = '#iB';
			$divisor = 1024;
		endif;
		for($laps = 0;bccomp($number,$divisor) >= 0;$laps++):
			$number = bcdiv($number,$divisor,$decimals);
		endfor;
		return sprintf($fixedformat ? '%.' . $decimals . 'f%s' : '%s%s',(float)$number,$unit_grid[$laps] ?? $unit_overflow);
	}
}
