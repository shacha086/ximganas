<?php
/*
	fm_extra.php

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

trait fm_extra {
	use fm_debug;
	use fm_error;
	use fm_qxpath;
	use fm_session;
	use fm_str;

/**
 *	make link to next page
 *	@param string|null $_action
 *	@param string|null $_dir
 *	@param string|null $_item
 *	@param string|null $_order
 *	@param string|null $_srt
 *	@param string|null $_lang
 *	@return string
 */
	public function make_link(?string $_action = null,?string $_dir = null,?string $_item = null,?string $_order = null,?string $_srt = null,?string $_lang = null) {
		$a_query = [];
		if($_action == '' || $_action == null):
			$_action = 'list';
		endif;
		$a_query['action'] = $_action;
		if($_dir == ''):
			$_dir = null;
		endif;
		$a_query['dir'] = $_dir;
		if($_item == ''):
			$_item = null;
		endif;
		$a_query['item'] = $_item;
		$a_query['order'] = $_order ?? $this->order ?? null;
		$a_query['srt'] = $_srt ?? $this->srt ?? null;
		$a_query['lang'] = $_lang ?? $this->language ?? null;
		$link = sprintf('%s?%s',$this->script_name,http_build_query($a_query,'','&',PHP_QUERY_RFC3986));
		return $link;
	}
	public function get_abs_dir($path) {
		return $this->path_f($path);
	}
//	get absolute file+path
	public function get_abs_item($dir,$item) {
		return $this->get_abs_dir($dir) . DIRECTORY_SEPARATOR . $item;
	}
/**
	get file relative from home
 */
	public function get_rel_item($dir,$item) {
		return $dir == '' ? $item : "$dir/$item";
	}
/**
	can this file be edited?
  */
	public function get_is_file($dir,$item) {
		$filename = $this->get_abs_item($dir,$item);
		return @is_file($filename);
	}
//	is this a directory?
	public function get_is_dir($dir,$item) {
		return @is_dir($this->get_abs_item($dir,$item));
	}
//	parsed file type (d / l / -)
	public function parse_file_type($dir,$item) {
		$abs_item = $this->get_abs_item($dir,$item);
		if(@is_dir($abs_item)):
			return 'd';
		endif;
		if(@is_link($abs_item)):
			return 'l';
		endif;
		return '-';
	}
//	file permissions
	public function get_file_perms($dir,$item) {
		return @decoct(@fileperms($this->get_abs_item($dir,$item)) & 0777);
	}
/**
 *	convert file permissions into human readable file permissions
 *	@param int $mode
 *	@return string
 */
	public function parse_file_perms($mode) {
		if(strlen($mode) < 3):
			return '---------';
		endif;
		$parsed_mode = '';
		for($i = 0;$i < 3;$i++):
//			read
			if(($mode[$i] & 04)):
				$parsed_mode .= 'r';
			else:
				$parsed_mode .= '-';
			endif;
//			write
			if(($mode[$i] & 02)):
				$parsed_mode .= 'w';
			else:
				$parsed_mode .= '-';
			endif;
//			execute
			if(($mode[$i] & 01)):
				$parsed_mode .= 'x';
			else:
				$parsed_mode .= '-';
			endif;
		endfor;
		return $parsed_mode;
	}
/**
 *	file size
 *	@param string $dir
 *	@param string $item
 *	@return int
 */
	public function get_file_size($dir,$item) {
		return @filesize($this->get_abs_item($dir,$item));
	}
/**
 *	file date
 *  @param string $dir
 *  @param string $item
 *  @return int
 */
	public function get_file_date($dir,$item) {
		return @filemtime($this->get_abs_item($dir,$item));
	}
/**
 *	is this file editable?
 *	@param string $dir
 *	@param string $item
 *	@return boolean
 */
	public function get_is_editable($dir,$item) {
			if($this->get_is_file($dir,$item)):
			return(preg_match($this->editable_ext,$item) === 1);
		endif;
		return false;
	}
/**
 *	is this file unzipable?
 *	@param string $dir
 *	@param string $item
 *	@return boolean
 */
	public function get_is_unzipable($dir,$item) {
		if($this->get_is_file($dir,$item)):
			return(preg_match($this->unzipable_ext,$item) === 1);
		endif;
		return false;
	}
/**
 *	Provides mime information about a filesystem object
 *	@param string $item
 *	@return array
 */
	protected function _get_used_mime_info($item) {
		foreach($this->used_mime_types as $mime):
			[$desc,$img,$regex_ext,$type] = $mime;
			if(preg_match($regex_ext,$item) === 1):
				return [$mime,$img,$type];
			endif;
		endforeach;
		return [null,null,null];
	}
