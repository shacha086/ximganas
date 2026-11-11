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
namespace system\access\user;

use common\properties as myp;

class grid_properties extends myp\container_row {
	protected $x_name;
	public function init_name(): myp\property_text {
		$title = gettext('Login Name');
		$property = $this->x_name = new myp\property_text($this);
		$property->
			set_name('login')->
			set_title($title);
		return $property;
	}
	final public function get_name(): myp\property_text {
		return $this->x_name ?? $this->init_name();
	}
	protected $x_fullname;
	public function init_fullname(): myp\property_text {
		$title = gettext('Full Name');
		$property = $this->x_fullname = new myp\property_text($this);
		$property->
			set_name('fullname')->
			set_title($title);
		return $property;
	}
	final public function get_fullname(): myp\property_text {
		return $this->x_fullname ?? $this->init_fullname();
	}
	protected $x_uid;
	public function init_uid(): myp\property_int {
		$title = gettext('User ID');
		$property = $this->x_uid = new myp\property_int($this);
		$property->
			set_name('id')->
			set_title($title);
		return $property;
	}
	final public function get_uid(): myp\property_int {
		return $this->x_uid ?? $this->init_uid();
	}
	protected $x_password;
	public function init_password(): myp\property_text {
		$title = gettext('Password');
		$property = $this->x_password = new myp\property_text($this);
		$property->
			set_name('password')->
			set_title($title);
		return $property;
	}
	final public function get_password(): myp\property_text {
		return $this->x_password ?? $this->init_password();
	}
	protected $x_passwordsha;
	public function init_passwordsha(): myp\property_text {
		$title = gettext('SHA Password');
		$property = $this->x_passwordsha = new myp\property_text($this);
		$property->
			set_name('passwordsha')->
			set_title($title);
		return $property;
	}
	final public function get_passwordsha(): myp\property_text {
		return $this->x_passwordsha ?? $this->init_passwordsha();
	}
	protected $x_passwordmd4;
	public function init_passwordmd4(): myp\property_text {
		$title = gettext('MD4 Password');
		$property = $this->x_passwordmd4 = new myp\property_text($this);
		$property->
			set_name('passwordmd4')->
			set_title($title);
		return $property;
	}
	final public function get_passwordmd4(): myp\property_text {
		return $this->x_passwordmd4 ?? $this->init_passwordmd4();
	}
	protected $x_usershell;
	public function init_usershell(): myp\property_list {
		$title = gettext('Shell');
		$property = $this->x_usershell = new myp\property_list($this);
		$property->
			set_name('shell')->
			set_title($title);
		return $property;
	}
	final public function get_usershell(): myp\property_list {
		return $this->x_usershell ?? $this->init_usershell();
	}
	protected $x_primary_group;
	public function init_primary_group(): myp\property_list {
		$title = gettext('Primary Group');
		$property = $this->x_primary_group = new myp\property_list($this);
		$property->
			set_name('primarygroup')->
			set_title($title);
		return $property;
	}
	final public function get_primary_group(): myp\property_list {
		return $this->x_primary_group ?? $this->init_primary_group();
	}
	protected $x_additional_groups;
	public function init_additional_groups(): myp\property_list_multi {
		$title = gettext('Groups');
		$property = $this->x_additional_groups = new myp\property_list_multi($this);
		$property->
			set_name('group')->
			set_title($title);
		return $property;
	}
	final public function get_additional_groups(): myp\property_list_multi {
		return $this->x_additional_groups ?? $this->init_additional_groups();
	}
	protected $x_homedir;
	public function init_homedir(): myp\property_text {
		$title = gettext('Home Directory');
		$property = $this->x_homedir = new myp\property_text($this);
		$property->
			set_name('homedir')->
			set_title($title);
		return $property;
	}
	final public function get_homedir(): myp\property_text {
		return $this->x_homedir ?? $this->init_homedir();
	}
	protected $x_user_portal_access;
	public function init_user_portal_access(): myp\property_list {
		$title = gettext('User Portal');
		$property = $this->x_user_portal_access = new myp\property_list();
		$property->
			set_name('userportal')->
			set_title($title);
		return $property;
	}
	final public function get_user_portal_access(): myp\property_list {
		return $this->x_user_portal_access ?? $this->init_user_portal_access();
	}
	protected $x_language;
	public function init_language(): myp\property_list {
		$title = gettext('Language');
		$property = $this->x_language = new myp\property_list();
		$property->
			set_name('language')->
			set_title($title);
		return $property;
	}
	final public function get_language(): myp\property_list {
		return $this->x_language ?? $this->init_language();
	}
	protected $x_timezone;
	public function init_timezone(): myp\property_list {
		$title = gettext('Timezone');
		$property = $this->x_timezone = new myp\property_list();
		$property->
			set_name('timezone')->
			set_title($title);
		return $property;
	}
	final public function get_timezone(): myp\property_list {
		return $this->x_timezone ?? $this->init_timezone();
	}
	protected $x_fm_enable;
	public function init_fm_enable(): myp\property_bool {
		$title = gettext('File Manager Access');
		$property = $this->x_fm_enable = new myp\property_bool();
		$property->
			set_name('fm_enable')->
			set_title($title);
		return $property;
	}
	final public function get_fm_enable(): myp\property_bool {
		return $this->x_fm_enable ?? $this->init_fm_enable();
	}
	protected $x_fmp_show_hidden_items;
	public function init_fmp_show_hidden_items(): myp\property_bool {
		$title = gettext('Hidden Items');
		$property = $this->x_fmp_show_hidden_items = new myp\property_bool();
		$property->
			set_name('fmp_show_hidden_items')->
			set_title($title);
		return $property;
	}
	final public function get_fmp_show_hidden_items(): myp\property_bool {
		return $this->x_fmp_show_hidden_items ?? $this->init_fmp_show_hidden_items();
	}
	protected $x_fmp_read;
	public function init_fmp_read(): myp\property_bool {
		$title = gettext('Read Permission');
		$property = $this->x_fmp_read = new myp\property_bool();
		$property->
			set_name('fmp_read')->
			set_title($title);
		return $property;
	}
	final public function get_fmp_read(): myp\property_bool {
		return $this->x_fmp_read ?? $this->init_fmp_read();
	}
	protected $x_fmp_create;
	public function init_fmp_create(): myp\property_bool {
		$title = gettext('Create Permission');
		$property = $this->x_fmp_create = new myp\property_bool();
		$property->
			set_name('fmp_create')->
			set_title($title);
		return $property;
	}
	final public function get_fmp_create(): myp\property_bool {
		return $this->x_fmp_create ?? $this->init_fmp_create();
	}
	protected $x_fmp_change;
	public function init_fmp_change(): myp\property_bool {
		$title = gettext('Change Permission');
		$property = $this->x_fmp_change = new myp\property_bool();
		$property->
			set_name('fmp_change')->
			set_title($title);
		return $property;
	}
	final public function get_fmp_change(): myp\property_bool {
		return $this->x_fmp_change ?? $this->init_fmp_change();
	}
	protected $x_fmp_delete;
	public function init_fmp_delete(): myp\property_bool {
		$title = gettext('Delete Permission');
		$property = $this->x_fmp_delete = new myp\property_bool();
		$property->
			set_name('fmp_delete')->
			set_title($title);
		return $property;
	}
	final public function get_fmp_delete(): myp\property_bool {
		return $this->x_fmp_delete ?? $this->init_fmp_delete();
	}
}
