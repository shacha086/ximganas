<?php
/*
	lang.php

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

use Collator;
use DirectoryIterator;
use Locale;

class lang {
	private const PATH = '/usr/local/share/locale';

/**
 *	Function to read the locale folder names and return keys 'as is' and values in lowercase
 *	@return array
 */
	public static function get_list_loval(): array {
		$list = [
			'auto' => 'auto',
			'en_US' => 'en_us'
		];
		$dirite = new DirectoryIterator(self::PATH);
		foreach($dirite as $diriterow):
			if($diriterow->isDir() && !$diriterow->isDot()):
				$key = $diriterow->getFilename();
				$list[$key] = mb_strtolower($key);
			endif;
		endforeach;
		return($list);
	}
/**
 *	Function to read the locale folder names and return keys in lowercase and values 'as is'
 *	@return array
 */
	public static function get_list_lokey(): array {
		$list = [
			'auto' => 'auto',
			'en_us' => 'en_US'
		];
		$dirite = new DirectoryIterator(self::PATH);
		foreach($dirite as $diriterow):
			if($diriterow->isDir() && !$diriterow->isDot()):
				$key = $diriterow->getFilename();
				$list[mb_strtolower($key)] = $key;
			endif;
		endforeach;
		return($list);
	}
/**
 *	Function to calculate WebGUI language options
 *	Reads the locale folder names and returns a list of language display names in their local language.
 *	The list is amended with the default key 'en_US'.
 *	A leading key 'auto' => gettext('Autodetect') is added.
 *	The list is sorted using UCA standard,.
 *	@return array
 */
	public static function get_options(): array {
		$auto = [
			'auto' => gettext('Autodetect')
		];
		$list = [
			'en_US' => Locale::getDisplayName('en_US','en_US')
		];
		$dirite = new DirectoryIterator(self::PATH);
		foreach($dirite as $diriterow):
			if($diriterow->isDir() && !$diriterow->isDot()):
				$key = $diriterow->getFilename();
				$list[$key] = Locale::getDisplayName($key,$key);
			endif;
		endforeach;
		$uca = new Collator('root');
		$uca->asort($list);
		$options = array_merge($auto,$list);
		return($options);
	}
}
