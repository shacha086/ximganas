<?php
/*
	fm_archive.php

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

use zipstream\zipstream;

trait fm_archive {
	use fm_debug;
	use fm_error;

	public function zip_download($directory,$items) {
		$no_errors = true;
		$zipfile = new zipstream('downloads.zip');
		foreach($items as $item):
			$no_errors &= $this->_zipstream_add_file($zipfile,$directory,$item);
		endforeach;
		$zipfile->finish();
		exit;
		return $no_errors;
	}
	protected function _zipstream_add_file(zipstream $zipfile,$directory,$file_to_add) {
		$no_errors = true;
		$filename = $directory . DIRECTORY_SEPARATOR . $file_to_add;
		if(!@file_exists($filename)):
//			$this->show_error($filename . ' does not exist');
			$no_errors &= false;
		elseif(is_file($filename)):
			$this->_debug("adding file $filename");
			$zipfile->add_file($file_to_add,file_get_contents($filename));
		elseif(is_dir($filename)):
			$this->_debug("adding directory $filename");
			$files = glob($filename . DIRECTORY_SEPARATOR . '*');
			foreach($files as $file):
				$file = str_replace($directory . DIRECTORY_SEPARATOR,'',$file);
				$no_errors &= $this->_zipstream_add_file($zipfile,$directory,$file);
			endforeach;
		else:
			$no_errors &= false;
//			$this->_error("don't know how to handle $file_to_add");
		endif;
		return $no_errors;
	}
}
