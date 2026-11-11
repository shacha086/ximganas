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

namespace services\wsdd;

use common\properties as myp;

class grid_properties extends myp\container {
	protected $x_address_family;
	public function init_address_family(): myp\property_list {
		$title = gettext('IP Address Family');
		$property = $this->x_address_family = new myp\property_list($this);
		$property->
			set_name('ipprotocol')->
			set_title($title);
		return $property;
	}
	final public function get_address_family(): myp\property_list {
		return $this->x_address_family ?? $this->init_address_family();
	}
	protected $x_domain;
	public function init_domain(): myp\property_text {
		$title = gettext('Domain');
		$property = $this->x_domain = new myp\property_text($this);
		$property->
			set_name('domain')->
			set_title($title);
		return $property;
	}
	final public function get_domain(): myp\property_text {
		return $this->x_domain ?? $this->init_domain();
	}
	protected $x_enable;
	public function init_enable(): myp\property_enable {
		$property = $this->x_enable = new myp\property_enable($this);
		return $property;
	}
	final public function get_enable(): myp\property_enable {
		return $this->x_enable ?? $this->init_enable();
	}
	protected $x_extraoptions;
	public function init_extraoptions(): myp\property_text {
		$title = gettext('Extra Options');
		$property = $this->x_extraoptions = new myp\property_text($this);
		$property->
			set_name('extraoptions')->
			set_title($title);
		return $property;
	}
	final public function get_extraoptions(): myp\property_text {
		return $this->x_extraoptions ?? $this->init_extraoptions();
	}
	protected $x_hostname;
	public function init_hostname(): myp\property_text {
		$title = gettext('NETBIOS Hostname');
		$property = $this->x_hostname = new myp\property_text($this);
		$property->
			set_name('hostname')->
			set_title($title);
		return $property;
	}
	final public function get_hostname(): myp\property_text {
		return $this->x_hostname ?? $this->init_hostname();
	}
	protected $x_interface;
	public function init_interface(): myp\property_list {
		$title = gettext('Interface');
		$property = $this->x_interface = new myp\property_list($this);
		$property->
			set_name('if')->
			set_title($title);
		return $property;
	}
	final public function get_interface(): myp\property_list {
		return $this->x_interface ?? $this->init_interface();
	}
	protected $x_preservecase;
	public function init_preservecase(): myp\property_bool {
		$title = gettext('Preserve Case');
		$property = $this->x_preservecase = new myp\property_bool($this);
		$property->
			set_name('preservecase')->
			set_title($title);
		return $property;
	}
	final public function get_preservecase(): myp\property_bool {
		return $this->x_preservecase ?? $this->init_preservecase();
	}
	protected $x_server_mode;
	public function init_server_mode(): myp\property_list {
		$title = gettext('Server Mode');
		$property = $this->x_server_mode = new myp\property_list($this);
		$property->
			set_name('servermode')->
			set_title($title);
		return $property;
	}
	final public function get_server_mode(): myp\property_list {
		return $this->x_server_mode ?? $this->init_server_mode();
	}
	protected $x_workgroup;
	public function init_workgroup(): myp\property_text {
		$title = gettext('Workgroup Name');
		$property = $this->x_workgroup = new myp\property_text($this);
		$property->
			set_name('workgroup')->
			set_title($title);
		return $property;
	}
	final public function get_workgroup(): myp\property_text {
		return $this->x_workgroup ?? $this->init_workgroup();
	}
}
