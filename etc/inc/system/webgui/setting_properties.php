<?php
/*
	setting_properties.php

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

namespace system\webgui;

use common\properties as myp;

class setting_properties extends grid_properties {
	public function init_adddivsubmittodataframe(): myp\property_bool {
		$caption = gettext('Display action buttons in the scrollable area instead of the footer area.');
		$property = parent::init_adddivsubmittodataframe();
		$property->
			set_id('adddivsubmittodataframe')->
			set_caption($caption)->
			set_defaultvalue(false)->
			filter_use_default();
		return $property;
	}
	public function init_cssfcfile(): myp\property_text {
		$description = gettext('Fully qualified file name of a custom file chooser CSS file.');
		$placeholder = '/usr/local/www/css/fc.css';
		$property = parent::init_cssfcfile();
		$property->
			set_id('cssfcfile')->
			set_input_type($property::INPUT_TYPE_FILECHOOSER)->
			set_description($description)->
			set_placeholder($placeholder)->
			set_placeholderv($placeholder)->
			set_defaultvalue('')->
			filter_use_default_or_empty();
		return $property;
	}
	public function init_cssfcfilemode(): myp\property_list {
		$description = gettext('Select file mode of the custom file chooser CSS file.');
		$options = [
			'' => gettext('Disable'),
			'replace' => gettext('Replace the default CSS file'),
			'append' => gettext('Append content to the default CSS file')
		];
		$property = parent::init_cssfcfilemode();
		$property->
			set_id('cssfcfilemode')->
			set_description($description)->
			set_defaultvalue('')->
			set_options($options)->
			filter_use_default();
		return $property;
	}
	public function init_cssguifile(): myp\property_text {
		$description = gettext('Fully qualified file name of a custom GUI CSS file.');
		$placeholder = '/usr/local/www/css/gui.css';
		$property = parent::init_cssguifile();
		$property->
			set_id('cssguifile')->
			set_input_type($property::INPUT_TYPE_FILECHOOSER)->
			set_description($description)->
			set_placeholder($placeholder)->
			set_placeholderv($placeholder)->
			set_defaultvalue('')->
			filter_use_default_or_empty();
		return $property;
	}
	public function init_cssguifilemode(): myp\property_list {
		$description = gettext('Select file mode of the custom GUI CSS file.');
		$options = [
			'' => gettext('Disable'),
			'replace' => gettext('Replace the default CSS file'),
			'append' => gettext('Append content to the default CSS file')
		];
		$property = parent::init_cssguifilemode();
		$property->
			set_id('cssguifilemode')->
			set_description($description)->
			set_defaultvalue('')->
			set_options($options)->
			filter_use_default();
		return $property;
	}
	public function init_cssnavbarfile(): myp\property_text {
		$description = gettext('Fully qualified file name of a custom navigation bar CSS file.');
		$placeholder = '/usr/local/www/css/navbar.css';
		$property = parent::init_cssnavbarfile();
		$property->
			set_id('cssnavbarfile')->
			set_input_type($property::INPUT_TYPE_FILECHOOSER)->
			set_description($description)->
			set_placeholder($placeholder)->
			set_placeholderv($placeholder)->
			set_defaultvalue('')->
			filter_use_default_or_empty();
		return $property;
	}
	public function init_cssnavbarfilemode(): myp\property_list {
		$description = gettext('Select file mode of the custom navigation bar CSS file.');
		$options = [
			'' => gettext('Disable'),
			'replace' => gettext('Replace the default CSS file'),
			'append' => gettext('Append content to the default CSS file')
		];
		$property = parent::init_cssnavbarfilemode();
		$property->
			set_id('cssnavbarfilemode')->
			set_description($description)->
			set_defaultvalue('')->
			set_options($options)->
			filter_use_default();
		return $property;
	}
	public function init_csstabsfile(): myp\property_text {
		$description = gettext('Fully qualified file name of a custom tabs CSS file.');
		$placeholder = '/usr/local/www/css/tabs.css';
		$property = parent::init_csstabsfile();
		$property->
			set_id('csstabsfile')->
			set_input_type($property::INPUT_TYPE_FILECHOOSER)->
			set_description($description)->
			set_placeholder($placeholder)->
			set_placeholderv($placeholder)->
			set_defaultvalue('')->
			filter_use_default_or_empty();
		return $property;
	}
	public function init_csstabsfilemode(): myp\property_list {
		$description = gettext('Select file mode of the custom tabs CSS file.');
		$options = [
			'' => gettext('Disable'),
			'replace' => gettext('Replace the default CSS file'),
			'append' => gettext('Append content to the default CSS file')
		];
		$property = parent::init_csstabsfilemode();
		$property->
			set_id('csstabsfilemode')->
			set_description($description)->
			set_defaultvalue('')->
			set_options($options)->
			filter_use_default();
		return $property;
	}
	public function init_enabletogglemode(): myp\property_bool {
		$caption = gettext('Use toggle button instead of enable/disable buttons.');
		$property = parent::init_enabletogglemode();
		$property->
			set_id('enabletogglemode')->
			set_caption($caption)->
			set_defaultvalue(false)->
			filter_use_default();
		return $property;
	}
	public function init_navbartoplevelstyle(): myp\property_list {
		$description = gettext('Select the display mode of the top-level items in the navigation bar.');
		$options = [
			'text' => gettext('Show text only'),
			'symbol' => gettext('Show symbol only'),
			'symbolandtext' => gettext('Show symbol and text')
		];
		$property = parent::init_navbartoplevelstyle();
		$property->
			set_id('navbartoplevelstyle')->
			set_description($description)->
			set_defaultvalue('text')->
			set_options($options)->
			filter_use_default();
		return $property;
	}
	public function init_nonsidisksizevalues(): myp\property_bool {
		$caption = gettext('Display disk size values using binary prefixes instead of decimal prefixes.');
		$property = parent::init_nonsidisksizevalues();
		$property->
			set_id('nonsidisksizevalues')->
			set_caption($caption)->
			set_defaultvalue(false)->
			filter_use_default();
		return $property;
	}
	public function init_showcolorfulmeter(): myp\property_bool {
		$caption = gettext('Enable this option if you want to display colorful meter states.');
		$property = parent::init_showcolorfulmeter();
		$property->
			set_id('showcolorfulmeter')->
			set_caption($caption)->
			set_defaultvalue(false)->
			filter_use_default();
		return $property;
	}
	public function init_skipviewmode(): myp\property_bool {
		$caption = gettext('Enable this option if you want to edit configuration pages directly without the need to switch to edit mode.');
		$property = parent::init_skipviewmode();
		$property->
			set_id('skipviewmode')->
			set_caption($caption)->
			set_defaultvalue(false)->
			filter_use_default();
		return $property;
	}
	public function init_showmaxcpus(): myp\property_int {
		$caption = gettext('Please enter a number between 0 and 256 or leave this field empty.');
		$description = gettext('Limit the maximum number of CPUs to be displayed on the home page.');
		$placeholder = '16';
		$property = parent::init_showmaxcpus();
		$property->
			set_caption($caption)->
			set_defaultvalue('')->
			set_description($description)->
			set_id('showmaxcpus')->
			set_max(256)->
			set_maxlength(5)->
			set_min(0)->
			set_placeholder($placeholder)->
			set_placeholderv($placeholder)->
			set_size(4)->
			filter_use_default_or_empty();
		return $property;
	}
	public function init_lp_diag_infos(): myp\property_list{
		$description = gettext('Set the landing page for the Diagnostics > Information menu.');
		$options = [
			'/diag_infos_disks.php' => gettext('Disks'),
			'/diag_infos_disks_info.php' => gettext('Disks (Info)'),
			'/diag_infos_part.php' => gettext('Partitions'),
			'/diag_infos_smart.php' => gettext('S.M.A.R.T.'),
			'/diag_infos_space.php' => gettext('Space Used'),
			'/diag_infos_swap.php' => gettext('Swap'),
			'/diag_infos_mount.php' => gettext('Mounts'),
			'/diag_infos_raid.php' => gettext('Software RAID'),
			'/diag_infos_iscsi.php' => gettext('iSCSI Initiator'),
			'/diag_infos_ad.php' => gettext('MS Domain'),
			'/diag_infos_samba.php' => gettext('SMB'),
			'/diag_infos_testparm.php' => gettext('testparm'),
			'/diag_infos_ftpd.php' => gettext('FTP'),
			'/diag_infos_rsync_client.php' => gettext('RSYNC Client'),
			'/diag_infos_netstat.php' => gettext('Netstat'),
			'/diag_infos_sockets.php' => gettext('Sockets'),
			'/diag_infos_ipmi.php' => gettext('IPMI Stats'),
			'/diag_infos_ups.php' => gettext('UPS')
		];
		$property = parent::init_lp_diag_infos();
		$property->
			set_id('lp_diag_infos')->
			set_description($description)->
			set_defaultvalue('/diag_infos_disks.php')->
			set_options($options)->
			filter_use_default();
		return $property;
	}
}
