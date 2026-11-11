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

namespace system\syslog\remote;

use common\properties as myp;

class grid_properties extends myp\container {
	protected $x_daemon;
	public function init_daemon(): myp\property_bool {
		$property = $this->x_daemon = new myp\property_bool($this);
		$property->
			set_name('daemon')->
			set_title(gettext('Daemon Events'));
		return $property;
	}
	final public function get_daemon(): myp\property_bool {
		return $this->x_daemon ?? $this->init_daemon();
	}
	protected $x_enable;
	public function init_enable(): myp\property_enable {
		$property = $this->x_enable = new myp\property_enable($this);
		$property->
			set_title(gettext('Enable Server'));
		return $property;
	}
	final public function get_enable(): myp\property_enable {
		return $this->x_enable ?? $this->init_enable();
	}
	protected $x_ftp;
	public function init_ftp(): myp\property_bool {
		$property = $this->x_ftp = new myp\property_bool($this);
		$property->
			set_name('ftp')->
			set_title(gettext('FTP Events'));
		return $property;
	}
	final public function get_ftp(): myp\property_bool {
		return $this->x_ftp ?? $this->init_ftp();
	}
	protected $x_ipaddr;
	public function init_ipaddr(): myp\property_ipaddress {
		$property = $this->x_ipaddr = new myp\property_ipaddress($this);
		$property->
			set_name('ipaddr')->
			set_title(gettext('IP Address'));
		return $property;
	}
	final public function get_ipaddr(): myp\property_ipaddress {
		return $this->x_ipaddr ?? $this->init_ipaddr();
	}
	protected $x_port;
	public function init_port(): myp\property_int {
		$property = $this->x_port = new myp\property_int($this);
		$property->
			set_name('port')->
			set_title(gettext('Port'));
		return $property;
	}
	final public function get_port(): myp\property_int {
		return $this->x_port ?? $this->init_port();
	}
	protected $x_rsyncd;
	public function init_rsyncd(): myp\property_bool {
		$property = $this->x_rsyncd = new myp\property_bool($this);
		$property->
			set_name('rsyncd')->
			set_title(gettext('RSYNC Events'));
		return $property;
	}
	final public function get_rsyncd(): myp\property_bool {
		return $this->x_rsyncd ?? $this->init_rsyncd();
	}
	protected $x_smartd;
	public function init_smartd(): myp\property_bool {
		$property = $this->x_smartd = new myp\property_bool($this);
		$property->
			set_name('smartd')->
			set_title(gettext('S.M.A.R.T. Events'));
		return $property;
	}
	final public function get_smartd(): myp\property_bool {
		return $this->x_smartd ?? $this->init_smartd();
	}
	protected $x_sshd;
	public function init_sshd(): myp\property_bool {
		$property = $this->x_sshd = new myp\property_bool($this);
		$property->
			set_name('sshd')->
			set_title(gettext('SSH Events'));
		return $property;
	}
	final public function get_sshd(): myp\property_bool {
		return $this->x_sshd ?? $this->init_sshd();
	}
	protected $x_system;
	public function init_system(): myp\property_bool {
		$property = $this->x_system = new myp\property_bool($this);
		$property->
			set_name('sendsystemeventmessages')->
			set_title(gettext('System Events'));
		return $property;
	}
	final public function get_system(): myp\property_bool {
		return $this->x_system ?? $this->init_system();
	}
}
