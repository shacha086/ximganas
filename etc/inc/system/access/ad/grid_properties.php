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
namespace system\access\ad;

use common\properties as myp;

class grid_properties extends myp\container {
	protected $x_enable;
	public function init_enable(): myp\property_enable {
		$property = $this->x_enable = new myp\property_enable($this);
		return $property;
	}
	final public function get_enable(): myp\property_enable {
		return $this->x_enable ?? $this->init_enable();
	}
	protected $x_domaincontrollername;
	public function init_domaincontrollername(): myp\property_text {
		$title = gettext('Domain Controller Name');
		$property = $this->x_domaincontrollername = new myp\property_text($this);
		$property->
			set_name('domaincontrollername')->
			set_title($title);
		return $property;
	}
	final public function get_domaincontrollername(): myp\property_text {
		return $this->x_domaincontrollername ?? $this->init_domaincontrollername();
	}
	protected $x_domainname_dns;
	public function init_domainname_dns(): myp\property_text {
		$title = gettext('Domain Name (DNS/Realm-Name)');
		$property = $this->x_domainname_dns = new myp\property_text($this);
		$property->
			set_name('domainname_dns')->
			set_title($title);
		return $property;
	}
	final public function get_domainname_dns(): myp\property_text {
		return $this->x_domainname_dns ?? $this->init_domainname_dns();
	}
	protected $x_domainname_netbios;
	public function init_domainname_netbios(): myp\property_text {
		$title = gettext('Domain Name (NetBIOS-Name)');
		$property = $this->x_domainname_netbios = new myp\property_text($this);
		$property->
			set_name('domainname_netbios')->
			set_title($title);
		return $property;
	}
	final public function get_domainname_netbios(): myp\property_text {
		return $this->x_domainname_netbios ?? $this->init_domainname_netbios();
	}
	protected $x_username;
	public function init_username(): myp\property_text {
		$title = gettext('Administrator Name');
		$property = $this->x_username = new myp\property_text($this);
		$property->
			set_name('username')->
			set_title($title);
		return $property;
	}
	final public function get_username(): myp\property_text {
		return $this->x_username ?? $this->init_username();
	}
	protected $x_password_encoded;
	public function init_password_encoded(): myp\property_text {
		$title = gettext('Administrator Password');
		$property = $this->x_password_encoded = new myp\property_text($this);
		$property->
			set_name('password_encoded')->
			set_title($title);
		return $property;
	}
	final public function get_password_encoded(): myp\property_text {
		return $this->x_password_encoded ?? $this->init_password_encoded();
	}
}
