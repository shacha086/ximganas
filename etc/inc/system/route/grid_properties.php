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

namespace system\route;

use common\properties as myp;

class grid_properties extends myp\container_row {
	public function init_description(): myp\property_description {
		$property = parent::init_description();
		$property->
			set_id('descr')->
			set_name('descr');
		return $property;
	}
	protected $x_interface;
	public function init_interface(): myp\property_list {
		$property = $this->x_interface = new myp\property_list();
		$property->
			set_name('interface')->
			set_title(gettext('Interface'));
		return $property;
	}
	final public function get_interface(): myp\property_list {
		return $this->x_interface ?? $this->init_interface();
	}
	protected $x_network;
	public function init_network(): myp\property_cidr {
		$property = $this->x_network = new myp\property_cidr($this);
		$property->
			set_name('network')->
			set_title(gettext('Destination Network'));
		return $property;
	}
	final public function get_network(): myp\property_cidr {
		return $this->x_network ?? $this->init_network();
	}
	protected $x_gateway;
	public function init_gateway(): myp\property_ipaddress {
		$property = $this->x_gateway = new myp\property_ipaddress($this);
		$property->
			set_name('gateway')->
			set_title(gettext('Gateway'));
		return $property;
	}
	final public function get_gateway(): myp\property_ipaddress {
		return $this->x_gateway ?? $this->init_gateway();
	}
}
