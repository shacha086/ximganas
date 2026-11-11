<?php
/*
	wui2.php

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

require_once 'autoload.php';
require_once 'config.inc';

use common\arr;
use common\lang;
use common\timezone;
use common\uuid;
use gui\document;

/*
 *	compatibility to previous versions which directly instantiated from co_DOMDocument.
 */
class_alias('gui\document','co_DOMDocument',true);

class HTMLBaseControl2 {
	var $_ctrlname = '';
	var $_title = '';
	var $_description = '';
	var $_value;
	var $_required = false;
	var $_readonly = false;
	var $_altpadding = false;
	var $_classtag = 'celltag';
	var $_classdata = 'celldata';
	var $_classaddonrequired = 'req';
	var $_classaddonpadalt = 'alt';
//	constructor
	public function __construct($ctrlname,$title,$value,$description = '') {
		$this->SetCtrlName($ctrlname);
		$this->SetTitle($title);
		$this->SetDescription($description);
		$this->SetValue($value);
	}
//	get/set methods
	function SetAltPadding($bool) {
		$this->_altpadding = $bool;
	}
	function GetAltPadding() {
		return $this->_altpadding;
	}
	function SetClassAddonRequired($cssclass) {
		$this->_classaddonrequired = $cssclass;
	}
	function GetClassAddonRequired() {
		return $this->_classaddonrequired;
	}
	function SetClassAddonPadAlt($cssclass) {
		$this->_classaddonpadalt = $cssclass;
	}
	function GetClassAddonPadAlt() {
		return $this->_classaddonpadalt;
	}
	function SetClassData($cssclass) {
		$this->_classdata = $cssclass;
	}
	function GetClassData() {
		return $this->_classdata;
	}
	function SetClassTag($cssclass) {
		$this ->_classtag = $cssclass;
	}
	function GetClassTag() {
		return $this->_classtag;
	}
	function SetCtrlName($name) {
		$this->_ctrlname = $name;
	}
	function GetCtrlName() {
		return $this->_ctrlname;
	}
	function SetDescription($description) {
		$this->_description = $description;
	}
	function GetDescription() {
		return $this->_description;
	}
	function SetReadOnly($bool) {
		$this->_readonly = $bool;
	}
	function GetReadOnly() {
		return $this->_readonly;
	}
	function SetRequired($bool) {
		$this->_required = $bool;
	}
	function GetRequired() {
		return $this->_required;
	}
	function SetTitle($title) {
		$this->_title = $title;
	}
	function GetTitle() {
		return $this->_title;
	}
	function SetValue($value) {
		$this->_value = $value;
	}
	function GetValue() {
		return $this->_value;
	}
//	support methods
	function GetClassOfTag() {
		$class = $this->GetClassTag();
		if($this->GetRequired() === true):
			$class .= $this->GetClassAddonRequired();
		endif;
		if($this->GetAltPadding() === true):
			$class .= $this->GetClassAddonPadAlt();
		endif;
		return $class;
	}
	function GetClassOfData() {
		$class = $this->GetClassData();
		if($this->GetRequired() === true):
			$class .= $this->GetClassAddonRequired();
		endif;
		if($this->GetAltPadding() === true):
			$class .= $this->GetClassAddonPadAlt();
		endif;
		return $class;
	}
	function GetDescriptionOutput() {
//		description:
//		string
//		[string, ...]
//		[ [string], ...]
//		[ [string,no_br], ...]
//		[ [string,color], ...]
//		[ [string,color,no_br], ...]
		$description = $this->GetDescription();
		$description_output = '';
		$suppressbr = true;
		if(!empty($description)):
//			string or array
			if(is_string($description)):
				$description_output = $description;
			elseif(is_array($description)):
				foreach($description as $description_row):
					if(is_string($description_row)):
						if($suppressbr):
							$description_output .= $description_row;
							$suppressbr = false;
						else:
							$description_output .= ('<br />' . $description_row);
						endif;
					elseif(is_array($description_row)):
						switch(count($description_row)):
							case 1:
								if(is_string($description_row[0])):
									if($suppressbr):
										$suppressbr = false;
									else:
										$description_output .= '<br />';
									endif;
									$description_output .= $description_row[0];
								endif;
								break;
							case 2:
								if(is_string($description_row[0])):
									$color = null;
									if(is_string($description_row[1])):
										$color = $description_row[1];
									endif;
									if(is_bool($description_row[1])):
										$suppressbr = $description_row[1];
									endif;
									if($suppressbr):
										$suppressbr = false;
									else:
										$description_output .= '<br />';
									endif;
									if(is_null($color)):
										$description_output .= $description_row[0];
									else:
										$description_output .= sprintf('<span style="color:%2$s">%1$s</span>',$description_row[0],$color);
									endif;
								endif;
								break;
							case 3:
//								allow not to break
								if(is_string($description_row[0])):
									$color = null;
									if(is_string($description_row[1])):
										$color = $description_row[1];
									endif;
									if(is_bool($description_row[2])):
										$suppressbr = $description_row[2];
									endif;
									if($suppressbr):
										$suppressbr = false;
									else:
										$description_output .= '<br />';
									endif;
									if(is_null($color)):
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
		return $description_output;
	}
	function Compose(?DOMNode &$anchor = null) {
//		create root DOM if anchor not provided
		if(is_null($anchor)):
			$anchor = new document();
		endif;
		$description = $this->GetDescriptionOutput();
//		compose
		$attributes = ['id' => sprintf('%s_tr',$this->GetCtrlName())];
		$tr = $anchor->addTR($attributes);
		$attributes = ['class' => $this->GetClassOfTag()];
//		if($this->GetReadOnly()):
			$tr->insTD($attributes,$this->GetTitle());
//		else:
//			$tdtag = $tr->addTD($attributes);
//			$attributes = ['for' => $ctrlname];
//			$tdtag->addElement('label',$attributes,$this->GetTitle());
//		endif;
		$attributes = ['class' => $this->GetClassOfData()];
		$tddata = $tr->addTD($attributes);
		$this->ComposeInner($tddata);
		if(!empty($description)):
			$attributes = ['class' => 'formfldadditionalinfo'];
			$tddata->insDIV($attributes,$description);
		endif;
		return $anchor;
	}
	function ComposeInner(&$anchor) {
	}
}
class HTMLBaseControlJS2 extends HTMLBaseControl2 {
	var $_onclick = '';
	function SetJSonClick($code) {
		$this->_onclick = $code;
	}
	function GetJSonClick() {
		return $this->_onclick;
	}
}
class HTMLEditBox2 extends HTMLBaseControl2 {
	var $_size = 40;
	var $_maxlength = 0;
	var $_placeholder = '';
	var $_classinputtext = 'formfld';
	var $_classinputtextro = 'formfldro';
//	constructor
	function __construct($ctrlname,$title,$value,$description,$size) {
		parent::__construct($ctrlname,$title,$value,$description);
		$this->SetSize($size);
	}
//	get/set methods
	function SetClassInputText($param) {
		$this->_classinputtext = $param;
	}
	function GetClassInputText() {
		return $this->_classinputtext;
	}
	function SetClassInputTextRO($param) {
		$this->_classinputtextro = $param;
	}
	function GetClassInputTextRO() {
		return $this->_classinputtextro;
	}
	function SetMaxLength($maxlength) {
		$this->_maxlength = $maxlength;
	}
	function GetMaxLength() {
		return $this->_maxlength;
	}
	function SetPlaceholder(string $placeholder = '') {
		$this->_placeholder = $placeholder;
	}
	function GetPlaceholder() {
		return $this->_placeholder;
	}
	function SetSize($size) {
		$this->_size = $size;
	}
	function GetSize() {
		return $this->_size;
	}
//	support methods
	function GetAttributes(array &$attributes = []) {
		if($this->GetReadOnly() === true):
			$attributes['readonly'] = 'readonly';
		endif;
		$tagval = $this->GetPlaceholder();
		if(preg_match('/\S/',$tagval)):
			$attributes['placeholder'] = $tagval;
		endif;
		$tagval = $this->GetMaxLength();
		if($tagval > 0):
			$attributes['maxlength'] = $tagval;
		endif;
		return $attributes;
	}
	function GetClassOfInputText() {
		if($this->GetReadOnly() === true):
			return $this->GetClassInputTextRO();
		else:
			return $this->GetClassInputText();
		endif;
	}
	function ComposeInner(&$anchor) {
		$attributes = [
			'type' => 'text',
			'id' => $this->GetCtrlName(),
			'name' => $this->GetCtrlName(),
			'class' => $this->GetClassOfInputText(),
			'size' => $this->GetSize(),
			'value' => $this->GetValue()
		];
		$this->GetAttributes($attributes);
		$anchor->insINPUT($attributes);
	}
}
class HTMLPasswordBox2 extends HTMLEditBox2 {
	var $_classinputpassword = 'formfld';
//	get/set methods
	function SetClassInputPassword($cssclass) {
		$this->_classinputpassword = $cssclass;
	}
	function GetClassInputPassword() {
		return $this->_classinputpassword;
	}
//	support methods
	function GetClassOfInputPassword() {
		return $this->GetClassInputPassword();
	}
	function ComposeInner(&$anchor) {
		$ctrlname = $this->GetCtrlName();
		$attributes = [
			'type' => 'password',
			'id' => $ctrlname,
			'name' => $ctrlname,
			'class' => $this->GetClassOfInputPassword(),
			'autocomplete' => 'off',
			'size' => $this->GetSize(),
			'value'=> $this->GetValue()
		];
		$this->GetAttributes($attributes);
		$anchor->insINPUT($attributes);
	}
}
class HTMLPasswordConfBox2 extends HTMLEditBox2 {
	var $_ctrlnameconf = '';
	var $_valueconf = '';
	var $_classinputpassword = 'formfld';
	var $_placeholderconfirm = '';
//	constructor
	function __construct($ctrlname,$ctrlnameconf,$title,$value,$valueconf,$description,$size) {
		parent::__construct($ctrlname,$title,$value,$description,$size);
		$this->SetCtrlNameConf($ctrlnameconf);
		$this->SetValueConf($valueconf);
	}
//	get/set methods
	function SetClassInputPassword($cssclass) {
		$this->_classinputpassword = $cssclass;
	}
	function GetClassInputPassword() {
		return $this->_classinputpassword;
	}
	function SetCtrlNameConf($name) {
		$this->_ctrlnameconf = $name;
	}
	function GetCtrlNameConf() {
		return $this->_ctrlnameconf;
	}
	function SetPlaceholderConfirm(string $placeholder = '') {
		$this->_placeholderconfirm = $placeholder;
	}
	function GetPlaceholderConfirm() {
		return $this->_placeholderconfirm;
	}
	function SetValueConf($value) {
		$this->_valueconf = $value;
	}
	function GetValueConf() {
		return $this->_valueconf;
	}
//	support methods
	function GetAttributesConfirm(array &$attributes = []) {
		$attributes = $this->GetAttributes($attributes);
		$tagval = $this->GetPlaceholderConfirm();
		if(preg_match('/\S/',$tagval)):
			$attributes['placeholder'] = $tagval;
		endif;
		return $attributes;
	}
	function GetClassOfInputPassword() {
		return $this->GetClassInputPassword();
	}
	function ComposeInner(&$anchor) {
		$ctrlname = $this->GetCtrlName();
		$ctrlnameconf = $this->GetCtrlNameConf();
		$attributes = [
			'type' => 'password',
			'id' => $ctrlname,
			'name' => $ctrlname,
			'class' => $this->GetClassOfInputPassword(),
			'autocomplete' => "off",
			'size' => $this->GetSize(),
			'value'=> $this->GetValue()
		];
		$this->GetAttributes($attributes);
		$o_div1 = $anchor->addDIV();
		$o_div1->insINPUT($attributes);
		$attributes = [
			'type' => 'password',
			'id' => $ctrlnameconf,
			'name' => $ctrlnameconf,
			'class' => $this->GetClassOfInputPassword(),
			'autocomplete' => "off",
			'size' => $this->GetSize(),
			'value' => $this->GetValueConf()
		];
		$this->GetAttributesConfirm($attributes);
		$o_div2 = $anchor->addDIV();
		$o_div2->insINPUT($attributes);
	}
}
class HTMLTextArea2 extends HTMLEditBox2 {
	var $_columns = 40;
	var $_rows = 5;
	var $_wrap = true;
	var $_classtextarea = 'formpre';
	var $_classtextarearo = 'formprero';
//	constructor
	function __construct($ctrlname,$title,$value,$description,$columns,$rows) {
		$this->SetCtrlName($ctrlname);
		$this->SetTitle($title);
		$this->SetValue($value);
		$this->SetDescription($description);
		$this->SetColumns($columns);
		$this->SetRows($rows);
	}
//	get/set methods
	function SetClasstextarea($cssclass) {
		$this->_classtextarea = $cssclass;
	}
	function GetClassTextarea() {
		return $this->_classtextarea;
	}
	function SetClasstextareaRO($cssclass) {
		$this->_classtextarearo = $cssclass;
	}
	function GetClassTextareaRO() {
		return $this->_classtextarearo;
	}
	function SetColumns($columns) {
		$this->_columns = $columns;
	}
	function GetColumns() {
		return $this->_columns;
	}
	function SetRows($rows) {
		$this->_rows = $rows;
	}
	function GetRows() {
		return $this->_rows;
	}
	function SetWrap($bool) {
		$this->_wrap = $bool;
	}
	function GetWrap() {
		return $this->_wrap;
	}
//	support methods
	function GetAttributes(array &$attributes = []) {
		parent::GetAttributes($attributes);
		if($this->GetWrap() === false):
			$attributes['wrap'] = 'soft';
		else:
			$attributes['wrap'] = 'hard';
		endif;
		return $attributes;
	}
	function GetClassOfTextarea() {
		return($this->GetReadOnly() ? $this->GetClassTextareaRO() : $this->GetClassTextarea());
	}
	function ComposeInner(&$anchor) {
		$ctrlname = $this->GetCtrlName();
		$attributes = [
			'id' => $ctrlname,
			'name' => $ctrlname,
			'class' => $this->GetClassOfTextarea(),
			'cols' => $this->GetColumns(),
			'rows' => $this->GetRows()
		];
		$this->GetAttributes($attributes);
		$anchor->addElement('textarea',$attributes,$this->GetValue());
	}
}
class HTMLFileChooser2 extends HTMLEditBox2 {
	var $_path = '';
	function __construct($ctrlname,$title,$value,$description,$size = 60) {
		parent::__construct($ctrlname,$title,$value,$description,$size);
	}
	function SetPath($path) {
		$this->_path = $path;
	}
	function GetPath() {
		return $this->_path;
	}
	function ComposeInner(&$anchor) {
//		helper variables
		$ctrlname = $this->GetCtrlName();
		$size = $this->GetSize();
//		input element
		$attributes = [
			'type' => 'text',
			'id' => $ctrlname,
			'name' => $ctrlname,
			'class' => 'formfld',
			'value' => $this->GetValue(),
			'size' => $size
		];
		$this->GetAttributes($attributes);
		$anchor->insINPUT($attributes);
//		file chooser
		$js = sprintf('%1$sifield = form.%1$s;',$ctrlname)
			. 'filechooser = window.open("filechooser.php?p="+'
			. sprintf('encodeURIComponent(%sifield.value)+',$ctrlname)
			. sprintf('"&sd=%s",',$this->GetPath())
			. '"filechooser",'
			. '"scrollbars=yes,toolbar=no,menubar=no,statusbar=no,width=550,height=300");'
			. sprintf('filechooser.ifield = %sifield;',$ctrlname)
			. sprintf('window.ifield = %sifield;',$ctrlname);
		$attributes = [
			'type' => 'button',
			'id' => $ctrlname . 'browsebtn',
			'name' => $ctrlname . 'browsebtn',
			'class' => 'formbtn',
			'value' => '...',
			'size' => $size,
			'onclick' => $js,
		];
		$this->GetAttributes($attributes);
		$anchor->insINPUT($attributes);
	}
}
class HTMLIPAddressBox2 extends HTMLEditBox2 {
	var $_ctrlnamenetmask = '';
	var $_valuenetmask = '';
//	constructor
	function __construct($ctrlname,$ctrlnamenetmask,$title,$value,$valuenetmask,$description) {
		$this->SetCtrlName($ctrlname);
		$this->SetCtrlNameNetmask($ctrlnamenetmask);
		$this->SetTitle($title);
		$this->SetValue($value);
		$this->SetValueNetmask($valuenetmask);
		$this->SetDescription($description);
	}
//	get/set Methods
	function SetCtrlNameNetmask($name) {
		$this->_ctrlnamenetmask = $name;
	}
	function GetCtrlNameNetmask() {
		return $this->_ctrlnamenetmask;
	}
	function SetValueNetmask($value) {
		$this->_valuenetmask = $value;
	}
	function GetValueNetmask() {
		return $this->_valuenetmask;
	}
}
class HTMLIPv4AddressBox2 extends HTMLIPAddressBox2 {
//	constructor
	function __construct($ctrlname,$ctrlnamenetmask,$title,$value,$valuenetmask,$description) {
		parent::__construct($ctrlname,$ctrlnamenetmask,$title,$value,$valuenetmask,$description);
		$this->SetSize(20);
	}
//	support methods
	function ComposeInner(&$anchor) {
		$ctrlname = $this->GetCtrlName();
		$ctrlnamenetmask = $this->GetCtrlNameNetmask();
		$valuenetmask = $this->GetValueNetmask();
		$attributes = [
			'type' => 'text',
			'id' => $ctrlname,
			'name' => $ctrlname,
			'class' => 'formfld',
			'value' => $this->GetValue(),
			'size' => $this->GetSize()
		];
		$anchor->insINPUT($attributes);
		$slash = $anchor->ownerDocument->createTextNode(' / ');
		$anchor->appendChild($slash);
		$attributes = ['id' => $ctrlnamenetmask,'name' => $ctrlnamenetmask,'class' => 'formfld'];
		$o_select = $anchor->addElement('select',$attributes);
		foreach(range(1,32) as $netmask):
			$attributes = ['value' => $netmask];
			if($netmask == $valuenetmask):
				$attributes['selected'] = 'selected';
			endif;
			$o_select->addElement('option',$attributes,$netmask);
		endforeach;
	}
}
class HTMLIPv6AddressBox2 extends HTMLIPAddressBox2 {
//	constructor
	function __construct($ctrlname,$ctrlnamenetmask,$title,$value,$valuenetmask,$description) {
		parent::__construct($ctrlname,$ctrlnamenetmask,$title,$value,$valuenetmask,$description);
		$this->SetSize(60);
	}
//	support methods
	function ComposeInner(&$anchor) {
		$ctrlname = $this->GetCtrlName();
		$ctrlnamenetmask = $this->GetCtrlNameNetmask();
		$attributes = [
			'type' => 'text',
			'id' => $ctrlname,
			'name' => $ctrlname,
			'class' => 'formfld',
			'value' => $this->GetValue(),
			'size' => $this->GetSize()
		];
		$anchor->insINPUT($attributes);
		$slash = $anchor->ownerDocument->createTextNode(' / ');
		$anchor->appendChild($slash);
		$attributes = [
			'type' => 'text',
			'id' => $ctrlnamenetmask,
			'name' => $ctrlnamenetmask,
			'class' => 'formfld',
			'value' => $this->GetValueNetmask(),
			'size' => 2
		];
		$anchor->insINPUT($attributes);
	}
}
class HTMLCheckBox2 extends HTMLBaseControlJS2 {
	var $_caption = '';
	var $_classcheckbox = 'celldatacheckbox';
	var $_classcheckboxro = 'celldatacheckbox';
//	constructor
	function __construct($ctrlname,$title,$value,$caption,$description = '') {
		parent::__construct($ctrlname,$title,$value,$description);
		$this->SetCaption($caption);
	}
//	get/set methods
	function SetChecked($bool) {
		$this->SetValue($bool);
	}
	function IsChecked() {
		return $this->GetValue();
	}
	function SetCaption($caption) {
		$this->_caption = $caption;
	}
	function GetCaption() {
		return $this->_caption;
	}
	function SetClassCheckbox($cssclass) {
		$this->_classcheckbox = $cssclass;
	}
	function GetClassCheckbox() {
		return $this->_classcheckbox;
	}
	function SetClassCheckboxRO($cssclass) {
		$this->_classcheckboxro = $cssclass;
	}
	function GetClassCheckboxRO() {
		return $this->_classcheckboxro;
	}
//	support methods
	function GetAttributes(array &$attributes = []) {
		if($this->IsChecked() === true):
			$attributes['checked'] = 'checked';
		endif;
		if($this->GetReadOnly() === true):
			$attributes['disabled'] = 'disabled';
		endif;
		$onclick = $this->GetJSonClick();
		if(!empty($onclick)):
			$attributes['onclick'] = $onclick;
		endif;
		return $attributes;
	}
	function GetClassOfCheckbox() {
		return($this->GetReadOnly() ? $this->GetClassCheckboxRO() : $this->GetClassCheckbox());
	}
	function ComposeInner(&$anchor) {
//		helper variables
		$ctrlname = $this->GetCtrlName();
//		compose
		$div = $anchor->addDIV(['class' => $this->GetClassOfCheckbox()]);
		$attributes = ['type' => 'checkbox','id' => $ctrlname,'name' => $ctrlname,'value' => 'yes'];
		$this->GetAttributes($attributes);
		$div->insINPUT($attributes);
		$div->addElement('label',['for' => $ctrlname],$this->GetCaption());
	}
}
class HTMLSelectControl2 extends HTMLBaseControlJS2 {
	var $_ctrlclass = '';
	var $_options = [];
//	constructor
	function __construct($ctrlclass,$ctrlname,$title,$value,$options,$description) {
		parent::__construct($ctrlname,$title,$value,$description);
		$this->SetCtrlClass($ctrlclass);
		$this->SetOptions($options);
	}
//	get/set methods
	function SetCtrlClass($ctrlclass) {
		$this->_ctrlclass = $ctrlclass;
	}
	function GetCtrlClass() {
		return $this->_ctrlclass;
	}
	function SetOptions(array $options = []) {
		$this->_options = $options;
	}
	function GetOptions() {
		return $this->_options;
	}
	function GetAttributes(array &$attributes = []) {
		if($this->GetReadOnly() === true):
			$attributes['disabled'] = 'disabled';
		endif;
		$onclick = $this->GetJSonClick();
		if(!empty($onclick)):
			$attributes['onclick'] = $onclick;
		endif;
		return $attributes;
	}
	function ComposeInner(&$anchor) {
		$ctrlname = $this->GetCtrlName();
		$value = $this->GetValue();
		$options = $this->GetOptions();
		$attributes = [
			'id' => $ctrlname,
			'name' => $ctrlname,
			'class' => $this->GetCtrlClass()
		];
		$this->GetAttributes($attributes);
		$select = $anchor->addElement('select',$attributes);
		foreach($options as $option_tag => $option_val):
			$attributes = ['value' => $option_tag];
			if($value == $option_tag):
				$attributes['selected'] = 'selected';
			endif;
			$select->addElement('option',$attributes,$option_val);
		endforeach;
	}
}
class HTMLMultiSelectControl2 extends HTMLSelectControl2 {
	var $_size = 10;
//	constructor
	function __construct($ctrlclass,$ctrlname,$title,$value,$options,$description) {
		parent::__construct($ctrlclass,$ctrlname,$title,$value,$options,$description);
	}
//	get/set methods
	function GetSize() {
		return $this->_size;
	}
	function SetSize($size) {
		$this->_size = $size;
	}
//	support methods
	function ComposeInner(&$anchor) {
		$ctrlname = $this->GetCtrlName();
		$value = $this->GetValue();
		$options = $this->GetOptions();
		$attributes = [
			'id' => $ctrlname,
			'name' => sprintf('%s[]',$ctrlname),
			'class' => $this->GetCtrlClass(),
			'multiple' => 'multiple',
			'size' => $this->GetSize()
		];
		$this->GetAttributes($attributes);
		$select = $anchor->addElement('select',$attributes);
		foreach($options as $option_tag => $option_val):
			$attributes = ['value' => $option_tag];
			if(is_array($value) && in_array($option_tag,$value)):
				$attributes['selected'] = 'selected';
			endif;
			$select->addElement('option',$attributes,$option_val);
		endforeach;
	}
}
class HTMLComboBox2 extends HTMLSelectControl2 {
//	constructor
	function __construct($ctrlname,$title,$value,$options,$description) {
		parent::__construct('formfld',$ctrlname,$title,$value,$options,$description);
	}
}
class HTMLRadioBox2 extends HTMLComboBox2 {
	function ComposeInner(&$anchor) {
		$ctrlname = $this->GetCtrlName();
		$value = $this->GetValue();
		$options = $this->GetOptions();
		$table = $anchor->addTABLE(['class' => 'area_data_selection']);
		$colgroup = $table->addCOLGROUP();
		$colgroup->insCOL(['style' => 'width:5%']);
		$colgroup->insCOL(['style' => 'width:95%']);
		$thead = $table->addTHEAD();
		$tr = $thead->addTR();
		$tr->insTHwC('lhelc');
		$tr->insTHwC('lhebl',$this->GetTitle());
		$tbody = $table->addTBODY();
		foreach($options as $option_tag => $option_val):
//			create a unique identifier for each row.
//			use label tag for text column to allow enabling the radio button by clicking on the text
			$uuid = sprintf('radio_%s',uuid::create_v4());
			$tr = $tbody->addTR();
			$tdl = $tr->addTDwC('lcelc');
			$attributes = [
				'name' => $ctrlname,
				'value' => $option_tag,
				'type' => 'radio',
				'id' => $uuid
			];
			if($value === (string)$option_tag):
				$attributes['checked'] = 'checked';
			endif;
			$tdl->insINPUT($attributes);
			$tdr = $tr->addTDwC('lcebl');
			$tdr->addElement('label',['for' => $uuid,'style' => 'white-space:pre-wrap;'],$option_val);
		endforeach;
	}
}
class HTMLMountComboBox2 extends HTMLComboBox2 {
//	constructor
	function __construct($ctrlname,$title,$value,$description) {
		global $config;

//		generate options.
		$a_mounts = &arr::make_branch($config,'mounts','mount');
		arr::sort_key($a_mounts,'devicespecialfile');
		$options = [];
		$options[''] = gettext('Must choose one');
		foreach($a_mounts as $r_mount):
			$options[$r_mount['uuid']] = $r_mount['sharename'];
		endforeach;
		parent::__construct($ctrlname,$title,$value,$options,$description);
	}
}
class HTMLTimeZoneComboBox2 extends HTMLComboBox2 {
	function __construct($ctrlname,$title,$value,$description) {
		$options = timezone::get_timezones_for_select();
		parent::__construct($ctrlname,$title,$value,$options,$description);
	}
}
class HTMLLanguageComboBox2 extends HTMLComboBox2 {
	function __construct($ctrlname,$title,$value,$description) {
		$options = lang::get_options();
		parent::__construct($ctrlname,$title,$value,$options,$description);
	}
}
class HTMLInterfaceComboBox2 extends HTMLComboBox2 {
	function __construct($ctrlname,$title,$value,$description) {
		global $config;

//		generate options.
		$options = ['lan' => 'LAN'];
		for($i = 1;isset($config['interfaces']['opt' . $i]);$i++):
			if(isset($config['interfaces']['opt' . $i]['enable'])):
				$options['opt' . $i] = $config['interfaces']['opt' . $i]['descr'];
			endif;
		endfor;
		parent::__construct($ctrlname,$title,$value,$options,$description);
	}
}
class HTMLListBox2 extends HTMLMultiSelectControl2 {
	function __construct($ctrlname,$title,$value,$options,$description) {
		parent::__construct('formselect',$ctrlname,$title,$value,$options,$description);
	}
}
class HTMLCheckboxBox2 extends HTMLListBox2 {
	function ComposeInner(&$anchor) {
		$ctrlname = $this->GetCtrlName();
		$value = $this->GetValue();
		$options = $this->GetOptions();
		$table = $anchor->addTABLE(['class' => 'area_data_selection']);
		$colgroup = $table->addCOLGROUP();
		$colgroup->insCOL(['style' => 'width:5%']);
		$colgroup->insCOL(['style' => 'width:95%']);
		$thead = $table->addTHEAD();
		$tr = $thead->addTR();
		$tr->insTHwC('lhelc');
		$tr->insTHwC('lhebl',$this->GetTitle());
		$tbody = $table->addTBODY();
		foreach($options as $option_tag => $option_val):
//			create a unique identifier for each row.
//			use label tag for text column to allow toggling the checkbox button by clicking on the text
			$uuid = sprintf('checkbox_%s',uuid::create_v4());
			$tr = $tbody->addTR();
			$tdl = $tr->addTDwC('lcelc');
			$attributes = [
				'name' => sprintf('%s[]',$ctrlname),
				'value' => $option_tag,
				'type' => 'checkbox',
				'id' => $uuid
			];
			if(is_array($value) && in_array($option_tag,$value)):
				$attributes['checked'] = 'checked';
			endif;
			$tdl->insINPUT($attributes);
			$tdr = $tr->addTDwC('lcebl');
			$tdr->addElement('label',['for' => $uuid,'style' => 'white-space:pre-wrap;'],$option_val);
		endforeach;
	}
}
class HTMLSeparator2 extends HTMLBaseControl2 {
	var $_colspan = 2;
	var $_classseparator = 'gap';
//	constructor
	function __construct() {
	}
//	get/set methods
	function SetClassSeparator($cssclass) {
		$this->_classseparator = $cssclass;
	}
	function GetClassSeparator() {
		return $this->_classseparator;
	}
	function SetColSpan($colspan) {
		$this->_colspan = $colspan;
	}
	function GetColSpan() {
		return $this->_colspan;
	}
//	support methods
	function GetClassOfSeparator() {
		return $this->GetClassSeparator();
	}
	function Compose(?DOMNode &$anchor = null) {
//		create root DOM if anchor not provided
		if(is_null($anchor)):
			$anchor = new document();
		endif;
//		helping variables
		$ctrlname = $this->GetCtrlName();
//		compose
		$attributes = [];
		if(preg_match('/\S/',$ctrlname)):
			$attributes['id'] = $ctrlname;
		endif;
		$o_tr = $anchor->addTR($attributes);
		$attributes = ['class' => $this->GetClassOfSeparator(),'colspan' => $this->GetColSpan()];
		$o_tr->addTD($attributes);
		return $anchor;
	}
}
class HTMLTitleLine2 extends HTMLBaseControl2 {
	var $_colspan = 2;
	var $_classtopic = 'lhetop';
//	get/set methods
	function SetClassTopic($cssclass) {
		$this->_classtopic = $cssclass;
	}
	function GetClassTopic() {
		return $this->_classtopic;
	}
	function SetColSpan($colspan) {
		$this->_colspan = $colspan;
	}
	function GetColSpan() {
		return $this->_colspan;
	}
//	support methods
	function GetClassOfTopic() {
		return $this->GetClassTopic();
	}
	function Compose(?DOMNode &$anchor = null) {
//		create root DOM if anchor not provided
		if(is_null($anchor)):
			$anchor = new document();
		endif;
//		helping variables
		$ctrlname = $this->GetCtrlName();
//		compose
		if(preg_match('/\S/',$ctrlname)):
			$tr_attributes['id'] = $ctrlname;
		else:
			$tr_attributes = [];
		endif;
		$th_attributes = ['class' => $this->GetClassOfTopic(),'colspan' => $this->GetColSpan()];
		$div_attributes = ['class' => 'cblot'];
		$anchor->
			addTR(attributes: $tr_attributes)->
				addTH(attributes: $th_attributes)->
					addDIV(attributes: $div_attributes)->
						insSPAN(value: $this->GetTitle());
		return $anchor;
	}
}
class HTMLTitleLineCheckBox2 extends HTMLCheckBox2 {
	var $_colspan = 2;
	var $_classtopic = 'lhetop';
//	constructor
	function __construct($ctrlname,$title,$value,$caption) {
		parent::__construct($ctrlname,$title,$value,$caption);
	}
//	get/set methods
	function SetClassTopic($cssclass) {
		$this->_classtopic = $cssclass;
	}
	function GetClassTopic() {
		return $this->_classtopic;
	}
	function SetColSpan($colspan) {
		$this->_colspan = $colspan;
	}
	function GetColSpan() {
		return $this->_colspan;
	}
//	support methods
	function GetClassOfTopic() {
		return $this->GetClassTopic();
	}
	function Compose(?DOMNode &$anchor = null) {
//		create root DOM if anchor not provided
		if(is_null($anchor)):
			$anchor = new document();
		endif;
//		helping variables
		$ctrlname = $this->GetCtrlName();
//		compose
		$tr_attributes = ['id' => sprintf('%s_tr',$ctrlname)];
		$th_attributes = ['class' => $this->GetClassOfTopic(),'colspan' => $this->GetColSpan()];
		$div_attributes = ['class' => 'cblot'];
		$label_attributes = ['class' => 'cblot'];
		$input_attributes = ['type' => 'checkbox','id' => $ctrlname,'class' => 'cblot','name' => $ctrlname,'value' => 'yes'];
		$this->getAttributes($input_attributes);
		$span_attributes = ['class' => 'cblot'];
		$anchor->
			addTR(attributes: $tr_attributes)->
				addTH(attributes: $th_attributes)->
					addDIV(attributes: $div_attributes)->
						insSPAN(value: $this->GetTitle())->
						addElement(name: 'label',attributes: $label_attributes)->
							insINPUT(attributes: $input_attributes)->
							addSPAN(attributes: $span_attributes,value: $this->GetCaption());
		return $anchor;
	}
}
class HTMLText2 extends HTMLBaseControl2 {
//	constructor
	function __construct($ctrlname,$title,$text) {
		$this->SetCtrlName($ctrlname);
		$this->SetReadOnly(true);
		$this->SetRequired(false);
		$this->SetTitle($title);
		$this->SetValue($text);
	}
//	support methods
	function ComposeInner(&$anchor) {
//		compose
		$anchor->addSPAN([],$this->GetValue());
	}
}
class HTMLTextInfo2 extends HTMLBaseControl2 {
	function __construct($ctrlname,$title,$text) {
		$this->SetCtrlName($ctrlname);
		$this->SetTitle($title);
		$this->SetValue($text);
	}
	function Compose(?DOMNode &$anchor = null) {
//		create root DOM if anchor not provided
		if(is_null($anchor)):
			$anchor = new document();
		endif;
//		helping variables
		$ctrlname = $this->GetCtrlName();
//		compose
		$attributes = ['id' => sprintf('%s_tr',$ctrlname)];
		$tr = $anchor->addTR($attributes);
		$attributes = ['class' => $this->GetClassOfTag()];
		$tdtag = $tr->addTD($attributes,$this->GetTitle());
		$attributes = ['class' => $this->GetClassOfData()];
		$tddata = $tr->addTD($attributes);
		$attributes = ['id' => $ctrlname];
		$tddata->addSPAN($attributes,$this->getValue());
		return $anchor;
	}
}
class HTMLRemark2 extends HTMLBaseControl2 {
	function __construct($ctrlname,$title,$text) {
		$this->SetCtrlName($ctrlname);
		$this->SetTitle($title);
		$this->SetValue($text);
	}
	function Compose(?DOMNode &$anchor = null) {
//		create root DOM if anchor not provided
		if(is_null($anchor)):
			$anchor = new document();
		endif;
//		helping variables
		$ctrlname = $this->GetCtrlName();
//		compose
		$attributes = ['id' => $ctrlname];
		$div1 = $anchor->addDIV($attributes);
		$attributes = ['class' => 'red'];
		$div1->addElement('strong',$attributes,$this->GetTitle());
		$attributes = [];
		$div2 = $anchor->addDIV($attributes,$this->GetValue());
		return $anchor;
	}
}
class HTMLFolderBox2 extends HTMLBaseControl2 {
	var $_path = '';

