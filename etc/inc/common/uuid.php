<?php
/*
	uuid.php

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

declare(strict_types = 1);

namespace common;

use Throwable;

/**
 *	Wrapper class for autoloading functions
 */
class uuid {
	public const pattern = '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i';
//	public const pattern_v3 = '/^[0-9a-f]{8}-[0-9a-f]{4}-3[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i';
	public const pattern_v4 = '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i';
//	public const pattern_v5 = '/^[0-9a-f]{8}-[0-9a-f]{4}-5[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i';

/**
 *	Create a version 3 UUID according to RFC 4122.
 *	time_low:            xxxx xxxx xxxx xxxx xxxx xxxx xxxx xxxx
 *	time_mid:            xxxx xxxx xxxx xxxx
 *	time_hi_and_version: 0011 xxxx xxxx xxxx
 *	clk_seq_hi_res:      10xx xxxx
 *	clk_seq_low:         xxxx xxxx
 *	node (0-1):          xxxx xxxx xxxx xxxx
 *	node (2-5):          xxxx xxxx xxxx xxxx xxxx xxxx xxxx xxxx
 *	@param string $namespace
 *	@param string $name
 *	@return string|null
	public static function create_v3(string $namespace,string $name): ?string {
		if(self::is($namespace)):
//			strip dashes from $namespace
			$namespace_stripped = str_replace('-','',$namespace);
			$namespace_transformed = '';
//			convert $namespace_stripped
			for($i = 0;$i < strlen($namespace_stripped);$i += 2):
				$namespace_transformed .= chr(hexdec($namespace_stripped[$i] . $namespace_stripped[$i + 1]));
			endfor;
			$hash = md5($namespace_transformed . $name);
			return sprintf('%08s-%04s-%04x-%04x-%12s',
				substr($hash,0,8),
				substr($hash,8,4),
				(hexdec(substr($hash,12,4)) & 0x0fff) | 0x3000,
				(hexdec(substr($hash,16,4)) & 0x3fff) | 0x8000,
				substr($hash,20,12)
			);
		endif;
		return null;
	}
 */
/**
 *	create a version 4 UUID according to RFC 4122.
 *	uses random_bytes, creates cryptographically secure pseudo-random bytes.
 *	time_low:            xxxx xxxx xxxx xxxx xxxx xxxx xxxx xxxx
 *	time_mid:            xxxx xxxx xxxx xxxx
 *	time_hi_and_version: 0100 xxxx xxxx xxxx
 *	clk_seq_hi_res:      10xx xxxx
 *	clk_seq_low:         xxxx xxxx
 *	node (0-1):          xxxx xxxx xxxx xxxx
 *	node (2-5):          xxxx xxxx xxxx xxxx xxxx xxxx xxxx xxxx
 *	@return string|null
 */
	public static function create_v4_prb(): ?string {
		try {
			$prb = random_bytes(16);
			$prbu = unpack('Ltime_low/Stime_mid/Stime_hi_and_version/Cclk_seq_hi_res/Cclk_seq_lo/Snode0-1/Lnode2-5',$prb);
			return sprintf('%08x-%04x-%04x-%02x%02x-%04x%08x',
				$prbu['time_low'],
				$prbu['time_mid'],
				($prbu['time_hi_and_version'] & 0x0fff) | 0x4000,
				($prbu['clk_seq_hi_res'] & 0x3f) | 0x80,$prbu['clk_seq_lo'],
				$prbu['node0-1'],$prbu['node2-5']);
		} catch(Throwable $ignore) {
//			random_bytes throws an exception when no appropriate source of randomness was found
			unset($ignore);
		}
		return null;
	}
/**
 *	create a version 4 UUID according to RFC 4122.
 *	uses openssl_random_pseudo_bytes, can create cryptographically secure pseudo-random bytes.
 *	time_low:            xxxx xxxx xxxx xxxx xxxx xxxx xxxx xxxx
 *	time_mid:            xxxx xxxx xxxx xxxx
 *	time_hi_and_version: 0100 xxxx xxxx xxxx
 *	clk_seq_hi_res:      10xx xxxx
 *	clk_seq_low:         xxxx xxxx
 *	node (0-1):          xxxx xxxx xxxx xxxx
 *	node (2-5):          xxxx xxxx xxxx xxxx xxxx xxxx xxxx xxxx
 *	@return string|null
 */
	public static function create_v4_orpb(): ?string {
		$prb = openssl_random_pseudo_bytes(16);
		if($prb !== false):
			$prbu = unpack('Ltime_low/Stime_mid/Stime_hi_and_version/Cclk_seq_hi_res/Cclk_seq_lo/Snode0-1/Lnode2-5',$prb);
			return sprintf('%08x-%04x-%04x-%02x%02x-%04x%08x',
				$prbu['time_low'],
				$prbu['time_mid'],
				($prbu['time_hi_and_version'] & 0x0fff) | 0x4000,
				($prbu['clk_seq_hi_res'] & 0x3f) | 0x80,$prbu['clk_seq_lo'],
				$prbu['node0-1'],$prbu['node2-5']);
		endif;
		return null;
	}
/**
 *	create a version 4 UUID according to RFC 4122.
 *	uses mt_rand, does not generate cryptographically secure values.
 *	time_low:            xxxx xxxx xxxx xxxx xxxx xxxx xxxx xxxx
 *	time_mid:            xxxx xxxx xxxx xxxx
 *	time_hi_and_version: 0100 xxxx xxxx xxxx
 *	clk_seq_hi_res:      10xx xxxx
 *	clk_seq_low:         xxxx xxxx
 *	node (0-1):          xxxx xxxx xxxx xxxx
 *	node (2-5):          xxxx xxxx xxxx xxxx xxxx xxxx xxxx xxxx
 *	@return string
 */
	public static function create_v4_rand(): string {
		return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
			mt_rand(0,0xffff),mt_rand(0,0xffff),
			mt_rand(0,0xffff),
			mt_rand(0,0x0fff) | 0x4000,
			mt_rand(0,0x3fff) | 0x8000,
			mt_rand(0,0xffff),mt_rand(0,0xffff),mt_rand(0,0xffff));
	}
