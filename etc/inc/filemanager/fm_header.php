<?php
/*
	fm_header.php

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

use function display_headermenu;
use function genhtmltitle;
use function gentitle;
use function gtext;
use function strtohtml;
use function system_get_language_code;

trait fm_header {
	public function show_header($title) {
		$pgtitle = [gtext('Tools'), gtext('File Manager')];

		header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
		header("Cache-Control: no-cache, must-revalidate");
		header("Pragma: no-cache");
		header("Content-Type: text/html; charset=".'UTF-8');
/*		XigmaNAS® & QUIXPLORER CODE*/
//		HTML & Page Headers
		echo '<!DOCTYPE html>',"\n";
		echo '<html lang="',system_get_language_code(),'">',"\n";
		echo '<head>',"\n";
		echo '<meta charset="UTF-8">',"\n";
		echo '<meta name="format-detection" content="telephone=no">',"\n";
		echo '<meta name="viewport" content="width=device-width, initial-scale=1.0">',"\n";
		echo '<title>',genhtmltitle($pgtitle ?? []),'</title>',"\n";
		echo '<link href="/css/gui.css.php" rel="stylesheet" type="text/css">',"\n";
		echo '<link href="/css/navbar.css.php" rel="stylesheet" type="text/css">',"\n";
		echo '<link href="/css/tabs.css.php" rel="stylesheet" type="text/css">',"\n";
		echo '<script src="/js/jquery.min.js"></script>',"\n";
		echo '<script src="/js/gui.js"></script>',"\n";
		echo '<script src="/js/spinner.js"></script>',"\n";
		echo '<script src="/js/spin.min.js"></script>',"\n";
		if(isset($pglocalheader) && !empty($pglocalheader)):
			if(is_array($pglocalheader)):
				foreach($pglocalheader as $pglocalheaderv):
					echo $pglocalheaderv,"\n";
				endforeach;
			else:
				echo $pglocalheader,"\n";
			endif;
		endif;
		echo '</head>',"\n";
//		XigmaNAS® Header
		echo '<body id="main">',"\n";
		echo '<div id="spinner_main"></div>',"\n";
		echo '<div id="spinner_overlay" style="display: none; background-color: white; position: fixed; left:0; top:0; height:100%; width:100%; opacity: 0.25;"></div>',"\n";
		echo '<header id="g4h">',"\n";
		echo '<div id="gapheader"></div>',"\n";
		display_headermenu();
//		QuiXplorer Header
		if(!isset($pgtitle_omit) || !$pgtitle_omit):
			echo '<div id="pgtitle">','<p class="pgtitle">',gentitle($pgtitle),'</p>','</div>',"\n";
		endif;
		echo '</header>',"\n",
			'<main id="g4m2">',"\n".
				'<div id="pagecontent">',"\n".
					'<div class="area_data_top">',"\n",
						'<table class="area_data_settings"><thead><tr>',"\n",
							'<th class="lhetop">';
								$uname = session::get_user_name();
								if($uname !== false):
									echo '[',strtohtml($uname),'] ';
								endif;
								echo $title;
		echo				'</th>',"\n",
							'<th class="lhetop" style="text-align:right">Powered by QuiXplorer</th>',"\n",
						'</tr></thead></table>',"\n",
					'</div>',"\n";
/*
 *		echo	'</div',"\n",
 *			'</main>',"\n";
 */
	}
}
