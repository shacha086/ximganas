<?php
/*
	shared_toolbox.php

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

namespace system\sysctl\info;

use common\sphere as mys;
use DOMDocument;

/**
 *	Wrapper class for autoloading functions
 */
class shared_toolbox {
	private const ROW_IDENTIFIER = 'name';
/**
 *	Configure shared sphere settings
 *	@global array $config
 *	@param mys\root $sphere
 */
	public static function init_sphere(mys\root $sphere) {
//		global $config;

		$sphere->
			set_row_identifier(self::ROW_IDENTIFIER)->
			set_enadis(false)->
			set_lock(true)->
			add_page_title(gettext('System'),gettext('Advanced'),gettext('sysctl.conf'),gettext('Info'));
		$sphere->grid = [];
	}
/**
 *	Add the tab navigation menu of this sphere
 *	@param DOMDocument $document
 *	@return int
 */
	public static function add_tabnav(DOMDocument $document) {
		$retval = 0;
		$document->
			add_area_tabnav()->
				push()->
				add_tabnav_upper()->
					ins_tabnav_record('system_advanced.php',gettext('Advanced'))->
					ins_tabnav_record('system_email.php',gettext('Email'))->
					ins_tabnav_record('system_email_reports.php',gettext('Email Reports'))->
					ins_tabnav_record('system_monitoring.php',gettext('Monitoring'))->
					ins_tabnav_record('system_swap.php',gettext('Swap'))->
					ins_tabnav_record('system_rc.php',gettext('Command Scripts'))->
					ins_tabnav_record('system_cron.php',gettext('Cron'))->
					ins_tabnav_record('system_loaderconf.php',gettext('loader.conf'))->
					ins_tabnav_record('system_rcconf.php',gettext('rc.conf'))->
					ins_tabnav_record('system_sysctl.php',gettext('sysctl.conf'),gettext('Reload page'),true)->
					ins_tabnav_record('system_syslogconf.php',gettext('syslog.conf'))->
				pop()->
				add_tabnav_lower()->
					ins_tabnav_record('system_sysctl.php',gettext('sysctl.conf'))->
					ins_tabnav_record('system_sysctl_info.php',gettext('Information'),gettext('Reload page'),true);
		return $retval;
	}
}
