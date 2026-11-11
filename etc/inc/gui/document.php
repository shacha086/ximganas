<?php
/*
	document.php

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

use DOMDocument;

class document extends DOMDocument {
	use tools;

	protected $hook_stack = [];
	protected $stack = [];
	protected $options = [];
	protected $js_on_load = [];
	protected $js_document_ready = [];

	public function __construct(string $version = '1.0',string $encoding = 'UTF-8') {
		parent::__construct(version: $version,encoding: $encoding);
		$this->preserveWhiteSpace = false;
		$this->formatOutput = true;
		$this->registerNodeClass(baseClass: 'DOMElement',extendedClass: __NAMESPACE__ . '\element');
	}
	public function set_options(string ...$options) {
		foreach($options as $value):
			$this->options[$value] = $value;
		endforeach;
		return $this;
	}
	public function option_exists(string $option) {
		return array_key_exists(key: $option,array: $this->options);
	}
	public function push($element) {
		array_push($this->stack,$element);
		return $element;
	}
	public function pop() {
		return array_pop(array: $this->stack);
	}
	public function last() {
		return $this->stack[array_key_last(array: $this->stack)];
	}
	public function reset_hooks() {
		$this->hook_stack = [];
	}
	public function add_hook($dom_element,string $identifier) {
		$this->hook_stack[$identifier] = $dom_element;
		return $this;
	}
	public function get_hooks() {
		return $this->hook_stack;
	}
	public function add_js_on_load(string $jcode = '',?string $key = null) {
		$preg_match_result = preg_match(pattern: '/\S/',subject: $jcode);
		if($preg_match_result === 1):
			if(isset($key)):
				$this->js_on_load[$key] = $jcode;
			else:
				$this->js_on_load[] = $jcode;
			endif;
		endif;
		return $this;
	}
	public function add_js_document_ready(string $jcode = '',?string $key = null) {
		$preg_match_result= preg_match(pattern: '/\S/',subject: $jcode);
		if($preg_match_result === 1):
			if(isset($key)):
				$this->js_document_ready[$key] = $jcode;
			else:
				$this->js_document_ready[] = $jcode;
			endif;
		endif;
		return $this;
	}
	protected function clc_javascript() {
		$body = $this->getElementById(elementId: 'main');
		if(isset($body)):
			if(!empty($this->js_on_load)):
				$jdata = implode(separator: "\n",array: ['$(window).on("load", function() {',implode(separator: "\n",array: $this->js_on_load),'});']);
				$body->ins_javascript(text: $jdata);
			endif;
			$jdata = implode(separator: "\n",array: $this->js_document_ready);
			$body->ins_javascript(text: $jdata);
		endif;
		return $this;
	}
	public function render() {
		$this->clc_javascript();
		echo $this->saveHTML();
		return $this;
	}
	public function get_html() {
		$this->clc_javascript();
		return $this->saveHTML();
	}
}
