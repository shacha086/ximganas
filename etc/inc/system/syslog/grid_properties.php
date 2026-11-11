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

namespace system\syslog;

use common\properties as myp;

class grid_properties extends myp\container {
	protected $x_disablecomp;
	public function init_disablecomp(): myp\property_bool {
		$property = $this->x_disablecomp = new myp\property_bool($this);
		$property->
			set_name('disablecomp')->
			set_title(gettext('Compression'));
		return $property;
	}
	final public function get_disablecomp(): myp\property_bool {
		return $this->x_disablecomp ?? $this->init_disablecomp();
	}
	protected $x_disablesecure;
	public function init_disablesecure(): myp\property_bool {
		$property = $this->x_disablesecure = new myp\property_bool($this);
		$property->
			set_name('disablesecure')->
			set_title(gettext('Remote Syslog Messages'));
		return $property;
	}
	final public function get_disablesecure(): myp\property_bool {
		return $this->x_disablesecure ?? $this->init_disablesecure();
	}
	protected $x_nentries;
	public function init_nentries(): myp\property_int {
		$property = $this->x_nentries = new myp\property_int($this);
		$property->
			set_name('nentries')->
			set_title(gettext('Show Log Entries'));
		return $property;
	}
	final public function get_nentries(): myp\property_int {
		return $this->x_nentries ?? $this->init_nentries();
	}
	protected $x_resolve;
	public function init_resolve(): myp\property_bool {
		$property = $this->x_resolve = new myp\property_bool($this);
		$property->
			set_name('resolve')->
			set_title(gettext('Resolve IP'));
		return $property;
	}
	final public function get_resolve(): myp\property_bool {
		return $this->x_resolve ?? $this->init_resolve();
	}
	protected $x_reverse;
	public function init_reverse(): myp\property_bool {
		$property = $this->x_reverse = new myp\property_bool($this);
		$property->
			set_name('reverse')->
			set_title(gettext('Log Order'));
		return $property;
	}
	final public function get_reverse(): myp\property_bool {
		return $this->x_reverse ?? $this->init_reverse();
	}
}
