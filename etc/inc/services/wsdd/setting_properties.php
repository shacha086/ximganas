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

namespace services\wsdd;

use common\properties as myp;

class setting_properties extends grid_properties {
	public function init_address_family(): myp\property_list {
		$defaultvalue = '';
		$description = '';
		$options = [
			'' => gettext('Default'),
			'4' => gettext('IPv4 only'),
			'6' => gettext('IPv6 only')
		];
		$property = parent::init_address_family();
		$property->
			set_defaultvalue($defaultvalue)->
			set_description($description)->
			set_id('ipprotocol')->
			set_options($options)->
			filter_use_default();
		return $property;
	}
	public function init_domain(): myp\property_text {
		$defaultvalue = '';
		$description = gettext('Set domain name');
		$placeholder = gettext('Domain');
		$placeholderv = gettext('Autodetect');
		$parent = parent::init_domain();
		$parent->
			set_defaultvalue($defaultvalue)->
			set_description($description)->
			set_id('domain')->
			set_placeholder($placeholder)->
			set_placeholderv($placeholderv)->
			set_size(60)->
			filter_use_default_or_empty();
		return $parent;
	}
	public function init_enable(): myp\property_enable {
		$property = parent::init_enable();
		$property->
			set_defaultvalue(false);
		return $property;
	}
	public function init_extraoptions(): myp\property_text {
		$text_1 = gettext('These options are appended to the command line parameters of the wsdd script.');
		$text_2 = '<a href="https://github.com/christgau/wsdd#options" target="_blank">' . gettext('Please check the documentation') . '</a>.';
		$description = sprintf('%s %s',$text_1,$text_2);
		$placeholder = gettext('Enter extra options');
		$property = parent::init_extraoptions();
		$property->
			set_defaultvalue('')->
			set_description($description)->
			set_id('extraoptions')->
			set_maxlength(4096)->
			set_placeholder($placeholder)->
			set_size(60)->
			filter_use_default_or_empty();
		return $property;
	}
	public function init_hostname(): myp\property_text {
//		disallowed 1st character: .\/:*?"<>|
//		disallowed characters: \/:*?"<>|
		$regexp_quote_1 = preg_quote('.','/');
		$regexp_quote_2 = preg_quote('\/:*?"<>|','/');
		$regexp = '/^[^\s' . $regexp_quote_1 . $regexp_quote_2 . '][^\s' . $regexp_quote_2 . ']{0,14}$/';
		$defaultvalue = '';
		$description = gettext('Override (NETBIOS) hostname to be used.');
		$placeholder = gettext('Hostname');
		$placeholderv = gettext('Autodetect');
		$property = parent::init_hostname();
		$property->
			set_defaultvalue($defaultvalue)->
			set_description($description)->
			set_id('hostname')->
			set_placeholder($placeholder)->
			set_placeholderv($placeholderv)->
			set_size(60)->
			filter_use_default_set_regexp($regexp)->
			filter_use_default_or_empty();
		return $property;
	}
	public function init_interface(): myp\property_list {
		$description = gettext('Select which interface to use.');
		$property = parent::init_interface();
		$property->
			set_defaultvalue('')->
			set_description($description)->
			set_id('if')->
			filter_use_default();
		return $property;
	}
	public function init_preservecase(): myp\property_bool{
		$caption = gettext('Preserve case of the provided/detected hostname.');
		$defaultvalue = false;
		$property = parent::init_preservecase();
		$property->
			set_caption($caption)->
			set_defaultvalue($defaultvalue)->
			set_id('preservecase')->
			filter_use_default();
		return $property;
	}
	public function init_server_mode(): myp\property_list {
		$defaultvalue = 'workgroup';
		$description = gettext('Select between domain mode and workgroup mode.');
		$options = [
			'domain' => gettext('Domain'),
			'workgroup' => gettext('Workgroup')
		];
		$property = parent::init_server_mode();
		$property->
			set_defaultvalue($defaultvalue)->
			set_description($description)->
			set_id('servermode')->
			set_options($options)->
			filter_use_default();
		return $property;
	}
	public function init_workgroup(): myp\property_text {
//		disallowed 1st character: .\/:*?"<>|
//		disallowed characters: \/:*?"<>|
		$regexp_quote_1 = preg_quote('.','/');
		$regexp_quote_2 = preg_quote('\/:*?"<>|','/');
		$regexp = '/^[^\s' . $regexp_quote_1 . $regexp_quote_2 . '][^\s' . $regexp_quote_2 . ']{0,14}$/';
		$defaultvalue = '';
		$description = gettext('Set workgroup name');
		$placeholder = gettext('Workgroup');
		$placeholderv = gettext('Autodetect');
		$property = parent::init_workgroup();
		$property->
			set_defaultvalue($defaultvalue)->
			set_description($description)->
			set_id('workgroup')->
			set_placeholder($placeholder)->
			set_placeholderv($placeholderv)->
			set_size(60)->
			filter_use_default_set_regexp($regexp)->
			filter_use_default_or_empty();
		return $property;
	}
}
