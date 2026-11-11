<?php
/*
	fbegin.inc

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
header('Content-Type: text/html; charset=' . system_get_language_codeset());
//	XML declarations
/*
	some browser might be broken.
	echo '<?xml version="1.0" encoding="'.system_get_language_codeset().'"?>';
	echo "\n";
*/
echo '<!DOCTYPE html>',"\n";
?>
<html lang="<?=system_get_language_code();?>">
	<head>
		<meta charset="<?=system_get_language_codeset();?>">
		<meta name="format-detection" content="telephone=no">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta name="robots" content="noindex,nofollow">
		<meta name="description" content="XigmaNAS® - The Free Network Attached Storage Project">
		<title><?=genhtmltitle($pgtitle ?? []);?></title>
		<link href="/css/gui.css.php" rel="stylesheet" type="text/css">
		<link href="/css/navbar.css.php" rel="stylesheet" type="text/css">
		<link href="/css/tabs.css.php" rel="stylesheet" type="text/css">
		<script src="/js/jquery.min.js"></script>
		<script src="/js/gui.js"></script>
		<script src="/js/spinner.js"></script>
		<script src="/js/spin.min.js"></script>
<?php
		if(isset($pglocalheader) && !empty($pglocalheader)):
			if(is_array($pglocalheader)):
				foreach($pglocalheader as $pglocalheaderv):
					echo $pglocalheaderv,"\n";
				endforeach;
			else:
				echo $pglocalheader,"\n";
			endif;
		endif;
?>
	</head>
	<body id="main">
		<div id="spinner_main"></div>
		<div id="spinner_overlay" style="display: none; background-color: white; position: fixed; left:0; top:0; height:100%; width:100%; opacity: 0.25;"></div>
		<header id="g4h">
			<div id="gapheader"></div>
<?php
			display_headermenu();
			if(!isset($pgtitle_omit) || !$pgtitle_omit):
?>
			<div id="pgtitle">
				<p class="pgtitle"><?=gentitle($pgtitle);?></p>
			</div>
<?php
			endif;
?>
		</header>
<script>
//<![CDATA[
$(window).on("load", function() {
	$(".spin,#tabnav,#tabnav2").click(function() { spinner(); });
});
//]]>
</script>
		<main id="g4m2">
			<div id="pagecontent">
