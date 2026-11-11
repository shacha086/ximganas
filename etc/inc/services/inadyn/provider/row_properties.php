<?php
/*
	row_properties.php

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

namespace services\inadyn\provider;

use common\properties as myp;

class row_properties extends grid_properties {
	public function init_enable(): myp\property_enable {
		$defaultvalue = true;
		$property = parent::init_enable();
		$property->
			set_defaultvalue($defaultvalue);
		return $property;
	}
	public function init_recordtype(): myp\property_list {
		$description = gettext('Select "Custom Provider" for customizing the DDNS update server details settings.');
		$defaultvalue = 'provider';
		$property = parent::init_recordtype();
		$property->
			set_description($description)->
			set_defaultvalue($defaultvalue)->
			set_id('recordtype')->
			filter_use_default();
		return $property;
	}
	public function init_provider(): myp\property_list {
		$description = gettext('Select from predefined providers.');
		$property = parent::init_provider();
		$property->
			set_defaultvalue('')->
			set_description($description)->
			set_id('provider')->
			set_input_type($property::INPUT_TYPE_SELECT)->
			filter_use_default();
		return $property;
	}
	public function init_customprovider(): myp\property_text {
		$defaultvalue = '';
		$description = gettext('Enter an identifier for this custom DDNS configuration.');
		$placeholder = gettext('Identifier');
		$property = parent::init_customprovider();
		$property->
			set_defaultvalue($defaultvalue)->
			set_description($description)->
			set_id('customprovider')->
			set_placeholder($placeholder)->
			set_size(60)->
			filter_use_default_or_empty();
		return $property;
	}
	public function init_identifier(): myp\property_text {
		$defaultvalue = '';
		$description = gettext('To support multiple users of the same DDNS provider, an identifier must be defined. The identifier can be any free form alphanumeric string as long as the combination is unique.');
		$placeholder = gettext('Identifier');
		$regex = '/^[[:alnum:]]*$/';
		$property = parent::init_identifier();
		$property->
			set_defaultvalue($defaultvalue)->
			set_description($description)->
			set_id('identifier')->
			set_size(10)->
			filter_use_default_set_regexp($regex)->
			filter_use_default();
		return $property;
	}
	public function init_hostnames(): myp\property_text {
		$defaultvalue = '';
		$description = gettext('The list of hostnames. Use a comma to separate multiple names.');
		$placeholder = gettext('Hostnames');
		$property = parent::init_hostnames();
		$property->
			set_defaultvalue($defaultvalue)->
			set_description($description)->
			set_id('hostname')->
			set_placeholder($placeholder)->
			set_size(60)->
			filter_use_default();
		return $property;
	}
	public function init_username(): myp\property_text {
		$defaultvalue = '';
		$description = gettext('The username, if applicable. This might be referred to as hash.');
		$placeholder = gettext('Username');
		$property = parent::init_username();
		$property->
			set_defaultvalue($defaultvalue)->
			set_description($description)->
			set_id('username')->
			set_placeholder($placeholder)->
			set_size(60)->
			filter_use_default_or_empty();
		return $property;
	}
	public function init_password(): myp\property_text {
		$description = gettext('The password, if applicable.');
		$placeholder = gettext('Password');
		$property = parent::init_password();
		$property->
			set_defaultvalue('')->
			set_description($description)->
			set_id('password')->
			set_input_type($property::INPUT_TYPE_PASSWORD)->
			set_placeholder($placeholder)->
			filter_use_default_or_empty();
		return $property;
	}
	public function init_wildcard(): myp\property_bool {
		$caption = gettext('Enable domain name wildcarding of the domain name, for DDNS providers that support this.');
		$defaultvalue = false;
		$property = parent::init_wildcard();
		$property->
			set_caption($caption)->
			set_defaultvalue($defaultvalue)->
			set_id('wildcard')->
			filter_use_default();
		return $property;
	}
	public function init_usessl(): myp\property_bool {
		$caption = gettext('Use HTTPS, both when checking for IP changes and updating the DNS record. Default is to use HTTPS.');
		$defaultvalue = true;
		$property = parent::init_usessl();
		$property->
			set_caption($caption)->
			set_defaultvalue($defaultvalue)->
			set_id('usessl')->
			filter_use_default();
		return $property;
	}
	public function init_checkip_server(): myp\property_text {
		$defaultvalue = '';
		$description = gettext('Server to use for periodic IP address changes');
		$placeholder = gettext('api.ipify.org');
		$property = parent::init_checkip_server();
		$property->
			set_defaultvalue($defaultvalue)->
			set_description($description)->
			set_id('checkipserver')->
			set_placeholder($placeholder)->
			set_size(60)->
			filter_use_default_or_empty();
		return $property;
	}
	public  function init_checkip_path(): myp\property_text {
		$defaultvalue = '';
		$description = gettext('Optional server path for check IP server');
		$placeholder = gettext('/');
		$property = parent::init_checkip_path();
		$property->
			set_defaultvalue($defaultvalue)->
			set_description($description)->
			set_id('checkippath')->
			set_placeholder($placeholder)->
			set_size(60)->
			filter_use_default_or_empty();
		return $property;
	}
	public function init_checkip_ssl(): myp\property_bool {
		$caption = gettext('This setting usually follows the ssl setting, but can be used to disable HTTPS for the IP address check.');
		$defaultvalue = true;
		$property = parent::init_checkip_ssl();
		$property->
			set_caption($caption)->
			set_defaultvalue($defaultvalue)->
			set_id('checkipssl')->
			filter_use_default();
		return $property;
	}
	public function init_checkip_command(): myp\property_text {
		$defaultvalue = '';
		$description = gettext('Shell command, or script, for IP address update checking. The command must output a text with the IP address to its standard output.');
		$property = parent::init_checkip_command();
		$property->
			set_defaultvalue($defaultvalue)->
			set_description($description)->
			set_id('checkipcommand')->
			set_size(60)->
			filter_use_default_or_empty();
		return $property;
	}
	public function init_useragent(): myp\property_text {
		$defaultvalue = '';
		$description = gettext('Same as the global setting, but only for this provider. If omitted it defaults to the global setting, which if unset uses the default user agent string.');
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
	public function init_ddns_server(): myp\property_text {
		$defaultvalue = '';
		$description = gettext('DDNS server name, not the full URL.');
		$placeholder = gettext('update.example.com');
		$property = parent::init_ddns_server();
		$property->
			set_defaultvalue($defaultvalue)->
			set_description($description)->
			set_id('ddnsserver')->
			set_placeholder($placeholder)->
			set_size(60)->
			filter_use_default_or_empty();
		return $property;
	}
	public function init_ddns_path(): myp\property_text{
		$defaultvalue = '';
		$description = gettext('DDNS server path. By default the hostname is appended to the path, unless append-myip is set.');
		$placeholder = gettext('/update?domain=');
		$property = parent::init_ddns_path();
		$property->
			set_defaultvalue($defaultvalue)->
			set_description($description)->
			set_id('ddnspath')->
			set_placeholder($placeholder)->
			set_size(60)->
			filter_use_default_or_empty();
		return $property;
	}
	public function init_append_myip(): myp\property_bool {
		$caption = gettext('Append the current IP to the DDNS server update path.');
		$defaultvalue = false;
		$description = gettext('If this setting is not enabled, the hostname is appended. Unless the ddns-path is given with format specifiers, in which case this setting is unused.');
		$property = parent::init_append_myip();
		$property->
			set_caption($caption)->
			set_defaultvalue($defaultvalue)->
			set_description($description)->
			set_id('appendmyip')->
			filter_use_default();
		return $property;
	}
	public function init_auxparam(): myp\property_auxparam {
		$description = gettext('These parameters will be added to this provider configuration');
		$property = parent::init_auxparam();
		$property->
			set_description($description);
		return $property;
	}
}
