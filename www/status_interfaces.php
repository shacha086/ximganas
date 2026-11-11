<?php
/*
	status_interfaces.php

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

require_once 'auth.inc';
require_once 'guiconfig.inc';

$sphere_scriptname = basename(__FILE__);
$show_separator = false;
$ifdescrs = ['lan' => 'LAN'];
for($j = 1;isset($config['interfaces']['opt' . $j]);$j++):
	$ifdescrs['opt' . $j] = $config['interfaces']['opt' . $j]['descr'];
endfor;
$pgtitle = [gettext('Status'),gettext('Interfaces')];
$document = new_page(page_title: $pgtitle,options: 'notabnav');
//	get areas
$body = $document->getElementById('main');
$pagecontent = $document->getElementById('pagecontent');
$content = $pagecontent->add_area_data();
foreach($ifdescrs as $ifdescr => $ifname):
	$sphere_record = get_interface_info_ex($ifdescr);
	$table = $content->add_table_data_settings();
	$table->ins_colgroup_data_settings();
	$thead = $table->addTHEAD();
	$tbody = $table->addTBODY();
	$thead->c2_titleline(sprintf('%s: %s',gettext('Interface'),$ifname));
	$show_ip_details = true;
	$tbody->c2_textinfo('interface',gettext('Interface'),$sphere_record['hwif']);
	if(isset($sphere_record['dhcplink']) && $sphere_record['dhcplink']):
		$tbody->c2_textinfo('dhcplink',gettext('DHCP'),$sphere_record['dhcplink']);
		if($sphere_record['dhcplink'] === 'down'):
			$show_ip_details = false;
		endif;
	endif;
	if(isset($sphere_record['pppoelink']) && $sphere_record['pppoelink']):
		$tbody->c2_textinfo('pppoelink',gettext('PPPoE'),$sphere_record['pppoelink']);
		if($sphere_record['pppoelink'] === 'down'):
			$show_ip_details = false;
		endif;
	endif;
	if(isset($sphere_record['pptplink']) && $sphere_record['pptplink']):
		$tbody->c2_textinfo('pptplink',gettext('PPTP'),$sphere_record['pptplink']);
		if($sphere_record['pptplink'] === 'down'):
			$show_ip_details = false;
		endif;
	endif;
	if(isset($sphere_record['macaddr']) && $sphere_record['macaddr']):
		$tbody->c2_textinfo('macaddr',gettext('MAC Address'),$sphere_record['macaddr']);
	endif;
	if($sphere_record['status'] !== 'down'):
		if($show_ip_details):
			if(isset($sphere_record['ipaddr']) && $sphere_record['ipaddr']):
				$tbody->c2_textinfo('ipaddr',gettext('IP Address'),$sphere_record['ipaddr']);
			endif;
			if(isset($sphere_record['subnet']) && $sphere_record['subnet']):
				$tbody->c2_textinfo('subnet',gettext('Subnet Mask'),$sphere_record['subnet']);
			endif;
			if(isset($sphere_record['gateway']) && $sphere_record['gateway']):
				$tbody->c2_textinfo('gateway',gettext('Gateway'),$sphere_record['gateway']);
			endif;
			if(isset($sphere_record['ipv6addr']) && $sphere_record['ipv6addr']):
				$tbody->c2_textinfo('ipv6addr',gettext('IPv6 Address'),$sphere_record['ipv6addr']);
			endif;
			if(isset($sphere_record['ipv6subnet']) && $sphere_record['ipv6subnet']):
				$tbody->c2_textinfo('ipv6subnet',gettext('IPv6 Prefix'),$sphere_record['ipv6subnet']);
			endif;
			if(isset($sphere_record['ipv6gateway']) && $sphere_record['ipv6gateway']):
				$tbody->c2_textinfo('ipv6gateway',gettext('IPv6 Gateway'),$sphere_record['ipv6gateway']);
			endif;
			if(($ifdescr === 'wan') && file_exists("{$g['varetc_path']}/nameservers.conf")):
				$filename = sprintf('%s/nameservers.conf',$g['varetc_path']);
				$helpinghand = sprintf('<pre>%s</pre>',file_get_contents($filename));
				$tbody->c2_textinfo('ispdnsservers',gettext('ISP DNS Servers') . $helpinghand);
			endif;
		endif;
		if(isset($sphere_record['media']) && $sphere_record['media']):
			$tbody->c2_textinfo('media',gettext('Media'),$sphere_record['media']);
		endif;
		if(isset($sphere_record['channel']) && $sphere_record['channel']):
			$tbody->c2_textinfo('channel',gettext('Channel'),$sphere_record['channel']);
		endif;
		if(isset($sphere_record['ssid']) && $sphere_record['ssid']):
			$tbody->c2_textinfo('ssid',gettext('SSID'),$sphere_record['ssid']);
		endif;
		$tbody->c2_textinfo('mtu',gettext('MTU'),$sphere_record['mtu']);
		$helpinghand = sprintf('%s/%s (%s/%s)',$sphere_record['inpkts'],$sphere_record['outpkts'],format_bytes($sphere_record['inbytes']),format_bytes($sphere_record['outbytes']));
		$tbody->c2_textinfo('inpkts',gettext('In/Out Packets'),$helpinghand);
		if(isset($sphere_record['inerrs'])):
			$helpinghand = sprintf('%s/%s',$sphere_record['inerrs'],$sphere_record['outerrs']);
			$tbody->c2_textinfo('inerrs',gettext('In/Out Errors'),$helpinghand);
		endif;
		if(isset($sphere_record['collisions'])):
			$tbody->c2_textinfo('collisions',gettext('Collisions'),$sphere_record['collisions']);
		endif;
	endif;
	$tbody->c2_textinfo('status',gettext('Status'),$sphere_record['status']);
endforeach;
$document->render();
