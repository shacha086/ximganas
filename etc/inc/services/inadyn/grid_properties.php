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

namespace services\inadyn;

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
	protected $x_verifyaddress;
	public function init_verifyaddress(): myp\property_bool {
		$title = gettext('Verify Address');
		$property = $this->x_verifyaddress = new myp\property_bool($this);
		$property->
			set_name('verifyaddress')->
			set_title($title);
		return $property;
	}
	final public function get_verifyaddress(): myp\property_bool {
		return $this->x_verifyaddress ?? $this->init_verifyaddress();
	}
	protected $x_fakeaddress;
	public function init_fakeaddress(): myp\property_bool {
		$title = gettext('Fake Address');
		$property = $this->x_fakeaddress = new myp\property_bool($this);
		$property->
			set_name('fakeaddress')->
			set_title($title);
		return $property;
	}
	final public function get_fakeaddress(): myp\property_bool {
		return $this->x_fakeaddress ?? $this->init_fakeaddress();
	}
	protected $x_allowipv6;
	public function init_allowipv6(): myp\property_bool {
		$title = gettext('Allow IPv6');
		$property = $this->x_allowipv6 = new myp\property_bool($this);
		$property->
			set_name('allowipv6')->
			set_title($title);
		return $property;
	}
	final public function get_allowipv6(): myp\property_bool {
		return $this->x_allowipv6 ?? $this->init_allowipv6();
	}
	protected $x_iface;
	public function init_iface(): myp\property_list {
		$title = gettext('Network Interface');
		$property = $this->x_iface = new myp\property_list($this);
		$property->
			set_name('iface')->
			set_title($title);
		return $property;
	}
	final public function get_iface(): myp\property_list {
		return $this->x_iface ?? $this->init_iface();
	}
	protected $x_iterations;
	public function init_iterations(): myp\property_int {
		$title = gettext('Iterations');
		$property = $this->x_iterations = new myp\property_int($this);
		$property->
			set_name('iterations')->
			set_title($title);
		return $property;
	}
	final public function get_iterations(): myp\property_int {
		return $this->x_iterations ?? $this->init_iterations();
	}
	protected $x_period;
	public function init_period(): myp\property_int {
		$title = gettext('Check Interval');
		$property = $this->x_period = new myp\property_int($this);
		$property->
			set_name('period')->
			set_title($title);
		return $property;
	}
	final public function get_period(): myp\property_int {
		return $this->x_period ?? $this->init_period();
	}
	protected $x_forceupdate;
	public function init_forceupdate(): myp\property_int {
		$title = gettext('Force Update Interval');
		$property = $this->x_forceupdate = new myp\property_int($this);
		$property->
			set_name('forceupdate')->
			set_title($title);
		return $property;
	}
	final public function get_forceupdate(): myp\property_int {
		return $this->x_forceupdate ?? $this->init_forceupdate();
	}
	protected $x_securessl;
	public function init_securessl(): myp\property_bool {
		$title = gettext('Secure SSL');
		$property = $this->x_securessl = new myp\property_bool($this);
		$property->
			set_name('securessl')->
			set_title($title);
		return $property;
	}
	final public function get_securessl(): myp\property_bool {
		return $this->x_securessl ?? $this->init_securessl();
	}
	protected $x_brokenrtc;
	public function init_brokenrtc(): myp\property_bool {
		$title = gettext('Broken RTC');
		$property = $this->x_brokenrtc = new myp\property_bool($this);
		$property->
			set_name('brokenrtc')->
			set_title($title);
		return $property;
	}
	final public function get_brokenrtc(): myp\property_bool {
		return $this->x_brokenrtc ?? $this->init_brokenrtc();
	}
	protected $x_catrustfile;
	public function init_catrustfile(): myp\property_text {
		$title = gettext('CA Trusted Certificates');
		$property = $this->x_catrustfile = new myp\property_text($this);
		$property->
			set_name('catrustfile')->
			set_title($title);
		return $property;
	}
	final public function get_catrustfile(): myp\property_text {
		return $this->x_catrustfile ?? $this->init_catrustfile();
	}
	protected $x_useragent;
	public function init_useragent(): myp\property_text {
		$title = gettext('User Agent');
		$property = $this->x_useragent = new myp\property_text($this);
		$property->
			set_name('useragent')->
			set_title($title);
		return $property;
	}
	final public function get_useragent(): myp\property_text {
		return $this->x_useragent ?? $this->init_useragent();
	}
	protected $x_cachedir;
	public function init_cachedir(): myp\property_text {
		$title = gettext('Cache Directory');
		$property = $this->x_cachedir = new myp\property_text($this);
		$property->
			set_name('cachedir')->
			set_title($title);
		return $property;
	}
	final public function get_cachedir(): myp\property_text {
		return $this->x_cachedir ?? $this->init_cachedir();
	}
	protected $x_startupdelay;
	public function init_startupdelay(): myp\property_int {
		$title = gettext('Startup Delay');
		$property = $this->x_startupdelay = new myp\property_int($this);
		$property->
			set_name('startupdelay')->
			set_title($title);
		return $property;
	}
	final public function get_startupdelay(): myp\property_int {
		return $this->x_startupdelay ?? $this->init_startupdelay();
	}
	protected $x_loglevel;
	public function init_loglevel(): myp\property_list {
		$title = gettext('Log Level');
		$property = $this->x_loglevel = new myp\property_list($this);
		$property->
			set_name('loglevel')->
			set_title($title);
		return $property;
	}
	final public function get_loglevel(): myp\property_list {
		return $this->x_loglevel ?? $this->init_loglevel();
	}
	protected $x_auxparam;
	public function init_auxparam(): myp\property_auxparam {
		$property = $this->x_auxparam = new myp\property_auxparam($this);
		return $property;
	}
	final public function get_auxparam(): myp\property_auxparam {
		return $this->x_auxparam ?? $this->init_auxparam();
	}
}
