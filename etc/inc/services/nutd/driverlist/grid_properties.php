<?php
/*
	grid_properties.php

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

namespace services\nutd\driverlist;

use common\properties as myp;

use function gettext;

class grid_properties extends myp\container {
	protected $x_manufacturer;
	public function init_manufacturer(): myp\property_text {
		$property = $this->x_manufacturer = new myp\property_text($this);
		$property->
			set_name('manufacturer')->
			set_title(gettext('Manufacturer'));
		return $property;
	}
	final public function get_manufacturer(): myp\property_text {
		return $this->x_manufacturer ?? $this->init_manufacturer();
	}
	protected $x_modelname;
	public function init_modelname(): myp\property_text {
		$property = $this->x_modelname = new myp\property_text($this);
		$property->
			set_name('modelname')->
			set_title(gettext('Model Name'));
		return $property;
	}
	final public function get_modelname(): myp\property_text {
		return $this->x_modelname ?? $this->init_modelname();
	}
	protected $x_modelextra;
	public function init_modelextra(): myp\property_text {
		$property = $this->x_modelextra = new myp\property_text($this);
		$property->
			set_name('modelextra')->
			set_title(gettext('Model Extra'));
		return $property;
	}
	final public function get_modelextra(): myp\property_text {
		return $this->x_modelextra ?? $this->init_modelextra();
	}
	protected $x_driver;
	public function init_driver(): myp\property_text {
		$property = $this->x_driver = new myp\property_text($this);
		$property->
			set_name('driver')->
			set_title(gettext('Driver'));
		return $property;
	}
	final public function get_driver(): myp\property_text {
		return $this->x_driver ?? $this->init_driver();
	}
}
