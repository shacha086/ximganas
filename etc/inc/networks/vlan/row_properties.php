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

namespace networks\vlan;

use common\properties as myp;

class row_properties extends grid_properties {
	public function init_if(): myp\property_text {
		$description = gettext('The name of the VLAN interface');
		$placeholder = gettext('vlan0');
		$property =  parent::init_if();
		$property->
			set_defaultvalue('')->
			set_description($description)->
			set_id('if')->
			set_maxlength(16)->
			set_placeholder($placeholder)->
			set_size(20)->
			filter_use_default_set_regexp('/^[a-z][0-9a-z]*(_[0-9a-z]+)*$/')->
			filter_use_default();
		return $property;
	}
	public function init_tag(): myp\property_int {
		$description = gettext('802.1Q VLAN tag (between 1 and 4094).');
		$placeholder = $placeholderv = gettext('102');
		$property = parent::init_tag();
		$property->
			set_defaultvalue('')->
			set_description($description)->
			set_id('tag')->
			set_max(4094)->
			set_min(1)->
			set_placeholder($placeholder)->
			set_size(10)->
			filter_use_default();
		return $property;
	}
	public function init_vlandev(): myp\property_list {
		$property = parent::init_vlandev();
		$property->
			set_defaultvalue('')->
			set_id('vlandev')->
			filter_use_default();
		return $property;
	}
	public function init_description(): myp\property_description {
		$property = parent::init_description();
		$property->
			set_maxlength(1024);
		return $property;
	}
	public function init_vlanpcp(): myp\property_list {
		$description = gettext('Priority code point (PCP) is an 3-bit field which refers to the IEEE 802.1p class of service and maps to the frame priority level.');
		$options = [
			'' => gettext('Default'),
			'0' => gettext('Best effort (default)'),
			'1' => gettext('Background (lowest)'),
			'2' => gettext('Excellent effort'),
			'3' => gettext('Critical applications'),
			'4' => gettext('Video < 100ms latency'),
			'5' => gettext('Video < 10ms latency'),
			'6' => gettext('Internetwork control'),
			'7' => gettext('Network control (highest)')
		];
		$property = parent::init_vlanpcp();
		$property->
			set_defaultvalue('')->
			set_description($description)->
			set_id('vlanpcp')->
			set_options($options)->
			filter_use_default();
		return $property;
	}
	public function init_vlanproto(): myp\property_list {
		$description = gettext('Set the VLAN encapsulation protocol.');
		$options = [
			'' => gettext('Default'),
			'802.1Q' => gettext('802.1Q (default)'),
			'802.1ad' => gettext('802.1ad (QinQ)')
		];
		$property = parent::init_vlanproto();
		$property->
			set_defaultvalue('')->
			set_description($description)->
			set_id('vlanproto')->
			set_options($options)->
			filter_use_default();
		return $property;
	}
}
