<?php
/*
	fm_search.php

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

use common\session;

use function gtext;

trait fm_search {
	use fm_extra;
	use fm_header;

//	find items
	public function find_item($dir,$regex,&$list,$recur) {
		$abs_dir = $this->get_abs_dir($dir);
//		exclude folders from scan
		if(in_array($abs_dir,['/var/run/samba4/fd'])):
			return;
		endif;
		$handle = @opendir($abs_dir);
//		open dir successful?
		if($handle !== false):
			while(($new_item = readdir($handle)) !== false):
				if(@file_exists($this->get_abs_item($dir,$new_item)) && $this->get_show_item($dir,$new_item)):
//				match?
					if(preg_match($regex,$new_item) === 1):
						$list[] = [$dir,$new_item];
					endif;
//					search sub-directories
					if($this->get_is_dir($dir,$new_item) && $recur):
						$this->find_item($this->get_rel_item($dir,$new_item),$regex,$list,$recur);
					endif;
				endif;
			endwhile;
			closedir($handle);
		endif;
	}
//	make list of found items
	public function find_make_list($dir,$item,$subdir) {
//		convert shell-wildcards to PCRE Regex Syntax
		$matches = preg_split('/([\?\*])/',$item,-1,PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
		if(is_array($matches) && count($matches) > 0):
			$converted_matches = [];
			foreach($matches as $single_match):
				switch($single_match):
					case '?':
						$converted_matches[] = '.';
						break;
					case '*':
						$converted_matches[] = '.*';
						break;
					default:
						$converted_matches[] = preg_quote($single_match,'/');
						break;
				endswitch;
			endforeach;
			$regex = '/^' . implode('',$converted_matches) . '$/i';
		else:
			$regex = '/.*/';
		endif;
//		search
		$this->find_item($dir,$regex,$list,$subdir);
		if(is_array($list)):
			sort($list);
		endif;
		return $list;
	}
//	print table of found items
	public function find_print_table($list) {
		if(is_array($list)):
			$cnt = count($list);
			for($i = 0;$i < $cnt;++$i):
				$dir = $list[$i][0];
				$item = $list[$i][1];
				$link = '';
				$target = '';
				if($this->get_is_dir($dir,$item)):
					$img = 'dir.gif';
					$link = $this->make_link('list',$this->get_rel_item($dir,$item),null);
				else:
					$img = $this->get_mime_type($dir,$item,'img');
					$link = $this->make_link('download',$dir,$item);
				endif;
				echo '<tr>',
						'<td class="lcell" style="white-space: nowrap">',
							'<img border="0" width="16" height="16" align="ABSMIDDLE" src="/images/fm_img/' . $img . '" alt="">',
							'&nbsp;',
							'<a href="',$link,'" target="',$target,'">',htmlspecialchars($item),'</a>';
				echo	'</td>';
				echo	'<td class="lcebl" style="white-space: nowrap">',
							'<a href="',$this->make_link('list',$dir,null),'"> /',htmlspecialchars($dir),'</a>',
						'</td>';
				echo '</tr>',"\n";
			endfor;
		endif;
	}