/**
 *	Determine the mime type of a filesystem object
 *	@param string $dir
 *	@param string $item
 *	@param string $query
 *	@return mixed
 */
	public function get_mime_type($dir,$item,$query) {
		$fqfn = $this->get_abs_item($dir,$item);
		switch(@filetype($fqfn)):
			case false:
//				error filetype
				$this->_debug(sprintf('error calling filetype for file [%s]',$fqfn));
				$mime_type = $this->super_mimes['file'][0];
				$image = $this->super_mimes['file'][1];
				break;
			case 'dir':
				$mime_type = $this->super_mimes['dir'][0];
				$image = $this->super_mimes['dir'][1];
				break;
			case 'link':
				$mime_type = $this->super_mimes['link'][0];
				$image = $this->super_mimes['link'][1];
				break;
			default:
				[$mime_type,$image,$type] = $this->_get_used_mime_info($item);
				if($mime_type != null):
					$this->_debug("found mime type $mime_type[0]");
					break;
				endif;
				if(@is_executable($fqfn)):
					$mime_type = $this->super_mimes['exe'][0];
					$image = $this->super_mimes['exe'][1];
				elseif(preg_match($this->super_mimes['exe'][2],$item)):
					$mime_type = $this->super_mimes['exe'][0];
					$image = $this->super_mimes['exe'][1];
				else:
//					unknown file
					$this->_debug(sprintf('unknown file type for file [%s]',$fqfn));
					$mime_type = $this->super_mimes['file'][0];
					$image = $this->super_mimes['file'][1];
				endif;
				break;
		endswitch;
		switch($query):
			case 'img':
				$result = $image;
				break;
			case 'ext':
				$result = $type;
				break;
			default:
				$result = $mime_type;
				break;
		endswitch;
		return $result;
	}
/**
	Check if user is allowed to access $file in $directory
 */
	public function get_show_item($directory,$file = null) {
//		no relative paths are allowed in directories
		if(preg_match('/\.\./',$directory)):
			return false;
		endif;
		if(is_scalar($file)):
//			file name must not contain any path separators
			if(preg_match('/[\/\\\\]/',$file)):
				return false;
			endif;
//			do not display own and parent directory
			if($file == '.' || $file == '..' ):
				return false;
			endif;
//			determine full path to the file
			$full_path = $this->get_abs_item($directory,$file);
			$this->_debug("full_path: $full_path");
			if(!$this->str_startswith($full_path,$this->path_f())):
				return false;
			endif;
			if($this->matches_noaccess_pattern($file)):
				return false;
			endif;
//			check if user is allowed to access hidden files
			if(!$this->show_hidden):
				if($file[0] == '.'):
					return false;
				endif;
			endif;
		endif;
//		check if user is allowed to access hidden files
		if(!$this->show_hidden):
//			no part of the path may be hidden
			$directory_parts = explode('/',$directory);
			foreach($directory_parts as $directory_part):
				if($directory_part[0] == '.'):
					return false;
				endif;
			endforeach;
		endif;
		return true;
	}
/**
 *	copy dir
 *	@param string $src
 *	@param string $dst
 *	@return boolean
 */
	public function copy_dir($src,$dst) {
		$ok = true;
		if(!@mkdir($dst,0777)):
			$ok = false;
		else:
			$handle_dir = @opendir($src);
			if($handle_dir === false):
				$ok = false;
				$this->show_error($src . 'xx:' . basename($src) . 'xx : ' . gtext('Unable to open source directory.'));
			else:
				while(($file = readdir($handle_dir)) !== false):
					if(($file == '..') || ($file == '.')):
						continue;
					else:
						$new_src = $src . '/' . $file;
						$new_dst = $dst . '/' . $file;
						if(@is_dir($new_src)):
							$ok = $this->copy_dir($new_src,$new_dst);
						else:
							$handle_src = @fopen($new_src,'rb');
							if($handle_src !== false):
								$handle_dst = @fopen($new_dst,'w+b');
								if($handle_dst !== false):
									$ok = stream_copy_to_stream($handle_src,$handle_dst) !== false;
									fclose($handle_dst);
								else:
									$ok = false;
								endif;
								fclose($handle_src);
							else:
								$ok = false;
							endif;
						endif;
					endif;
				endwhile;
				closedir($handle_dir);
			endif;
		endif;
		return $ok;
	}
/**
	remove file / dir
 */
	public function remove($item) {
		$ok = true;
		if(@is_link($item) || @is_file($item)):
			$ok = @unlink($item);
		elseif(@is_dir($item)):
			$handle = @opendir($item);
			if($handle === false):
				$this->show_error($item . ':' . basename($item) . ': ' . gtext('Unable to open directory.'));
			endif;
			while(($file = readdir($handle)) !== false):
				if(($file == '..' || $file == '.')):
					continue;
				endif;
				$new_item = $item . '/' . $file;
				if(!@file_exists($new_item)):
					$this->show_error(basename($item) . ': ' . gtext('Unable to read directory.'));
				endif;
//				if(!$this->get_show_item($item,$new_item)) continue;
				if(@is_dir($new_item)):
					$ok = $this->remove($new_item);
				else:
					$ok = @unlink($new_item);
				endif;
			endwhile;
			closedir($handle);
			$ok = @rmdir($item);
		endif;
		return $ok;
	}
//	dir deeper than home?
	public function down_home($abs_dir) {
		$real_home = @realpath($this->home_dir);
		$real_dir = @realpath($abs_dir);

		if($real_home === false || $real_dir === false):
			if(preg_match('/\\.\\./i',$abs_dir)):
				return false;
			endif;
		elseif(strcmp($real_home,@substr($real_dir,0,strlen($real_home)))):
			return false;
		endif;
		return true;
	}
}
