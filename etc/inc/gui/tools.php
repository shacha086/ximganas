<?php
/*
	tools.php

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

namespace gui;

use common\properties\property;
use common\session;
use common\uuid;
use DOMDocument;
use DOMNode;

use function calc_adddivsubmittodataframe;
use function get_headermenu;
use function get_product_copyright;
use function make_headermenu_extensions;
use function system_get_hostname;
use function system_get_language_codeset;

trait tools {
/**
 *	Appends a child node to an element and returns the new node.
 *	@param string $name
 *	@param array $attributes
 *	@param string $value
 *	@param string $namespaceURI
 *	@return DOMNode $subnode
 */
	public function addElement(string $name,array $attributes = [],?string $value = null,string $namespaceURI = '') {
		$subnode = $this->appendChild(node: new element(qualifiedName: $name,namespace: $namespaceURI));
		$check_for_html = $this->check_for_html(name: $name);
		$subnode->import_soup(value: $value,check_for_html: $check_for_html);
		$subnode->addAttributes(attributes: $attributes);
		return $subnode;
	}
/**
 *	Appends a child node to an element and returns the element.
 *	@param string $name
 *	@param array $attributes
 *	@param string $value
 *	@param string $namespaceURI
 *	@return DOMNode $this
 */
	public function insElement(string $name,array $attributes = [],?string $value = null,string $namespaceURI = '') {
		$subnode = $this->appendChild(node: new element(qualifiedName: $name,namespace: $namespaceURI));
		$check_for_html = $this->check_for_html(name: $name);
		$subnode->import_soup(value: $value,check_for_html: $check_for_html);
		$subnode->addAttributes(attributes: $attributes);
		return $this;
	}
/**
 *	Inserts a child node on top of the children of an element and returns the new node.
 *	@param string $name
 *	@param array $attributes
 *	@param string $value
 *	@param string $namespaceURI
 *	@return DOMNode $subnode
 */
	public function prepend_element(string $name,array $attributes = [],?string $value = null,string $namespaceURI = '') {
		if(is_null(value: $this->firstChild)):
			$subnode = $this->appendChild(node: new element(qualifiedName: $name,namespace: $namespaceURI));
		else:
			$subnode = $this->insertBefore(node: new element(qualifiedName: $name,namespace: $namespaceURI),child: $this->firstChild);
		endif;
		$check_for_html = $this->check_for_html(name: $name);
		$subnode->import_soup(value: $value,check_for_html: $check_for_html);
		$subnode->addAttributes(attributes: $attributes);
		return $subnode;
	}
	public function check_for_html($name): bool {
		return in_array(needle: $name,haystack: ['div','li','p','span','td']);
	}
