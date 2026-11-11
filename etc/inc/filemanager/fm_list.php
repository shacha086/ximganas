<?php
/*
	fm_list.php

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

use function format_bytes;
use function get_datetime_locale;
use function gtext;

trait fm_list {
	use fm_error;
	use fm_extra;
	use fm_javascript;
	use fm_permissions;

/**
 *	Make a list of files
 *	@param array $_list1
 *	@param array $_list2
 *	@return array
 */
	public function make_list($_list1,$_list2) {
		$list = [];
		if($this->srt == 'yes'):
			$list1 = $_list1;
			$list2 = $_list2;
		else:
			$list1 = $_list2;
			$list2 = $_list1;
		endif;
		if(is_array($list1)):
			foreach($list1 as $key => $val):
				$list[$key] = $val;
			endforeach;
		endif;
		if(is_array($list2)):
			foreach($list2 as $key => $val):
				$list[$key] = $val;
			endforeach;
		endif;
		return $list;
	}
/**
 *	make table of files in dir
 *	make tables & place results in reference-variables passed to function
 *	also 'return' total filesize & total number of items
 *	@param string $dir
 *	@param array $dir_list
 *	@param array $file_list
 *	@param int $tot_file_size
 *	@param int $num_items
 */
	public function make_tables($dir,&$dir_list,&$file_list,&$tot_file_size,&$num_items) {
		$tot_file_size = $num_items = 0;

//		Clear stat cache
		clearstatcache();
//		Open directory
		$handle = @opendir($this->get_abs_dir($dir));
		if($handle === false):
			$this->show_error($dir . ': ' . gtext('Unable to open directory.'));
		endif;
//		Read directory
		while(($new_item = readdir($handle)) !== false):
			$abs_new_item = $this->get_abs_item($dir,$new_item);
			if(!$this->get_show_item($dir,$new_item)):
				continue;
			endif;
			$new_file_size = is_link($abs_new_item) ? 0 : @filesize($abs_new_item);
			$tot_file_size += $new_file_size;
			$num_items++;
			if(is_dir($dir . DIRECTORY_SEPARATOR . $new_item)):
				switch($this->order):
					case 'mod':
						$dir_list[$new_item] = @filemtime($abs_new_item);
						break;
//					case 'size':
//					case 'type':
//					case 'name':
					default:
						$dir_list[$new_item] = $new_item;
						break;
				endswitch;
			else:
				switch($this->order):
					case 'mod':
						$file_list[$new_item] = @filemtime($abs_new_item);
						break;
					case 'size':
						$file_list[$new_item] = $new_file_size;
						break;
					case 'type':
						$file_list[$new_item] = $this->get_mime_type($dir,$new_item,'type');
						break;
//					case 'name':
					default:
						$file_list[$new_item] = $new_item;
						break;
				endswitch;
			endif;
		endwhile;
		closedir($handle);
//		sort directories
		if(is_array($dir_list)):
			switch($this->order):
				case 'mod':
					if($this->srt == 'yes'):
						arsort($dir_list);
					else:
						asort($dir_list);
					endif;
					break;
				case 'size':
				case 'type':
					if($this->srt == 'yes'):
						ksort($dir_list);
					else:
						krsort($dir_list);
					endif;
					break;
//				case 'name':
				default:
					if($this->srt == 'yes'):
						ksort($dir_list,SORT_NATURAL | SORT_FLAG_CASE);
					else:
						krsort($dir_list,SORT_NATURAL | SORT_FLAG_CASE);
					endif;
					break;
			endswitch;
		endif;
//		sort files
		if(is_array($file_list)):
			switch($this->order):
				case 'mod':
					if($this->srt == 'yes'):
						arsort($file_list);
					else:
						asort($file_list);
					endif;
					break;
				case 'size':
				case 'type':
					if($this->srt == 'yes'):
						asort($file_list);
					else:
						arsort($file_list);
					endif;
					break;
//				case 'name':
				default:
					if($this->srt == 'yes'):
						ksort($file_list,SORT_NATURAL | SORT_FLAG_CASE);
					else:
						krsort($file_list,SORT_NATURAL | SORT_FLAG_CASE);
					endif;
			endswitch;
		endif;
	}
