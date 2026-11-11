<?php
/*
	system_edit.php

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

require_once 'autoload.php';
require_once 'auth.inc';
require_once 'guiconfig.inc';

$sphere_scriptname = basename(__FILE__);
$savetopath = (isset($_POST['savetopath'])) ? htmlspecialchars($_POST['savetopath']) : '';
$hlm_enabled = (isset($_POST['hlm'])) ? ($_POST['hlm'] == 'enabled') : false;
$rows = (isset($_POST['rows']) && $_POST['rows'] > 16) ? $_POST['rows'] : 30;
$cols = (isset($_POST['cols']) && $_POST['cols'] > 16) ? $_POST['cols'] : 66;
if(isset($_POST['submit'])):
	switch($_POST['submit']):
		case 'highlightdis':
			$hlm_enabled = false;
			break;
		case 'highlightena':
			$hlm_enabled = true;
			break;
	endswitch;
endif;
$hlm_available = false;
if(isset($_POST['submit'])):
	switch($_POST['submit']):
		case 'rowcol':
		case 'highlightdis':
		case 'highlightena':
			$content = $_POST['code'] ?? null;
		case 'open':
			if(preg_match('/\S/',$savetopath)):
				if(file_exists($savetopath) && is_file($savetopath)):
					$content ??= file_get_contents($savetopath);
					$extension_to_language = [
						'.php'  => 'php',
						'.inc'  => 'php',
						'.sh'   => 'core',
						'.xml'  => 'xml',
						'.js'   => 'js',
						'.css'  => 'css',
						'.html' => 'xml',
						'.py'   => 'py'
					];
					$language_scripts_to_load = [
						'php' => ['shCore.js','shBrushPhp.js'],
						'sh'  => ['shCore.js'],
						'xml' => ['shCore.js','shBrushXml.js'],
						'js'  => ['shCore.js','shBrushJScript.js'],
						'css' => ['shCore.js','shBrushCss.js'],
						'py'  => ['shCore.js','shBrushPython.js']
/*
						'???' => ['shCore.js','shBrushCSharp.js'],
						'???' => ['shCore.js','shBrushJava.js'],
						'???' => ['shCore.js','shBrushVb.js'],
						'???' => ['shCore.js','shBrushSql.js'],
						'???' => ['shCore.js','shBrushDelphi.js'],
						'???' => ['shCore.js','shBrushRuby.js'],
 */
					];
					foreach($extension_to_language as $needle => $language):
						if(stripos($savetopath,$needle) !== false):
							$hlm_available = true;
							$scripts_to_load = $language_scripts_to_load[$language];
							break;
						endif;
					endforeach;
				else:
					$savemsg = sprintf('%s %s',gtext('File not found'),$savetopath);
					$content = '';
					$savetopath = '';
				endif;
			endif;
			break;
		case 'save':
			if(preg_match('/\S/',$savetopath)):
				conf_mount_rw();
				$content = preg_replace("/\r/",'',$_POST['code']) ;
				file_put_contents($savetopath,$content);
				$savemsg = sprintf('%s %s',gtext('Saved file to'),$savetopath);
				if($savetopath === "{$g['cf_conf_path']}/config.xml"):
					unlink_if_exists("{$g['tmp_path']}/config.cache");
				endif;
				conf_mount_ro();
			endif;
			break;
	endswitch;
endif;
$pgtitle = [gtext('Tools'),gtext('File Editor')];
include 'fbegin.inc';
?>
<script>
//<![CDATA[
$(window).on("load", function() {
<?php	// Init spinner onsubmit()?>
	$("#iform").submit(function() { spinner(); });
});
//]]>
</script>
<form action="<?=$sphere_scriptname;?>" method="post" name="iform" id="iform" class="pagecontent">
	<div class="area_data_top"></div>
	<div id="area_data_frame">
<?php
		if(!empty($savemsg)):
			print_info_box($savemsg);
		endif;
?>
		<table class="area_data_settings">
			<colgroup>
				<col class="area_data_settings_col_tag">
				<col class="area_data_settings_col_data">
			</colgroup>
			<thead>
<?php
				html_titleline2(gettext('File Editor'),2);
