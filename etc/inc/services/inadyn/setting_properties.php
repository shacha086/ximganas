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

namespace services\inadyn;

use common\properties as myp;

class setting_properties extends grid_properties {
	public function init_enable(): myp\property_enable {
		$property = parent::init_enable();
		$property->
			set_defaultvalue(false);
		return $property;
	}
	public function init_verifyaddress(): myp\property_bool {
		$caption = gettext('IP address validation can be disabled by setting this option to false.');
		$description = gettext('By default inadyn verifies both IPv4 and IPv6 addresses, making sure the address is a valid Internet address. Invalid addresses are, e.g., link local, loopback, multicast and known experimental addresses. For more information, see RFC3330.');
		$property = parent::init_verifyaddress();
		$property->
			set_caption($caption)->
			set_defaultvalue(true)->
			set_description($description)->
			set_id('verifyaddress')->
			filter_use_default();
		return $property;
	}
	public function init_fakeaddress(): myp\property_bool {
		$caption = gettext('Use this option to fake an address update with a "random" address in the 203.0.113.0/24 range before updating with the actual IP address.');
		$defaultvalue = false;
		$property = parent::init_fakeaddress();
		$property->
			set_caption($caption)->
			set_defaultvalue($defaultvalue)->
			set_id('fakeaddress')->
			filter_use_default();
		return $property;
	}
	public function init_allowipv6(): myp\property_bool {
		$caption = gettext('This option controls if IPv6 addresses should be allowed or discarded.');
		$defaultvalue = false;
		$property = parent::init_allowipv6();
		$property->
			set_caption($caption)->
			set_defaultvalue($defaultvalue)->
			set_id('allowipv6')->
			filter_use_default();
		return $property;
	}
	public function init_iface(): myp\property_list {
		$caption = gettext('Use this network interface as source of IP address changes instead of querying an external server.');
		$property = parent::init_iface();
		$property->
			set_caption($caption)->
			set_id('iface')->
			filter_use_default();
		return $property;
	}
	public function init_iterations(): myp\property_int {
		$defaultvalue = '';
		$description = gettext('Set the number of DNS updates. The default is 0, which means infinity.');
		$property = parent::init_iterations();
		$property->
			set_defaultvalue($defaultvalue)->
			set_description($description)->
			set_id('iterations')->
			set_min(0)->
			set_size(10)->
			filter_use_default_or_empty();
		return $property;
	}
	public function init_period(): myp\property_int {
		$defaultvalue = '';
		$description = gettext('How often the IP is checked, in seconds. Default: approx. 1 minute. Max: 10 days.');
		$placeholder = $placeholderv = '60';
		$property = parent::init_period();
		$property->
			set_defaultvalue($defaultvalue)->
			set_description($description)->
			set_id('period')->
			set_max(864000)->
			set_min(0)->
			set_placeholder($placeholder)->
			set_placeholderv($placeholderv)->
			set_size(10)->
			filter_use_default_or_empty();
		return $property;
	}
	public function init_forceupdate(): myp\property_int {
		$defaultvalue = '';
		$description = gettext('How often the IP should be updated even if it is not changed. The time should be given in seconds. Default is equal to 30 days.');
		$placeholder = $placeholderv = '2592000';
		$property = parent::init_forceupdate();
		$property->
			set_defaultvalue($defaultvalue)->
			set_description($description)->
			set_id('forceupdate')->
			set_min(0)->
			set_placeholder($placeholder)->
			set_placeholderv($placeholderv)->
			set_size(10)->
			filter_use_default_or_empty();
		return $property;
	}
	public function init_securessl(): myp\property_bool {
		$caption = gettext('Enable this option to abort an DDNS update if the HTTPS certificate validation fails.');
		$defaultvalue = true;
		$property = parent::init_securessl();
		$property->
			set_caption($caption)->
			set_defaultvalue($defaultvalue)->
			set_id('securessl')->
			filter_use_default();
		return $property;
	}
	public function init_brokenrtc(): myp\property_bool {
		$caption = gettext('Enable this option only on systems without hardware real-time clock and default bootup time far in the past.');
		$defaultvalue = false;
		$property = parent::init_brokenrtc();
		$property->
			set_caption($caption)->
			set_defaultvalue($defaultvalue)->
			set_id('brokenrtc')->
			filter_use_default();
		return $property;
	}
	public function init_catrustfile(): myp\property_text {
		global $g;

		$defaultvalue = '';
		$description = gettext('This setting overrides the built-in paths and fallback locations and provides a way to specify the path to a trusted set of CA certificates, in PEM format, bundled into one file.');
		$placeholderv = $placeholder = $g['media_path'];
		$property = parent::init_catrustfile();
		$property->
			set_defaultvalue($defaultvalue)->
			set_description($description)->
			set_id('catrustfile')->
			set_input_type($property::INPUT_TYPE_FILECHOOSER)->
			set_placeholder($placeholder)->
			set_placeholderv($placeholderv)->
			set_size(60)->
			filter_use_default_or_empty();
		return $property;
	}
	public function init_useragent(): myp\property_text {
		$defaultvalue = '';
		$description = gettext('Specify the User-Agent string to send to the DDNS provider on checkip and update requests.');
		$placeholder = gettext('User agent string');
		$property = parent::init_useragent();
		$property->
			set_defaultvalue($defaultvalue)->
			set_description($description)->
			set_id('useragent')->
			set_placeholder($placeholder)->
			set_size(60)->
			filter_use_default_or_empty();
		return $property;
	}
	public function init_cachedir(): myp\property_text {
		$defaultvalue = '';
		$description = gettext('Set directory for persistent cache files, The cache files are used to keep track of which addresses have been successfully sent to their respective DDNS provider and when.');
		$placeholder = $placeholderv = '/var/cache/inadyn';
		$property = parent::init_cachedir();
		$property->
			set_defaultvalue($defaultvalue)->
			set_description($description)->
			set_id('cachedir')->
			set_input_type($property::INPUT_TYPE_FILECHOOSER)->
			set_placeholder($placeholder)->
			set_placeholderv($placeholderv)->
			set_size(60)->
			filter_use_default_or_empty();
		return $property;
	}
	public function init_startupdelay(): myp\property_int {
		$defaultvalue = '';
		$description = gettext('Initial startup delay. Default is no delay.');
		$placeholder = $placeholderv = gettext('no delay');
		$property = parent::init_startupdelay();
		$property->
			set_defaultvalue($defaultvalue)->
			set_description($description)->
			set_id('startupdelay')->
			set_min(0)->
			set_placeholder($placeholder)->
			set_placeholderv($placeholderv)->
			set_size(10)->
			filter_use_default_or_empty();
		return $property;
	}
	public function init_loglevel(): myp\property_list {
		$caption = gettext('Set log level');
		$defaultvalue = 'notice';
		$options = [
			'none' => gettext('None'),
			'err' => gettext('Errors'),
			'info' => gettext('Info'),
			'notice' => gettext('Notice'),
			'warning' => gettext('Warning'),
			'debug' => gettext('Debug')
		];
		$property = parent::init_loglevel();
		$property->
			set_id('loglevel')->
			set_caption($caption)->
			set_options($options)->
			set_defaultvalue($defaultvalue)->
			filter_use_default();
		return $property;
	}
	public function init_auxparam(): myp\property_auxparam {
		$description = gettext('These parameters are appended to the inadyn.conf file.');
		$property = parent::init_auxparam();
		$property->
			set_description($description);
		return $property;
	}
}