/**
  print table of files
 */
	public function print_table($dir,$list) {
		if(!is_array($list)):
			return;
		endif;
		foreach($list as $item => $value):
//			link to dir / file
			$abs_item = $this->get_abs_item($dir,$item);
			echo '<tr>';
			echo '<td class="lcelc"><input type="checkbox" name="selitems[]" value="',htmlspecialchars($item),'" onclick="javascript:Toggle(this);"></td>',"\n";
//			icon + link
			echo '<td class="lcell" style="white-space: nowrap">';
			if($this->permissions_grant(dir: $dir,file: $item,action: 'read')):
				if(is_dir($abs_item)):
					$link = $this->make_link('list',$this->get_rel_item($dir,$item),null);
				else:
					$link = $this->make_link('download',$dir,$item);
				endif;
				echo '<a href="',$link,'"><div>';
			endif;
			echo '<img style="vertical-align:middle" width="16" height="16" src="/images/fm_img/',$this->get_mime_type($dir,$item,'img'),'" alt="">&nbsp;';
			$s_item = $item;
			if(strlen($s_item) > 50):
				$s_item = substr($s_item,0,47) . '...';
			endif;
			echo htmlspecialchars($s_item);
			if($this->permissions_grant(dir: $dir,file: $item,action: 'read')):
				echo '</div></a>';
			endif;
			echo '</td>',"\n";
//			size
			echo '<td class="lcell">',format_bytes($this->get_file_size($dir,$item),2,false,false),sprintf('%10s','&nbsp;'),'</td>',"\n";
//			type
			echo '<td class="lcell">',$this->_get_link_info($dir,$item,'type'),'</td>',"\n";
//			modified
			echo '<td class="lcell">',get_datetime_locale($this->get_file_date($dir,$item)),'</td>',"\n";
//			permissions
			echo '<td class="lcell">';
			if($this->permissions_grant(dir: $dir,action: 'change')):
				echo '<a href="',$this->make_link('chmod',$dir,$item),'" title="',gtext('CHANGE PERMISSIONS'),'">';
			endif;
			echo $this->parse_file_type($dir,$item). $this->parse_file_perms($this->get_file_perms($dir,$item));
			if($this->permissions_grant(dir: $dir,action: 'change')):
				echo '</a>';
			endif;
			echo '</td>',"\n";
//			actions
			echo '<td class="lcebl">';
			echo '<table><tbody><tr>';
//			edit
			if($this->get_is_editable($dir,$item)):
				$this->_print_link('edit',$this->permissions_grant(dir: $dir,file: $item,action: 'change'),$dir,$item);
			elseif($this->get_is_unzipable($dir,$item)):
				$this->_print_link('unzip',$this->permissions_grant(dir: $dir,file: $item,action: 'create'),$dir,$item);
			else:
				echo '<td><img style="vertical-align:middle" width="16" height="16" src="',$this->baricons['none'],'" alt=""></td>',"\n";
			endif;
//			download
			if($this->get_is_file($dir,$item)):
				$this->_print_link('download',$this->permissions_grant(dir: $dir,file: $item,action: 'read'),$dir,$item);
			else:
				echo '<td><img style="vertical-align:middle" width="16" height="16" src="',$this->baricons['none'],'" alt=""></td>',"\n";
			endif;
			echo '</tr></tbody></table>';
			echo '</td>';
			echo '</tr>',"\n";
		endforeach;
	}