//	search for item
	public function search_items($dir) {
		if(isset($_POST['searchitem'])):
			$searchitem = $_POST['searchitem'];
			$subdir = (isset($_POST['subdir']) && $_POST['subdir'] == 'y');
			$list = $this->find_make_list($dir,$searchitem,$subdir);
		else:
			$searchitem = null;
			$subdir = true;
		endif;
		$this->show_header(gtext('Search Directory') . ': ' . $this->_breadcrumbs_search($dir));
		echo '<div class="area_data_pot">',"\n";
//		search box
		echo '<form name="searchform" action="',$this->make_link('search',$dir,null),'" method="post">',"\n";
		echo	'<div id="formextension">',"\n",'<input name="authtoken" type="hidden" value="',session::get_authtoken(),'">',"\n",'</div>',"\n";
		echo	'<table class="area_data_selection">',
					'<colgroup>',
						'<col style="width:100%">',
					'</colgroup>',
					'<thead>',
						'<tr>',
							'<th class="lhebl">',
								'<input type="text" class="formfld" name="searchitem" size="25" value="',htmlspecialchars($searchitem ?? ''),'" placeholder="',gtext('Search Filter'),'">',
								'<input type="checkbox" name="subdir" value="y"',($subdir ? ' checked' : ''),'>',
								'<span style="font-weight:normal">',gtext('Search subfolder'),'&nbsp;</span>',
								'<input type="submit" value="',gtext('Search'),'">','&nbsp;',
								'<input type="button" value="',gtext('Close'),'" onClick="javascript:location=\'',$this->make_link('list',$dir,null),'\';">',
							'</th>',
						'</tr>',
					'</thead>',
				'</table>',"\n";
		echo '</form>',"\n";
		echo '</div>',"\n";
//		search result
		echo '<div id="area_data_frame">',"\n";
		if($searchitem !== null):
			$lhetop_string = sprintf(gettext('Search Result for %1$s'),$searchitem);
			echo '<table class="area_data_selection">',"\n";
			echo	'<colgroup>',
						'<col style="width:42%">',
						'<col style="width:58%">',
					'</colgroup>',"\n";
			echo	'<thead>',"\n";
			echo		'<tr>',
							'<th class="lhetop" colspan="2">', htmlspecialchars($lhetop_string),'</th>',
						'</tr>',"\n";
			echo		'<tr>',
							'<th class="lhell"><b>',gtext('Name'),'</b></td>',
							'<th class="lhebl"><b>',gtext('Path'),'</b></td>',
						'</tr>',"\n";
			echo	'</thead>',"\n";
//			make & print table of found items
			if(is_countable($list) && count($list) > 0):
				echo '<tbody>',"\n";
				$this->find_print_table($list);
				echo '</tbody>',"\n";
			endif;
			echo	'<tfoot>',"\n";
			echo		'<tr>';
			echo			'<td class="lcebl" colspan="2">';
			if(is_countable($list) && count($list) > 0):
				echo		count($list),' ',gtext('Item(s)'),'.';
			else:
				echo		gtext('No results available.');
			endif;
			echo			'</td>';
			echo		'</tr>',"\n";
			echo	'</tfoot>',"\n";
			echo '</table>',"\n";
		endif;
		echo '<script>',"\n";
		echo '//<![CDATA[',"\n";
		echo '	if(document.searchform) document.searchform.searchitem.focus();',"\n";
		echo '//]]>',"\n";
		echo '</script>',"\n";
		echo '</div>',"\n";
	}
/**
 *	The breadcrumbs function will take the user's current path and build a breadcrumb.
 *  Typical syntax:
 *		echo breadcrumbs($dir, ">>");
 *		show_header(gtext('Directory').":".breadcrumbs($dir));
 *	@param string $curdir users current directory.
 *	@param string $displayseparator (optional) string that will be displayed betweenach crumb.
 *	@return string
 */
	public function _breadcrumbs_search($curdir,$displayseparator = ' &raquo; ') {
//		get localized name for the home directory
		$homedir = gtext('HOME');
//		initialize first crumb and set it to the home directory.
		$breadcrumbs[] = sprintf('<a href="%s">%s</a>',$this->make_link('search','',null),$homedir);
//		take the current directory and split the string into an array at each '/'.
		$patharray = explode('/',$curdir);
//		find out the index for the last value in our path array
		$lastx = array_keys($patharray);
		$last = end($lastx);
//		build the rest of the breadcrumbs
		$crumbdir = '';
		foreach($patharray AS $x => $crumb):
//			add a new directory to the directory list so the link has the correct path to the current crumb.
			$crumbdir = $crumbdir . $crumb;
			if($x != $last):
//				if we are not on the last index, then create a link using $crumb as the text.
				$breadcrumbs[] = sprintf('<a href="%s">%s</a>',$this->make_link('search',$crumbdir,null),htmlspecialchars($crumb));
//				add a separator between our crumbs.
				$crumbdir = $crumbdir . DIRECTORY_SEPARATOR;
			else:
//				don't create a link for the final crumb, just display the crumb name.
				$breadcrumbs[] = htmlspecialchars($crumb);
			endif;
		endforeach;
//		build temporary array into one string.
		return implode($displayseparator,$breadcrumbs);
	}
}
