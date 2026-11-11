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

namespace disks\zfs\setting;

use common\properties as myp;

class setting_properties extends grid_properties {
	public function init_showusedavail(): myp\property_bool {
		$caption = gettext('Display Used/Avail information from the filesystem instead of the Alloc/Free information from the pool.');
		$description = gettext('Used/Avail lists storage information after all redundancy is taken into account but is impacted by compression, deduplication and quotas. Alloc/Free lists the raw storage information of a pool.');
		$property = parent::init_showusedavail();
		$property->
			set_id('showusedavail')->
			set_caption($caption)->
			set_description($description)->
			set_defaultvalue(false)->
			filter_use_default();
		return $property;
	}
	public function init_scanondisk(): myp\property_bool {
		$caption = gettext('Read on-disk configuration.');
		$description = gettext('Reading the configuration from the disks is slower than reading from cache file.');
		$property = parent::init_scanondisk();
		$property->
			set_id('scanondisk')->
			set_caption($caption)->
			set_description($description)->
			set_defaultvalue(false)->
			filter_use_default();
		return $property;
	}
	public function test_capacity_warning($value = '') {
		if(is_string($value) && preg_match('/^(8[0-9])?$/',$value)):
			return $value;
		endif;
		return null;
	}
	public function init_capacity_warning(): myp\property_int {
		$caption = gettext('Set the warning threshold to a value between 80 and 89.');
		$description =
			gettext('An alert email is sent when the capacity of a pool exceeds the warning threshold.') .
			' ' .
			gettext('A cron job must be setup to schedule script /etc/capacitycheck.zfs.');
		$property = parent::init_capacity_warning();
		$property->
			set_id('capacity_warning')->
			set_caption($caption)->
			set_description($description)->
			set_defaultvalue('')->
			set_size(3)->
			set_maxlength(4)->
			set_placeholder('80')->
			set_filter(FILTER_CALLBACK)->
			set_filter_options([$this,'test_capacity_warning'])->
			set_message_error(sprintf('%s: %s',$property->get_title(),gettext('Must be a number between 80 and 89.')));
		return $property;
	}
	public function test_capacity_critical($value = '') {
		if(is_string($value) && preg_match('/^(9[0-5])?$/',$value)):
			return $value;
		endif;
		return null;
	}
	public function init_capacity_critical(): myp\property_int {
		$caption = gettext('Set the critical threshold to a value between 90 and 95.');
		$description =
			gettext('An alert email is sent when the capacity of a pool exceeds the critical threshold.') .
			' ' .
			gettext('A cron job must be setup to schedule script /etc/capacitycheck.zfs.');
		$property = parent::init_capacity_critical();
		$property->
			set_id('capacity_critical')->
			set_caption($caption)->
			set_description($description)->
			set_defaultvalue('')->
			set_size(3)->
			set_maxlength(4)->
			set_placeholder('90')->
			set_filter(FILTER_CALLBACK)->
			set_filter_options([$this,'test_capacity_critical'])->
			set_message_error(sprintf('%s: %s',$property->get_title(),gettext('Must be a number between 90 and 95.')));
		return $property;
	}
	public function init_destroy_dependents(): myp\property_bool {
		$caption = gettext('Destroy dataset and its dependents.');
		$description = gettext('When enabled, destroying a dataset will also destroy all dependents (datasets, snapshots and clones).');
		$property = parent::init_destroy_dependents();
		$property->
			set_id('destroy_dependents')->
			set_caption($caption)->
			set_description($description)->
			set_defaultvalue(false)->
			filter_use_default();
		return $property;
	}
}
