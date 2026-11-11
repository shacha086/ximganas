<?php
/*
	co_sphere.php

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

use gui\document;
/*
use function gettext,implode,is_null,is_object,preg_match,sprintf,unicode_escape_javascript;
 */
class co_sphere_scriptname {
	protected $x_basename = null;
	protected $x_extension = null;
//	methods
	public function __construct(?string $basename = null,?string $extension = null) {
		$this->set_basename($basename);
		$this->set_extension($extension);
	}
	public function set_basename(string $basename) {
		$this->x_basename = $basename;
		return $this;
	}
	public function get_basename() {
		return $this->x_basename;
	}
	public function set_extension(string $extension) {
		$this->x_extension = $extension;
		return $this;
	}
	public function get_extension() {
		return $this->x_extension;
	}
	public function get_scriptname() {
		return sprintf('%s.%s',$this->get_basename(),$this->get_extension());
	}
	public function get_location() {
		return sprintf('Location: %s',$this->get_scriptname());
	}
}
class co_sphere_level1 extends co_sphere_scriptname { // for settings, services, row and grid
//	parent
	public $parent = null;
	public $grid = [];
	public $row = [];
	public $row_default = [];
	protected $x_enadis = false;
	protected $x_class_button = 'formbtn';
//	methods
	public function __destruct() {
		unset($this->parent);
	}
	public function get_parent() {
		if(!is_object($this->parent)):
			$this->parent = new co_sphere_scriptname($this->get_basename(),$this->get_extension());
		endif;
		return $this->parent;
	}
	/**
	 *	Enable/disable enable/disable option
	 *	@param bool $flag
	 *	@return $this
	 */
	public function set_enadis(bool $flag = false) {
		$this->x_enadis = $flag;
		return $this;
	}
	/**
	 *	Returns the status of the enable/disable option.
	 *	@return bool
	 */
	public function is_enadis_enabled() {
		return $this->x_enadis;
	}
	public function doj() {
		$output = [];
		$output[] = '';
		return implode("\n",$output);
	}
	public function get_js_on_load() {
		return '';
	}
	public function get_js_document_ready() {
		return '';
	}
	public function get_js() {
		return '';
	}
	public function html_button(?string $value = null,?string $content = null,?string $id = null) {
		$element = 'button';
		if(is_null($value)):
			$value = 'cancel';
		endif;
		if(is_null($id)):
			$id = sprintf('%1$s_%2$s',$element,$value);
		endif;
		if(is_null($content)):
			$content = gettext('Cancel');
		endif;
		$button_attributes = [
			'name' => 'submit',
			'type' => 'submit',
			'class' => $this->x_class_button,
			'value' => $value,
			'id' => $id
		];
		if($value === 'cancel'):
			$button_attributes['formnovalidate'] = 'formnovalidate';
		endif;
		$root = new document();
		$o_button = $root->addElement($element,$button_attributes,$content);
		return $root->get_html();
	}
}
class co_sphere_level2 extends co_sphere_level1 { // for row and grid
	protected $x_notifier = null;
	protected $x_notifier_processor = null;
	public $row_id = null;
	protected $x_row_identifier = null;
	protected $x_lock = false;
//	methods
	/**
	 *	Enable/disable record lock support
	 *	@param bool $flag
	 *	@return $this
	 */
	public function set_lock(bool $flag = false) {
		$this->x_lock = $flag;
		return $this;
	}
	/**
	 *	Returns true when record lock support is enabled
	 *	@return bool
	 */
	public function is_lock_enabled() {
		return $this->x_lock;
	}
	public function set_notifier(?string $notifier = null) {
		if(isset($notifier)):
			$this->x_notifier = $notifier;
			$this->x_notifier_processor = $notifier . '_process_updatenotification';
		else:
			$this->x_notifier = $notifier;
			$this->x_notifier_processor = '_process_updatenotification';
		endif;
		return $this;
	}
	public function get_notifier() {
		return $this->x_notifier ?? false;
	}
	public function row_identifier(?string $row_identifier = null) {
		if(isset($row_identifier)):
			if(preg_match('/^[a-z]+$/',$row_identifier) == 1):
				$this->x_row_identifier = $row_identifier;
			endif;
		endif;
		return $this->x_row_identifier ?? false;
	}
	public function set_row_key($key = null) {
		$this->row_id = $key;
		return $this;
	}
	public function get_row_key() {
		return $this->row_id;
	}
	public function get_row_identifier() {
		return $this->x_row_identifier ?? false;
	}
	public function set_row_identifier(?string $row_identifier = null) {
		$this->x_row_identifier = $row_identifier;
		return $this;
	}
	public function get_row_identifier_value() {
		return $this->row[$this->x_row_identifier] ?? null;
	}
}
class co_sphere_settings extends co_sphere_level1 {
//	methods
	public function copyrowtogrid() {
		//	settings uses one record, therefore row can be soft-copied to grid
		foreach($this->row as $row_key => $row_val):
			$this->grid[$row_key] = $row_val;
		endforeach;
	}
}
class co_sphere_row extends co_sphere_level2 {
//	methods
	public function doj(bool $with_envelope = true) {
		$output = [];
		if($with_envelope):
			$output[] = '<script>';
			$output[] = '//<![CDATA[';
		endif;
		$output[] = '$(window).on("load", function() {';
		//	Init spinner.
		$output[] = "\t" . '$("#iform").submit(function() { spinner(); });';
		$output[] = "\t" . '$(".spin").click(function() { spinner(); });';
		$output[] = '});';
		if($with_envelope):
			$output[] = '//]]>';
			$output[] = '</script>';
			$output[] = '';
		endif;
		return implode("\n",$output);
	}
	public function upsert() {
		//	update existing grid record with row record or add row record to grid
		if($this->row_id === false):
			$this->grid[] = $this->row;
		else:
			foreach($this->row as $row_key => $row_val):
				$this->grid[$this->row_id][$row_key] = $row_val;
			endforeach;
		endif;
	}
}
class co_sphere_grid extends co_sphere_level2 {
//	children
	public $modify = null; // modify
	public $maintain = null; // maintenance
	public $inform = null; // information
//	transaction manager
	protected $x_cbm_suffix = '';
//	checkbox member array
	protected $x_cbm_name = 'cbm_grid';
	public $cbm_grid = [];
	public $cbm_row = [];
//	gtext
	protected $x_cbm_delete = null;
	protected $x_cbm_disable = null;
	protected $x_cbm_enable = null;
	protected $x_cbm_lock = null;
	protected $x_cbm_toggle = null;
	protected $x_cbm_unlock = null;
	protected $x_cbm_delete_confirm = null;
	protected $x_cbm_disable_confirm = null;
	protected $x_cbm_enable_confirm = null;
	protected $x_cbm_lock_confirm = null;
	protected $x_cbm_toggle_confirm = null;
	protected $x_cbm_unlock_confirm = null;
	protected $x_sym_add = null;
	protected $x_sym_mod = null;
	protected $x_sym_del = null;
	protected $x_sym_loc = null;
	protected $x_sym_unl = null;
	protected $x_sym_mai = null;
	protected $x_sym_inf = null;
	protected $x_sym_mup = null;
	protected $x_sym_mdn = null;
//	html id tags
	protected $x_cbm_button_id_delete = 'delete_selected_rows';
	protected $x_cbm_button_id_disable = 'disable_selected_rows';
	protected $x_cbm_button_id_enable = 'enable_selected_rows';
	protected $x_cbm_button_id_toggle = 'toggle_selected_rows';
	protected $x_cbm_checkbox_id_toggle = 'togglemembers';
//	html value tags
	protected $x_cbm_button_val_delete = 'rows.delete';
	protected $x_cbm_button_val_disable = 'rows.disable';
	protected $x_cbm_button_val_enable = 'rows.enable';
	protected $x_cbm_button_val_toggle = 'rows.toggle';
//	methods
	public function __destruct() {
		unset($this->inform,$this->maintain,$this->modify);
		parent::__destruct();
	}
	public function get_modify() {
		if(!is_object($this->modify)):
			$this->modify = new co_sphere_scriptname($this->get_basename(),$this->get_extension());
		endif;
		return $this->modify;
	}
	public function get_maintain() {
		if(!is_object($this->maintain)):
			$this->maintain = new co_sphere_scriptname($this->get_basename(),$this->get_extension());
		endif;
		return $this->maintain;
	}
	public function get_inform() {
		if(!is_object($this->inform)):
			$this->inform = new co_sphere_scriptname($this->get_basename(),$this->get_extension());
		endif;
		return $this->inform;
	}
	public function get_notifier_processor() {
		return $this->x_notifier_processor ?? false;
	}
	public function toggle() {
		return $this->is_enadis_enabled() && calc_enabletogglemode();
	}
	public function get_cbm_suffix() {
		return $this->x_cbm_suffix;
	}
	public function set_cbm_suffix(string $value) {
		if(preg_match('/^[a-z\d_]+$/i',$value)):
			$this->x_cbm_suffix = $value;
		endif;
		return $this;
	}
	public function get_cbm_name() {
		return $this->x_cbm_name . $this->get_cbm_suffix();
	}
	public function set_cbm_name(string $value) {
		if(preg_match('/^\S+$/i',$value)):
			$this->x_cbm_name = $value;
		endif;
		return $this;
	}
	public function get_cbm_button_id_delete() {
		return $this->x_cbm_button_id_delete . $this->get_cbm_suffix();
	}
	public function set_cbm_button_id_delete(string $id) {
		if(preg_match('/^\S+$/',$id)):
			$this->x_cbm_button_id_delete = $id;
		endif;
		return $this;
	}
	public function get_cbm_button_id_disable() {
		return $this->x_cbm_button_id_disable . $this->get_cbm_suffix();
	}
	public function set_cbm_button_id_disable(string $id) {
		if(preg_match('/^\S+$/',$id)):
			$this->x_cbm_button_id_disable = $id;
		endif;
		return $this;
	}
	public function get_cbm_button_id_enable() {
		return $this->x_cbm_button_id_enable . $this->get_cbm_suffix();
	}
	public function set_cbm_button_id_enable(string $id) {
		if(preg_match('/^\S+$/',$id)):
			$this->x_cbm_button_id_enable = $id;
		endif;
		return $this;
	}
	public function get_cbm_button_id_toggle() {
		return $this->x_cbm_button_id_toggle . $this->get_cbm_suffix();
	}
	public function set_cbm_button_id_toggle(string $id) {
		if(preg_match('/^\S+$/',$id)):
			$this->x_cbm_button_id_toggle = $id;
		endif;
		return $this;
	}
	public function get_cbm_checkbox_id_toggle() {
		return $this->x_cbm_checkbox_id_toggle . $this->get_cbm_suffix();
	}
	public function set_cbm_checkbox_id_toggle(string $id) {
		if(preg_match('/^\S+$/',$id)):
			$this->x_cbm_checkbox_id_toggle = $id;
		endif;
		return $this;
	}
	public function get_cbm_button_val_delete() {
		return $this->x_cbm_button_val_delete . $this->get_cbm_suffix();
	}
	public function set_cbm_button_val_delete(string $value) {
		if(preg_match('/^\S+$/',$id)):
			$this->x_cbm_button_val_delete = $value;
		endif;
		return $this;
	}
	public function get_cbm_button_val_disable() {
		return $this->x_cbm_button_val_disable . $this->get_cbm_suffix();
	}
	public function set_cbm_button_val_disable(string $value) {
		if(preg_match('/^\S+$/',$id)):
			$this->x_cbm_button_val_disable = $value;
		endif;
		return $this;
	}
	public function get_cbm_button_val_enable() {
		return $this->x_cbm_button_val_enable . $this->get_cbm_suffix();
	}
	public function set_cbm_button_val_enable(string $value) {
		if(preg_match('/^\S+$/',$id)):
			$this->x_cbm_button_val_enable = $value;
		endif;
		return $this;
	}
	public function get_cbm_button_val_toggle() {
		return $this->x_cbm_button_val_toggle . $this->get_cbm_suffix();
	}
	public function set_cbm_button_val_toggle(string $value) {
		if(preg_match('/^\S+$/',$id)):
			$this->x_cbm_button_val_toggle = $value;
		endif;
		return $this;
	}
	public function cbm_delete(?string $message = null) {
		if(isset($message)):
			$this->x_cbm_delete = $message;
		endif;
		return $this->x_cbm_delete ?? gettext('Delete Selected Records');
	}
	public function cbm_delete_confirm(?string $message = null) {
		if(isset($message)):
			$this->x_cbm_delete_confirm = $message;
		endif;
		return $this->x_cbm_delete_confirm ?? gettext('Do you want to delete selected records?');
	}
	public function cbm_disable(?string $message = null) {
		if(isset($message)):
			$this->x_cbm_disable = $message;
		endif;
		return $this->x_cbm_disable ?? gettext('Disable Selected Records');
	}
	public function cbm_disable_confirm(?string $message = null) {
		if(isset($message)):
			$this->x_cbm_disable_confirm = $message;
		endif;
		return $this->x_cbm_disable_confirm ?? gettext('Do you want to disable selected records?');
	}
	public function cbm_enable(?string $message = null) {
		if(isset($message)):
			$this->x_cbm_enable = $message;
		endif;
		return $this->x_cbm_enable ?? gettext('Enable Selected Records');
	}
	public function cbm_enable_confirm(?string $message = null) {
		if(isset($message)):
			$this->x_cbm_enable_confirm = $message;
		endif;
		return $this->x_cbm_enable_confirm ?? gettext('Do you want to enable selected records?');
	}
	public function cbm_lock(?string $message = null) {
		if(isset($message)):
			$this->x_cbm_lock = $message;
		endif;
		return $this->x_cbm_lock ?? gettext('Lock Selected Records');
	}
	public function cbm_lock_confirm(?string $message = null) {
		if(isset($message)):
			$this->x_cbm_lock_confirm = $message;
		endif;
		return $this->x_cbm_lock_confirm ?? gettext('Do you want to lock selected records?');
	}
	public function cbm_toggle(?string $message = null) {
		if(isset($message)):
			$this->x_cbm_toggle = $message;
		endif;
		return $this->x_cbm_toggle ?? gettext('Toggle Selected Records');
	}
	public function cbm_toggle_confirm(?string $message = null) {
		if(isset($message)):
			$this->x_cbm_toggle_confirm = $message;
		endif;
		return $this->x_cbm_toggle_confirm ?? gettext('Do you want to toggle selected records?');
	}
	public function cbm_unlock(?string $message = null) {
		if(isset($message)):
			$this->x_cbm_unlock = $message;
		endif;
		return $this->x_cbm_unlock ?? gettext('Unlock Selected Records');
	}
	public function cbm_unlock_confirm(?string $message = null) {
		if(isset($message)):
			$this->x_cbm_unlock_confirm = $message;
		endif;
		return $this->x_cbm_unlock_confirm ?? gettext('Do you want to unlock selected records?');
	}
	public function getmsg_cbm_delete() {
		return $this->x_cbm_delete ?? gettext('Delete Selected Records');
	}
	public function getmsg_cbm_delete_confirm() {
		return $this->x_cbm_delete_confirm ?? gettext('Do you want to delete selected records?');
	}
	public function getmsg_cbm_disable() {
		return $this->x_cbm_disable ?? gettext('Disable Selected Records');
	}
	public function getmsg_cbm_disable_confirm() {
		return $this->x_cbm_disable_confirm ?? gettext('Do you want to disable selected records?');
	}
	public function getmsg_cbm_enable() {
		return $this->x_cbm_enable ?? gettext('Enable Selected Records');
	}
	public function getmsg_cbm_enable_confirm() {
		return $this->x_cbm_enable_confirm ?? gettext('Do you want to enable selected records?');
	}
	public function getmsg_cbm_lock() {
		return $this->x_cbm_lock ?? gettext('Lock Selected Records');
	}
	public function getmsg_cbm_lock_confirm() {
		return $this->x_cbm_lock_confirm ?? gettext('Do you want to lock selected records?');
	}
	public function getmsg_cbm_toggle() {
		return $this->x_cbm_toggle ?? gettext('Toggle Selected Records');
	}
	public function getmsg_cbm_toggle_confirm() {
		return $this->x_cbm_toggle_confirm ?? gettext('Do you want to toggle selected records?');
	}
	public function getmsg_cbm_unlock() {
		return $this->x_cbm_unlock ?? gettext('Unlock Selected Records');
	}
	public function getmsg_cbm_unlock_confirm() {
		return $this->x_cbm_unlock_confirm ?? gettext('Do you want to unlock selected records?');
	}
	public function getmsg_sym_add() {
		return $this->x_sym_add ?? gettext('Add Record');
	}
	public function getmsg_sym_del(?string $message = null) {
		return $this->x_sym_del ?? gettext('Record is marked for deletion');
	}
	public function getmsg_sym_inf() {
		return $this->x_sym_inf ?? gettext('Record Information');
	}
	public function getmsg_sym_loc() {
		return $this->x_sym_loc ?? gettext('Record is protected');
	}
	public function getmsg_sym_mai() {
		return $this->x_sym_mai ?? gettext('Record Maintenance');
	}
	public function getmsg_sym_mdn() {
		return $this->x_sym_mdn ?? gettext('Move down');
	}
	public function getmsg_sym_mod() {
		return $this->x_sym_mod ?? gettext('Edit Record');
	}
	public function getmsg_sym_mup() {
		return $this->x_sym_mup ?? gettext('Move up');
	}
	public function getmsg_sym_unl() {
		return $this->x_sym_unl ?? gettext('Record is unlocked');
	}
	public function setmsg_cbm_delete(?string $message = null) {
		$this->x_cbm_delete = $message;
		return $this;
	}
	public function setmsg_cbm_delete_confirm(?string $message = null) {
		$this->x_cbm_delete_confirm = $message;
		return $this;
	}
	public function setmsg_cbm_disable(?string $message = null) {
		$this->x_cbm_disable = $message;
		return $this;
	}
	public function setmsg_cbm_disable_confirm(?string $message = null) {
		$this->x_cbm_disable_confirm = $message;
		return $this;
	}
	public function setmsg_cbm_enable(?string $message = null) {
		$this->x_cbm_enable = $message;
		return $this;
	}
	public function setmsg_cbm_enable_confirm(?string $message = null) {
		$this->x_cbm_enable_confirm = $message;
		return $this;
	}
	public function setmsg_cbm_lock(?string $message = null) {
		$this->x_cbm_lock = $message;
		return $this;
	}
	public function setmsg_cbm_lock_confirm(?string $message = null) {
		$this->x_cbm_lock_confirm = $message;
		return $this;
	}
	public function setmsg_cbm_toggle(?string $message = null) {
		$this->x_cbm_toggle = $message;
		return $this;
	}
	public function setmsg_cbm_toggle_confirm(?string $message = null) {
		$this->x_cbm_toggle_confirm = $message;
		return $this;
	}
	public function setmsg_cbm_unlock(?string $message = null) {
		$this->x_cbm_unlock = $message;
		return $this;
	}
	public function setmsg_cbm_unlock_confirm(?string $message = null) {
		$this->x_cbm_unlock_confirm = $message;
		return $this;
	}
	public function setmsg_sym_add(?string $message = null) {
		$this->x_sym_add = $message;
		return $this;
	}
	public function setmsg_sym_del(?string $message = null) {
		$this->x_sym_del = $message;
		return $this;
	}
	public function setmsg_sym_inf(?string $message = null) {
		$this->x_sym_inf = $message;
		return $this;
	}
	public function setmsg_sym_loc(?string $message = null) {
		$this->x_sym_loc = $message;
		return $this;
	}
	public function setmsg_sym_mai(?string $message = null) {
		$this->x_sym_mai = $message;
		return $this;
	}
	public function setmsg_sym_mdn(?string $message = null) {
		$this->x_sym_mdn = $message;
		return $this;
	}
	public function setmsg_sym_mod(?string $message = null) {
		$this->x_sym_mod = $message;
		return $this;
	}
	public function setmsg_sym_mup(?string $message = null) {
		$this->x_sym_mup = $message;
		return $this;
	}
	public function setmsg_sym_unl(?string $message = null) {
		$this->x_sym_unl = $message;
		return $this;
	}
	public function sym_add(?string $message = null) {
		if(isset($message)):
			$this->x_sym_add = $message;
		endif;
		return $this->x_sym_add ?? gettext('Add Record');
	}
	public function sym_del(?string $message = null) {
		if(isset($message)):
			$this->x_sym_del = $message;
		endif;
		return $this->x_sym_del ?? gettext('Record is marked for deletion');
	}
	public function sym_inf(?string $message = null) {
		if(isset($message)):
			$this->x_sym_inf = $message;
		endif;
		return $this->x_sym_inf ?? gettext('Record Information');
	}
	public function sym_loc(?string $message = null) {
		if(isset($message)):
			$this->x_sym_loc = $message;
		endif;
		return $this->x_sym_loc ?? gettext('Record is protected');
	}
	public function sym_mai(?string $message = null) {
		if(isset($message)):
			$this->x_sym_mai = $message;
		endif;
		return $this->x_sym_mai ?? gettext('Record Maintenance');
	}
	public function sym_mdn(?string $message = null) {
		if(isset($message)):
			$this->x_sym_mdn = $message;
		endif;
		return $this->x_sym_mdn ?? gettext('Move down');
	}
	public function sym_mod(?string $message = null) {
		if(isset($message)):
			$this->x_sym_mod = $message;
		endif;
		return $this->x_sym_mod ?? gettext('Edit Record');
	}
	public function sym_mup(?string $message = null) {
		if(isset($message)):
			$this->x_sym_mup = $message;
		endif;
		return $this->x_sym_mup ?? gettext('Move up');
	}
	public function sym_unl(?string $message = null) {
		if(isset($message)):
			$this->x_sym_unl = $message;
		endif;
		return $this->x_sym_unl ?? gettext('Record is unlocked');
	}
	public function doj(bool $with_envelope = true) {
		$output = [];
		if($with_envelope):
			$output[] = '<script>';
			$output[] = '//<![CDATA[';
		endif;
		$output[] = '$(window).on("load", function() {';
		//	Init action buttons.
		if($this->is_enadis_enabled()):
			if($this->toggle()):
				$output[] = "\t" . '$("#' . $this->get_cbm_button_id_toggle() . '").click(function () {';
				$output[] = "\t\t" . 'return confirm(' . unicode_escape_javascript($this->getmsg_cbm_toggle_confirm()) . ');';
				$output[] = "\t" . '});';
			else:
				$output[] = "\t" . '$("#' . $this->get_cbm_button_id_enable() . '").click(function () {';
				$output[] = "\t\t" . 'return confirm(' . unicode_escape_javascript($this->getmsg_cbm_enable_confirm()) . ');';
				$output[] = "\t" . '});';
				$output[] = "\t" . '$("#' . $this->get_cbm_button_id_disable() . '").click(function () {';
				$output[] = "\t\t" . 'return confirm(' . unicode_escape_javascript($this->getmsg_cbm_disable_confirm()) . ');';
				$output[] = "\t" . '});';
			endif;
		endif;
		$output[] = "\t" . '$("#' . $this->get_cbm_button_id_delete() . '").click(function () {';
		$output[] = "\t\t" . 'return confirm(' . unicode_escape_javascript($this->getmsg_cbm_delete_confirm()) . ');';
		$output[] = "\t" . '});';
		//	Disable action buttons.
		$output[] = "\t" . 'ab_disable' . $this->get_cbm_suffix() . '(true);';
		//	Init toggle checkbox.
		$output[] = "\t" . '$("#' . $this->get_cbm_checkbox_id_toggle() . '").click(function() {';
		$output[] = "\t\t" . 'cb_tbn' . $this->get_cbm_suffix() . '(this,"' . $this->get_cbm_name() . '[]");';
		$output[] = "\t" . '});';
		//	Init member checkboxes.
		$output[] = "\t" . '$("input[name=\'' . $this->get_cbm_name() . '[]\']").click(function() {';
		$output[] = "\t\t" . 'ab_control' . $this->get_cbm_suffix() . '(this,"' . $this->get_cbm_name() . '[]");';
		$output[] = "\t" . '});';
		//	Init spinner.
		if($with_envelope):
			$output[] = "\t" . '$("#iform").submit(function() { spinner(); });';
			$output[] = "\t" . '$(".spin").click(function() { spinner(); });';
		endif;
		$output[] = '});';
		$output[] = 'function ab_disable' . $this->get_cbm_suffix() . '(flag) {';
		if($this->is_enadis_enabled()):
			if($this->toggle()):
				$output[] = "\t" . '$("#' . $this->get_cbm_button_id_toggle() . '").prop("disabled",flag);';
			else:
				$output[] = "\t" . '$("#' . $this->get_cbm_button_id_enable() . '").prop("disabled",flag);';
				$output[] = "\t" . '$("#' . $this->get_cbm_button_id_disable() . '").prop("disabled",flag);';
			endif;
		endif;
		$output[] = "\t" . '$("#' . $this->get_cbm_button_id_delete() . '").prop("disabled",flag);';
		$output[] = '}';
		$output[] = 'function cb_tbn' . $this->get_cbm_suffix() . '(ego,tbn) {';
		$output[] = "\t" . 'var cba = $("input[name=\'"+tbn+"\']").filter(":enabled");';
		$output[] = "\t" . 'cba.prop("checked", function(_, checked) { return !checked; });';
		$output[] = "\t" . 'ab_disable' . $this->get_cbm_suffix() . '(1 > cba.filter(":checked").length);';
		$output[] = "\t" . 'ego.checked = false;';
		$output[] = '}';
		$output[] = 'function ab_control' . $this->get_cbm_suffix() . '(ego,tbn) {';
		$output[] = "\t" . 'var cba = $("input[name=\'"+tbn+"\']").filter(":enabled");';
		$output[] = "\t" . 'ab_disable' . $this->get_cbm_suffix() . '(1 > cba.filter(":checked").length);';
		$output[] = '}';
		if($with_envelope):
			$output[] = '//]]>';
			$output[] = '</script>';
			$output[] = '';
		endif;
		return implode("\n",$output);
	}
	public function get_js_on_load() {
		$output = [];
		//	Init action buttons.
		if($this->is_enadis_enabled()):
			if($this->toggle()):
				$output[] = "\t" . '$("#' . $this->get_cbm_button_id_toggle() . '").click(function () {';
				$output[] = "\t\t" . 'return confirm(' . unicode_escape_javascript($this->getmsg_cbm_toggle_confirm()) . ');';
				$output[] = "\t" . '});';
			else:
				$output[] = "\t" . '$("#' . $this->get_cbm_button_id_enable() . '").click(function () {';
				$output[] = "\t\t" . 'return confirm(' . unicode_escape_javascript($this->getmsg_cbm_enable_confirm()) . ');';
				$output[] = "\t" . '});';
				$output[] = "\t" . '$("#' . $this->get_cbm_button_id_disable() . '").click(function () {';
				$output[] = "\t\t" . 'return confirm(' . unicode_escape_javascript($this->getmsg_cbm_disable_confirm()) . ');';
				$output[] = "\t" . '});';
			endif;
		endif;
		$output[] = "\t" . '$("#' . $this->get_cbm_button_id_delete() . '").click(function () {';
		$output[] = "\t\t" . 'return confirm(' . unicode_escape_javascript($this->getmsg_cbm_delete_confirm()) . ');';
		$output[] = "\t" . '});';
		//	Disable action buttons.
		$output[] = "\t" . 'ab_disable' . $this->get_cbm_suffix() . '(true);';
		//	Init toggle checkbox.
		$output[] = "\t" . '$("#' . $this->get_cbm_checkbox_id_toggle() . '").click(function() {';
		$output[] = "\t\t" . 'cb_tbn' . $this->get_cbm_suffix() . '(this,"' . $this->get_cbm_name() . '[]");';
		$output[] = "\t" . '});';
		//	Init member checkboxes.
		$output[] = "\t" . '$("input[name=\'' . $this->get_cbm_name() . '[]\']").click(function() {';
		$output[] = "\t\t" . 'ab_control' . $this->get_cbm_suffix() . '(this,"' . $this->get_cbm_name() . '[]");';
		$output[] = "\t" . '});';
		return implode("\n",$output);
	}
	public function get_js() {
		$output = [];
		$output[] = 'function ab_disable' . $this->get_cbm_suffix() . '(flag) {';
		if($this->is_enadis_enabled()):
			if($this->toggle()):
				$output[] = "\t" . '$("#' . $this->get_cbm_button_id_toggle() . '").prop("disabled",flag);';
			else:
				$output[] = "\t" . '$("#' . $this->get_cbm_button_id_enable() . '").prop("disabled",flag);';
				$output[] = "\t" . '$("#' . $this->get_cbm_button_id_disable() . '").prop("disabled",flag);';
			endif;
		endif;
		$output[] = "\t" . '$("#' . $this->get_cbm_button_id_delete() . '").prop("disabled",flag);';
		$output[] = '}';
		$output[] = 'function cb_tbn' . $this->get_cbm_suffix() . '(ego,tbn) {';
		$output[] = "\t" . 'var cba = $("input[name=\'"+tbn+"\']").filter(":enabled");';
		$output[] = "\t" . 'cba.prop("checked", function(_, checked) { return !checked; });';
		$output[] = "\t" . 'ab_disable' . $this->get_cbm_suffix() . '(1 > cba.filter(":checked").length);';
		$output[] = "\t" . 'ego.checked = false;';
		$output[] = '}';
		$output[] = 'function ab_control' . $this->get_cbm_suffix() . '(ego,tbn) {';
		$output[] = "\t" . 'var cba = $("input[name=\'"+tbn+"\']").filter(":enabled");';
		$output[] = "\t" . 'ab_disable' . $this->get_cbm_suffix() . '(1 > cba.filter(":checked").length);';
		$output[] = '}';
		return implode("\n",$output);
	}
	public function html_button_delete_rows() {
		return $this->html_button($this->get_cbm_button_val_delete(),$this->getmsg_cbm_delete(),$this->get_cbm_button_id_delete());
	}
	public function html_button_disable_rows() {
		return $this->html_button($this->get_cbm_button_val_disable(),$this->getmsg_cbm_disable(),$this->get_cbm_button_id_disable());
	}
	public function html_button_enable_rows() {
		return $this->html_button($this->get_cbm_button_val_enable(),$this->getmsg_cbm_enable(),$this->get_cbm_button_id_enable());
	}
	public function html_button_toggle_rows() {
		return $this->html_button($this->get_cbm_button_val_toggle(),$this->getmsg_cbm_toggle(),$this->get_cbm_button_id_toggle());
	}
	public function html_checkbox_cbm(bool $disabled = false) {
		$element = 'input';
		$identifier = $this->get_row_identifier_value();
		$input_attributes = [
			'type' => 'checkbox',
			'name' => $this->get_cbm_name() . '[]',
			'value' => $identifier,
			'id' => $identifier,
			'class' => 'oneemhigh'
		];
		if($disabled):
			$input_attributes['disabled'] = 'disabled';
		endif;
		$root = new document();
		$o_input = $root->addElement($element,$input_attributes);
		return $root->get_html();
	}
	public function html_checkbox_toggle_cbm() {
		$element = 'input';
		$input_attributes = [
			'type' => 'checkbox',
			'name' => $this->get_cbm_checkbox_id_toggle(),
			'id' => $this->get_cbm_checkbox_id_toggle(),
			'title' => gettext('Invert Selection'),
			'class' => 'oneemhigh'
		];
		$root = new document();
		$o_input = $root->addElement($element,$input_attributes);
		return $root->get_html();
	}
	public function html_toolbox(bool $notprotected = true,bool $notdirty = true) {
		global $g_img;

		$root = new document();
		$o_td = $root->addTD();
		if($notdirty && $notprotected):
//			record is editable
			$querystring = http_build_query(['submit' => 'edit',$this->get_row_identifier() => $this->get_row_identifier_value()],'',ini_get('arg_separator.output'),PHP_QUERY_RFC3986);
			$link = sprintf('%s?%s',$this->get_modify()->get_scriptname(),$querystring);
			$o_td->addA(attributes: ['href' => $link,'class' => 'spin oneemhigh monotoolbox','title' => $this->getmsg_sym_mod()],value: $g_img['unicode.mod']);
		elseif($notprotected):
//			record is dirty
			$o_td->addDIV(attributes: ['class' => 'oneemhigh','title' => $this->getmsg_sym_del()],value: $g_img['unicode.del']);
		else:
//			record is protected
			$o_td->addDIV(attributes: ['class' => 'oneemhigh monotoolbox','title' => $this->getmsg_sym_loc()],value: $g_img['unicode.loc']);
		endif;
		return $root->get_html();
	}
	public function html_maintainbox() {
		global $g_img;

		$querystring = http_build_query(['submit' => 'edit',$this->get_row_identifier() => $this->get_row_identifier_value()],'',ini_get('arg_separator.output'),PHP_QUERY_RFC3986);
		$link = sprintf('%s?%s',$this->get_maintain()->get_scriptname(),$querystring);
		$root = new document();
		$root->addTD()->addA(attributes: ['href' => $link,'class' => 'spin oneemhigh monotoolbox','title' => $this->getmsg_sym_mai()],value: $g_img['unicode.mai']);
		return $root->get_html();
	}
	public function html_informbox() {
		global $g_img;

		$querystring = http_build_query(['submit' => 'edit',$this->get_row_identifier() => $this->get_row_identifier_value()],'',ini_get('arg_separator.output'),PHP_QUERY_RFC3986);
		$link = sprintf('%s?%s',$this->get_inform()->get_scriptname(),$querystring);
		$root = new document();
		$root->addTD()->addA(attributes: ['href' => $link,'class' => 'spin oneemhigh monotoolbox','title' => $this->getmsg_sym_inf()],value: $g_img['unicode.inf']);
		return $root->get_html();
	}
	public function html_footer_add(int $colspan = 2) {
		global $g_img;

		$link = sprintf('%s?submit=add',$this->get_modify()->get_scriptname());
		$root = new document();
		$o_tr = $root->addTR();
		if($colspan > 1):
			$o_tr->insTH(['class' => 'lcenl','colspan' => $colspan - 1]);
		endif;
		$o_tr->addTH(['class' => 'lceadd'])->addA(attributes: ['href' => $link,'class' => 'spin oneemhigh monotoolbox','title' => $this->getmsg_sym_add()],value: $g_img['unicode.add']);
		return $root->get_html();
	}
}