/**
 *	Appends a child node to an element and returns the element.<br/>
 *	If the string contains html tags, loadHTML is called, otherwise a<br/>
 *	text node is created.
 *	@param string $value The text/html string
 *	@return $this
 */
	public function import_soup(?string $value = null,bool $check_for_html = true) {
		if(!is_null(value: $value)):
//			rough check if value contains html code, if found try to import as HTML, otherwise add as text
			$html_import_successful = false;
			if($check_for_html && preg_match(pattern: '~/[a-z]*>~i',subject: $value)):
				$backup_use_internal_errors = libxml_use_internal_errors(use_errors: true);
//				libxml_disable_entity_loader is deprecated since PHP 8.0.0
				if(PHP_VERSION_ID < 80000):
					$backup_disable_entity_loader = libxml_disable_entity_loader(disable: true);
				endif;
				$document = $this->ownerDocument ?? $this;
				$htmldocument = new DOMDocument(version: '1.0',encoding: 'UTF-8');
				$html_import_successful = $htmldocument->loadHTML(source: '<!DOCTYPE html><html><head><meta charset="UTF-8"></head><body>' . $value . '</body></html>',options: LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
				libxml_clear_errors();
//				libxml_disable_entity_loader is deprecated since PHP 8.0.0
				if(PHP_VERSION_ID < 80000):
					libxml_disable_entity_loader(disable: $backup_disable_entity_loader);
				endif;
				libxml_use_internal_errors(use_errors: $backup_use_internal_errors);
			endif;
			if($html_import_successful):
				$items = $htmldocument->getElementsByTagName(qualifiedName: 'body');
				foreach($items as $item):
					foreach($item->childNodes as $childnode):
						$newnode = $document->importNode(node: $childnode,deep: true);
						$this->appendChild(node: $newnode);
					endforeach;
				endforeach;
			else:
				$document = $this->ownerDocument ?? $this;
				$this->appendChild(node: $document->createTextNode(data: $value));
			endif;
		endif;
		return $this;
	}
/**
 *	Inserts a JavaScript node into DOM
 *	@param string $text
 *	@return DOMNode $this
 */
	public function ins_javascript(string $text = '') {
		if(preg_match(pattern: '/\S/',subject: $text)):
			$node = $this->addElement(name: 'script');
			if($node !== false):
				$opening = $node->ownerDocument->createTextNode(data: "\n" . '//<![CDATA[' . "\n");
				$ending = $node->ownerDocument->createTextNode(data: "\n" . '//]]>' . "\n");
				if(($opening !== false) && ($ending !== false)):
					$node->appendChild(node: $opening);
					$cdata = $node->ownerDocument->createTextNode(data: $text);
					if($cdata !== false):
						$node->appendChild(node: $cdata);
					endif;
					$node->appendChild(node: $ending);
				endif;
			endif;
		endif;
		return $this;
	}
//	tags
	public function addA(array $attributes = [],?string $value = null) {
		return $this->addElement(name: 'a',attributes: $attributes,value: $value);
	}
	public function insA(array $attributes = [],?string $value = null) {
		return $this->insElement(name: 'a',attributes: $attributes,value: $value);
	}
	public function insCOL(array $attributes = []) {
		return $this->insElement(name: 'col',attributes: $attributes);
	}
	public function addDIV(array $attributes = [],?string $value = null) {
		return $this->addElement(name: 'div',attributes: $attributes,value: $value);
	}
	public function insDIV(array $attributes = [],?string $value = null) {
		return $this->insElement(name: 'div',attributes: $attributes,value: $value);
	}
	public function addFORM(array $attributes = [],?string $value = null) {
		return $this->addElement(name: 'form',attributes: $attributes,value: $value);
	}
	public function insIMG(array $attributes = []) {
		return $this->insElement(name: 'img',attributes: $attributes);
	}
	public function insINPUT(array $attributes = [],?string $value = null) {
		return $this->insElement(name: 'input',attributes: $attributes,value: $value);
	}
	public function addLI(array $attributes = [],?string $value = null) {
		return $this->addElement(name: 'li',attributes: $attributes,value: $value);
	}
	public function addP(array $attributes = [],?string $value = null) {
		return $this->addElement(name: 'p',attributes: $attributes,value: $value);
	}
	public function addSPAN(array $attributes = [],?string $value = null) {
		return $this->addElement(name: 'span',attributes: $attributes,value: $value);
	}
	public function insSPAN(array $attributes = [],?string $value = null) {
		return $this->insElement(name: 'span',attributes: $attributes,value: $value);
	}
	public function addUL(array $attributes = [],?string $value = null) {
		return $this->addElement(name: 'ul',attributes: $attributes,value: $value);
	}
//	table tags
	public function addTABLE(array $attributes = [],?string $value = null) {
		return $this->addElement(name: 'table',attributes: $attributes,value: $value);
	}
	public function addCOLGROUP(array $attributes = [],?string $value = null) {
		return $this->addElement(name: 'colgroup',attributes: $attributes,value: $value);
	}
	public function addTHEAD(array $attributes = [],?string $value = null) {
		return $this->addElement(name: 'thead',attributes: $attributes,value: $value);
	}
	public function addTBODY(array $attributes = [],?string $value = null) {
		return $this->addElement(name: 'tbody',attributes: $attributes,value: $value);
	}
	public function addTFOOT(array $attributes = [],?string $value = null) {
		return $this->addElement(name: 'tfoot',attributes: $attributes,value: $value);
	}
	public function addTR(array $attributes = [],?string $value = null) {
		return $this->addElement(name: 'tr',attributes: $attributes,value: $value);
	}
	public function addTD(array $attributes = [],?string $value = null) {
		return $this->addElement(name: 'td',attributes: $attributes,value: $value);
	}
	public function insTD(array $attributes = [],?string $value = null) {
		return $this->insElement(name: 'td',attributes: $attributes,value: $value);
	}
	public function addTDwC(string $class,?string $value = null) {
		return $this->addElement(name: 'td',attributes: ['class' => $class],value: $value);
	}
	public function insTDwC(string $class,?string $value = null) {
		return $this->insElement(name: 'td',attributes: ['class' => $class],value: $value);
	}
	public function addTH(array $attributes = [],?string $value = null) {
		return $this->addElement(name: 'th',attributes: $attributes,value: $value);
	}
	public function insTH(array $attributes = [],?string $value = null) {
		return $this->insElement(name: 'th',attributes: $attributes,value: $value);
	}
	public function addTHwC(string $class,?string $value = null) {
		return $this->addElement(name: 'th',attributes: ['class' => $class],value: $value);
	}
	public function insTHwC(string $class,?string $value = null) {
		return $this->insElement(name: 'th',attributes: ['class' => $class],value: $value);
	}
//	tab menu fragments and macros
/**
 *
 *	@return DOMNode $subnode
 */
	public function add_area_tabnav() {
		$table_attributes = ['id' => 'area_navigator'];
		$document = $this->ownerDocument ?? $this;
		$target = $document->getElementById(elementId: 'g4h');
		if(isset($target)):
//			last element of header section
			$append_mode = true;
			$div_attributes = [
				'id' => 'area_tabnav'
			];
			if($append_mode):
				$subnode = $target->
					addDIV(attributes: $div_attributes)->
						addTABLE(attributes: $table_attributes)->
							addTBODY();
			else:
				$subnode = $target->
					prepend_element(name: 'div',attributes: $div_attributes)->
						addTABLE(attributes: $table_attributes)->
							addTBODY();
			endif;
		else:
//			workaround for unconverted pages because of padding
			$target = $this;
			$subnode = $target->
				addTABLE(attributes: $table_attributes)->
					addTBODY();
		endif;
		return $subnode;
	}
/**
 *	Creates tags for upper navigation menu
 *	@return DOMNode $subnode
 */
	public function add_tabnav_upper() {
		$subnode = $this->
			addTR()->
				addTDwC(class: 'tabnavtbl')->
					addUL(attributes: ['id' => 'tabnav']);
		return $subnode;
	}
/**
 *	Creates tags for lower navigation menu
 *	@return DOMNode $subnode
 */
	public function add_tabnav_lower() {
		$subnode = $this->
			addTR()->
				addTDwC(class: 'tabnavtbl')->
					addUL(attributes: ['id' => 'tabnav2']);
		return $subnode;
	}
/**
 *	Adds a menu item to the navigation menu
 *	@param string $href Link to script
 *	@param string $value Name of the menu item
 *	@param string $title Title of the menu item
 *	@param bool $active Flag to indicate an active menu item
 *	@return object $this
 */
	public function ins_tabnav_record(string $href = '',string $value = '',string $title = '',bool $active = false) {
		$attributes = [];
		if(preg_match(pattern: '/\S/',subject: $href)):
			$attributes['href'] = $href;
		endif;
		if(preg_match(pattern: '/\S/',subject: $title)):
			$attributes['title'] = $title;
		endif;
		$this->
			addLI(attributes: ['class' => $active ? 'tabact' : 'tabinact'])->
				addA(attributes: $attributes)->
					addSPAN(value: $value);
		return $this;
	}
/**
 *
 *	@return DOMNode $subnode
 */
	public function add_area_data() {
		$this->insDIV(attributes: ['class' => 'area_data_top']);
		$subnode = $this->
			addDIV(attributes: ['id' => 'area_data_frame']);
		$this->insDIV(attributes: ['class' => 'area_data_pot']);
		return $subnode;
	}
	public function ins_input_errors(array $input_errors = []) {
		global $g_img;

		$this->reset_hooks();
		if(is_array(value: $input_errors)):
			$id = 'errorbox';
			$class = 'iconerr mbci-min';
			$text = "\u{26a0}\u{fe0e}";
			$firstrowtrigger = true;
			foreach($input_errors as $rowvalue):
				if(is_string(value: $rowvalue) && preg_match(pattern: '/\S/',subject: $rowvalue)):
					if($firstrowtrigger):
						$hook_id = $this->addDIV(attributes: ['id' => $id]);
						$mbcl1 = $hook_id->addDIV(attributes: ['class' => 'mbcl-1']);
						$this->add_hook(dom_element: $mbcl1,identifier: 'mbcl-1');
						$mbcl2 = $mbcl1->addDIV(attributes: ['class' => 'mbcl-2 mbci-min']);
						$mbcl2->addDIV(attributes: ['class' => $class],value: $text);
						$mbcl2i2 = $mbcl2->addDIV(attributes: ['class' => 'message mbci-max']);
						$mbcl3 = $mbcl2i2->addDIV(attributes: ['class' => 'mbcl-3 mbci-min']);
						$hook_messages = $mbcl3->
							addDIV(value: sprintf('%s:',gettext(message: 'The following errors were detected'),':'))->
								addUL();
						$this->add_hook(dom_element: $hook_messages,identifier: 'messages');
						$firstrowtrigger = false;
					endif;
					$hook_messages->addLI(value: htmlspecialchars_decode(string: $rowvalue,flags: ENT_QUOTES | ENT_HTML5));
//					$hook_messages->addLI(value: $rowvalue);
				endif;
			endforeach;
		endif;
		return $this;
	}
/**
 *	Show error, info or warning messages
 *	@param mixed $message The message(s) to be shown
 *	@param string $message_type e)rror, i)info, w)arning
 *	@return $this
 */
	private function ins_message_box($message,?string $message_type = null) {
		global $g_img;

		$this->reset_hooks();
		if(is_string(value: $message)):
			$grid = [$message];
		elseif(is_array(value: $message)):
			$grid = $message;
		endif;
		if(is_array(value: $grid)):
			switch($message_type):
				default:
					$id = 'errorbox';
					$class = 'iconerr mbci-min';
					break;
				case 'info':
					$id = 'infobox';
					$class = 'iconinf mbci-min';
					break;
				case 'warning':
					$id = 'warningbox';
					$class = 'iconwrn mbci-min';
					break;
			endswitch;
			$text = "\u{26a0}\u{fe0e}";
			$firstrowtrigger = true;
			foreach($grid as $rowvalue):
				if(is_string(value: $rowvalue) && preg_match(pattern: '/\S/',subject: $rowvalue)):
					if($firstrowtrigger):
						$hook_id = $this->addDIV(attributes: ['id' => $id]);
						$mbcl1 = $hook_id->addDIV(attributes: ['class' => 'mbcl-1']);
						$this->add_hook(dom_element: $mbcl1,identifier: 'mbcl-1');
						$mbcl2 = $mbcl1->addDIV(attributes: ['class' => 'mbcl-2 mbci-min']);
						$mbcl2->addDIV(attributes: ['class' => $class],value: $text);
						$mbcl2i2 = $mbcl2->addDIV(attributes: ['class' => 'message mbci-max']);
						$hook_messages = $mbcl2i2->addDIV(attributes: ['class' => 'mbcl-3 mbci-min']);
						$this->add_hook(dom_element: $hook_messages,identifier: 'messages');
						$firstrowtrigger = false;
					endif;
					$hook_messages->insDIV(value: htmlspecialchars_decode(string: $rowvalue,flags: ENT_QUOTES | ENT_HTML5));
//					$mbcl3->insDIV(value: $rowvalue);
				endif;
			endforeach;
		endif;
		return $this;
	}
	public function ins_error_box($message = null) {
		return $this->ins_message_box(message: $message,message_type: 'error');
	}
	public function ins_info_box($message = null) {
		return $this->ins_message_box(message: $message,message_type: 'info');
	}
	public function ins_warning_box($message = null) {
		return $this->ins_message_box(message: $message,message_type: 'warning');
	}
	public function ins_config_save_message_box($errorcode) {
		global $d_sysrebootreqd_path;

		if($errorcode == 0):
			if(file_exists(filename: $d_sysrebootreqd_path)):
				$message = [
					gettext(message: 'The changes have been saved.'),
					sprintf('<a href="reboot.php">%s</a>',gettext(message: 'You have to reboot the system for the changes to take effect.'))
				];
			else:
				$message = gettext(message: 'The changes have been applied successfully.');
			endif;
		else:
			$message = sprintf('%s: %s (%s %s).',gettext(message: 'Error'),gettext(message: 'The changes could not be applied'),gettext(message: 'Error Code'),$errorcode);
		endif;
		$this->ins_info_box(message: $message);
		return $this;
	}
	public function ins_config_has_changed_box() {
		$gt_info = [
			gettext(message: 'The configuration has been changed.'),
			gettext(message: 'You must apply the changes in order for them to take effect.'),
			sprintf('<a href="diag_log.php">%s</a>',gettext(message: 'If this message persists take a look at the system log for more information.'))
		];
		$this->addDIV(attributes: ['id' => 'applybox'])->ins_info_box(message: $gt_info);
		$hooks = $this->get_hooks();
		if(array_key_exists(key: 'mbcl-1',array: $hooks)):
			$hooks['mbcl-1']->addDIV(attributes: ['class' => 'mbci-min'])->ins_button_apply();
		endif;
		return $this;
	}
//	data settings table macros
	public function add_table_data_settings() {
		$subnode = $this->addTABLE(attributes: ['class' => 'area_data_settings']);
		return $subnode;
	}
	public function ins_colgroup_data_settings() {
		$this->ins_colgroup_with_classes(data: ['area_data_settings_col_tag','area_data_settings_col_data']);
		return $this;
	}
	public function add_table_data_selection() {
		$subnode = $this->addTABLE(attributes: ['class' => 'area_data_selection']);
		return $subnode;
	}
	public function ins_colgroup_with_classes(array $data = []) {
		$colgroup = $this->addCOLGROUP();
		foreach($data as $value):
			$colgroup->insCOL(attributes: ['class' => $value]);
		endforeach;
		return $this;
	}
	public function ins_colgroup_with_styles(string $tag,array $data = []) {
		$colgroup = $this->addCOLGROUP();
		foreach($data as $value):
			$colgroup->insCOL(attributes: ['style' => sprintf('%s:%s;',$tag,$value)]);
		endforeach;
		return $this;
	}
//	title macros
	public function ins_titleline(?string $title = null,int $colspan = 0,?string $id = null) {
		if(!is_null(value: $id) && preg_match(pattern: '/\S/',subject: $id)):
			$tr_attributes = ['id' => sprintf('%s_tr',$id)];
		else:
			$tr_attributes = [];
		endif;
		$th_attributes['class'] = 'lhetop';
		if($this->option_exists(option: 'tablesort')):
			$tr_attributes['class'] = 'tablesorter-ignoreRow';
		endif;
		if($colspan > 0):
			$th_attributes['colspan'] = $colspan;
		else:
			$th_attributes = [];
		endif;
		$div_attributes = ['class' => 'cblot'];
		$this->
			addTR(attributes: $tr_attributes)->
				addTH(attributes: $th_attributes)->
					addDIV(attributes: $div_attributes)->
						insSPAN(value: $title);
		return $this;
	}
	public function ins_titleline_with_checkbox(property $property,$value,bool $is_required = false,bool $is_readonly = false,string $title = '',int $colspan = 0) {
		$preset = is_object(value: $value) ? $value->row[$property->get_name()] : $value;
		$tr_attributes = ['id' => sprintf('%s_tr',$property->get_id())];
		if($this->option_exists(option: 'tablesort')):
			$tr_attributes['class'] = 'tablesorter-ignoreRow';
		endif;
		$th_attributes = ['class' => 'lhetop'];
		if($colspan > 0):
			$th_attributes['colspan'] = $colspan;
		endif;
		$div_attributes = ['class' => 'cblot'];
		$label_attributes = ['class' => 'cblot'];
		$input_attributes = [
			'type' => 'checkbox',
			'id' => $property->get_id(),
			'class' => 'cblot',
			'name' => $property->get_name(),
			'value' => 'yes'
		];
		if(isset($preset) && $preset):
			$input_attributes['checked'] = 'checked';
		endif;
		if($is_readonly):
			$input_attributes['disabled'] = 'disabled';
			$is_required = false;
		endif;
		if($is_required):
			$input_attributes['required'] = 'required';
		endif;
		$span_attributes = ['class' => 'cblot'];
		$this->
			addTR(attributes: $tr_attributes)->
				addTH(attributes: $th_attributes)->
					addDIV(attributes: $div_attributes)->
						insSPAN(value: $title)->
						addElement(attributes: $label_attributes,name: 'label')->
							insINPUT(attributes: $input_attributes)->
							addSPAN(attributes: $span_attributes,value: $property->get_caption());
		return $this;
	}
	public function ins_description(property $property) {
//		description can be:
//		string
//		[string, ...]
//		[ [string], ...]
//		[ [string,no_br], ...]
//		[ [string,color], ...]
//		[ [string,color,no_br], ...]
		$description = $property->get_description();
		if(isset($description)):
			$description_output = '';
			$suppressbr = true;
			if(!empty($description)):
//				string or array
				if(is_string(value: $description)):
					$description_output = $description;
				elseif(is_array(value: $description)):
					foreach($description as $description_row):
						if(is_string(value: $description_row)):
							if($suppressbr):
								$description_output .= $description_row;
								$suppressbr = false;
							else:
								$description_output .= ('<br />' . $description_row);
							endif;
						elseif(is_array(value: $description_row)):
							switch(count(value: $description_row)):
								case 1:
									if(is_string(value: $description_row[0])):
										if($suppressbr):
											$suppressbr = false;
										else:
											$description_output .= '<br />';
										endif;
										$description_output .= $description_row[0];
									endif;
									break;
								case 2:
									if(is_string(value: $description_row[0])):
										$color = null;
										if(is_string(value: $description_row[1])):
											$color = $description_row[1];
										endif;
										if(is_bool(value: $description_row[1])):
											$suppressbr = $description_row[1];
										endif;
										if($suppressbr):
											$suppressbr = false;
										else:
											$description_output .= '<br />';
										endif;
										if(is_null(value: $color)):
											$description_output .= $description_row[0];
										else:
											$description_output .= sprintf('<span style="color:%2$s">%1$s</span>',$description_row[0],$color);
										endif;
									endif;
									break;
								case 3:
//									allow not to break
									if(is_string(value: $description_row[0])):
										$color = null;
										if(is_string(value: $description_row[1])):
											$color = $description_row[1];
										endif;
										if(is_bool(value: $description_row[2])):
											$suppressbr = $description_row[2];
										endif;
										if($suppressbr):
											$suppressbr = false;
										else:
											$description_output .= '<br />';
										endif;
										if(is_null(value: $color)):
											$description_output .= $description_row[0];
										else:
											$description_output .= sprintf('<span style="color:%2$s">%1$s</span>',$description_row[0],$color);
										endif;
									endif;
									break;
							endswitch;
						endif;
					endforeach;
				endif;
			endif;
			if(preg_match(pattern: '/\S/',subject: $description_output)):
				$this->addDIV(attributes: ['class' => 'cd-description'],value: $description_output);
			endif;
		endif;
		return $this;
	}
	public function ins_checkbox(property $property,$value,bool $is_required = false,bool $is_readonly = false) {
		$this->reset_hooks();
		$preset = is_object(value: $value) ? $value->row[$property->get_name()] : $value;
		$id = $property->get_id();
		$caption = $property->get_caption();
		$input_attributes = [
			'type' => 'checkbox',
			'id' => $id,
			'name' => $property->get_name(),
			'value' => 'yes',
			'class' => 'oneemhigh'
		];
		if(isset($preset) && $preset):
			$input_attributes['checked'] = 'checked';
		endif;
		if($is_readonly):
			$input_attributes['disabled'] = 'disabled';
			$is_required = false;
		endif;
		if($is_required):
			$input_attributes['required'] = 'required';
		endif;
		$hook = $this->
			addDIV(attributes: ['class' => 'cd-input-checkbox'])->
				insINPUT(attributes: $input_attributes)->
					addDIV(attributes: ['class' => 'cd-container']);
		if($is_readonly):
			$hook->insSPAN(value: $caption);
		else:
			$hook->addElement(name: 'label',attributes: ['for' => $input_attributes['id']],value: $caption);
		endif;
		$this->add_hook(dom_element: $hook,identifier: $id);
		return $this;
	}
	public function ins_input(property $property,$value,bool $is_required = false,bool $is_readonly = false,int $type = 0) {
		$this->reset_hooks();
		$preset = is_object(value: $value) ? $value->row[$property->get_name()] : $value;
		$id = $property->get_id();
		$caption = $property->get_caption();
		$input_attributes = [
			'id' => $id,
			'name' => $property->get_name(),
			'value' => $preset
		];
		switch($type):
			default:
				$input_attributes['type'] = 'text';
				break;
			case 1:
				$input_attributes['type'] = 'password';
				$input_attributes['autocomplete'] = 'off';
				break;
		endswitch;
		if($is_readonly):
			$input_attributes['readonly'] = 'readonly';
			$is_required = false;
			$maxlength = 0;
			$placeholder = $property->get_placeholderv() ?? $property->get_placeholder();
		else:
			$maxlength = $property->get_maxlength();
			$placeholder = $property->get_placeholder();
		endif;
		if($is_required):
			$input_attributes['required'] = 'required';
		endif;
		if(isset($placeholder)):
			$input_attributes['placeholder'] = $placeholder;
		endif;
		$size = $property->get_size();
		$input_attributes['size'] = 1;
		if($size > 0):
			$input_attributes['style'] = sprintf('max-width: %uch',$size);
		endif;
		if($maxlength > 0):
			$input_attributes['maxlength'] = $maxlength;
		endif;
		$hook = $this->addDIV(attributes: ['class' => 'cd-input-text']);
		$this->add_hook(dom_element: $hook,identifier: $id);
		$hook->insINPUT(attributes: $input_attributes);
		$switch_value = (isset($caption) ? 1 : 0) + ($is_readonly ? 0 : 2);
		switch($switch_value):
			case 1:
//				caption + read mode + text
				$hook->insSPAN(value: $caption);
				break;
			case 3:
//				caption + edit mode + text
				$hook->insElement(name: 'label',attributes: ['for' => $id],value: $caption);
				break;
		endswitch;
		return $this;
	}
	public function ins_input_hidden(?string $name = null,$value = '') {
		if(isset($name) && preg_match(pattern: '/\S/',subject: $name) && is_scalar(value: $value)):
			$input_attributes = ['type' => 'hidden'];
			if(preg_match(pattern: '/\S/',subject: $name)):
				$input_attributes['name'] = $name;
			endif;
			if(is_scalar(value: $value)):
				$input_attributes['value'] = $value;
			endif;
			$this->addDIV()->insINPUT(attributes: $input_attributes);
		endif;
		return $this;
	}
	public function ins_checkbox_grid(property $property,$value,bool $is_required = false,bool $is_readonly = false,bool $use_tablesort = false) {
		$this->reset_hooks();
		$preset = is_object(value: $value) ? $value->row[$property->get_name()] : $value;
		$table = $this->addDIV(attributes: ['class' => 'cd-checkbox-grid'])->add_table_data_selection();
		$thead = $table->addTHEAD();
		$tbody = $table->addTBODY();
		$input_attributes = [
			'name' => sprintf('%s[]',$property->get_name()),
			'type' => 'checkbox',
			'class' => 'oneemhigh'
		];
		if($is_readonly):
			$input_attributes['disabled'] = 'disabled';
			$is_required = false;
		endif;
		if($is_required):
			$input_attributes['required'] = 'required';
		endif;
		$n_options = 0;
		foreach($property->get_options() as $option_key => $option_val):
			$option_tag = (string)$option_key;
			$input_attributes['value'] = $option_tag;
			$input_attributes['id'] = sprintf('checkbox_%s',uuid::create_v4());
			if(is_array(value: $preset) && in_array(needle: $option_tag,haystack: $preset)):
				$input_attributes['checked'] = 'checked';
			elseif(array_key_exists(key: 'checked',array: $input_attributes)):
				unset($input_attributes['checked']);
			endif;
			$hook = $tbody->
				addTR()->
					addTDwC(class: 'lcebl')->
						addDIV(attributes: ['class' => 'cd-input-checkbox-grid'])->
							insINPUT(attributes: $input_attributes)->
							addDIV(attributes: ['class' => 'cd-container']);
			if($is_readonly):
				$hook->insSPAN(value: $option_val);
			else:
				$hook->addElement(name: 'label',attributes: ['for' => $input_attributes['id']],value: $option_val);
			endif;
			$this->add_hook(dom_element: $hook,identifier: $option_tag);
			$n_options++;
		endforeach;
		switch($n_options <=> 1):
			case -1:
				$message_info = $property->get_message_info();
				if(!is_null(value: $message_info)):
					$table->addTFOOT()->addTR()->addTDwC(class: 'lcebl',value: $message_info);
				endif;
				$suppress_tablesort = $this->option_exists(option: 'tablesort');
				break;
			case 0:
				$suppress_tablesort = $this->option_exists(option: 'tablesort');
				break;
			case 1:
				$suppress_tablesort = ($this->option_exists(option: 'tablesort') && !$use_tablesort);
				break;
		endswitch;
		if($suppress_tablesort):
			$tr = $thead->addTR(attributes: ['class' => 'tablesorter-ignoreRow']);
		else:
			$tr = $thead->addTR();
		endif;
		$tr->insTHwC(class: 'lhebl',value: $property->get_title());
		return $this;
	}
	public function ins_filechooser(property $property,$value,bool $is_required = false,bool $is_readonly = false) {
		$preset = is_object(value: $value) ? $value->row[$property->get_name()] : $value;
		$id = $property->get_id();
		$caption = $property->get_caption();
		$name = $property->get_name();
		$input_attributes = [
			'type' => 'text',
			'id' => $id,
			'name' => $name,
			'value' => $preset
		];
		if($is_readonly):
			$input_attributes['readonly'] = 'readonly';
			$is_required = false;
			$maxlength = 0;
			$placeholder = $property->get_placeholderv() ?? $property->get_placeholder();
		else:
			$maxlength = $property->get_maxlength();
			$placeholder = $property->get_placeholder();
		endif;
		if($is_required):
			$input_attributes['required'] = 'required';
		endif;
		if(isset($placeholder)):
			$input_attributes['placeholder'] = $placeholder;
		endif;
		$size = $property->get_size();
		$input_attributes['size'] = 1;
		if($size > 0):
			$input_attributes['style'] = sprintf('max-width: %uch',$size);
		endif;
		if($maxlength > 0):
			$input_attributes['maxlength'] = $maxlength;
		endif;
//	file chooser start
		if(!$is_readonly):
			$var = 'ifield';
			$idifield = sprintf('%1$s%2$s',$id,$var);
			$js = <<<EOJ
{$idifield} = form.{$id};
filechooser = window.open("filechooser.php?p="+encodeURIComponent({$idifield}.value)+"&sd={$preset}","filechooser","scrollbars=yes,toolbar=no,menubar=no,statusbar=no,width=550,height=300");
filechooser.{$var} = {$idifield};
window.{$var} = {$idifield};
EOJ;
			$button_attributes = [
				'type' => 'button',
				'id' => $id . 'browsebtn',
				'name' => $name . 'browsebtn',
				'class' => 'formbtn',
				'onclick' => $js,
				'value' => '...'
			];
		endif;
//	file chooser end
		$hook = $this->addDIV(attributes: ['class' => 'cd-filechooser']);
		$switch_value = (isset($caption) ? 1 : 0) + ($is_readonly ? 0 : 2);
		switch($switch_value):
			case 0:
//				no caption + read mode
				$hook->insINPUT(attributes: $input_attributes);
				break;
			case 1:
//				caption + read mode
				$hook->insINPUT(attributes: $input_attributes)->insSPAN(value: $caption);
				break;
			case 2:
//				no caption + edit mode
				$hook->
					addDIV(attributes: ['class' => 'cd-group'])->
						insINPUT(attributes: $input_attributes)->
						insINPUT(attributes: $button_attributes);
				break;
			case 3:
//				caption + edit mode
				$hook->
					addDIV(attributes: ['class' => 'cd-group'])->
						insINPUT(attributes: $input_attributes)->
						insINPUT(attributes: $button_attributes)->
				$hook->
					addElement(name: 'label',attributes: ['for' => $id],value: $caption);
				break;
		endswitch;
		return $this;
	}
	public function ins_radio_grid(property $property,$value,bool $is_required = false,bool $is_readonly = false,bool $use_tablesort = false) {
		$this->reset_hooks();
		$preset = (string)(is_object(value: $value) ? $value->row[$property->get_name()] : $value);
		$table = $this->addDIV(attributes: ['class' => 'cd-radio-grid'])->add_table_data_selection();
		$thead = $table->addTHEAD();
		$tbody = $table->addTBODY();
		$input_attributes = [
			'name' => $property->get_name(),
			'type' => 'radio',
			'class' => 'oneemhigh'
		];
		if($is_readonly):
			$input_attributes['disabled'] = 'disabled';
			$is_required = false;
		endif;
		if($is_required):
			$input_attributes['required'] = 'required';
		endif;
		$n_options = 0;
		foreach($property->get_options() as $option_key => $option_val):
			$option_tag = (string)$option_key;
			$input_attributes['value'] = $option_tag;
			$input_attributes['id'] = sprintf('radio_%s',uuid::create_v4());
			if($option_tag == $preset):
				$input_attributes['checked'] = 'checked';
			elseif(array_key_exists(key: 'checked',array: $input_attributes)):
				unset($input_attributes['checked']);
			endif;
			$hook = $tbody->
						addTR()->
							addTDwC(class: 'lcebl')->
								addDIV(attributes: ['class' => 'cd-input-radio-grid'])->
									insINPUT(attributes: $input_attributes)->
									addDIV(attributes: ['class' => 'cd-container']);
			if($is_readonly):
				$hook->insSPAN(value: $option_val);
			else:
				$hook->addElement(name: 'label',attributes: ['for' => $input_attributes['id']],value: $option_val);
			endif;
			$this->add_hook(dom_element: $hook,identifier: $option_tag);
			$n_options++;
		endforeach;
		switch($n_options <=> 1):
			case -1:
				$message_info = $property->get_message_info();
				if(!is_null(value: $message_info)):
					$table->addTFOOT()->addTR()->addTDwC(class: 'lcebl',value: $message_info);
				endif;
				$suppress_tablesort = $this->option_exists(option: 'tablesort');
				break;
			case 0:
				$suppress_tablesort = $this->option_exists(option: 'tablesort');
				break;
			case 1:
				$suppress_tablesort = ($this->option_exists(option: 'tablesort') && !$use_tablesort);
				break;
		endswitch;
		if($suppress_tablesort):
			$tr = $thead->addTR(attributes: ['class' => 'tablesorter-ignoreRow']);
		else:
			$tr = $thead->addTR();
		endif;
		$tr->insTHwC(class: 'lhebl',value: $property->get_title());
		return $this;
	}
	public function ins_select(property $property,$value,bool $is_required = false,bool $is_readonly = false) {
		$preset = (string)(is_object(value: $value) ? $value->row[$property->get_name()] : $value);
		$caption = $property->get_caption();
		$select_attributes = [
			'id' => $property->get_id(),
			'name' => $property->get_name()
		];
		if($is_readonly):
			$select_attributes['disabled'] = 'disabled';
			$is_required = false;
		endif;
		if($is_required):
			$select_attributes['required'] = 'required';
		endif;
		$hook = $this->addDIV(attributes: ['class' => 'cd-select']);
		$select = $hook->addElement(name: 'select',attributes: $select_attributes);
		if($is_required):
			$select->addElement(name: 'option',attributes: ['value' => ''],value: gettext(message: 'Choose...'));
		endif;
		foreach($property->get_options() as $option_key => $option_val):
			$option_tag = (string)$option_key;
			$option_attributes = ['value' => $option_tag];
			if($option_tag == $preset):
				$option_attributes['selected'] = 'selected';
			endif;
			$select->addElement(name: 'option',attributes: $option_attributes,value: $option_val);
		endforeach;
		if(isset($caption)):
			$hook->insSPAN(value: $caption);
		endif;
		return $this;
	}
	public function ins_separator(int $colspan = 0,?string $id = null) {
		$tr_attributes = [];
		if($this->option_exists(option: 'tablesort')):
			$tr_attributes = ['class' => 'tablesorter-ignoreRow'];
		endif;
		if(isset($id) && preg_match(pattern: '/\S/',subject: $id)):
			$tr_attributes['id'] = sprintf('%s_tr',$id);
		endif;
		$td_attributes = [
			'class' => 'gap'
		];
		if($colspan > 0):
			$td_attributes['colspan'] = $colspan;
		endif;
		$this->addTR(attributes: $tr_attributes)->addTD(attributes: $td_attributes);
		return $this;
	}
	public function ins_textarea(property $property,$value,bool $is_required = false,bool $is_readonly = false) {
		$n_rows_min = 5;
		$n_rows_max = 64;
		$preset = is_object(value: $value) ? $value->row[$property->get_name()] : $value;
		$id = $property->get_id();
		$caption = $property->get_caption();
		$textarea_attributes = [
			'id' => $id,
			'name' => $property->get_name()
		];
		if($is_readonly):
			$textarea_attributes['class'] = 'cd-textarea-input';
			$textarea_attributes['readonly'] = 'readonly';
			$is_required = false;
			$maxlength = 0;
			$placeholder = $property->get_placeholderv() ?? $property->get_placeholder();
		else:
			$textarea_attributes['class'] = 'cd-textarea-input';
			$maxlength = $property->get_maxlength();
			$placeholder = $property->get_placeholder();
		endif;
		if($is_required):
			$textarea_attributes['class'] = 'cd-textarea-input';
			$textarea_attributes['required'] = 'required';
		endif;
		if(isset($placeholder)):
			$textarea_attributes['placeholder'] = $placeholder;
		endif;
		$n_rows = $property->get_rows();
		if(is_null(value: $n_rows)):
//			calculate the number of rows within min-max
			$textarea_attributes['rows'] = min($n_rows_max,max($n_rows_min,1 + substr_count(haystack: $preset,needle: "\n")));
		elseif($n_rows > 0):
			$textarea_attributes['rows'] = $n_rows;
		endif;
		$textarea_attributes['wrap'] = $property->get_wrap() ? 'hard' : 'soft';
		if($maxlength > 0):
			$textarea_attributes['maxlength'] = $maxlength;
		endif;
		$label_attributes = [
			'class' => 'cd-textarea-label',
			'for' => $id
		];
		$div_attributes = [
			'class' => 'cd-textarea'
		];
		$hook = $this->addDIV(attributes: $div_attributes);
		$switch_value = (isset($caption) ? 1 : 0) + ($is_readonly ? 0 : 2);
		switch($switch_value):
			case 0:
//				no caption + read mode
			case 2:
//				no caption + edit mode
				$hook->insElement(name: 'textarea',attributes: $textarea_attributes,value: $preset);
				break;
			case 1:
//				caption + read mode
				$hook->insElement(name: 'textarea',attributes: $textarea_attributes,value: $preset)->insSPAN(value: $caption);
				break;
				break;
			case 3:
//				caption + edit mode
				$hook->insElement(name: 'textarea',attributes: $textarea_attributes,value: $preset)->addElement(name: 'label',attributes: $label_attributes,value: $caption);
				break;
		endswitch;
		return $this;
	}
	public function ins_textinfo(?string $id = null,?string $value = null) {
		if(isset($value)):
			$span_attributes = [];
			if(isset($id)):
				$span_attributes = ['id' => $id];
			endif;
			$this->insSPAN(attributes: $span_attributes,value: $value);
		endif;
		return $this;
	}
//	elements requiring sphere
	public function ins_cbm_checkbox_toggle($sphere) {
		$cbm_toggle_id = $sphere->get_cbm_checkbox_id_toggle();
		$input_attributes = [
			'type' => 'checkbox',
			'name' => $cbm_toggle_id,
			'id' => $cbm_toggle_id,
			'title' => gettext(message: 'Invert Selection'),
			'class' => 'oneemhigh'
		];
		$this->insINPUT(attributes: $input_attributes);
		return $this;
	}
	public function ins_cbm_checkbox($sphere,bool $disabled = false) {
		$identifier = $sphere->get_row_identifier_value();
		$input_attributes = [
			'type' => 'checkbox',
			'name' => $sphere->get_cbm_name() . '[]',
			'value' => $identifier,
			'id' => $identifier,
			'class' => 'oneemhigh'
		];
		if($disabled):
			$input_attributes['disabled'] = 'disabled';
		endif;
		$this->insINPUT(attributes: $input_attributes);
		return $this;
	}
/**
 *	Creates a TD element, signaling enabled/active or disabled/inactive
 *	@global array $g_img
 *	@param bool $is_enabled
 *	@param int $mode
 *		0: Enabled = light background, Disabled = dark background
 *		1: Enabled = dark background, Disabled = light background
 *		2: light background
 *		3: dark background
 *	@param int $type
 *		0: Enabled / Disabled
 *		1: Running / Stopped
 *		2: Active / Inactive
 *		3: Online / Offline
 *		4: On / Off
 *		5: Yes / No
 *	@return $this
 */
	public function ins_enadis_icon(bool $is_enabled = false,int $mode = 0,int $type = 0) {
		global $g_img;

		switch($mode):
			default:
				$class = $is_enabled ? 'lcelc unicode-ena' : 'lcelcd unicode-dis';
				break;
			case 1:
				$class = $is_enabled ? 'lcelcd unicode-ena' : 'lcelc unicode-dis';
				break;
			case 2:
				$class = $is_enabled ? 'lcelc unicode-ena' : 'lcelc unicode-dis';
				break;
			case 3:
				$class = $is_enabled ? 'lcelcd unicode-ena' : 'lcelcd unicode-dis';
				break;
		endswitch;
		$value = $is_enabled ? $g_img['unicode.ena'] : $g_img['unicode.dis'];
		switch($type):
			default:
				$title = $is_enabled ? gettext(message: 'Enabled') : gettext(message: 'Disabled');
				break;
			case 1:
				$title = $is_enabled ? gettext(message: 'Running') : gettext(message: 'Stopped');
				break;
			case 2:
				$title = $is_enabled ? gettext(message: 'Active') : gettext(message: 'Inactive');
				break;
			case 3:
				$title = $is_enabled ? gettext(message: 'Online') : gettext(message: 'Offline');
				break;
			case 4:
				$title = $is_enabled ? gettext(message: 'On') : gettext(message: 'Off');
				break;
			case 5:
				$title = $is_enabled ? gettext(message: 'Yes') : gettext(message: 'No');
				break;
		endswitch;
		$this->addTD(attributes: ['class' => $class,'title' => $title],value: $value);
		return $this;
	}
	public function add_toolbox_area(bool $lcrgrid = true) {
		$subnode = $this->
			addTDwC(class: 'lcebld')->
				addDIV(attributes: ['class' => $lcrgrid ? 'lcrgridx' : 'lgridx']);
		return $subnode;
	}
	public function ins_toolbox($sphere,bool $notprotected = true,bool $notdirty = true) {
		global $g_img;

		if($notdirty && $notprotected):
//			record is editable
			$querystring = http_build_query(data: ['submit' => 'edit',$sphere->get_row_identifier() => $sphere->get_row_identifier_value()],numeric_prefix: '',arg_separator: ini_get(option: 'arg_separator.output'),encoding_type: PHP_QUERY_RFC3986);
			$link = sprintf('%s?%s',$sphere->get_modify()->get_scriptname(),$querystring);
			$div = $this->addDIV(attributes: ['class' => 'lcrgridl']);
			$div->addA(attributes: ['href' => $link,'class' => 'spin oneemhigh monotoolbox','title' => $sphere->getmsg_sym_mod()],value: $g_img['unicode.mod']);
		elseif($notprotected):
//			record is dirty
			$this->addDIV(attributes: ['class' => 'lcrgridl','title' => $sphere->getmsg_sym_del()],value: $g_img['unicode.del']);
		else:
//			record is protected
			$this->addDIV(attributes: ['class' => 'lcrgridl monotoolbox','title' => $sphere->getmsg_sym_loc()],value: $g_img['unicode.loc']);
		endif;
		return $this;
	}
	public function ins_maintainbox($sphere,bool $show_link = false) {
		global $g_img;

		$div = $this->addDIV(attributes: ['class' => 'lcrgridc']);
		if($show_link):
			$querystring = http_build_query(data: ['submit' => 'maintain',$sphere->get_row_identifier() => $sphere->get_row_identifier_value()],numeric_prefix: '',arg_separator: ini_get(option: 'arg_separator.output'),encoding_type: PHP_QUERY_RFC3986);
			$link = sprintf('%s?%s',$sphere->get_maintain()->get_scriptname(),$querystring);
			$div->addA(attributes: ['href' => $link,'class' => 'spin oneemhigh monotoolbox','title' => $sphere->getmsg_sym_mai()],value: $g_img['unicode.mai']);
		endif;
		return $this;
	}
	public function ins_informbox($sphere,bool $show_link = false) {
		global $g_img;

		$div = $this->addDIV(attributes: ['class' => 'lcrgridr']);
		if($show_link):
			$querystring = http_build_query(data: ['submit' => 'inform',$sphere->get_row_identifier() => $sphere->get_row_identifier_value()],numeric_prefix: '',arg_separator: ini_get(option: 'arg_separator.output'),encoding_type: PHP_QUERY_RFC3986);
			$link = sprintf('%s?%s',$sphere->get_inform()->get_scriptname(),$querystring);
			$div->addA(attributes: ['href' => $link,'class' => 'spin oneemhigh monotoolbox','title' => $sphere->getmsg_sym_inf()],value: $g_img['unicode.inf']);
		endif;
		return $this;
	}
	public function ins_updownbox($sphere,bool $show_arrows = false) {
		global $g_img;

		$div = $this->addDIV(attributes: ['class' => 'lgridl']);
		if($show_arrows):
			$span_attribute_mup = [
				'title' => $sphere->getmsg_sym_mup(),
				'class' => 'oneemhigh move up monotoolbox'
			];
			$span_attribute_mdn = [
				'title' => $sphere->getmsg_sym_mdn(),
				'class' => 'oneemhigh move down monotoolbox'
			];
			$div->
				insSPAN(attributes: $span_attribute_mup,value: $g_img['unicode.mup'])->
				insSPAN(attributes: $span_attribute_mdn,value: $g_img['unicode.mdn']);
		endif;
		return $this;
	}
	public function ins_record_add($sphere,int $colspan = 0) {
		global $g_img;

		$querystring = http_build_query(data: ['submit' => 'add'],numeric_prefix: '',arg_separator: ini_get(option: 'arg_separator.output'),encoding_type: PHP_QUERY_RFC3986);
		$link = sprintf('%s?%s',$sphere->get_modify()->get_scriptname(),$querystring);
//		PHP_QUERY_RFC3986
		$tr = $this->addTR();
		if($colspan > 1):
			$tr->addTH(attributes: ['class' => 'lcenl','colspan' => $colspan - 1]);
		endif;
		$tr->
			addTHwC(class: 'lceadd')->
				addA(attributes: ['href' => $link,'class' => 'spin oneemhigh monotoolbox','title' => $sphere->getmsg_sym_add()],value: $g_img['unicode.add']);
		return $this;
	}
	public function ins_no_records_found(int $colspan = 0,?string $message = null) {
		if(is_null(value: $message)):
			$message = gettext(message: 'No records found.');
		endif;
		$td_attributes = ['class' => 'lcebl'];
		if($colspan > 0):
			$td_attributes['colspan'] = $colspan;
		endif;
		$this->addTR()->addTD(attributes: $td_attributes,value: $message);
		return $this;
	}
	public function ins_cbm_button_delete($sphere) {
		$this->ins_button_submit(id: $sphere->get_cbm_button_id_delete(),value: $sphere->get_cbm_button_val_delete(),content: $sphere->getmsg_cbm_delete());
		return $this;
	}
	public function ins_cbm_button_enadis($sphere) {
		if($sphere->is_enadis_enabled()):
			if($sphere->toggle()):
				$this->ins_button_submit(id: $sphere->get_cbm_button_id_toggle(),value:$sphere->get_cbm_button_val_toggle(),content: $sphere->getmsg_cbm_toggle());
			else:
				$this->ins_button_submit(id: $sphere->get_cbm_button_id_enable(),value: $sphere->get_cbm_button_val_enable(),content: $sphere->getmsg_cbm_enable());
				$this->ins_button_submit(id: $sphere->get_cbm_button_id_disable(),value: $sphere->get_cbm_button_val_disable(),content: $sphere->getmsg_cbm_disable());
			endif;
		endif;
		return $this;
	}
//	cr blocks
	public function cr_checkbox(property $property,$value,bool $is_required = false,bool $is_readonly = false) {
		$this->
			addDIV(attributes: ['class' => 'cd-element'])->
				ins_checkbox(property: $property,value: $value,is_required: $is_required,is_readonly: $is_readonly)->
				ins_description(property: $property);
		return $this;
	}
	public function cr_checkbox_grid(property $property,$value,bool $is_required = false,bool $is_readonly = false,bool $use_tablesort = false) {
		$this->
			addDIV(attributes: ['class' => 'cd-element'])->
				ins_checkbox_grid(property: $property,value: $value,is_required: $is_required,is_readonly: $is_readonly,use_tablesort: $use_tablesort)->
				ins_description(property: $property);
		return $this;
	}
	public function cr_filechooser(property $property,$value,bool $is_required = false,bool $is_readonly = false) {
		$this->
			addDIV(attributes: ['class' => 'cd-element'])->
				ins_filechooser(property: $property,value: $value,is_required: $is_required,is_readonly: $is_readonly)->
				ins_description(property: $property);
		return $this;
	}
	public function cr_input_text(property $property,$value,bool $is_required = false,bool $is_readonly = false) {
		$this->
			addDIV(attributes: ['class' => 'cd-element'])->
				ins_input(property: $property,value: $value,is_required: $is_required,is_readonly: $is_readonly,type: 0)->
				ins_description(property: $property);
		return $this;
	}
	public function cr_input_password(property $property,$value,bool $is_required = false,bool $is_readonly = false) {
		$this->
			addDIV(attributes: ['class' => 'cd-element'])->
				ins_input(property: $property,value: $value,is_required: $is_required,is_readonly: $is_readonly,type: 1)->
				ins_description(property: $property);
		return $this;
	}
	public function cr_radio_grid(property $property,$value,bool $is_required = false,bool $is_readonly = false,bool $use_tablesort = false) {
		$this->
			addDIV(attributes: ['class' => 'cd-element'])->
				ins_radio_grid(property: $property,value: $value,is_required: $is_required,is_readonly: $is_readonly,use_tablesort: $use_tablesort)->
				ins_description(property: $property);
		return $this;
	}
/**
 *	Add scheduler
 *	@param array $cops Array containing cop-objects for scheduler,
 *		all_minutes, all_hours, all_days, all_months, all_weekdays,
 *		minutes, hours, days, months and weekdays
 *	@param mys\sphere $sphere
 *	@param bool $is_required
 *	@param bool $is_readonly
 *	@return $this
 */
	public function cr_scheduler($cops,$sphere,bool $is_required = false,bool $is_readonly = false) {
//		init matrix
		$matrix = [];
		if(array_key_exists(key: 'all_minutes',array: $cops) && array_key_exists(key: 'minutes',array: $cops)):
			$matrix['minutes'] = ['all' => $cops['all_minutes'],'sel' => $cops['minutes'],'val_min' => 0,'val_steps' => 60,'val_break' => 15];
		endif;
		if(array_key_exists(key: 'all_hours',array: $cops) && array_key_exists(key: 'hours',array: $cops)):
			$matrix['hours'] = ['all' => $cops['all_hours'],'sel' => $cops['hours'],'val_min' => 0,'val_steps' => 24,'val_break' => 6];
		endif;
		if(array_key_exists(key: 'all_days',array: $cops) && array_key_exists(key: 'days',array: $cops)):
			$matrix['days'] = ['all' => $cops['all_days'],'sel' => $cops['days'],'val_min' => 1,'val_steps' => 31,'val_break' => 7];
		endif;
		if(array_key_exists(key: 'all_months',array: $cops) && array_key_exists(key: 'months',array: $cops)):
			$matrix['months'] = ['all' => $cops['all_months'],'sel' => $cops['months']];
		endif;
		if(array_key_exists(key: 'all_weekdays',array: $cops) && array_key_exists(key: 'weekdays',array: $cops)):
			$matrix['weekdays'] = ['all' => $cops['all_weekdays'],'sel' => $cops['weekdays']];
		endif;
		$root_for_scheduler = $this;
		if(array_key_exists(key: 'preset',array: $cops)):
			$this->cr(property: $cops['preset'],value: $sphere,is_required: $is_required,is_readonly: $is_readonly);
			$hooks = $this->get_hooks();
			if(array_key_exists(key: 'custom',array: $hooks)):
				$root_for_scheduler = $hooks['custom']->addDIV(attributes: ['class' => 'showifchecked']);
			endif;
		endif;
//		scheduler
		$div = $root_for_scheduler->addDIV(attributes: ['class' => 'scheduler-container']);
		$root_for_scheduler->ins_description(property: $cops['scheduler']);
//		insert elements
		foreach($matrix as $matrix_key => $control):
			$all_id = $control['all']->get_id();
			$all_name = $control['all']->get_name();
			$sel_name = $control['sel']->get_name();
			$sel_title = $control['sel']->get_title();
			$attr_all = ['type' => 'radio','class' => 'rblo','name' => $all_name,'id' => sprintf('%s1',$all_id),'value' => 1];
			$attr_sel = ['type' => 'radio','class' => 'rblo dimassoctable','name' => $all_name,'id' => sprintf('%s0',$all_id),'value' => 0];
			if(isset($sphere->row[$all_name]) && $sphere->row[$all_name] == 1):
				$attr_all['checked'] = 'checked';
			else:
				$attr_sel['checked'] = 'checked';
			endif;
			$tr = $div->
				addDIV(attributes: ['style' => 'flex: 0 0 auto;'])->
					insDIV(attributes: ['class' => 'lhebl'],value: $sel_title)->
					addDIV(attributes: ['class' => 'lcebl'])->
						push()->
						addDIV(attributes: ['class' => 'rblo'])->
							insINPUT(attributes: $attr_all)->
							addElement(name: 'label',attributes: ['for' => sprintf('%s1',$all_id)])->
								insSPAN(attributes: ['class' => 'rblo'],value: gettext(message: 'All'))->
						pop()->
						addDIV(attributes: ['class' => 'rblo'])->
							push()->
							insINPUT(attributes: $attr_sel)->
								addElement(name: 'label',attributes: ['for' => sprintf('%s0',$all_id)])->
									insSPAN(attributes: ['class' => 'rblo'],value: gettext(message: 'Selected...'))->
							pop()->
							addTABLE()->
								addTBODY(attributes: ['class' => 'donothighlight'])->
									addTR();
			switch($matrix_key):
				case 'minutes':
				case 'hours':
				case 'days':
					$val_min = $key = $control['val_min'];
					$val_count = $control['val_steps'];
					$val_max = $val_min + $val_count - 1;
					$val_break = $control['val_break'];
					$outer_max = ceil(num: $val_count / $val_break) - 1;
					$inner_max = $val_min + $val_break - 1;
					for($outer = 0;$outer <= $outer_max;$outer++):
						$td = $tr->addTDwC(class: 'lcefl');
						for($innerer = $val_min;$innerer <= $inner_max;$innerer++):
							if($key <= $val_max):
								$attributes = [
									'type' => 'checkbox',
									'class' => 'cblo',
									'name' => sprintf('%s[]',$sel_name),
									'value' => $key
								];
								if(isset($sphere->row[$sel_name]) && is_array(value: $sphere->row[$sel_name]) && in_array(needle: (string)$key,haystack: $sphere->row[$sel_name])):
									$attributes['checked'] = 'checked';
								endif;
								$td->
									addDIV(attributes: ['class' => 'cblo'])->
										addElement(name: 'label')->
											insINPUT(attributes: $attributes)->
											insSPAN(attributes: ['class' => 'cblo'],value: sprintf('%02d',$key));
							else:
								break;
							endif;
							$key++;
						endfor;
					endfor;
					break;
				case 'months':
					$td = $tr->addTDwC(class: 'lcefl');
					foreach($control['sel']->get_options() as $key => $val):
						$attributes = [
							'type' => 'checkbox',
							'class' => 'cblo',
							'name' => sprintf('%s[]',$sel_name),
							'value' => $key
						];
						if(isset($sphere->row[$sel_name]) && is_array(value: $sphere->row[$sel_name]) && in_array(needle: (string)$key,haystack: $sphere->row[$sel_name])):
							$attributes['checked'] = 'checked';
						endif;
						$td->
							addDIV(attributes: ['class' => 'cblo'])->
								addElement(name: 'label')->
									insINPUT(attributes: $attributes)->
									insSPAN(attributes: ['class' => 'cblo'],value: $val);
					endforeach;
					break;
				case 'weekdays':
					$td = $tr->addTDwC(class: 'lcefl');
					foreach($control['sel']->get_options() as $key => $val):
						$attributes = [
							'type' => 'checkbox',
							'class' => 'cblo',
							'name' => sprintf('%s[]',$sel_name),
							'value' => $key
						];
						if(isset($sphere->row[$sel_name]) && is_array(value: $sphere->row[$sel_name])):
							if(in_array(needle: (string)$key,haystack: $sphere->row[$sel_name])):
								$attributes['checked'] = 'checked';
							elseif($key == 7):
//								compatibility for non-ISO day of week 0 for Sunday
								if(in_array(needle: '0',haystack: $sphere->row[$sel_name])):
									$attributes['checked'] = 'checked';
								endif;
							endif;
						endif;
						$td->
							addDIV(attributes: ['class' => 'cblo'])->
								addElement(name: 'label')->
									insINPUT(attributes: $attributes)->
									insSPAN(attributes: ['class' => 'cblo'],value: $val);
					endforeach;
					break;
			endswitch;
		endforeach;
		return $this;
	}
/**
 *	add select element and description
 *	@param property $property
 *	@param mixed $value
 *	@param bool $is_required
 *	@param bool $is_readonly
 *	@return $this
 */
	public function cr_select(property $property,$value,bool $is_required = false,bool $is_readonly = false) {
		$this->
			addDIV(attributes: ['class' => 'cd-element'])->
				ins_select(property: $property,value: $value,is_required: $is_required,is_readonly: $is_readonly)->
				ins_description(property: $property);
		return $this;
	}
/**
 *	add textarea element and description
 *	@param property $property
 *	@param mixed $value
 *	@param bool $is_required
 *	@param bool $is_readonly
 *	@param int $n_cols
 *	@param int $n_rows
 *	@return $this
 */
	public function cr_textarea(property $property,$value,bool $is_required = false,bool $is_readonly = false,?int $n_cols = null,?int $n_rows = null) {
		$property->set_cols(cols: $n_cols);
		$property->set_rows(rows: $n_rows);
		$this->
			addDIV(attributes: ['class' => 'cd-element'])->
				ins_textarea(property: $property,value: $value,is_required: $is_required,is_readonly: $is_readonly)->
				ins_description(property: $property);
		return $this;
	}
/**
 *	Hub for cr methods
 *	@param property $property
 *	@param mixed $value
 *	@param bool $is_required
 *	@param bool $is_readonly
 *	@param array $additional_parameter
 *	@return $this
 */
	public function cr(property $property,$value,bool $is_required = false,bool $is_readonly = false,...$additional_parameter) {
		switch($property->get_input_type()):
			case property::INPUT_TYPE_TEXT:
				$this->cr_input_text(property: $property,value: $value,is_required: $is_required,is_readonly: $is_readonly);
				break;
			case property::INPUT_TYPE_CHECKBOX:
				$this->cr_checkbox(property: $property,value: $value,is_required: $is_required,is_readonly: $is_readonly);
				break;
			case property::INPUT_TYPE_CHECKBOX_GRID:
				$param_tablesort = $additional_parameter[0] ?? false;
				$use_tablesort = is_bool(value: $param_tablesort) ? $param_tablesort : false;
				$this->cr_checkbox_grid(property: $property,value: $value,is_required: $is_required,is_readonly: $is_readonly,use_tablesort: $use_tablesort);
				break;
			case property::INPUT_TYPE_RADIO_GRID:
				$param_tablesort = $additional_parameter[0] ?? false;
				$use_tablesort = is_bool(value: $param_tablesort) ? $param_tablesort : false;
				$this->cr_radio_grid(property: $property,value: $value,is_required: $is_required,is_readonly: $is_readonly,use_tablesort: $use_tablesort);
				break;
			case property::INPUT_TYPE_TEXTAREA:
				$param_cols = $additional_parameter[0] ?? null;
				$n_cols = is_int(value: $param_cols) ? $param_cols : null;
				$param_rows = $additional_parameter[1] ?? null;
				$n_rows = is_int(value: $param_rows) ? $param_rows : null;
				$this->cr_textarea(property: $property,value: $value,is_required: $is_required,is_readonly: $is_readonly,n_cols: $n_cols,n_rows: $n_rows);
				break;
			case property::INPUT_TYPE_SELECT:
				$this->cr_select(property: $property,value: $value,is_required: $is_required,is_readonly: $is_readonly);
				break;
			case property::INPUT_TYPE_PASSWORD:
				$this->cr_input_password(property: $property,value: $value,is_required: $is_required,is_readonly: $is_readonly);
				break;
			case property::INPUT_TYPE_FILECHOOSER:
				$this->cr_filechooser(property: $property,value: $value,is_required: $is_required,is_readonly: $is_readonly);
				break;
		endswitch;
		return $this;
	}
//	c2 blocks
/**
 *	Add code for table row with two columns
 *	@param property $property
 *	@param bool $is_required
 *	@param bool $is_readonly
 *	@param bool $tagaslabel
 *	@return type
 */
	public function c2_row(property $property,bool $is_required = false,bool $is_readonly = false,bool $tagaslabel = false) {
		if($is_readonly):
//			if readonly, ignore required
			$class_tag = 'celltag';
			$class_data = 'celldata';
		elseif($is_required):
			$class_tag = 'celltagreq';
			$class_data = 'celldatareq';
		else:
			$class_tag = 'celltag';
			$class_data = 'celldata';
		endif;
		$tr = $this->addTR(attributes: ['id' => sprintf('%s_tr',$property->get_id())]);
		if($tagaslabel):
			$tr->addTDwC(class: $class_tag)->addElement(name: 'label',attributes: ['for' => $property->get_id()],value: $property->get_title());
		else:
			$tr->addTDwC(class: $class_tag,value: $property->get_title());
		endif;
		$subnode = $tr->addTDwC(class: $class_data)->addDIV(attributes: ['class' => 'cd-container']);
		return $subnode;
	}
/**
 *	Add table row witch checkbox
 *	@param property $property
 *	@param mixed $value
 *	@param bool $is_required
 *	@param bool $is_readonly
 *	@return $this
 */
	public function c2_checkbox(property $property,$value,bool $is_required = false,bool $is_readonly = false) {
		$this->c2_row(property: $property,is_required: $is_required,is_readonly: $is_readonly,tagaslabel: true)->cr_checkbox(property: $property,value: $value,is_required: $is_required,is_readonly: $is_readonly);
		return $this;
	}
/**
 *	Add table row with checkbox grid
 *	@param property $property
 *	@param mixed $value
 *	@param bool $is_required
 *	@param bool $is_readonly
 *	@param bool $use_tablesort
 *	@return $this
 */
	public function c2_checkbox_grid(property $property,$value,bool $is_required = false,bool $is_readonly = false,bool $use_tablesort = false) {
		$this->c2_row(property: $property,is_required: $is_required,is_readonly: $is_readonly,tagaslabel: false)->cr_checkbox_grid(property: $property,value: $value,is_required: $is_required,is_readonly: $is_readonly,use_tablesort: $use_tablesort);
		return $this;
	}
/**
 *	Add table row with filechooser
 *	@param property $property
 *	@param mixed $value
 *	@param bool $is_required
 *	@param bool $is_readonly
 *	@return $this
 */
	public function c2_filechooser(property $property,$value,bool $is_required = false,bool $is_readonly = false) {
		$hook = $this->c2_row(property: $property,is_required: $is_required,is_readonly: $is_readonly,tagaslabel: true);
		$hook->cr_filechooser(property: $property,value: $value,is_required: $is_required,is_readonly: $is_readonly);
		$this->reset_hooks();
		$this->add_hook(dom_element: $hook,identifier: 'fc');
		return $this;
	}
/**
 *	Add table row with input
 *	@param property $property
 *	@param mixed $value
 *	@param bool $is_required
 *	@param bool $is_readonly
 *	@return $this
 */
	public function c2_input_text(property $property,$value,bool $is_required = false,bool $is_readonly = false) {
		$this->c2_row(property: $property,is_required: $is_required,is_readonly: $is_readonly,tagaslabel: true)->cr_input_text(property: $property,value: $value,is_required: $is_required,is_readonly: $is_readonly);
		return $this;
	}
/**
 *	Add table row with input password
 *	@param property $property
 *	@param mixed $value
 *	@param bool $is_required
 *	@param bool $is_readonly
 *	@return $this
 */
	public function c2_input_password(property $property,$value,bool $is_required = false,bool $is_readonly = false) {
		$this->c2_row(property: $property,is_required: $is_required,is_readonly: $is_readonly,tagaslabel: true)->cr_input_password(property: $property,value: $value,is_required: $is_required,is_readonly: $is_readonly);
		return $this;
	}
/**
 *	Add table row with radio grid
 *	@param property $property
 *	@param mixed $value
 *	@param bool $is_required
 *	@param bool $is_readonly
 *	@return $this
 */
	public function c2_radio_grid(property $property,$value,bool $is_required = false,bool $is_readonly = false,bool $use_tablesort = false) {
		$this->c2_row(property: $property,is_required: $is_required,is_readonly: $is_readonly,tagaslabel: false)->cr_radio_grid(property: $property,value: $value,is_required: $is_required,is_readonly: $is_readonly,use_tablesort: $use_tablesort);
		return $this;
	}
/**
 *	Add table row with scheduler
 *	@param array $cops Array containing cop-objects for
 *		scheduler,
 *		all_minutes, all_hours, all_days, all_months, all_weekdays,
 *		minutes, hours, days, months and weekdays
 *	@param mys\sphere $sphere
 *	@param bool $is_required
 *	@param bool $is_readonly
 *	@return $this
 */
	public function c2_scheduler($cops,$sphere,bool $is_required = false,bool $is_readonly = false) {
		$this->c2_row(property: $cops['scheduler'],is_required: $is_required,is_readonly: $is_readonly,tagaslabel: false)->cr_scheduler(cops: $cops,sphere: $sphere,is_required: $is_required,is_readonly: $is_readonly);
		return $this;
	}
/**
 *	Add table row with select
 *	@param property $property
 *	@param mixed $value
 *	@param bool $is_required
 *	@param bool $is_readonly
 *	@return $this
 */
	public function c2_select(property $property,$value,bool $is_required = false,bool $is_readonly = false) {
		$this->c2_row(property: $property,is_required: $is_required,is_readonly: $is_readonly,tagaslabel: true)->cr_select(property: $property,value: $value,is_required: $is_required,is_readonly: $is_readonly);
		return $this;
	}
/**
 *	Add table row with horizontal line
 *	@return $this
 */
	public function c2_separator() {
		$this->ins_separator(colspan: 2);
		return $this;
	}
/**
 *	Add table row with textarea
 *	@param property $property
 *	@param mixed $value
 *	@param bool $is_required
 *	@param bool $is_readonly
 *	@param int $n_cols
 *	@param int $n_rows
 *	@return $this
 */
	public function c2_textarea(property $property,$value,bool $is_required = false,bool $is_readonly = false,?int $n_cols = null,?int $n_rows = null) {
		$this->c2_row(property: $property,is_required: $is_required,is_readonly: $is_readonly,tagaslabel: true)->cr_textarea(property: $property,value: $value,is_required: $is_required,is_readonly: $is_readonly,n_cols: $n_cols,n_rows: $n_rows);
		return $this;
	}
/**
 *	Add table row with text info
 *	@param string $id
 *	@param string $title
 *	@param mixed $value
 *	@return $this
 */
	public function c2_textinfo(string $id,string $title,$value) {
		$tr_attributes = [];
		if(isset($id)):
			$tr_attributes['id'] = sprintf('%s_tr',$id);
		endif;
		$tr = $this->addTR(attributes: $tr_attributes);
		$tr->addTDwC(class: 'celltag',value: $title);
		$tr->addTDwC(class: 'celldata')->ins_textinfo(id: $id,value: $value);
		return $this;
	}
/**
 *	Add table header
 *	@param string $title
 *	@return $this
 */
	public function c2_titleline(string $title = '') {
		$this->ins_titleline(title: $title,colspan: 2);
		return $this;
	}
/**
 *	Add table header with checkbox
 *	@param property $property
 *	@param mixed $value
 *	@param bool $is_required
 *	@param bool $is_readonly
 *	@param string $title
 *	@return $this
 */
	public function c2_titleline_with_checkbox(property $property,$value,bool $is_required = false,bool $is_readonly = false,string $title = '') {
		$this->ins_titleline_with_checkbox(property: $property,value: $value,is_required: $is_required,is_readonly: $is_readonly,title: $title,colspan: 2);
		return $this;
	}
/**
 *	Hub for c2 methods
 *	@param property $property
 *	@param mixed $value
 *	@param bool $is_required
 *	@param bool $is_readonly
 *	@param array $additional_parameter
 *	@return $this
 */
	public function c2(property $property,$value,bool $is_required = false,bool $is_readonly = false,...$additional_parameter) {
		switch($property->get_input_type()):
			case property::INPUT_TYPE_TEXT:
				$this->c2_input_text(property: $property,value: $value,is_required: $is_required,is_readonly: $is_readonly);
				break;
			case property::INPUT_TYPE_CHECKBOX:
				$this->c2_checkbox(property: $property,value: $value,is_required: $is_required,is_readonly: $is_readonly);
				break;
			case property::INPUT_TYPE_CHECKBOX_GRID:
				$param_tablesort = $additional_parameter[0] ?? false;
				$use_tablesort = is_bool(value: $param_tablesort) ? $param_tablesort : false;
				$this->c2_checkbox_grid(property: $property,value: $value,is_required: $is_required,is_readonly: $is_readonly,use_tablesort: $use_tablesort);
				break;
			case property::INPUT_TYPE_RADIO_GRID:
				$param_tablesort = $additional_parameter[0] ?? false;
				$use_tablesort = is_bool(value: $param_tablesort) ? $param_tablesort : false;
				$this->c2_radio_grid(property: $property,value: $value,is_required: $is_required,is_readonly: $is_readonly,use_tablesort: $use_tablesort);
				break;
			case property::INPUT_TYPE_TEXTAREA:
				$param_cols = $additional_parameter[0] ?? null;
				$n_cols = is_int(value: $param_cols) ? $param_cols : null;
				$param_rows = $additional_parameter[1] ?? null;
				$n_rows = is_int(value: $param_rows) ? $param_rows : null;
				$this->c2_textarea(property: $property,value: $value,is_required: $is_required,is_readonly: $is_readonly,n_cols: $n_cols,n_rows: $n_rows);
				break;
			case property::INPUT_TYPE_SELECT:
				$this->c2_select(property: $property,value: $value,is_required: $is_required,is_readonly: $is_readonly);
				break;
			case property::INPUT_TYPE_PASSWORD:
				$this->c2_input_password(property: $property,value: $value,is_required: $is_required,is_readonly: $is_readonly);
				break;
			case property::INPUT_TYPE_FILECHOOSER:
				$this->c2_filechooser(property: $property,value: $value,is_required: $is_required,is_readonly: $is_readonly);
				break;
			case property::INPUT_TYPE_TITLELINE_CHECKBOX:
				$param_title = $additional_parameter[0] ?? '';
				$title = is_string(value: $param_title) ? $param_title : '';
				$this->c2_titleline_with_checkbox(property: $property,value: $value,is_required: $is_required,is_readonly: $is_readonly,title: $title);
				break;
		endswitch;
		return $this;
	}
//	submit area macros
	public function add_area_buttons(bool $use_config_setting = true,bool $noscript = false) {
		$div_attributes = ['id' => 'submit'];
		if($use_config_setting):
			$root = $this->ownerDocument ?? $this;
			if(calc_adddivsubmittodataframe()):
				$target = $root->getElementById(elementId: 'area_data_frame') ?? $this;
				if($noscript):
					$subnode = $target->addElement(name: 'noscript')->addDIV(attributes: $div_attributes);
				else:
					$subnode = $target->addDIV(attributes: $div_attributes);
				endif;
			else:
				$target = $root->getElementById(elementId: 'g4f') ?? $this;
				$div_attributes['class'] = 'outer-frame-trbl';
				if($noscript):
					$subnode = $target->prepend_element(name: 'noscript')->addElement(name: 'div',attributes: $div_attributes);
				else:
					$subnode = $target->prepend_element(name: 'div',attributes: $div_attributes);
				endif;
			endif;
		elseif($noscript):
			$subnode = $this->addElement(name: 'noscript')->addDIV(attributes: $div_attributes);
		else:
			$subnode = $this->addDIV(attributes: $div_attributes);
		endif;
		return $subnode;
	}
	public function ins_button_submit(?string $id = null,?string $name = null,?string $value = null,?string $content = null,?array $attributes = null) {
		$element = 'button';
		$class_button = 'formbtn';
		$value ??= 'cancel';
		$content ??= gettext(message: 'Cancel');
		$attributes ??= [];
		$id ??= sprintf('%s_%s',$element,$value);
		$name ??= 'submit';
		$button_attributes  = [
			'name' => $name,
			'type' => 'submit',
			'class' => $class_button,
			'value' => $value,
			'id' => $id,
			'title' => $content
		];
		foreach($attributes as $key_attribute => $val_attribute):
			switch($key_attribute):
				case 'class+':
					$button_attributes['class'] += ' ' . $val_attribute;
					break;
				default:
					$button_attributes[$key_attribute] = $val_attribute;
					break;
			endswitch;
		endforeach;
		$this->addElement(name: $element,attributes: $button_attributes,value: $content);
		return $this;
	}
	public function ins_button_add(?string $content = null) {
		$id = null;
		$name = null;
		$value = 'save';
		$content ??= gettext(message: 'Add');
		$attributes = null;
			$this->ins_button_submit(id: $id,name: $name,value: $value,content: $content,attributes: $attributes);
		return $this;
	}
	public function ins_button_apply(?string $content = null) {
		$id = null;
		$name = null;
		$value = 'apply';
		$content ??= gettext(message: 'Apply Changes');
		$attributes = null;
			$this->ins_button_submit(id: $id,name: $name,value: $value,content: $content,attributes: $attributes);
		return $this;
	}
	public function ins_button_cancel(?string $content = null) {
		$id = null;
		$name = null;
		$value = 'cancel';
		$content ??= gettext(message: 'Cancel');
		$attributes = ['formnovalidate' => 'formnovalidate'];
			$this->ins_button_submit(id: $id,name: $name,value: $value,content: $content,attributes: $attributes);
		return $this;
	}
	public function ins_button_clone(?string $content = null) {
		$id = null;
		$name = null;
		$value = 'clone';
		$content ??= gettext(message: 'Clone Configuration');
		$attributes = null;
			$this->ins_button_submit(id: $id,name: $name,value: $value,content: $content,attributes: $attributes);
		return $this;
	}
	public function ins_button_edit(?string $content = null) {
		$id = null;
		$name = null;
		$value = 'edit';
		$content ??= gettext(message: 'Edit');
		$attributes = null;
			$this->ins_button_submit(id: $id,name: $name,value: $value,content: $content,attributes: $attributes);
		return $this;
	}
	public function ins_button_enadis(bool $enable = false,?string $content_on = null,?string $content_off = null) {
		$id = null;
		$name = null;
		$value = $enable ? 'enable' : 'disable';
		$content_on ??= gettext(message: 'Enable');
		$content_off ??= gettext(message: 'Disable');
		$content = $enable ? $content_on : $content_off;
		$attributes = null;
			$this->ins_button_submit(id: $id,name: $name,value: $value,content: $content,attributes: $attributes);
		return $this;
	}
	public function ins_button_reload(bool $enable = false,?string $content = null) {
		if($enable):
			$id = null;
			$name = null;
			$value = 'reload';
			$content ??= gettext(message: 'Reload');
			$attributes = null;
			$this->ins_button_submit(id: $id,name: $name,value: $value,content: $content,attributes: $attributes);
		endif;
		return $this;
	}
	public function ins_button_reorder(bool $enable = false,?string $content = null) {
		if($enable):
			$id = null;
			$name = null;
			$value = 'reorder';
			$content ??= gettext(message: 'Reorder');
			$attributes = null;
			$this->ins_button_submit(id: $id,name: $name,value: $value,content: $content,attributes: $attributes);
		endif;
		return $this;
	}
	public function ins_button_rescan(bool $enable = false,?string $content = null) {
		if($enable):
			$id = null;
			$name = null;
			$value = 'rescan';
			$content ??= gettext(message: 'Rescan');
			$attributes = null;
			$this->ins_button_submit(id: $id,name: $name,value: $value,content: $content,attributes: $attributes);
		endif;
		return $this;
	}
	public function ins_button_restart(bool $enable = false,?string $content = null) {
		if($enable):
			$id = null;
			$name = null;
			$value = 'restart';
			$content ??= gettext(message: 'Restart');
			$attributes = null;
			$this->ins_button_submit(id: $id,name: $name,value: $value,content: $content,attributes: $attributes);
		endif;
		return $this;
	}
	public function ins_button_save(?string $content = null) {
		$id = null;
		$name = null;
		$value = 'save';
		$content ??= gettext(message: 'Apply');
		$attributes = null;
		$this->ins_button_submit(id: $id,name: $name,value: $value,content: $content,attributes: $attributes);
		return $this;
	}
//	remark area macros
	public function add_area_remarks() {
		$subnode = $this->addDIV(attributes: ['id' => 'remarks']);
		return $subnode;
	}
	public function ins_remark($ctrlname,$title,$text) {
		$this->addDIV(attributes: ['id' => $ctrlname])->addElement(name: 'strong',attributes: ['class' => 'red'],value: $title);
		$this->addDIV(value: $text);
		return $this;
	}
	public function ins_authtoken() {
		$input_attributes = [
			'name' => 'authtoken',
			'type' => 'hidden',
			'value' => session::get_authtoken()
		];
		$this->insINPUT(attributes: $input_attributes);
		return $this;
	}
	public function clc_page_title(array $page_title = []) {
		$output = implode(separator: ' > ',array: $page_title);
		return $output;
	}
	public function clc_html_page_title(array $page_title = []) {
		global $d_sysrebootreqd_path;
		global $g_img;

		$output = '';
		if(session::is_admin() && file_exists(filename: $d_sysrebootreqd_path)):
			$output .= $g_img['unicode.reboot'] . ' ';
		endif;
		$output .= system_get_hostname();
		if(!empty($page_title)):
			$output .= ' > ';
			$output .= $this->clc_page_title(page_title: $page_title);
		endif;
		return $output;
	}
	public function ins_head(array $page_title = []) {
		$head = $this->addElement(name: 'head',attributes: ['id' => 'head']);
		$head->
			insElement(name: 'meta',attributes: ['charset' => system_get_language_codeset()])->
			insElement(name: 'meta',attributes: ['name' => 'format-detection','content' => 'telephone=no'])->
			insElement(name: 'meta',attributes: ['name' => 'viewport','content' => 'width=device-width, initial-scale=1.0'])->
			insElement(name: 'meta',attributes: ['name' => 'robots','content' => 'noindex,nofollow'])->
			insElement(name: 'meta',attributes: ['name' => 'description','content' => 'XigmaNAS® - The Free Network Attached Storage Project'])->
			insElement(name: 'title',value: $this->clc_html_page_title(page_title: $page_title))->
			insElement(name: 'link',attributes: ['href' => '/css/gui.css.php','rel' => 'stylesheet','type' => 'text/css'])->
			insElement(name: 'link',attributes: ['href' => '/css/navbar.css.php','rel' => 'stylesheet','type' => 'text/css'])->
			insElement(name: 'link',attributes: ['href' => '/css/tabs.css.php','rel' => 'stylesheet','type' => 'text/css']);
		if($this->option_exists(option: 'login')):
			header(header: "Content-Security-Policy: frame-ancestors 'none'");
		endif;
		$head->
			insElement(name: 'style',value: '.avoid-fouc { visibility:hidden; }');
		$head->
			insElement(name: 'script',attributes: ['src' => '/js/jquery.min.js'])->
			insElement(name: 'script',attributes: ['src' => '/js/gui.js'])->
			insElement(name: 'script',attributes: ['src' => '/js/spinner.js'])->
			insElement(name: 'script',attributes: ['src' => '/js/spin.min.js']);
		if($this->option_exists(option: 'tablesort')):
			$head->insElement(name: 'script',attributes: ['src' => '/js/jquery.tablesorter.min.js']);
			if($this->option_exists(option: 'tablesort-widgets')):
				$head->insElement(name: 'script',attributes: ['src' => '/js/jquery.tablesorter.widgets.min.js']);
			endif;
			if($this->option_exists(option: 'sorter-bytestring')):
				$head->insElement(name: 'script',attributes: ['src' => '/js/parser-bytestring.js']);
			endif;
			if($this->option_exists(option: 'sorter-checkbox')):
				$head->insElement(name: 'script',attributes: ['src' => '/js/parser-checkbox.js']);
			endif;
			if($this->option_exists(option: 'sorter-radio')):
				$head->insElement(name: 'script',attributes: ['src' => '/js/parser-radio.js']);
			endif;
		endif;
		if($this->option_exists(option: 'datechooser')):
			$head->
				insElement(name: 'link',attributes: ['href' => 'js/datechooser.css','rel' => 'stylesheet','type' => 'text/css'])->
				insElement(name: 'script',attributes: ['src' => 'js/datechooser.js']);
		endif;
		return $this;
	}
/**
 *	Creates the body element of the page with all basic subnodes.
 *
 *	@param array $page_title
 *	@param string $action_url If $action_url empty no form element will be created.
 *	@return DOMNode $this
 */
	public function ins_body(array $page_title = [],?string $action_url = null) {
		$is_login = $this->option_exists(option: 'login');
		$is_multipart = $this->option_exists(option: 'multipart');
		$is_tablesort = $this->option_exists(option: 'tablesort');
		$is_form = (isset($action_url) && preg_match(pattern: '/^\S+$/',subject: $action_url));
		$is_spinonsubmit = $is_form && !$this->option_exists(option: 'nospinonsubmit');
		$is_tabnav = !($is_login || $this->option_exists(option: 'notabnav'));
		$body = $this->addElement(name: 'body',attributes: ['id' => 'main']);
		if($is_form):
			$form_attributes = [
				'action' => $action_url,
				'method' => 'post',
				'id' => 'iform',
				'name' => 'iform'
			];
			if($is_multipart):
				$form_attributes['enctype'] = 'multipart/form-data';
			endif;
			$flexcontainer = $body->addFORM(attributes: $form_attributes)->addDIV(attributes: ['id' => 'pagebodyflex']);
		else:
			$flexcontainer = $body->addDIV(attributes: ['id' => 'pagebodyflex']);
		endif;
		$flexcontainer->addDIV(attributes: ['id' => 'spinner_main']);
		$flexcontainer->addDIV(attributes: ['id' => 'spinner_overlay','style' => 'display: none; background-color: white; position: fixed; left:0; top:0; height:100%; width:100%; opacity: 0.25;']);
		if(!$is_login && $is_form):
			$flexcontainer->addDIV(attributes: ['id' => 'formextension'])->ins_authtoken();
		endif;
		if($is_login):
			$flexcontainer->ins_header_login();
		else:
			$flexcontainer->ins_header(page_title: $page_title);
		endif;
		$flexcontainer->ins_main();
		$flexcontainer->ins_footer();
		if($is_tabnav):
			$jdata = <<<'EOJ'
	$(".spin,#tabnav,#tabnav2").click(function() { spinner(); });
EOJ;
		else:
			$jdata = <<<'EOJ'
	$(".spin").click(function() { spinner(); });
EOJ;
		endif;
		$this->add_js_on_load(jcode: $jdata);
		if($is_spinonsubmit):
			$jdata = <<<'EOJ'
	$("#iform").submit(function() { spinner(); });
EOJ;
			$this->add_js_on_load(jcode: $jdata);
		endif;
		$jdata = <<<'EOJ'
var foucNodeList = document.querySelectorAll(".avoid-fouc");
var foucNodeCount = foucNodeList.length;
for(let i = 0;i < foucNodeCount;i++) { foucNodeList[i].classList.remove("avoid-fouc"); }
EOJ;
		$this->add_js_document_ready(jcode: $jdata);
		if($is_tablesort):
			$jdata = <<<'EOJ'
$.tablesorter.defaults.textSorter = $.tablesorter.sortText;
$(".area_data_selection").tablesorter({
	emptyTo: 'none'
});
EOJ;
			$this->add_js_document_ready(jcode: $jdata);
		endif;
		return $this;
	}
	public function ins_header_login() {
		$header = $this->
			addElement(name: 'header',attributes: ['id' => 'g4h'])->
				addDIV(attributes: ['id' => 'gapheader']);
		return $this;
	}
	public function ins_header_menu() {
		global $config;

		$navbartoplevelstyle = $config['system']['webgui']['navbartoplevelstyle'] ?? '';
		$hard_link_regex = '~^[a-z]+://~';
		$menu = get_headermenu();
//		function cares about access rights itself
		make_headermenu_extensions(menu: $menu);
		$menu_list = ['home','system','network','disks','access','services','vm','status','diagnostics','extensions','tools','help'];
		$ul_lev1 = $this->addDIV(attributes: ['id' => 'area_navhdr'])->addElement(name: 'nav',attributes: ['id' => 'navhdr'])->addUL(attributes: ['class' => 'lev1']);
		$li_lev1 = $ul_lev1->addLI(attributes: ['class' => 'lev1']);
		$li_lev1->addA(attributes: ['class' => 'lev1','onclick' => ''],value: "\u{2630}\u{fe0f}");
		switch($navbartoplevelstyle):
			case 'symbol':
				$a_lev2_attributes = $li_lev2_attributes = $ul_lev2_attributes = ['class' => 'lev2 lev2so'];
				break;
			case 'symbolandtext':
				$a_lev2_attributes = $li_lev2_attributes = $ul_lev2_attributes = ['class' => 'lev2 lev2st'];
				break;
			default:
				$a_lev2_attributes = $li_lev2_attributes = $ul_lev2_attributes = ['class' => 'lev2 lev2to'];
				break;
		endswitch;
		$ul_lev2 = $li_lev1->addUL(attributes: $ul_lev2_attributes);
		foreach($menu_list as $menuid):
			if($menu[$menuid]['visible']):
//				render menu when visible
				$li_lev2 = $ul_lev2->addLI(attributes: $li_lev2_attributes);
				$attributes = $a_lev2_attributes;
				switch($menu[$menuid]['type']):
					case 'external':
						$attributes = $a_lev2_attributes;
						$attributes['href'] = $menu[$menuid]['link'];
						$attributes['target'] = '_blank';
						$attributes['rel'] = 'noreferrer';
						break;
					case 'internal':
						$attributes = $a_lev2_attributes;
						$attributes['href'] = $menu[$menuid]['link'];
						$attributes['onclick'] = 'spinner()';
						break;
					case 'nolink':
						$attributes = $a_lev2_attributes;
						$attributes['onclick'] = '';
						break;
				endswitch;
//				$tags = implode(separator: ' ',array: $a_tag);
				if(empty($menu[$menuid]['img'])):
					switch($navbartoplevelstyle):
						case 'symbol':
							$value = $menu[$menuid]['symbol'] ?? $menu[$menuid]['description'];
							break;
						case 'symbolandtext':
							$value = ($menu[$menuid]['symbol'] ? $menu[$menuid]['symbol'] . ' ' : '') . $menu[$menuid]['description'];
							break;
						default:
							$value = $menu[$menuid]['description'];
							break;
					endswitch;
					$li_lev2->addA(attributes: $attributes,value: $value);
				else:
					$li_lev2->addA(attributes: $attributes)->insIMG(attributes: ['src' => $menu[$menuid]['img'],'title' => $menu[$menuid]['description'],'alt' => $menu[$menuid]['description']]);
				endif;
				if(!empty($menu[$menuid]['menuitem'])):
					$ul_lev3 = $li_lev2->addUL(attributes: ['class' => 'lev3']);
//					Display menu items.
					foreach($menu[$menuid]['menuitem'] as $menu_item):
						if($menu_item['visible']):
//							render menuitem when visible
							$a_lev3_attributes = ['class' => 'lev3'];
							switch($menu_item['type']):
								case 'external':
									$a_lev3_attributes['href'] = $menu_item['link'];
									$a_lev3_attributes['target'] = '_blank';
									$a_lev3_attributes['rel'] = 'noreferrer';
									if(preg_match(pattern: $hard_link_regex,subject: $menu_item['link']) !== 1):
//										local link = spinner
										$a_lev3_attributes['onclick'] = 'spinner()';
									endif;
									$ul_lev3->addLI(attributes: ['class' => 'lev3'])->insA(attributes: $a_lev3_attributes,value: $menu_item['description']);
									break;
								case 'internal':
									$a_lev3_attributes['href'] = $menu_item['link'];
									$a_lev3_attributes['target'] = '_self';
									if(preg_match(pattern: $hard_link_regex,subject: $menu_item['link']) !== 1):
//										local link = spinner
										$a_lev3_attributes['onclick'] = 'spinner()';
									endif;
									$ul_lev3->addLI(attributes: ['class' => 'lev3'])->insA(attributes: $a_lev3_attributes,value: $menu_item['description']);
									break;
								case 'separator':
									$ul_lev3->addLI(attributes: ['class' => 'lev3 tabseparator'])->insSPAN(attributes: ['class' => 'lev3 tabseparator']);
									break;
							endswitch;
						endif;
					endforeach;
				endif;
			endif;
		endforeach;
		return $this;
	}
	public function ins_header(array $page_title = []) {
		$header = $this->addElement(name: 'header',attributes: ['id' => 'g4h']);
		$header->addDIV(attributes: ['id' => 'gapheader']);
		$header->ins_header_menu();
		if(!empty($page_title)):
			$header->addDIV(attributes: ['id' => 'pgtitle'])->addP(attributes: ['class' => 'pgtitle'],value: $this->clc_page_title(page_title: $page_title));
		endif;
		return $this;
	}
	public function ins_main() {
		$this->
			addElement(name: 'main',attributes: ['id' => 'g4m2'])->
				ins_javascript(text: 'document.getElementById("g4m2").classList.add("avoid-fouc");')->
				addDIV(attributes: ['id' => 'pagecontent']);
		return $this;
	}
/**
 *	Insert footer
 *	@global string $d_sysrebootreqd_path
 *	@return $this
 */
	public function ins_footer() {
		global $d_sysrebootreqd_path;
		global $g_img;

		$g4fx = $this->
			addElement(name: 'footer',attributes: ['id' => 'g4f'])->
				insDIV(attributes: ['id' => 'gapfooter'])->
				addDIV(attributes: ['id' => 'pagefooter','class' => 'lcrgridx']);
		$g4fl = $g4fx->addDIV(attributes: ['class' => 'g4fl lcrgridl']);
		if(session::is_admin()):
			if(file_exists(filename: $d_sysrebootreqd_path)):
				$g4fl->addA(attributes: ['class' => 'g4fi spin','href' => '/reboot.php','title' => gettext(message: 'A reboot is required')],value: $g_img['unicode.reboot']);
			endif;
		endif;
		$g4fx->addDIV(attributes: ['class' => 'g4fc lcrgridc'],value: get_product_copyright());
		$g4fx->addDIV(attributes: ['class' => 'g4fr lcrgridr'],value: system_get_hostname());
		return $this;
	}
}
