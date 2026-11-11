<?php
/*
	fm_down.php

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

trait fm_down {
	use fm_archive;
	use fm_debug;
	use fm_error;
	use fm_extra;
	use fm_permissions;
	use fm_qxpage;

/**
 *	download_selected
 *	@param string $dir
 **/
	public function download_selected($dir) {
		$items = $this->qxpage_selected_items();
		$this->_download_items($dir,$items);
	}
/**
 *	download file
 *	@param string $dir
 *	@param string $item
 */
	public function download_item($dir,$item) {
		$this->_download_items($dir,[$item]);
	}
/**
 *	_download_items
 *	@param string $dir
 *	@param array $items
 */
	public function _download_items($dir,$items) {
//		check if user selected any items to download
		$this->_debug("count items: '$items[0]'");
		if(count($items) == 0):
			$this->show_error(gtext("You haven't selected any item(s)."));
		endif;
//		check if user has permissions to download this file
		if(!$this->_is_download_allowed($dir,$items)):
			$this->show_error(gtext('You are not allowed to access this item.'));
		endif;
//		if we have exactly one file and this is a real file, we directly download
		if((count($items) === 1) && $this->get_is_file($dir,$items[0])):
			$abs_item = $this->get_abs_item($dir,$items[0]);
			$this->_download($abs_item,$items[0]);
//		otherwise we do the zip download
		else:
			$this->zip_download($this->get_abs_dir($dir),$items);
		endif;
	}
/**
 *	Downloads a file with X-Sendfile
 *	@param string $file Full path name + file name + extension
 *	@param string $localname File name + extension
 */
	public function _download(string $file,string $localname) {
		header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
		header(sprintf('Content-Disposition: attachment; filename="%s"',$localname));
		header('Content-Type: application/octet-stream');
		header('Content-Length: ' . filesize($file));
		header(sprintf('X-Sendfile: %s',$file));
		exit;
	}
/**
 *	_is_download_allowed
 *	@param string $dir
 *	@param array $items
 *	@return boolean
 */
	public function _is_download_allowed($dir,$items) {
		foreach($items as $file):
			if(!$this->permissions_grant(dir: $dir,file: $file,action: 'read')):
				return false;
			endif;
			if(!$this->get_show_item($dir,$file)):
				return false;
			endif;
			if(!file_exists($this->get_abs_item($dir,$file))):
				return false;
			endif;
		endforeach;
		return true;
	}
}
