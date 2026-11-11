<?php
/*
	container_row.php

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

/**
 *	Class for row properties
 */
abstract class container_row extends container {
	protected $x_uuid;
	public function init_uuid(): property_uuid {
		$property = $this->x_uuid = new property_uuid($this);
		return $property;
	}
	final public function get_uuid(): property_uuid {
		return $this->x_uuid ?? $this->init_uuid();
	}
	protected $x_description;
	public function init_description(): property_description {
		$property = $this->x_description = new property_description($this);
		return $property;
	}
	final public function get_description(): property_description {
		return $this->x_description ?? $this->init_description();
	}
	protected $x_enable;
	public function init_enable(): property_enable {
		$property = $this->x_enable = new property_enable($this);
		return $property;
	}
	final public function get_enable(): property_enable {
		return $this->x_enable ?? $this->init_enable();
	}
	protected $x_protected;
	public function init_protected(): property_protected {
		$property = $this->x_protected = new property_protected($this);
		return $property;
	}
	final public function get_protected(): property_protected {
		return $this->x_protected ?? $this->init_protected();
	}
	protected $x_toolbox;
	public function init_toolbox(): property_toolbox {
		$property = $this->x_toolbox = new property_toolbox($this);
		return $property;
	}
	final public function get_toolbox(): property_toolbox {
		return $this->x_toolbox ?? $this->init_toolbox();
	}
	public function get_row_identifier():property_uuid {
		return $this->get_uuid();
	}
}
