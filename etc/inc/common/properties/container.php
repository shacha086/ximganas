<?php
/*
	container.php

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

namespace common\properties;

use ReflectionMethod;

/**
 *	Container for property collection
 */
abstract class container {
	public function __construct() {
//		$this->reset();
	}
/**
 *	Lazy call of a public get_ method using a non-existing property.
 *	__get() is utilized for reading data from inaccessible (protected or private) or non-existing properties.
 *	@param string $name the name of the property
 *	@return mixed Result of the method
 */
	public function __get(string $name) {
		$method_name = 'get_' . $name;
		if(method_exists($this,$method_name)):
			$reflection = new ReflectionMethod($this,$method_name);
			if($reflection->isPublic()):
				return $this->{$method_name}();
			endif;
		endif;
	}
	public function __destruct() {
//		$this->reset();
	}
/**
 *	Unsets all 'x_*' properties
 *	@return $this
 */
	public function reset() {
		foreach($this as $key => $value):
			if(strncmp($key,'x_',2) === 0):
				unset($this->$key);
			endif;
		endforeach;
		return $this;
	}
/**
 *	calls get method for each x_ property found
 *	@return array Array containing initialized objects
 */
/*
	public function init_all() {
		$a_objects = [];
		foreach($this as $key => $value):
			unset($matches);
			if(preg_match('/^x_(.+)/',$key,$matches) === 1):
				$method_name = 'get_' . $matches[1];
				if(method_exists($this,$method_name)):
					$a_objects[] = $this->$method_name();
				endif;
			endif;
		endforeach;
		return $a_objects;
 	}
 */
}
