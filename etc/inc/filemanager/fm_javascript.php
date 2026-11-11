<?php
/*
	fm_javascript.php

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

namespace filemanager;

use function unicode_escape_javascript;

trait fm_javascript {
	public function js_show() {
?>
<script>
//<![CDATA[
//	Checkboxes
	function Toggle(e) {
		if(e.checked) {
			document.selform.toggleAllC.checked = AllChecked();
		} else {
			document.selform.toggleAllC.checked = false;
		}
	}
	function ToggleAll(e) {
		if(e.checked) CheckAll();
		else ClearAll();
	}
	function CheckAll() {
		var ml = document.selform;
		var len = ml.elements.length;
		for(var i=0; i<len; ++i) {
			var e = ml.elements[i];
			if(e.name == "selitems[]") {
				e.checked = true;
			}
		}
		ml.toggleAllC.checked = true;
	}
	function ClearAll() {
		var ml = document.selform;
		var len = ml.elements.length;
		for (var i=0; i<len; ++i) {
			var e = ml.elements[i];
			if(e.name == "selitems[]") {
				e.checked = false;
			}
		}
		ml.toggleAllC.checked = false;
	}
	function AllChecked() {
		ml = document.selform;
		len = ml.elements.length;
		for(var i=0; i<len; ++i) {
			if(ml.elements[i].name == "selitems[]" && !ml.elements[i].checked) return false;
		}
		return true;
	}
	function NumChecked() {
		ml = document.selform;
		len = ml.elements.length;
		num = 0;
		for(var i=0; i<len; ++i) {
			if(ml.elements[i].name == "selitems[]" && ml.elements[i].checked) ++num;
		}
		return num;
	}
//	Copy / Move / Delete
	function Copy() {
		if(NumChecked()==0) {
			alert(<?= unicode_escape_javascript(gettext("You haven't selected any item(s).")); ?>);
			return;
		}
		document.selform.do_action.value = "copy";
		document.selform.submit();
	}
	function Move() {
		if(NumChecked()==0) {
			alert(<?= unicode_escape_javascript(gettext("You haven't selected any item(s).")); ?>);
			return;
		}
		document.selform.do_action.value = "move";
		document.selform.submit();
	}
	function Delete() {
		num = NumChecked();
		if(num == 0) {
			alert(<?= unicode_escape_javascript(gettext("You haven't selected any item(s).")); ?>);
			return;
		}
		if(confirm(<?= unicode_escape_javascript(gettext('Are you sure you want to delete selected item(s)?')); ?>)) {
			document.selform.do_action.value = "delete";
			document.selform.submit();
		}
	}
	function DownloadSelected() {
		if(NumChecked() == 0) {
			alert(<?= unicode_escape_javascript(gettext("You haven't selected any item(s).")); ?>);
			return;
		}
		document.selform.do_action.value = "download_selected";
		document.selform.submit();
	}
	function Unzip() {
		if(NumChecked() == 0) {
			alert(<?= unicode_escape_javascript(gettext("You haven't selected any item(s).")); ?>);
			return;
		}
		document.selform.do_action.value = "unzip";
		document.selform.submit();
	}
//]]>
</script>
<?php
	}
}