/**
 *	Create a version 4 UUID according to RFC 4122.
 *	The following functions are called, the result of the first successful call is returned:
 *	1. random_bytes
 *	2. openssl_random_pseudo_bytes
 *	3. mt_rand
 *	@param bool $allow_insecure Allow to call mt_rand when no appropriate source of randomness was found
 *	@return string|null
 */
	public static function create_v4(bool $allow_insecure = true): ?string {
//		$uuid = null;
//		if(is_null($uuid)):
			$uuid = self::create_v4_prb();
//		endif;
		if(is_null($uuid)):
			$uuid = self::create_v4_orpb();
		endif;
		if(is_null($uuid) && $allow_insecure):
			$uuid = self::create_v4_rand();
		endif;
		return $uuid;
	}
/**
 *	Create a version 5 UUID according to RFC 4122.
 *	time_low:            xxxx xxxx xxxx xxxx xxxx xxxx xxxx xxxx
 *	time_mid:            xxxx xxxx xxxx xxxx
 *	time_hi_and_version: 0101 xxxx xxxx xxxx
 *	clk_seq_hi_res:      10xx xxxx
 *	clk_seq_low:         xxxx xxxx
 *	node (0-1):          xxxx xxxx xxxx xxxx
 *	node (2-5):          xxxx xxxx xxxx xxxx xxxx xxxx xxxx xxxx
 *	@param string $namespace
 *	@param string $name
 *	@return string|null
	public static function create_v5(string $namespace,string $name): ?string {
		if(self::is($namespace)):
//			strip dashes from $namespace
			$namespace_stripped = str_replace('-','',$namespace);
			$namespace_transformed = '';
//			convert $namespace_stripped
			for($i = 0;$i < strlen($namespace_stripped);$i += 2):
				$namespace_transformed .= chr(hexdec($namespace_stripped[$i] . $namespace_stripped[$i + 1]));
			endfor;
			$hash = sha1($namespace_transformed . $name);
			$retval = sprintf('%08s-%04s-%04x-%04x-%12s',
				substr($hash,0,8),
				substr($hash,8,4),
				(hexdec(substr($hash,12,4)) & 0x0fff) | 0x5000,
				(hexdec(substr($hash,16,4)) & 0x3fff) | 0x8000,
				substr($hash,20,12)
			);
		else:
			$retval = null;
		endif;
		return $retval;
	}
 */
/**
 *	Returns true if $uuid is a valid UUID.
 *	@param string $uuid Universal Unique Identifier
 *	@return bool returns true if the given string is a valid uuid.
 */
	public static function is(string $uuid): bool {
		return preg_match(self::pattern,$uuid) === 1;
	}
/**
 *	Returns true if $uuid is a valid version 3 UUID.
 *	@param string $uuid Universal Unique Identifier
 *	@return bool returns true if the given string is a valid uuid.
	public static function is_v3(string $uuid): bool {
		return preg_match(self::pattern_v3,$uuid) === 1;
	}
 */
/**
 *	Returns true if $uuid is a valid version 4 UUID.
 *	@param string $uuid Universal Unique Identifier
 *	@return bool returns true if the given string is a valid uuid.
 */
	public static function is_v4(string $uuid): bool {
		return preg_match(self::pattern_v4,$uuid) === 1;
	}
/**
 *	Returns true if $uuid is a valid version 5 UUID.
 *	@param string $uuid Universal Unique Identifier
 *	@return bool returns true if the given string is a valid uuid.
	public static function is_v5(string $uuid): bool {
		return preg_match(self::pattern_v5,$uuid) === 1;
	}
 */
}
