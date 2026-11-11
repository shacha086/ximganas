<?php
/*
	fm_copy_move.php

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

trait fm_copy_move {
	use fm_extra;
	use fm_header;
	use fm_permissions;

/**
 *	make list of directories
 *	@param type $dir
 *	@return type
 */
	public function dir_list($dir) {
//		this list is used to copy/move items to a specific location
		$dir_list = [];
		$handle = @opendir($this->get_abs_dir($dir));
		if($handle === false):
//			unable to open dir
			return;
		endif;
		while(($new_item = readdir($handle)) !== false):
//			if(!@file_exists($this->get_abs_item($dir, $new_item))):
//				continue;
//			endif;
			if(!$this->get_show_item($dir,$new_item)):
				continue;
			endif;
			if(!$this->get_is_dir($dir,$new_item)):
				continue;
			endif;
			$dir_list[$new_item] = $new_item;
		endwhile;
//		sort
		if(is_array($dir_list)):
			ksort($dir_list);
		endif;
		return $dir_list;
	}
/**
 *	print list of directories
 *	@param array $dir_list
 *	@param string $new_dir
 *	@return void
 */
	public function dir_print($dir_list,$new_dir) {
		$dir_up = dirname($new_dir);
		if($dir_up == '.'):
			$dir_up = '';
		endif;
		$js_string = addslashes($dir_up);
		echo '<tr>',"\n",
				'<td class="lcelc">',
					'<a href="javascript:NewDir(\'',$js_string,'\');"><div>',
						'<img style="border:0px;vertical-align:middle" width="16" height="16" src="',$this->baricons['up'],'" alt="">',
					'</div></a>',
				'</td>',"\n",
				'<td class="lcebl">',
					'<a href="javascript:NewDir(\'',$js_string,'\');"><div>',
						htmlspecialchars('..'),
					'</div></a>',
				'</td>',"\n",
			'</tr>',"\n";
//		print list of target directories
		if(!is_array($dir_list)):
			return;
		endif;
		foreach($dir_list as $new_item => $value):
			$s_item = $new_item;
			if(strlen($s_item) > 40):
				$s_item = substr($s_item,0,37) . '...';
			endif;
			$js_string = addslashes($this->get_rel_item($new_dir,$new_item));
			echo '<tr>',"\n",
					'<td class="lcelc">',
						'<a href="javascript:NewDir(\'',$js_string,'\');"><div>',
							'<img style="border:0px;vertical-align:middle" width="16" height="16" src="/images/fm_img/dir.gif" alt="">',
						'</div></a>',
					'</td>',"\n",
					'<td class="lcebl">',
						'<a href="javascript:NewDir(\'',$js_string,'\');"><div>',
							htmlspecialchars($s_item),
						'</div></a>',
					'</td>',"\n",
				'</tr>',"\n";
		endforeach;
	}
