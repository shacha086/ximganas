<?php
/*
	element.php

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

use DOMElement;

class element extends DOMElement {
	use tools;

	public function addAttributes($attributes = []) {
		foreach($attributes as $key => $value):
			$this->setAttribute(qualifiedName: (string)$key,value: (string)$value);
		endforeach;
		return $this;
	}
	public function option_exists(string $option) {
		return $this->ownerDocument->option_exists(option: $option);
	}
	public function push() {
		$this->ownerDocument->push(element: $this);
		return $this;
	}
	public function pop() {
		return $this->ownerDocument->pop();
	}
	public function last() {
		return $this->ownerDocument->last();
	}
	public function reset_hooks() {
		$this->ownerDocument->reset_hooks();
		return $this;
	}
	public function add_hook($dom_element,string $identifier) {
		$this->ownerDocument->add_hook(dom_element: $dom_element,identifier: $identifier);
		return $this;
	}
	public function get_hooks() {
		return $this->ownerDocument->get_hooks();
	}
	public function add_js_on_load(string $jcode = '',?string $key = null) {
		return $this->ownerDocument->add_js_on_load(jcode: $jcode,key: $key);
	}
	public function add_js_document_ready(string $jcode = '',?string $key = null) {
		return $this->ownerDocument->add_js_document_ready(jcode: $jcode,key: $key);
	}
}