/**
 *	main function
 */
	public function list_dir($dir) {
		if(!$this->get_show_item(directory: $dir)):
			$this->show_error(gtext('You are not allowed to access this directory.') . " : '$dir'");
		endif;
//		make file & dir tables, & get total filesize & number of items
		$this->make_tables($dir,$dir_list,$file_list,$tot_file_size,$num_items);
		$s_dir = $dir;
		if(strlen($s_dir) > 50):
			$s_dir = '...' . substr($s_dir,-47);
		endif;
		$this->show_header(gtext('Directory') . ': ' . $this->_breadcrumbs_list($dir));
		echo '<div class="area_data_pot">',"\n";
//		Javascript functions:
		$this->js_show();
//		Sorting of items
		$_img = '&nbsp;<img style="vertical-align:middle" width="10" height="10" src="/images/fm_img/';
		if($this->srt == 'yes'):
			$_srt = 'no';
			$_img .= '_arrowup.gif" alt="^">';
		else:
			$_srt = 'yes';
			$_img .= '_arrowdown.gif" alt="v">';
		endif;
//		Toolbar
		echo '<table class="area_data_settings"><thead><tr>',"\n";
		echo '<th class="lhell">',"\n";
		echo '<table><tbody><tr>',"\n";
//		PARENT DIR
		echo '<td style="padding-right:4px"><a href="',$this->make_link('list',$this->path_up($dir),null),'">',
				'<img style="vertical-align:middle" width="16" height="16" src="',$this->baricons['up'],'" alt="',gtext('UP'),'" title="',gtext('UP'),'">',
			'</a></td>',"\n";
//		HOME DIR
		echo '<td style="padding-right:4px"><a href="',$this->make_link('list',null,null),'">',
				'<img style="vertical-align:middle" width="16" height="16" src="',$this->baricons['home'],'" alt="',gtext('HOME'),'" title="',gtext('HOME'),'">',
			'</a></td>',"\n";
//		RELOAD
		echo '<td style="padding-right:4px"><a href="javascript:location.reload();">',
				'<img style="vertical-align:middle" width="16" height="16" src="',$this->baricons['reload'],'" alt="',gtext('RELOAD'),'" title="',gtext('RELOAD'),'">',
			'</a></td>',"\n";
//		SEARCH
		echo '<td style="padding-right:4px"><a href="',$this->make_link('search',$dir,null),'">',
				'<img style="vertical-align:middle" width="16" height="16" src="',$this->baricons['search'],'" alt="',gtext('SEARCH'),'" title="',gtext('SEARCH'),'">',
			'</a></td>',"\n";
		echo '<td style="padding-right:8px"></td>',"\n";
//		print the download button
		$this->_print_link('download_selected',$this->permissions_grant(dir: $dir,action: 'read'),$dir,null);
//		print the edit buttons
		$this->_print_edit_buttons($dir);
		echo '</tr></tbody></table>',"\n";
		echo '</th>',"\n";
//		Create File / Dir
		if($this->permissions_grant(dir: $dir,action: 'create')):
			echo '<th class="lherr">',"\n";
			echo '<form name="mkiform" method="post" action="',$this->make_link('mkitem',$dir,null),'">',"\n";
			echo '<div id="formextension1">',"\n",'<input name="authtoken" type="hidden" value="',session::get_authtoken(),'">',"\n",'</div>',"\n";
			echo '<table style="width:100%"><tbody><tr><td>',"\n";
			echo '<img style="vertical-align:middle" width="16" height="16" src="',$this->baricons['add'],'" alt="">',"\n";
			echo '<select name="mktype">',"\n";
			echo '<option value="file">',gtext('File'),'</option>',"\n";
			echo '<option value="dir">',gtext('Directory'),'</option>',"\n";
			echo '</select>',"\n";
			echo '<input type="text" class="formfld" name="mkname" size="15">',"\n";
			echo '<input type="submit" value="',gtext('Create'),'">',"\n";
			echo '</td></tr></tbody></table>',"\n";
			echo '</form>',"\n";
			echo '</th>',"\n";
		endif;
		echo "</tr></thead></table>\n";
		echo '</div>',"\n";
//		End Toolbar
//		Begin Table + Form for checkboxes
		echo '<div id="area_data_frame">',"\n";
		echo '<form name="selform" method="post" action="',$this->make_link('post',$dir,null),'">',"\n";
		echo '<div id="formextension2">',"\n",'<input name="authtoken" type="hidden" value="',session::get_authtoken(),'">',"\n",'</div>',"\n";
		echo '<table class="area_data_selection">';
		echo	'<colgroup>',
					'<col style="width:5%">',
					'<col style="width:35%">',
					'<col style="width:10%">',
					'<col style="width:15%">',
					'<col style="width:15%">',
					'<col style="width:10%">',
					'<col style="width:10%">',
				'</colgroup>',"\n";
//				Table Header
		echo	'<thead>',
					'<tr>',
						'<th class="lhelc">',"\n",
							'<input type="checkbox" name="toggleAllC" onclick="javascript:ToggleAll(this);">',
						'</th>',"\n";
		$new_srt = ($this->order == 'name') ? $_srt : 'yes';
		echo			'<th class="lhell">',
							'<a href="',$this->make_link('list',$dir,null,'name',$new_srt),'">',gtext('Name');
		if($this->order == 'name'):
								echo $_img;
		endif;
		echo				'</a>',
						'</th>',"\n";
		$new_srt = ($this->order == 'size') ? $_srt : 'yes';
		echo			'<th class="lhell">',
							'<a href="',$this->make_link('list',$dir,null,'size',$new_srt),'">',gtext('Size');
		if($this->order == 'size'):
								echo $_img;
		endif;
		echo				'</a>',
						'</th>',"\n";
		$new_srt = ($this->order == 'type') ? $_srt : 'yes';
		echo			'<th class="lhell">',
							'<a href="',$this->make_link('list',$dir,null,'type',$new_srt),'">',gtext('Type');
		if($this->order == 'type'):
								echo $_img;
		endif;
		echo				'</a>',
						'</th>',"\n";
		$new_srt = ($this->order == 'mod') ? $_srt : 'yes';
		echo			'<th class="lhell">',
							'<a href="',$this->make_link('list',$dir,null,'mod',$new_srt),'">',gtext('Modified');
		if($this->order == 'mod'):
								echo $_img;
		endif;
		echo				'</a>',
						'</th>',"\n";
		echo			'<th class="lhell">',
							gtext('Permissions'),
						'</th>',"\n";
		echo			'<th class="lhebl">',
							gtext('Actions'),
						'</th>',"\n";
		echo		'</tr>',"\n";
		echo	'</thead>',"\n";
//		make & print Table using lists
		echo	'<tbody>',"\n";
					$this->print_table($dir,$this->make_list($dir_list,$file_list));
		echo	'</tbody>',"\n";
//		print number of items & total filesize
		$free = 0;
		$abs_dir = $this->get_abs_dir($dir);
		if($abs_dir !== false):
			$free_space = diskfreespace($abs_dir);
			if($free_space !== false):
				$free = format_bytes($free_space,2,false,false);
			endif;
		endif;
		echo	'<tfoot>',"\n",
					'<tr>',"\n",
						'<th class="lcell"></th>',"\n",
						'<th class="lcell">',$num_items,' ',gtext('Item(s)'),' (',gtext('Free'),': ',$free,')</th>',"\n",
						'<th class="lcell">',format_bytes($tot_file_size,2,false,false),'</th>',"\n",
						'<th class="lcebl" colspan="4"></th>',"\n",
					'</tr>',"\n",
				'</tfoot>',"\n";
		echo '</table>',"\n";
		echo '<div id="submit">',"\n",
				'<input type="hidden" name="do_action">',"\n",
				'<input type="hidden" name="first" value="y">',"\n",
			'</div>',"\n";
		echo '</form>';
?>
<script>
//<![CDATA[
//	Uncheck all items (to avoid problems with new items)
	var ml = document.selform;
	var len = ml.elements.length;
	for(var i=0; i<len; ++i) {
		var e = ml.elements[i];
		if(e.name == "selitems[]" && e.checked == true) {
			e.checked=false;
		}
	}
//]]>
</script>
<?php
		echo '</div>',"\n";
	}
