<?php
/*
	fm_unzip.php

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
use PharData;
use Throwable;

use function gtext;

trait fm_unzip {
	use fm_copy_move;
	use fm_error;
	use fm_extra;
	use fm_header;
	use fm_permissions;

/**
 *	unzip file/dir
 *
 *	@param string $dir
 *	@return void
 */
	public function unzip_item($dir) {
//		copy and move are only allowed if the user may read and change files
		if(!$this->permissions_grant(dir: $dir,action: 'copy')):
			$this->show_error(gtext('You are not allowed to use this function.'));
		endif;
//		Vars
		$new_dir = $_POST['new_dir'] ?? $dir;
		$_img = $this->baricons['unzip'];
//		Get Selected Item
		if(!isset($_POST['item']) && isset($_GET['item'])):
			$s_item = $_GET['item'];
		elseif(isset($_POST['item'])):
			$s_item = $_POST['item'];
		endif;
		$dir_extract = sprintf('%s/%s',$this->home_dir,$new_dir);
		if($new_dir != ''):
			$dir_extract .= '/';
		endif;
		$archive_name = sprintf('%s/%s/%s',$this->home_dir,$dir,$s_item);
//		Get New Location & Names
		if(!isset($_POST['confirm']) || $_POST['confirm'] != 'true'):
			$this->show_header(gtext('Extracting'));
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
									'<img style="border:0px;vertical-align:middle" src="',$this->baricons['unzipto'],'" alt="">',"\n",
								'</th>',
								'<th class="lhebl">',"\n",
									'&nbsp;',htmlspecialchars(sprintf('%s: /%s',gtext('Directory'),$s_ndir)),
								'</th>',"\n",
							'</tr>',"\n",
						'</thead>',"\n",
						'<tbody>',"\n",
							$this->dir_print($this->dir_list($new_dir),$new_dir),
						'</tbody>',"\n",
					'</table>',"\n";
			echo	'<table class="area_data_selection">',"\n",
						'<colgroup>',"\n",
							'<col style="width:5%">',"\n",
							'<col style="width:95%">',"\n",
						'</colgroup>',"\n",
						'<thead>',"\n",
							'<tr>',"\n",
								'<th class="gap" colspan="2">','</th>',"\n",
							'</tr>',"\n",
							'<tr>',"\n",
								'<th class="lhetop" colspan="3">',htmlspecialchars('Archive'),'</th>',"\n",
							'</tr>',"\n",
							'<tr>',"\n",
								'<th class="lhelc">','</th>',
								'<th class="lhebl">',htmlspecialchars('Archive Name'),'</th>',"\n",
							'</tr>',"\n",
						'</thead>',
						'<tbody>',"\n",
							'<tr>',
								'<td class="lcelc">',
									'<img style="border:0px;vertical-align:middle" src="',$this->baricons['zip'],'" alt="">',
								'</td>',
								'<td class="lcebl">',
									'<input type="hidden" name="item" value="',htmlspecialchars($s_item),'">',htmlspecialchars($s_item),
								'</td>',
							'</tr>',
						'</tbody>',
					'</table>';
//			Submit & Cancel
			echo	'<div id="submit">',
						'<input type="submit" class="formbtn" value="', gtext('Unzip'),'" onclick="javascript:Execute();">',
						'<input type="button" class="formbtn" value="',gtext('Cancel'),'" onClick="javascript:location=\'',$this->make_link('list',$dir,null),'\';">',
						'<input type="hidden" name="do_action" value="',$this->action,'">',"\n",
						'<input type="hidden" name="confirm" value="false">',"\n",
						'<input type="hidden" name="new_dir" value="',htmlspecialchars($new_dir),'">',"\n",
					'</div>';
			echo '</form>',"\n";
			echo '</div',"\n";
			return;
		endif;
//		DO COPY/MOVE
//		ALL OK?
		if(!@file_exists($this->get_abs_dir($new_dir))):
			$this->show_error(htmlspecialchars($new_dir) . ': ' . gtext("The target directory doesn't exist."));
		endif;
		if(!$this->get_show_item($new_dir,'')):
			$this->show_error(htmlspecialchars($new_dir) . ': ' . gtext('You are not allowed to access the target directory.'));
		endif;
		if(!$this->down_home($this->get_abs_dir($new_dir))):
			$this->show_error(htmlspecialchars($new_dir) . ': ' . gtext('The target directory may not be above the home directory.'));
		endif;
//		extract files
		try {
			$archive_phar = new PharData($archive_name);
			$archive_phar->extractTo($dir_extract);
			$success = true;
		} catch(Throwable $ignore) {
			unset($ignore);
			$success = false;
		}
//		there were errors
		if(!$success):
			$error_string = gtext('Failed to extract archive.');
			$err_msg = '';
			$err_msg .= $archive_name . ' : ' . $error_string . '<br>' . "\n";
			$this->show_error($err_msg);
		endif;
		header('Location: ' . $this->make_link('list',$dir,null));
	}
}
