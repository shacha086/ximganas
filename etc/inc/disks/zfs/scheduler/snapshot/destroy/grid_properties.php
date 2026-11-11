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

declare(strict_types = 1);

namespace disks\zfs\scheduler\snapshot\destroy;

use common\properties as myp;

class grid_properties extends myp\container_row {
	protected $x_path;
	public function init_path(): myp\property_list {
		$title = gettext('Path');
		$property = $this->x_path = new myp\property_list($this);
		$property->
			set_name('path')->
			set_title($title);
		return $property;
	}
	final public function get_path(): myp\property_list {
		return $this->x_path ?? $this->init_path();
	}
	protected $x_recursive;
	public function init_recursive(): myp\property_bool {
		$title = gettext('Recursive');
		$property = $this->x_recursive = new myp\property_bool($this);
		$property->
			set_name('recursive')->
			set_title($title);
		return $property;
	}
	final public function get_recursive(): myp\property_bool {
		return $this->x_recursive ?? $this->init_recursive();
	}
	protected $x_lifetime_val;
	public function init_lifetime_val(): myp\property_int {
		$title = gettext('Lifetime');
		$property = $this->x_lifetime_val = new myp\property_int($this);
		$property->
			set_name('lifetime_val')->
			set_title($title);
		return $property;
	}
	final public function get_lifetime_val(): myp\property_int {
		return $this->x_lifetime_val ?? $this->init_lifetime_val();
	}
	protected $x_lifetime_uom;
	public function init_lifetime_uom(): myp\property_list {
		$options = [
			'S' => gettext('Second(s)'),
			'M' => gettext('Minute(s)'),
			'H' => gettext('Hour(s)'),
			'd' => gettext('Day(s)'),
			'w' => gettext('Week(s)'),
			'm' => gettext('Month(s)'),
			'y' => gettext('Year(s)')
		];
		$property = $this->x_lifetime_uom = new myp\property_list($this);
		$property->
			set_name('lifetime_uom')->
			set_options($options)->
			set_title('');
		return $property;
	}
	final public function get_lifetime_uom(): myp\property_list {
		return $this->x_lifetime_uom ?? $this->init_lifetime_uom();
	}
	protected $x_scheduler;
	public function init_scheduler(): myp\property_bool {
		$title = gettext('Schedule Time');
		$property = $this->x_scheduler = new myp\property_bool($this);
		$property->
			set_name('scheduler')->
			set_title($title);
		return $property;
	}
	final public function get_scheduler(): myp\property_bool {
		return $this->x_scheduler ?? $this->init_scheduler();
	}
	protected $x_preset;
	public function init_preset(): myp\property_list {
		$options = [
			'@hourly' => gettext('Hourly'),
			'@daily' => gettext('Daily'),
			'@weekly' => gettext('Weekly'),
			'@monthly' => gettext('Monthly'),
			'@yearly' => gettext('Yearly'),
			'custom' => gettext('Custom Setting')
		];
		$title = gettext('Schedule');
		$property = $this->x_preset = new myp\property_list($this);
		$property->
			set_name('preset')->
			set_options($options)->
			set_title($title);
		return $property;
	}
	final public function get_preset(): myp\property_list {
		return $this->x_preset ?? $this->init_preset();
	}
	protected $x_minutes;
	public function init_minutes(): myp\property_list_multi {
		$title = gettext('Minutes');
		$property = $this->x_minutes = new myp\property_list_multi($this);
		$property->
			set_name('minute')->
			set_title($title);
		return $property;
	}
	final public function get_minutes(): myp\property_list_multi {
		return $this->x_minutes ?? $this->init_minutes();
	}
	protected $x_hours;
	public function init_hours(): myp\property_list_multi {
		$title = gettext('Hours');
		$property = $this->x_hours = new myp\property_list_multi($this);
		$property->
			set_name('hour')->
			set_title($title);
		return $property;
	}
	final public function get_hours(): myp\property_list_multi {
		return $this->x_hours ?? $this->init_hours();
	}
	protected $x_days;
	public function init_days(): myp\property_list_multi {
		$title = gettext('Days');
		$property = $this->x_days = new myp\property_list_multi($this);
		$property->
			set_name('day')->
			set_title($title);
		return $property;
	}
	final public function get_days(): myp\property_list_multi {
		return $this->x_days ?? $this->init_days();
	}
	protected $x_months;
	public function init_months(): myp\property_list_multi {
		$title = gettext('Months');
		$property = $this->x_months = new myp\property_list_multi($this);
		$property->
			set_name('month')->
			set_title($title);
		return $property;
	}
	final public function get_months(): myp\property_list_multi {
		return $this->x_months ?? $this->init_months();
	}
	protected $x_weekdays;
	public function init_weekdays(): myp\property_list_multi {
		$title = gettext('Weekdays');
		$property = $this->x_weekdays = new myp\property_list_multi($this);
		$property->
			set_name('weekday')->
			set_title($title);
		return $property;
	}
	final public function get_weekdays(): myp\property_list_multi {
		return $this->x_weekdays ?? $this->init_weekdays();
	}
	protected $x_all_minutes;
	public function init_all_minutes(): myp\property_int {
		$title = gettext('All Minutes');
		$property = $this->x_all_minutes = new myp\property_int($this);
		$property->
			set_name('all_mins')->
			set_title($title);
		return $property;
	}
	final public function get_all_minutes(): myp\property_int {
		return $this->x_all_minutes ?? $this->init_all_minutes();
	}
	protected $x_all_hours;
	public function init_all_hours(): myp\property_int {
		$title = gettext('All Hours');
		$property = $this->x_all_hours = new myp\property_int($this);
		$property->
			set_name('all_hours')->
			set_title($title);
		return $property;
	}
	final public function get_all_hours(): myp\property_int {
		return $this->x_all_hours ?? $this->init_all_hours();
	}
	protected $x_all_days;
	public function init_all_days(): myp\property_int {
		$title = gettext('All Days');
		$property = $this->x_all_days = new myp\property_int($this);
		$property->
			set_name('all_days')->
			set_title($title);
		return $property;
	}
	final public function get_all_days(): myp\property_int {
		return $this->x_all_days ?? $this->init_all_days();
	}
	protected $x_all_months;
	public function init_all_months(): myp\property_int {
		$title = gettext('All Months');
		$property = $this->x_all_months = new myp\property_int($this);
		$property->
			set_name('all_months')->
			set_title($title);
		return $property;
	}
	final public function get_all_months(): myp\property_int {
		return $this->x_all_months ?? $this->init_all_months();
	}
	protected $x_all_weekdays;
	public function init_all_weekdays(): myp\property_int {
		$title = gettext('All Weekdays');
		$property = $this->x_all_weekdays = new myp\property_int($this);
		$property->
			set_name('all_weekdays')->
			set_title($title);
		return $property;
	}
	final public function get_all_weekdays(): myp\property_int {
		return $this->x_all_weekdays ?? $this->init_all_weekdays();
	}
}