//	copy/move file/dir
	public function copy_move_items($dir) {
//		copy and move are only allowed if the user may read and change files
		if($this->action == 'copy' && !$this->permissions_grant(dir: $dir,action: 'copy')):
			$this->show_error(gtext('You are not allowed to use this function.'));
		endif;
		if($this->action == 'move' && !$this->permissions_grant(dir: $dir,action: 'move')):
			$this->show_error(gtext('You are not allowed to use this function.'));
		endif;
//		Vars
		$first = $_POST['first'];
		if($first == 'y'):
			$new_dir = $dir;
		else:
			$new_dir = $_POST['new_dir'];
		endif;
		if($new_dir == '.'):
			$new_dir = '';
		endif;
		$cnt = count($_POST['selitems']);
//		Copy or Move?
		if($this->action != 'move'):
			$_img = '/images/fm_img/__copy.gif';
		else:
			$_img = '/images/fm_img/__cut.gif';
		endif;
//		Get New Location & Names
		if(!isset($_POST['confirm']) || $_POST['confirm'] != 'true'):
			$msg = $this->action != 'move' ? gtext('Copy item(s)') : gtext('Move item(s)');
			$this->show_header($msg);
			echo '<div id="area_data_frame">',"\n";
//			JavaScript for Form:
//			Select new target directory / execute action
?>
<script>
//<![CDATA[
function NewDir(newdir) {
	document.selform.new_dir.value = newdir;
	document.selform.submit();
}
function Execute() {
	document.selform.confirm.value = "true";
}
//]]>
</script>
<?php
//			"Copy / Move from .. to .."
			$s_dir = $dir;
			if(strlen($s_dir) > 40):
				$s_dir = '...' . substr($s_dir,-37);
			endif;
			$s_ndir = $new_dir;
			if(strlen($s_ndir) > 40):
				$s_ndir = '...' . substr($s_ndir,-37);
			endif;
//			form for target directory & new names
			echo '<form name="selform" method="post" action="',$this->make_link('post',$dir,null),'">';
			echo	'<div id="formextension">',"\n",'<input name="authtoken" type="hidden" value="',session::get_authtoken(),'">',"\n",'</div>',"\n";
			echo	'<table class="area_data_selection">',"\n",
						'<colgroup>',"\n",
							'<col style="width:5%">',"\n",
							'<col style="width:95%">',"\n",
						'</colgroup>',
						'<thead>',"\n",
							'<tr>',"\n",
								'<th class="lhelc">',
//									'<img style="border:0px;vertical-align:middle" src="',$_img,'" alt="">',
//									htmlspecialchars(' > '),
									'<img style="border:0px;vertical-align:middle" src="/images/fm_img/__paste.gif" alt="">',"\n",
								'</th>',
								'<th class="lhebl">',"\n",
									'&nbsp;',htmlspecialchars(sprintf(($this->action != 'move' ? gtext('Copy from /%s to /%s ') : gtext('Move from /%s to /%s ')),$s_dir,$s_ndir)),
								'</th>',"\n",
							'</tr>',"\n",
						'</thead>',"\n",
						'<tbody>',"\n",
							$this->dir_print($this->dir_list($new_dir),$new_dir),
						'</tbody>',"\n",
					'</table>',"\n";
//			echo	'<br>',"\n";
//			Print Text Inputs to change Names
			echo	'<table class="area_data_selection">',"\n",
						'<colgroup>',"\n",
							'<col style="width:5%">',"\n",
							'<col style="width:45%">',"\n",
							'<col style="width:50%">',"\n",
						'</colgroup>',"\n",
						'<thead>',"\n",
							'<tr>',"\n",
								'<th class="gap" colspan="3">','</th>',"\n",
							'</tr>',"\n",
							'<tr>',"\n",
								'<th class="lhetop" colspan="3">',htmlspecialchars('Rename Item(s)'),'</th>',"\n",
							'</tr>',"\n",
							'<tr>',"\n",
								'<th class="lhelc">','</th>',"\n",
								'<th class="lhell">',htmlspecialchars('New Name'),'</th>',"\n",
								'<th class="lhebl">',htmlspecialchars('Current Name'),'</th>',"\n",
							'</tr>',"\n",
						'</thead>',"\n",
						'<tbody>',"\n";
			for($i = 0;$i < $cnt;++$i):
				$selitem = $_POST['selitems'][$i];
				if(isset($_POST['newitems'][$i])):
					$newitem = $_POST['newitems'][$i];
					if($first == 'y'):
						$newitem = $selitem;
					endif;
				else:
					$newitem = $selitem;
				endif;
				echo		'<tr>',"\n",
								'<td class="lcelc">',
									'<img style="border:0px;vertical-align:middle" src="/images/fm_img/_info.gif" alt="">',
								'</td>',"\n",
								'<td class="lcell">',
									'<input type="text" class="formfld" size="40" name="newitems[]" value="',htmlspecialchars($newitem),'">',
								'</td>',"\n",
								'<td class="lcebl">',
									'<input type="hidden" name="selitems[]" value="',htmlspecialchars($selitem),'">',htmlspecialchars($selitem),
								'</td>',"\n",
							'</tr>',"\n";
			endfor;
			echo		'</tbody>',
					'</table>';
//			Submit & Cancel
			echo	'<div id="submit">',
						'<input type="submit" class="formbtn" value="',($this->action != 'move' ? gtext('Copy') : gtext('Move')),'" onclick="javascript:Execute();">',
						'<input type="button" class="formbtn" value="',gtext('Cancel'),'" onClick="javascript:location=\'',$this->make_link('list',$dir,null),'\';">',
						'<input type="hidden" name="do_action" value="',$this->action,'">',"\n",
						'<input type="hidden" name="confirm" value="false">',"\n",
						'<input type="hidden" name="first" value="n">',"\n",
						'<input type="hidden" name="new_dir" value="',htmlspecialchars($new_dir),'">',"\n",
					'</div>';
			echo '</form>',"\n";
			echo '</div>',"\n";
			return;
		endif;
//		DO COPY/MOVE
//		ALL OK?
		if(!@file_exists($this->get_abs_dir($new_dir))):
			$this->show_error($new_dir . ': ' . gtext("The target directory doesn't exist."));
		endif;
		if(!$this->get_show_item($new_dir,'')):
			$this->show_error($new_dir . ': ' . gtext('You are not allowed to access the target directory.'));
		endif;
		if(!$this->down_home($this->get_abs_dir($new_dir))):
			$this->show_error($new_dir . ': ' . gtext('The target directory may not be above the home directory.'));
		endif;
//		copy / move files
		$err = false;
		$items = [];
		for($i = 0;$i < $cnt;++$i):
			$tmp = $_POST['selitems'][$i];
			$new = basename($_POST['newitems'][$i]);
			$abs_item = $this->get_abs_item($dir,$tmp);
			$abs_new_item = $this->get_abs_item($new_dir,$new);
			$items[$i] = $tmp;
//			Check
			if($new == ''):
				$error[$i] = gtext('You must supply a name.');
				$err = true;
				continue;
			endif;
			if(!@file_exists($abs_item)):
				$error[$i] = gtext("This item doesn't exist.");
				$err = true;
				continue;
			endif;
			if(!$this->get_show_item($dir,$tmp)):
				$error[$i] = gtext('You are not allowed to access this item.');
				$err = true;
				continue;
			endif;
			if(@file_exists($abs_new_item)):
				$error[$i] = gtext('The target item already exists.');
				$err = true;
				continue;
			endif;
//			Copy / Move
			if($this->action == 'copy'):
				if(@is_link($abs_item) || @is_file($abs_item)):
					$handle_src = @fopen($abs_item,'rb');
					if($handle_src !== false):
						$handle_dst = @fopen($abs_new_item,'w+b');
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
				elseif(@is_dir($abs_item)):
					$ok = $this->copy_dir($abs_item,$abs_new_item);
				endif;
			else:
				$ok = @rename($abs_item,$abs_new_item);
			endif;
			if($ok === false):
				$error[$i] = ($this->action == 'copy' ? gtext('Copying failed.') : gtext('Moving failed.'));
				$err = true;
				continue;
			endif;
			$error[$i] = null;
		endfor;
		if($err):
//			there were errors
			$err_msg = '';
			for($i = 0;$i < $cnt;++$i):
				if($error[$i] == null):
					continue;
				endif;
				$err_msg .= $items[$i] . ' : ' . $error[$i] . '<br>' . "\n";
			endfor;
			$this->show_error($err_msg);
		endif;
		header('Location: ' . $this->make_link('list',$dir,null));
	}
}
