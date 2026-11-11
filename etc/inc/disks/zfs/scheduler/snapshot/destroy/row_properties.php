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

declare(strict_types = 1);

namespace disks\zfs\scheduler\snapshot\destroy;

use common\properties as myp;

class row_properties extends grid_properties {
	public function init_path(): myp\property_list {
		$description = gettext('Select Pool, Filesystem or Volume');
		$property = parent::init_path();
		$property->
			set_description($description)->
			set_defaultvalue('')->
			set_id('path')->
			set_input_type($property::INPUT_TYPE_SELECT)->
			filter_use_default();
		return $property;
	}
	public function init_recursive(): myp\property_bool {
		$caption = gettext('Recursively destroy snapshots of all descendent datasets.');
		$property = parent::init_recursive();
		$property->
			set_caption($caption)->
			set_id('recursive')->
			filter_use_default();
		return $property;
	}
	public function init_lifetime_val(): myp\property_int {
		$title = gettext('Lifetime Value');
		$property = parent::init_lifetime_val();
		$property->
			set_defaultvalue(6)->
			set_id('lifetime_val')->
			set_max(1024)->
			set_maxlength(4)->
			set_min(1)->
			set_size(10)->
			set_title($title)->
			filter_use_default();
		return $property;
	}
	public function init_lifetime_uom(): myp\property_list {
		$title = gettext('Lifetime Units');
		$property = parent::init_lifetime_uom();
		$property->
			set_defaultvalue('m')->
			set_id('lifetime_uom')->
			set_title($title)->
			filter_use_default();
		return $property;
	}
	public function init_scheduler(): myp\property_bool {
		$description = gettext('Commands are executed by the scheduler when the minute, hour, and month of year fields match the current time, and when at least one of the two day fields (day of month, or day of week) matches the current time.');
		$property = parent::init_scheduler();
		$property->
			set_defaultvalue(true)->
			set_description($description)->
			set_id('scheduler')->
			filter_use_default();
		return $property;
	}
	public function init_preset(): myp\property_list {
		$title = gettext('Presets');
		$property = parent::init_preset();
		$property->
			set_defaultvalue('@daily')->
			set_id('preset')->
			set_title($title)->
			filter_use_default();
		return $property;
	}
	public function init_minutes(): myp\property_list_multi {
		$options = [];
		for($i = 0;$i <= 59;$i++):
			$options[$i] = $i;
		endfor;
		$property = parent::init_minutes();
		$property->
			set_defaultvalue('')->
			set_id('minute')->
			set_options($options)->
			filter_use_default();
		return $property;
	}
	public function init_hours(): myp\property_list_multi {
		$options = [];
		for($i = 0;$i <= 23;$i++):
			$options[$i] = $i;
		endfor;
		$property = parent::init_hours();
		$property->
			set_defaultvalue('')->
			set_id('hour')->
			set_options($options)->
			filter_use_default();
		return $property;
	}
	public function init_days(): myp\property_list_multi {
		$options = [];
		for($i = 1;$i <= 31;$i++):
			$options[$i] = $i;
		endfor;
		$property = parent::init_days();
		$property->
			set_defaultvalue('')->
			set_id('day')->
			set_options($options)->
			filter_use_default();
		return $property;
	}
	public function init_months(): myp\property_list_multi {
		global $g_months;

		$options = $g_months;
		$property = parent::init_months();
		$property->
			set_defaultvalue('')->
			set_id('month')->
			set_options($options)->
			filter_use_default();
		return $property;
	}
	public function init_weekdays(): myp\property_list_multi {
		global $g_weekdays;

		$options = $g_weekdays;
		$property = parent::init_weekdays();
		$property->
			set_defaultvalue('')->
			set_id('weekday')->
			set_options($options)->
			filter_use_default();
		return $property;
	}
	public function init_all_minutes(): myp\property_int {
		$property = parent::init_all_minutes();
		$property->
			set_defaultvalue(1)->
			set_id('all_mins')->
			set_min(0)->
			set_max(1)->
			filter_use_default();
		return $property;
	}
	public function init_all_hours(): myp\property_int {
		$property = parent::init_all_hours();
		$property->
			set_defaultvalue(1)->
			set_id('all_hours')->
			set_min(0)->
			set_max(1)->
			filter_use_default();
		return $property;
	}
	public function init_all_days(): myp\property_int {
		$property = parent::init_all_days();
		$property->
			set_defaultvalue(1)->
			set_id('all_days')->
			set_min(0)->
			set_max(1)->
			filter_use_default();
		return $property;
	}
	public function init_all_months(): myp\property_int {
		$property = parent::init_all_months();
		$property->
			set_defaultvalue(1)->
			set_id('all_months')->
			set_min(0)->
			set_max(1)->
			filter_use_default();
		return $property;
	}
	public function init_all_weekdays(): myp\property_int {
		$property = parent::init_all_weekdays();
		$property->
			set_defaultvalue(1)->
			set_id('all_weekdays')->
			set_min(0)->
			set_max(1)->
			filter_use_default();
		return $property;
	}
}
