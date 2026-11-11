<?php
/*
	filechooser.php

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

header("Content-Type: text/html; charset=" . system_get_language_codeset());
?>
<!DOCTYPE html>
<html lang="<?=system_get_language_code();?>">
	<head>
		<meta charset="<?=system_get_language_codeset();?>"/>
		<title><?=gtext('filechooser');?></title>
		<link href="css/gui.css.php" rel="stylesheet" type="text/css">
		<link href="css/fc.css.php" rel="stylesheet" type="text/css">
		<script>
//<![CDATA[
			function onSubmit() {
				var slash = eval("opener.slash_"+opener.ifield.id);
				if (typeof slash === "undefined" || slash == 0) {
					opener.ifield.value = document.forms[0].p.value.replace(/\/$/, '');
				} else {
					opener.ifield.value = document.forms[0].p.value;
				}
				close();
			}
			function onReset() {
				close();
			}
//]]>
		</script>
	</head>
	<body class="filechooser">
		<table cellspacing="0">
			<tbody>
				<tr>
					<td class="navbar">
						<form method="get" action="filechooser.php" onSubmit="onSubmit();" onReset="onReset();">
<?php
							new filechooser\filechooser();
							include 'formend.inc';
?>
						</form>
					</td>
				</tr>
			</tbody>
		</table>
	</body>
</html>
