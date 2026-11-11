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

namespace disks\zfs\setting;

use common\properties as myp;

class grid_properties extends myp\container {
	protected $x_showusedavail;
	public function init_showusedavail(): myp\property_bool {
		$title = gettext('Show Used/Avail');
		$property = $this->x_showusedavail = new myp\property_bool($this);
		$property->
			set_name('showusedavail')->
			set_title($title);
		return $property;
	}
	final public function get_showusedavail(): myp\property_bool {
		return $this->x_showusedavail ?? $this->init_showusedavail();
	}
	protected $x_scanondisk;
	public function init_scanondisk(): myp\property_bool {
		$title = gettext('On-Disk Configuration');
		$property = $this->x_scanondisk = new myp\property_bool($this);
		$property->
			set_name('scanondisk')->
			set_title($title);
		return $property;
	}
	final public function get_scanondisk(): myp\property_bool {
		return $this->x_scanondisk ?? $this->init_scanondisk();
	}
	protected $x_capacity_warning;
	public function init_capacity_warning(): myp\property_int {
		$title = gettext('Capacity Warning Threshold');
		$property = $this->x_capacity_warning = new myp\property_int($this);
		$property->
			set_name('capacity_warning')->
			set_title($title);
		return $property;
	}
	final public function get_capacity_warning(): myp\property_int {
		return $this->x_capacity_warning ?? $this->init_capacity_warning();
	}
	protected $x_capacity_critical;
	public function init_capacity_critical(): myp\property_int {
		$title = gettext('Capacity Critical Threshold');
		$property = $this->x_capacity_critical = new myp\property_int($this);
		$property->
			set_name('capacity_critical')->
			set_title($title);
		return $property;
	}
	final public function get_capacity_critical(): myp\property_int {
		return $this->x_capacity_critical ?? $this->init_capacity_critical();
	}
	protected $x_destroy_dependents;
	public function init_destroy_dependents(): myp\property_bool {
		$title = gettext('Extended Destroy');
		$property = $this->x_destroy_dependents = new myp\property_bool($this);
		$property->
			set_name('destroy_dependents')->
			set_title($title);
		return $property;
	}
	final public function get_destroy_dependents(): myp\property_bool {
		return $this->x_destroy_dependents ?? $this->init_destroy_dependents();
	}
}
