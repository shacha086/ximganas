<?php
/*
	filemanager.php

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

class filemanager {
	use fm_chmod;
	use fm_copy_move;
	use fm_del;
	use fm_down;
	use fm_edit_editarea;
	use fm_error;
	use fm_footer;
	use fm_init;
	use fm_list;
	use fm_mkitem;
	use fm_qx;
	use fm_search;
	use fm_unzip;

	protected string $language;
	protected string $script_name;
	protected string $home_dir;
	protected string $home_url;
	protected bool $show_hidden = false;
	protected bool $no_access_pattern;
	protected string $site_name;
	protected string $img_root;
	protected array $baricons;

	protected string $action;
	protected string $dir;
	protected string $item;
	protected string $order;
	protected string $srt;

	protected string $editable_ext;
	protected string $unzipable_ext;
	protected array $super_mimes;
	protected array $used_mime_types;

	function __construct() {
		global $config;

//		Language
		$this->language = $_SESSION['userlang'] ?? $config['system']['language'];
//		The filename of the QuiXplorer script:
		$this->script_name = sprintf('%s://%s%s',$config['system']['webgui']['protocol'],$_SERVER['HTTP_HOST'],$_SERVER['PHP_SELF']);
//		the home directory for the filemanager:
//		use forward slashed to seperate directories ('/')
//		not '\' or '\\', no trailing '/'
//		don't use the root directory as home_dir!
		$this->home_dir = '/';
//		the url corresponding with the home directory: (no trailing '/')
		$this->home_url = sprintf('%s://%s',$config['system']['webgui']['protocol'],$_SERVER['HTTP_HOST']);
//		show hidden files in QuiXplorer: (hide files starting with '.', as in Linux/UNIX)
		$this->show_hidden = true;
//		filenames not allowed to access: (uses PCRE regex syntax)
		$this->no_access_pattern = '^\.ht';
//		The title which is displayed in the browser
		$this->site_name = 'My Download Server';
//		root folder for images
		$this->img_root = '/images/fm_img/';
//		icons for toolbar
		$this->baricons = [
			'up'			=> $this->img_root . 'smallicons/navigation-090-frame.png',
			'home'			=> $this->img_root . 'smallicons/house.png',
			'reload'		=> $this->img_root . 'smallicons/arrow_refresh_small.png',
			'search'		=> $this->img_root . 'smallicons/magnifier-left.png',
			'copy'			=> $this->img_root . 'smallicons/page_copy.png',
			'notcopy'		=> $this->img_root . '_copy_.gif',
			'move'			=> $this->img_root . 'smallicons/page_go.png',
			'notmove'		=> $this->img_root . '_move_.gif',
			'delete'		=> $this->img_root . 'smallicons/cross-script.png',
			'notdelete'		=> $this->img_root . '_delete_.gif',
			'add'			=> $this->img_root . 'smallicons/add.png',
			'edit'			=> $this->img_root . 'smallicons/blue-document-code.png',
			'notedit'		=> $this->img_root . '_edit_.gif',
			'unzip'			=> $this->img_root . 'smallicons/package_go.png',
			'notunzip'		=> $this->img_root . '_.gif',
			'download'		=> $this->img_root . 'smallicons/drive-download.png',
			'notdownload'	=> $this->img_root . '_download_.gif',
			'unzipto'		=> $this->img_root . 'smallicons/folder.png',
			'zip'			=> $this->img_root . 'smallicons/icon_zip.gif',
			'none'			=> $this->img_root . '_.gif',
		];
//		editable files:
		$this->editable_ext = '/\.(txt|php|php3|phtml|inc|ini|sql|pl|htm|html|shtml|dhtml|xml|js|css|cgi|cpp|c|cc|cxx|hpp|h|pas|p|java|py|sh|tcl|tk|dxs|uni|htaccess)$/i';
//		----------------------------------------------------------------------------
//		unzipable files:
		$this->unzipable_ext = '/\.(zip|gz|tar|bz2|tgz)$/i';
//		----------------------------------------------------------------------------
//		mime types: (description,image,extension,type)
		$this->super_mimes = [
//			dir, exe, file
			'dir' => [gtext('Directory'),'filetypes/folder_2.png','','dir'],
			'exe' => [gtext('Executable File'),'exe.gif','/\.(exe|com|bin)$/i','','exe'],
			'file' => [gtext('File'),'filetypes/icon_generic.gif','','file'],
			'link' => [gtext('Link'),'filetypes/icon_generic.gif','','link']
		];
		$this->used_mime_types = [
//			text
			'text' => [gtext('Text File'),'filetypes/document-text.png','/\.(txt|htaccess|plexignore)$/i','text'],
//			programming
			'php' => [gtext('PHP Script'),'filetypes/page_white_php.png','/\.(php|php3|phtml|inc|dxs|uni)$/i','php'],
			'sql' => [gtext('SQL File'),'src.gif','/\.(sql)$/i','sql'],
			'perl' => [gtext('PERL Script'),'pl.gif','/\.(pl)$/i','pl'],
			'html' => [gtext('HTML Page'),'html.gif','/\.(htm|html|shtml|dhtml)$/i','html'],
			'xml' => [gtext('XML File'),'filetypes/icon_xml.gif','/\.(xml)$/i','xml'],
			'js' => [gtext('Javascript File'),'filetypes/icon_js.gif','/\.(js)$/i','js'],
			'css' => [gtext('CSS File'),'src.gif','/\.(css)$/i','css'],
			'cgi' => [gtext('CGI Script'),'exe.gif','/\.(cgi)$/i','cgi'],
			'py' => [gtext('Python Script'),'filetypes/document-text.png','/\.(py)$/i','py'],
			'sh' => [gtext('Shell Script'),'filetypes/document-text.png','/\.(sh)$/i','sh'],
//			C++
			'c' => [gtext('C File'),'filetypes/page_white_c.png','/\.(c)$/i','c'],
			'cpps' => [gtext('C++ Source File'),'filetypes/page_white_cplusplus.png','/\.(cpp|cc|cxx)$/i','cpp'],
			'cpph' => [gtext('C++ Header File'),'h.gif','/\.(hpp|h)$/i','cpp'],
//			Java
			'javas' => [gtext('Java Source File'),'java.gif','/\.(java)$/i','java'],
			'javac' => [gtext('Java Class File'),'java.gif','/\.(class|jar)$/i','java'],
//			Pascal
			'pas' => [gtext('Pascal File'),'src.gif','/\.(p|pas)$/i','pas'],
//			images
			'gif' => [gtext('GIF Image'),'filetypes/picture_2.png','/\.(gif)$/i','gif'],
			'jpg' => [gtext('JPG Image'),'filetypes/picture_2.png','/\.(jpg|jpeg)$/i','jpg'],
			'bmp' => [gtext('BMP Image'),'filetypes/picture_2.png','/\.(bmp)$/i','bmp'],
			'png' => [gtext('PNG Image'),'filetypes/picture_2.png','/\.(png)$/i','png'],
			'tif' => [gtext('TIF Image'),'filetypes/picture_2.png','/\.(tif|tiff)$/i','tif'],
//			PSD
			'psd' => [gtext('Photoshop File'),'filetypes/icon_photoshop.gif','/\.(psd)$/i','psd'],
//			compressed
			'zip' => [gtext('ZIP Archive'),'filetypes/compress.png','/\.(zip)$/i','zip'],
			'tar' => [gtext('TAR Archive'),'tar.gif','/\.(tar)$/i','tar'],
			'gzip' => [gtext('GZIP Archive'),'tgz.gif','/\.(tgz|gz)$/i','gzip'],
			'bzip2' => [gtext('BZIP2 Archive'),'tgz.gif','/\.(bz2)$/i','bzip2'],
			'rar' => [gtext('RAR Archive'),'tgz.gif','/\.(rar)$/i','rar'],
//			'deb' => [gtext('Debian Package File'),'package.gif','/\.(deb)$/i','deb'],
//			'rpm' => [gtext('Redhat Package File'),'package.gif','/\.(rpm)$/i','rpm'],
//			music
			'mp3' => [gtext('MP3 Audio File'),'filetypes/music.png','/\.(mp3)$/i','mp3'],
			'flac' => [gtext('FLAC Audio File'),'flac.gif','/\.(flac)$/i','flac'],
			'wav' => [gtext('WAV Audio File'),'sound.gif','/\.(wav)$/i','wav'],
			'midi' => [gtext('MIDI File'),'midi.gif','/\.(mid)$/i','mid'],
			'real' => [gtext('RealAudio File'),'real.gif','/\.(rm|ra|ram)$/i','real'],
			'dts' => [gtext('DTS Audio File'),'sound.gif','/\.(dts)$/i','dts'],
			'ac3' => [gtext('AC3 Audio File'),'sound.gif','/\.(ac3)$/i','ac3'],
			'ogg' => [gtext('OGG Audio File'),'sound.gif','/\.(ogg)$/i','ogg'],
			'play' => [gtext('MP3 Playlist'),'mp3.gif','/\.(pls|m3u)$/i','m3u'],
//			movie
			'avi' => [gtext('AVI File'),'filetypes/film_2.png','/\.(avi)$/i','avi'],
			'mpg' => [gtext('MPEG File'),'video.gif','/\.(mpg|mpeg)$/i','mpeg'],
			'mov' => [gtext('MOV File'),'video.gif','/\.(mov)$/i','mov'],
			'mkv' => [gtext('MKV File'),'mkv.gif','/\.(mkv)$/i','mkv'],
			'vob' => [gtext('VOB File'),'vob.gif','/\.(vob)$/i','vob'],
			'flash' => [gtext('Flash File'),'flash.gif','/\.(swf)$/i','swf'],
			'mp4' => [gtext('MP4 File'),'filetypes/film_1.png','/\.(mp4)$/i','mp4'],
			'ts' => [gtext('TS File'),'filetypes/ts.png','/\.(ts)$/i','ts'],
//			Micosoft / Adobe
			'word' => [gtext('Word Document'),'filetypes/page_white_word_1.png','/\.(doc|docx)$/i','doc'],
			'excel' => [gtext('Excel Sheet'),'filetypes/page_white_excel_1.png','/\.(xls|xlsx)$/i','xls'],
			'power' => [gtext('Powerpoint Presentation'),'filetypes/page_white_powerpoint.png','/\.(ppt|pptx|pps)$/i','ppt'],
			'pdf' => [gtext('PDF File'),'filetypes/document-pdf.png','/\.(pdf)$/i','pdf']
		];
	}
	public function runner() {
		$this->init();
		$current_dir = $this->qx_request('dir','');
		switch($this->action):
			case 'edit':
//				edit file
				$this->edit_file($current_dir,$this->item);
				break;
			case 'delete':
//				delete items
				$this->del_items($current_dir);
				break;
			case 'copy':
//				copy items
			case 'move':
//				move items
				$this->copy_move_items($current_dir);
				break;
			case 'download':
//				download item
				if($this->item == ''):
					$this->show_error(gtext("You haven't selected any item(s)."));
				endif;
				$this->download_item($current_dir,$this->item);
				exit;
				break;
			case 'download_selected':
//				download selected items
				$this->download_selected($current_dir);
				exit;
				break;
			case 'unzip':
//				unzip item
				$this->unzip_item($current_dir);
				break;
			case 'mkitem':
//				create item
				$this->make_item($current_dir);
				break;
			case 'chmod':
//				change item permission
				$this->chmod_item($current_dir,$this->item);
				break;
			case 'search':
//				search for items
				$this->search_items($current_dir);
				break;
			default:
				$this->list_dir($current_dir);
				break;
		endswitch;
		$this->show_footer();
	}
}