	function __construct($ctrlname,$title,$value,$description = '') {
		parent::__construct($ctrlname,$title,$value,$description);
	}
	function GetPath() {
		return $this->_path;
	}
	function SetPath($path) {
		$this->_path = $path;
	}
	function ComposeInner(&$anchor) {
//		helping variables
		$ctrlname = $this->GetCtrlName();
		$ctrlnamedata = $ctrlname . 'data';
		$value = $this->GetValue();
//		control code for folders
		$t = [];
		$t[] = sprintf('function onchange_%s() {',$ctrlname);
		$t[] = "\t" . sprintf('document.getElementById("%s").value = document.getElementById("%s").value;',$ctrlnamedata,$ctrlname);
		$t[] = '}';
		$t[] = sprintf('function onclick_add_%s() {',$ctrlname);
		$t[] = "\t" . sprintf('var value = document.getElementById("%s").value;',$ctrlnamedata);
		$t[] = "\t" . 'if (value != "") {';
		$t[] = "\t\t" . 'var found = false;';
		$t[] = "\t\t" . sprintf('var element = document.getElementById("%s");',$ctrlname);
		$t[] = "\t\t" . 'for (var i = 0; i < element.length; i++) {';
		$t[] = "\t\t\t" . 'if (element.options[i].text == value) {';
		$t[] = "\t\t\t\t" . 'found = true;';
		$t[] = "\t\t\t\t" . 'break;';
		$t[] = "\t\t\t" . '}';
		$t[] = "\t\t" . '}';
		$t[] = "\t\t" . 'if (found != true) {';
		$t[] = "\t\t\t" . 'element.options[element.length] = new Option(value, value, false, true);';
		$t[] = "\t\t\t" . sprintf('document.getElementById("%s").value = "";',$ctrlnamedata);
		$t[] = "\t\t" . '}';
		$t[] = "\t" . '}';
		$t[] = '}';
		$t[] = sprintf('function onclick_delete_%s() {',$ctrlname);
		$t[] = "\t" . sprintf('var element = document.getElementById("%s");',$ctrlname);
		$t[] = "\t" . 'if (element.value != "") {';
		$t[] = "\t\t" . sprintf('var msg = confirm(%s);',unicode_escape_javascript(gettext('Do you really want to remove the selected item from the list?')));
		$t[] = "\t\t" . 'if (msg == true) {';
		$t[] = "\t\t\t" . 'element.options[element.selectedIndex] = null;';
		$t[] = "\t\t\t" . sprintf('document.getElementById("%s").value = "";',$ctrlnamedata);
		$t[] = "\t\t" . '}';
		$t[] = "\t" . '} else {';
		$t[] = "\t\t" . sprintf('alert(%s);',unicode_escape_javascript(gettext('Select item to remove from the list')));
		$t[] = "\t" . '}';
		$t[] = '}';
		$t[] = sprintf('function onclick_change_%s() {',$ctrlname);
		$t[] = "\t" . sprintf('var element = document.getElementById("%s");',$ctrlname);
		$t[] = "\t" . 'if (element.value != "") {';
		$t[] = "\t\t" . sprintf('var value = document.getElementById("%s").value;',$ctrlnamedata);
		$t[] = "\t\t" . 'element.options[element.selectedIndex].text = value;';
		$t[] = "\t\t" . 'element.options[element.selectedIndex].value = value;';
		$t[] = "\t" . '}';
		$t[] = '}';
		$t[] = sprintf('function onsubmit_%s() {',$ctrlname);
		$t[] = "\t" . sprintf('var element = document.getElementById("%s");',$ctrlname);
		$t[] = "\t" . 'for (var i = 0; i < element.length; i++) {';
		$t[] = "\t\t" . 'if (element.options[i].value != "")';
		$t[] = "\t\t\t" . 'element.options[i].selected = true;';
		$t[] = "\t" . '}';
		$t[] = '}';
		$anchor->ins_javascript(implode("\n",$t));
//		section 1: select + delete
		$div1 = $anchor->addDIV();
//		selected folder
		$attributes = [
			'id' => $ctrlname,
			'name' => sprintf('%s[]',$ctrlname),
			'class' => 'formfld',
			'multiple' => 'multiple',
			'size' => '4',
			'style' => 'width:350px',
			'onchange' => sprintf('onchange_%s()',$ctrlname)
		];
		$select = $div1->addElement('select',$attributes);
		foreach($value as $value_key => $value_val):
			$attributes = ['value' => $value_val];
			$select->addElement('option',$attributes,$value_val);
		endforeach;
//		delete button
		$attributes = [
			'type' => 'button',
			'id' => sprintf('%sdeletebtn',$ctrlname),
			'name' => sprintf('%sdeletebtn',$ctrlname),
			'class' => 'formbtn',
			'value' => gettext('Delete'),
			'onclick' => sprintf('onclick_delete_%s()',$ctrlname)
		];
		$div1->insINPUT($attributes);
//		section 2: choose, add + change
		$div2 = $anchor->addDIV();
//		path input field
		$attributes = [
			'type' => 'text',
			'id' => sprintf('%sdata',$ctrlname),
			'name' => sprintf('%sdata',$ctrlname),
			'class' => 'formfld',
			'value' => '',
			'size' => 60
		];
		$div2->insINPUT($attributes);
//		choose button
		$js = sprintf('ifield = form.%s;',$ctrlnamedata)
			. ' filechooser = window.open("filechooser.php'
			. '?p="+encodeURIComponent(ifield.value)+"'
			. sprintf('&sd=%s",',$this->GetPath())
			. ' "filechooser",'
			. ' "scrollbars=yes,toolbar=no,menubar=no,statusbar=no,width=550,height=300");'
			. ' filechooser.ifield = ifield;'
			. ' window.ifield = ifield;';
		$attributes = [
			'type' => 'button',
			'id' => sprintf('%sbrowsebtn',$ctrlname),
			'name' => sprintf('%sbrowsebtn',$ctrlname),
			'class' => 'formbtn',
			'value' => '...',
			'onclick' => $js
		];
		$div2->insINPUT($attributes);
//		add button
		$attributes = [
			'type' => 'button',
			'id' => sprintf('%saddbtn',$ctrlname),
			'name' => sprintf('%saddbtn',$ctrlname),
			'class' => 'formbtn',
			'value' => gettext('Add'),
			'onclick' => sprintf('onclick_add_%s()',$ctrlname)
		];
		$div2->insINPUT($attributes);
//		change button
		$attributes = [
			'type' => 'button',
			'id' => sprintf('%schangebtn',$ctrlname),
			'name' => sprintf('%schangebtn',$ctrlname),
			'class' => 'formbtn',
			'value' => gettext('Change'),
			'onclick' => sprintf('onclick_change_%s()',$ctrlname)
		];
		$div2->insINPUT($attributes);
	}
}
/**
 *
 * @param array $page_title
 * @param string $action_url
 * @param string ...$options
 * @return DOMDocument
 */
/*
 *	login
 *	datechooser
 *	multipart
 *	nospinonsubmit
 *	notabnav
 *	tablesort
 *		tablesort-widgets
 *		sorter-bytestring
 *		sorter-checkbox
 *		sorter-radio
 */
function new_page(array $page_title = [],?string $action_url = null,string ...$options) {
	$document = new document();
	$document->
		loadHTML('<!DOCTYPE html>',LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
	$document->
		set_options(...$options)->
		addElement('html',['lang' => system_get_language_code()])->
			ins_head($page_title)->
			ins_body($page_title,$action_url);
	return $document;
}
/**
 *	create a DOMDocument with GUI functions
 *	@param string $version
 *	@param string $encoding
 *	@return document
 */
function new_document(string $version = '1.0',string $encoding = 'UTF-8') {
	$document = new document(version: $version,encoding: $encoding);
	return $document;
}