?>
			</thead>
			<tbody>
				<tr>
					<td class="celltag"><?=gtext('File Path');?></td>
					<td class="celldata">
						<input type="text" size="42" id="savetopath" name="savetopath" value="<?=$savetopath;?>">
						<input type="button" name="browse" id="Browse" onclick='ifield = form.savetopath; filechooser = window.open("filechooser.php?p="+encodeURIComponent(ifield.value),"filechooser","scrollbars=yes,toolbar=no,menubar=no,statusbar=no,width=550,height=300"); filechooser.ifield = ifield; window.ifield = ifield;' value="...">
						<button type="submit" name="submit" id="open" value="open"><?=gtext('Open File');?></button>
<?php
						if($hlm_available && $hlm_enabled):
						else:
?>
							<button type="submit" name="submit" id="Save" value="save"><?=gtext('Save File');?></button>
<?php
						endif;
?>
					</td>
				</tr>
<?php
				if((!$hlm_available)):
?>
					<tr>
						<td class="celltag"><?=gtext('Size');?></td>
						<td class="celldata">
							<?=gtext('Rows'); ?>: <input type="text" size="3" name="rows" value="<?=$rows;?>">
							<?=gtext('Cols'); ?>: <input type="text" size="3" name="cols" value="<?=$cols;?>">
							<button type="submit" name="submit" id="rowcol" value="rowcol"><?=gtext('Set');?></button>
							<input type="hidden" name="hlm" value="<?=($hlm_enabled ? 'enabled' : 'disabled');?>">
						</td>
					</tr>
<?php
				elseif($hlm_enabled):
?>
					<tr>
						<td class="celltag"><?=gtext('Highlight View');?></td>
						<td class="celldata">
							<button type="submit" name="submit" id="highlight" value="highlightdis"><?=gtext('Disable');?></button>
							<input type="hidden" name="rows" value="<?=$rows;?>">
							<input type="hidden" name="cols" value="<?=$cols;?>">
							<input type="hidden" name="hlm" value="<?=($hlm_enabled ? 'enabled' : 'disabled');?>">
						</td>
					</tr>
<?php
				else:
?>
					<tr>
						<td class="celltag"><?=gtext('Size');?></td>
						<td class="celldata">
							<?=gtext('Rows'); ?>: <input type="text" size="3" name="rows" value="<?=$rows;?>">
							<?=gtext('Cols'); ?>: <input type="text" size="3" name="cols" value="<?=$cols;?>">
							<button type="submit" name="submit" id="rowcol" value="rowcol"><?=gtext('Set');?></button>
						</td>
					</tr>
					<tr>
						<td class="celltag"><?=gtext('Highlight View');?></td>
						<td class="celldata">
							<button type="submit" name="submit" id="highlight" value="highlightena"><?=gtext('Enable');?></button>
							<input type="hidden" name="hlm" value="<?=($hlm_enabled ? 'enabled' : 'disabled');?>">
						</td>
					</tr>
<?php
				endif;
?>
				<tr>
					<td class="celltag"><?=gtext('Content');?></td>
					<td class="celldata">
<?php
//						NOTE: The opening and the closing textarea tag must be on the same line.
?>
						<textarea style="width:100%;white-space:pre;" class="<?=$language;?>:showcolumns cd-textarea-input formpre" rows="<?=$rows;?>" cols="<?=$cols;?>" name="code"><?=htmlspecialchars($content ?? '');?></textarea>
					</td>
				</tr>
			</tbody>
		</table>
<?php
include 'formend.inc';
?>
	</div>
	<div class="area_data_pot"></div>
</form>
<?php
if($hlm_available && $hlm_enabled):
	foreach($scripts_to_load as $scriptname):
		echo sprintf('<script src="syntaxhighlighter/%s"></script>',$scriptname),"\n";
	endforeach;
?>
<script>
//<![CDATA[
//	append css for syntax highlighter.
	var head = document.getElementsByTagName("head")[0];
	var linkObj = document.createElement("link");
	linkObj.setAttribute("type","text/css");
	linkObj.setAttribute("rel","stylesheet");
	linkObj.setAttribute("href","syntaxhighlighter/SyntaxHighlighter.css");
	head.appendChild(linkObj);
//	activate dp.SyntaxHighlighter
	dp.SyntaxHighlighter.HighlightAll('code',true,true);
//]]>
</script>
<?php
endif;
include 'fend.inc';
