<?php
/*
	fm_mkitem.php

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

use function gtext;

trait fm_mkitem {
	use fm_error;
	use fm_extra;
	use fm_permissions;

/**
 *  make new directory or file
 *	@param string $dir
 */
	public function make_item($dir) {
		if(!$this->permissions_grant(dir: $dir,action: 'create')):
			$this->show_error(gtext('You are not allowed to use this function.'));
		endif;
		$mkname = $_POST['mkname'];
		$mktype = $_POST['mktype'];
		$mkname = basename($mkname);
		if($mkname == ''):
			$this->show_error(gtext('You must supply a name.'));
		endif;
		$new = $this->get_abs_item($dir,$mkname);
		if(@file_exists($new)):
			$this->show_error(htmlspecialchars($mkname) . ': ' . gtext('This item already exists.'));
		endif;
		if($mktype != 'file'):
			$ok = @mkdir($new,0777);
			$err = gtext('Directory creation failed.');
		else:
			$ok = @touch($new);
			$err = gtext('File creation failed.');
		endif;
		if($ok === false):
			$this->show_error($err);
		endif;
		header('Location: ' . $this->make_link('list',$dir,null));
	}
}
