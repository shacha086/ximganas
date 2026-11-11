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

namespace system\syslog;

use common\properties as myp;

class setting_properties extends grid_properties {
	public function init_disablecomp(): myp\property_bool {
		$caption = gettext('Disable the compression of repeating lines.');
		$property = parent::init_disablecomp();
		$property->
			set_id('disablecomp')->
			set_caption($caption)->
			set_defaultvalue(false)->
			filter_use_default();
		return $property;
	}
	public function init_disablesecure(): myp\property_bool {
		$caption = gettext('Accept remote syslog messages.');
		$property = parent::init_disablesecure();
		$property->
			set_id('disablesecure')->
			set_caption($caption)->
			set_defaultvalue(false)->
			filter_use_default();
		return $property;
	}
	public function init_nentries(): myp\property_int {
		$property = parent::init_nentries();
		$property->
			set_id('nentries')->
			set_defaultvalue(50)->
			set_size(5)->
			set_maxlength(4)->
			set_min(5)->set_max(1500)->
			filter_use_default();
		return $property;
	}
	public function init_resolve(): myp\property_bool {
		$caption = gettext('Resolve IP addresses to hostnames.');
		$description = [
			gettext('Hint'),
			[': ',true],
			[gettext('If this option is checked, IP addresses in the server logs are resolved to their hostnames where possible.'),true],
			[gettext('Warning'),'red'],
			[': ',true],
			[gettext('This can cause a huge delay in loading the log page!'),true]
		];
		$property = parent::init_resolve();
		$property->
			set_id('resolve')->
			set_caption($caption)->
			set_description($description)->
			set_defaultvalue(false)->
			filter_use_default();
		return $property;
	}
	public function init_reverse(): myp\property_bool {
		$caption = gettext('Show log entries in reverse order (newest entries on top).');
		$property = parent::init_reverse();
		$property->
			set_id('reverse')->
			set_caption($caption)->
			set_defaultvalue(false)->
			filter_use_default();
		return $property;
	}
}
