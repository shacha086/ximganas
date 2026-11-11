<?php
/*
	setting_properties.php

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

class setting_properties extends grid_properties {
	public function init_daemon(): myp\property_bool {
		$caption = gettext('Send daemon event messages.');
		$property = parent::init_daemon();
		$property->
			set_id('daemon')->
			set_caption($caption)->
			set_defaultvalue(false)->
			filter_use_default();
		return $property;
	}
	public function init_enable(): myp\property_enable {
		$property = parent::init_enable();
		$property->set_defaultvalue(false);
		return $property;
	}
	public function init_ftp(): myp\property_bool {
		$caption = gettext('Send FTP event messages.');
		$property = parent::init_ftp();
		$property->
			set_id('ftp')->
			set_caption($caption)->
			set_defaultvalue(false)->
			filter_use_default();
		return $property;
	}
	public function init_ipaddr(): myp\property_ipaddress {
		$description = gettext('IP address of the remote syslog server.');
		$property = parent::init_ipaddr();
		$property->
			set_id('ipaddr')->
			set_description($description)->
			set_defaultvalue('')->
			filter_use_default();
		return $property;
	}
	public function test_port($value = '') {
		if(is_string($value)):
			if(($value === '') || ($value === '514')):
				return $value;
			elseif(preg_match('/^[1-9][0-9]{2,5}$/',$value) === 1):
				$test = (int)$value;
//				test user ports
				if(($test >= 0x400) && ($test <= 0xbfff)):
					return $value;
				endif;
//				test dynamic ports
				if(($test >= 0xc000) && ($test <= 0xffff)):
					return $value;
				endif;
			endif;
		endif;
		return null;
	}
	public function init_port(): myp\property_int {
		$caption = gettext('Port of the remote syslog server. Leave blank to use the default port.');
		$description = gettext('Syslog sends UDP datagrams to port 514 on the specified remote syslog server. Be sure to set syslogd on the remote server to accept syslog messages from this server.');
		$property = parent::init_port();
		$property->
			set_id('port')->
			set_caption($caption)->
			set_description($description)->
			set_defaultvalue('')->
			set_size(6)->
			set_maxlength(5)->
			set_placeholder(gettext('514'))->
			set_filter(FILTER_CALLBACK)->
			set_filter_options([$this,'test_port'])->
			set_message_error(sprintf('%s: %s',$property->get_title(),gettext('Port number must be 514 or a number between 1024 and 65535.')));
		return $property;
	}
	public function init_rsyncd(): myp\property_bool {
		$caption = gettext('Send RSYNC event messages.');
		$property = parent::init_rsyncd();
		$property->
			set_id('rsyncd')->
			set_caption($caption)->
			set_defaultvalue(false)->
			filter_use_default();
		return $property;
	}
	public function init_smartd(): myp\property_bool {
		$caption = gettext('Send S.M.A.R.T. event messages.');
		$property = parent::init_smartd();
		$property->
			set_id('smartd')->
			set_caption($caption)->
			set_defaultvalue(false)->
			filter_use_default();
		return $property;
	}
	public function init_sshd(): myp\property_bool {
		$caption = gettext('Send SSH event messages.');
		$property = parent::init_sshd();
		$property->
			set_id('sshd')->
			set_caption($caption)->
			set_defaultvalue(false)->
			filter_use_default();
		return $property;
	}
	public function init_system(): myp\property_bool {
		$caption = gettext('Send system event messages.');
		$property = parent::init_system();
		$property->
			set_id('sendsystemeventmessages')->
			set_caption($caption)->
			set_defaultvalue(false)->
			filter_use_default();
		return $property;
	}
}