/*
	####################
	# HELPER FUNCTIONS #
	####################
*/
/**
 *	Print operation buttons
 *	@param string $dir
 */
	public function _print_edit_buttons($dir) {
//		for the copy button the user must have create and read rights
		$this->_print_link('copy',$this->permissions_grant(dir: $dir,action: 'copy'),$dir,null);
		$this->_print_link('move',$this->permissions_grant(dir: $dir,action: 'move'),$dir,null);
		$this->_print_link('delete',$this->permissions_grant(dir: $dir,action: 'delete'),$dir,null);
	}
/**
 *	print out an button link in the toolbar.
 *	@param string $function
 *	@param bool $allow if $allow is set, make this button active and work, otherwise print an inactive button.
 *	@param string $dir
 *	@param string $item
 */
	public function _print_link($function,$allow,$dir,$item) {
//		the list of all available button and the coresponding data
		switch($function):
			case 'copy': $v = ['jf' => 'javascript:Copy();','img' => $this->baricons['copy'],'imgdis' => $this->baricons['notcopy'],'msg' => gtext('COPY')];break;
			case 'move': $v = ['jf' => 'javascript:Move();','img' => $this->baricons['move'],'imgdis' => $this->baricons['notmove'],'msg' => gtext('MOVE')];break;
			case 'delete': $v = ['jf' => 'javascript:Delete();','img' => $this->baricons['delete'],'imgdis' => $this->baricons['notdelete'],'msg' => gtext('DELETE')];break;
			case 'edit': $v = ['jf' => $this->make_link('edit',$dir,$item),'img' => $this->baricons['edit'],'imgdis' => $this->baricons['notedit'],'msg' => gtext('EDIT')];break;
			case 'unzip': $v = ['jf' => $this->make_link('unzip',$dir,$item),'img' => $this->baricons['unzip'],'imgdis' => $this->baricons['notunzip'],'msg' => gtext('UNZIP')];break;
			case 'download': $v = ['jf' => $this->make_link('download',$dir,$item),'img' => $this->baricons['download'],'imgdis' => $this->baricons['notdownload'],'msg' => gtext('DOWNLOAD')];break;
			case 'download_selected': $v = ['jf' => 'javascript:DownloadSelected();','img' => $this->baricons['download'],'imgdis' => $this->baricons['notdownload'],'msg' => gtext('DOWNLOAD')];break;
		endswitch;
//		make an active link if access is allowed
		if($allow):
			echo '<td style="padding-right:4px"><a href="',$v['jf'],'">',
					'<img style="vertical-align:middle" width="16" height="16" src="',$v['img'],'" alt="',$v['msg'],'" title="',$v['msg'],'">',
				'</a></td>',"\n";
		elseif(isset($v['imgdis'])):
//			make an inactive link if access is forbidden
			echo '<td style="padding-right:4px">',
					'<img style="vertical-align:middle" width="16" height="16" src="',$v['imgdis'],'" alt="',$v['msg'],'" title="',$v['msg'],'">',
				'</td>',"\n";
		endif;
	}
