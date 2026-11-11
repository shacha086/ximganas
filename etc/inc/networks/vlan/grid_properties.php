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

namespace networks\vlan;

use common\properties as myp;

class grid_properties extends myp\container_row {
	protected $x_if;
	public function init_if(): myp\property_text {
		$title = gettext('VLAN Interface');
		$property = $this->x_if = new myp\property_text($this);
		$property->
			set_name('if')->
			set_title($title);
		return $property;
	}
	final public function get_if(): myp\property_text {
		return $this->x_if ?? $this->init_if();
	}
	protected $x_tag;
	public function init_tag(): myp\property_int {
		$title = gettext('VLAN Tag');
		$property = $this->x_tag = new myp\property_int($this);
		$property->
			set_name('tag')->
			set_title($title);
		return $property;
	}
	final public function get_tag(): myp\property_int {
		return $this->x_tag ?? $this->init_tag();
	}
	protected $x_vlandev;
	public function init_vlandev(): myp\property_list {
		$title = gettext('Physical Interface');
		$property = $this->x_vlandev = new myp\property_list($this);
		$property->
			set_name('vlandev')->
			set_title($title);
		return $property;
	}
	final public function get_vlandev(): myp\property_list {
		return $this->x_vlandev ?? $this->init_vlandev();
	}
	protected $x_vlanpcp;
	public function init_vlanpcp(): myp\property_list {
		$title = gettext('PCP');
		$property = $x_vlanpcp = new myp\property_list($this);
		$property->
			set_name('vlanpcp')->
			set_title($title);
		return $property;
	}
	final public function get_vlanpcp(): myp\property_list {
		return $this->x_vlanpcp ?? $this->init_vlanpcp();
	}
	protected $x_vlanproto;
	public function init_vlanproto(): myp\property_list {
		$title = gettext('Encapsulation Protocol');
		$property = $x_vlanproto = new myp\property_list($this);
		$property->
			set_name('vlanproto')->
			set_title($title);
		return $property;
	}
	final public function get_vlanproto(): myp\property_list {
		return $this->x_vlanproto ?? $this->init_vlanproto();
	}
	public function init_description(): myp\property_description {
		$property = parent::init_description();
		$property->
			set_id('desc')->
			set_name('desc');
		return $property;
	}
}
