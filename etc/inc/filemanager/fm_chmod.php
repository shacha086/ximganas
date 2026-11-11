<?php
/*
	fm_chmod.php

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

trait fm_chmod {
	use fm_error;
	use fm_extra;
	use fm_header;
	use fm_permissions;

//	change permissions
	public function chmod_item($dir,$item) {
		if(!$this->permissions_grant(dir: $dir,action: 'change')):
			$this->show_error(gtext('You are not allowed to use this function.'));
		endif;
		if(!file_exists($this->get_abs_item($dir,$item))):
			$this->show_error($item . ': ' . gtext("This file doesn't exist."));
		endif;
		if(!$this->get_show_item($dir,$item)):
			$this->show_error($item . ': ' . gtext('You are not allowed to access this file.'));
		endif;
//		Execute
		if(isset($_POST['confirm']) && $_POST['confirm'] == 'true'):
			$bin = '';
			for($i = 0;$i < 3;$i++):
				for($j = 0;$j < 3;$j++):
					$tmp = 'r_' . $i . $j;
					if(isset($_POST[$tmp]) && $_POST[$tmp] == '1'):
						$bin .= '1';
					else:
						$bin .= '0';
					endif;
				endfor;
			endfor;
			if(!@chmod($this->get_abs_item($dir,$item),bindec($bin))):
				$this->show_error($item . ': ' . gtext('Permission-change failed.'));
			endif;
			header('Location: ' . $this->make_link('link',$dir,null));
			return;
		endif;
		$mode = $this->parse_file_perms($this->get_file_perms($dir,$item));
		if($mode === false):
			$this->show_error($item . ': ' . gtext('Getting permissions failed.'));
		endif;
		$pos = 'rwx';
		$s_item = $this->get_rel_item($dir,$item);
		if(strlen($s_item) > 50):
			$s_item = '...' . substr($s_item,-47);
		endif;
		$this->show_header(gtext('Change permissions') . ': /'.$s_item);
		echo '<div id="area_data_frame">',"\n";
//		form
		echo '<form method="post" action="',$this->make_link('chmod',$dir,$item),'">',"\n";
		echo	'<div id="formextension">',"\n",'<input name="authtoken" type="hidden" value="',session::get_authtoken(),'">',"\n",'</div>',"\n";
		echo 	'<table width="175">',"\n",
					'<input type="hidden" name="confirm" value="true">',"\n";
//					print table with current perms & checkboxes to change
					$humanreadable_chmod = [gtext('Owner'),gtext('Group'),gtext('Public')];
					for($i = 0;$i < 3;++$i):
						echo '<tr><td>',$humanreadable_chmod[$i],'</td>';
						for($j = 0;$j < 3;++$j):
							echo '<td>',$pos[$j],"&nbsp;",'<input type="checkbox"';
							if($mode[(3 * $i) + $j] != '-'):
								echo ' checked';
							endif;
							echo ' name="r_' . $i . $j . '" value="1"></td>';
						endfor;
						echo "</tr>\n";
					endfor;
		echo	'</table>',"\n";
//				submit / Cancel
		echo	'<br>',"\n";
		echo	'<table>',
					'<tr>',"\n",
						'<td>',"\n",
							'<input type="submit" value="',gtext('Change'),'">',"\n",
						'</td>',"\n",
						'<td>',"\n",
							'<input type="button" value="',gtext('Cancel'),'" onClick="javascript:location=',"'",$this->make_link('list',$dir,null),"'",'">',"\n",
						'</td>',"\n",
					'</tr>',
				'</table>',"\n";
		echo '</form>',"\n";
		echo '</div>',"\n";
	}
}