/**
 *	@param string $dir
 *	@param string $item
 *	@return string
 */
	public function _get_link_info($dir,$item) {
		$type = $this->get_mime_type($dir,$item,'type');
		if(is_array($type)):
			$type = $type[0];
		endif;
		if(!file_exists($this->get_abs_item($dir,$item))):
			return '<span style="color:red;font-style:italic">' . $type . '</span>';
		endif;
		return $type;
	}
/**
 *	The breadcrumbs function will take the user's current path and build a breadcrumb.
 *  Typical syntax:
 *		echo breadcrumbs($dir, ">>");
 *		$this->show_header(gtext('Directory').":".breadcrumbs($dir));
 *	@param string $curdir users current directory.
 *	@param string $displayseparator (optional) string that will be displayed betweenach crumb.
 *	@return string
 */
	public function _breadcrumbs_list($curdir,$displayseparator = ' &raquo; ') {
//		get localized name for the home directory
		$homedir = gtext('HOME');
//		initialize first crumb and set it to the home directory.
		$breadcrumbs[] = sprintf('<a href="%s">%s</a>',$this->make_link('list','',null),$homedir);
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
				$breadcrumbs[] = sprintf('<a href="%s">%s</a>',$this->make_link('list',$crumbdir,null),htmlspecialchars($crumb));
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
