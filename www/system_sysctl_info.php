<?php
/*
	system_sysctl_info.php

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

use system\sysctl\info\grid_toolbox as toolbox;

//	init properties and sphere
$cop = toolbox::init_properties();
$sphere = toolbox::init_sphere();
unset($output);
mwexec2('/sbin/sysctl -adet',$output);
foreach($output as $row):
	$a_sysctl = explode('=',$row,3);
	$sysctl_name = $a_sysctl[0] ?? '';
	$sysctl_type = $a_sysctl[1] ?? '';
	$sysctl_info = $a_sysctl[2] ?? '';
	if(!empty($sysctl_type)):
		$sphere->grid[$sysctl_name] = ['sysctltype' => $sysctl_type,'sysctlinfo' => $sysctl_info];
	endif;
endforeach;
unset($row,$output,$sysctl_name,$sysctl_type,$sysctl_info,$a_sysctl);
mwexec2('/sbin/sysctl -ae',$output);
foreach($output as $row):
	$a_sysctl = explode('=',$row,2);
	$sysctl_name = $a_sysctl[0] ?? '';
	$sysctl_value = $a_sysctl[1] ?? '';
	if(array_key_exists($sysctl_name,$sphere->grid)):
		$sphere->grid[$sysctl_name]['value'] = $sysctl_value;
	endif;
endforeach;
unset($row,$output,$sysctl_name,$sysctl_value,$a_sysctl);
toolbox::render($cop,$sphere);
