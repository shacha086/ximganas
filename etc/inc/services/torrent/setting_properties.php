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

namespace services\torrent;

use common\properties as myp;

use function exec;
use function gettext;
use function http_build_query;
use function sprintf;

class setting_properties extends grid_properties {
	public function init_enable(): myp\property_enable {
		$property = parent::init_enable();
		$property->
			set_defaultvalue(false);
		return $property;
	}
	public function init_peerport(): myp\property_int {
		$defaultvalue = 51413;
		$description = sprintf(gettext('Port to listen for incoming peer connections. The default port is %d.'),$defaultvalue);
		$placeholder = $defaultvalue;
		$property = parent::init_peerport();
		$property->
			set_id('peerport')->
			set_description($description)->
			set_placeholder($placeholder)->
			set_placeholderv($placeholder)->
			set_defaultvalue($defaultvalue)->
			set_size(5)->
			set_min(1024)->
			set_max(65535)->
			filter_use_default_or_empty();
		return $property;
	}
	public function init_configdir(): myp\property_text {
		global $g;

		$defaultvalue = $g['media_path'];
		$description = gettext('Storage location of configuration information.');
		$placeholder = $defaultvalue;
		$property = parent::init_configdir();
		$property->
			set_input_type($property::INPUT_TYPE_FILECHOOSER)->
			set_id('configdir')->
			set_description($description)->
			set_placeholder($placeholder)->
			set_placeholderv($placeholder)->
			set_defaultvalue($defaultvalue)->
			set_size(60)->
			filter_use_default_or_empty();
		return $property;
	}
	public function init_incompletedir(): myp\property_text {
		global $g;

		$defaultvalue = $g['media_path'];
		$description = gettext('Storage location of partially downloaded files.');
		$placeholder = $defaultvalue;
		$property = parent::init_incompletedir();
		$property->
			set_input_type($property::INPUT_TYPE_FILECHOOSER)->
			set_id('incompletedir')->
			set_description($description)->
			set_placeholder($placeholder)->
			set_placeholderv($placeholder)->
			set_defaultvalue($defaultvalue)->
			set_size(60)->
			filter_use_default_or_empty();
		return $property;
	}
	public function init_downloaddir(): myp\property_text {
		global $g;

		$defaultvalue = $g['media_path'];
		$description = gettext('Storage location of downloaded files.');
		$placeholder = $defaultvalue;
		$property = parent::init_downloaddir();
		$property->
			set_input_type($property::INPUT_TYPE_FILECHOOSER)->
			set_id('downloaddir')->
			set_description($description)->
			set_placeholder($placeholder)->
			set_placeholderv($placeholder)->
			set_defaultvalue($defaultvalue)->
			set_size(60)->
			filter_use_default_or_empty();
		return $property;
	}
	public function init_watchdir(): myp\property_text {
		global $g;

		$defaultvalue = $g['media_path'];
		$description = gettext('Folder to monitor for new .torrent files.');
		$placeholder = $defaultvalue;
		$property = parent::init_watchdir();
		$property->
			set_input_type($property::INPUT_TYPE_FILECHOOSER)->
			set_id('watchdir')->
			set_description($description)->
			set_placeholder($placeholder)->
			set_placeholderv($placeholder)->
			set_defaultvalue($defaultvalue)->
			set_size(60)->
			filter_use_default_or_empty();
		return $property;
	}
	public function init_portforwarding(): myp\property_bool {
		$caption = gettext('Enable port forwarding via NAT-PMP or UPnP.');
		$property = parent::init_portforwarding();
		$property->
			set_id('portforwarding')->
			set_caption($caption)->
			set_defaultvalue(false)->
			filter_use_default();
		return $property;
	}
	public function init_pex(): myp\property_bool {
		$caption = gettext('Enable peer exchange (PEX).');
		$property = parent::init_pex();
		$property->
			set_id('pex')->
			set_caption($caption)->
			set_defaultvalue(false)->
			filter_use_default();
		return $property;
	}
	public function init_dht(): myp\property_bool {
		$caption = gettext('Enable distributed hash table.');
		$property = parent::init_dht();
		$property->
			set_id('dht')->
			set_caption($caption)->
			set_defaultvalue(false)->
			filter_use_default();
		return $property;
	}
	public function init_lpd(): myp\property_bool {
		$caption = gettext('Enable local peer discovery.');
		$property = parent::init_lpd();
		$property->
			set_id('lpd')->
			set_caption($caption)->
			set_defaultvalue(false)->
			filter_use_default();
		return $property;
	}
	public function init_utp(): myp\property_bool {
		$caption = gettext('Enable uTP for peer connections.');
		$property = parent::init_utp();
		$property->
			set_id('utp')->
			set_caption($caption)->
			set_defaultvalue(false)->
			filter_use_default();
		return $property;
	}
	public function init_uplimit(): myp\property_int {
		$description = gettext('The maximum upload bandwith in KB/s. Leave this field empty to allow unlimited upload bandwidth.');
		$placeholder = gettext('KB/s');
		$placeholderv = gettext('Unlimited');
		$property = parent::init_uplimit();
		$property->
			set_id('uplimit')->
			set_description($description)->
			set_placeholder($placeholder)->
			set_placeholderv($placeholderv)->
			set_defaultvalue('')->
			set_size(10)->
			set_min(0)->
			filter_use_default_or_empty();
		return $property;
	}
	public function init_downlimit(): myp\property_int {
		$description = gettext('The maximum download bandwith in KB/s. Leave this field empty to allow unlimited download bandwidth.');
		$placeholder = gettext('KB/s');
		$placeholderv = gettext('Unlimited');
		$property = parent::init_downlimit();
		$property->
			set_id('downlimit')->
			set_description($description)->
			set_placeholder($placeholder)->
			set_placeholderv($placeholderv)->
			set_defaultvalue('')->
			set_size(10)->
			set_min(0)->
			filter_use_default_or_empty();
		return $property;
	}
	public function init_umask(): myp\property_octal {
		$defaultvalue = '0002';
		$description = sprintf(gettext('Use this option to override the default permission modes for newly created files. (%s by default).'),$defaultvalue);
		$placeholder = gettext($defaultvalue);
		$property = parent::init_umask();
		$property->
			set_id('umask')->
			set_description($description)->
			set_placeholder($placeholder)->
			set_defaultvalue($defaultvalue)->
			set_size(5)->
			filter_use_default_or_empty();
		return $property;
	}
	public function init_preallocation(): myp\property_list {
		$options = [
			'0' => gettext('Disabled'),
			'1' => gettext('Fast'),
			'2' => gettext('Full')
		];
		$defaultvalue = '1';
		$description = sprintf(gettext('Select preallocation mode for files. The default preallocation mode is "%s".'),$options[$defaultvalue]);
		$property = parent::init_preallocation();
		$property->
			set_id('preallocation')->
			set_description($description)->
			set_options($options)->
			set_defaultvalue($defaultvalue)->
			filter_use_default();
		return $property;
	}
	public function init_encryption(): myp\property_list {
		$options = [
			'0' => gettext('Prefer unencrypted connections'),
			'1' => gettext('Prefer encrypted connections'),
			'2' => gettext('Require encrypted connections')
		];
		$defaultvalue = '1';
		$description = sprintf(gettext('The peer connection encryption mode. The default encryption mode is "%s"'),$options[$defaultvalue]);
		$property = parent::init_encryption();
		$property->
			set_id('encryption')->
			set_description($description)->
			set_options($options)->
			set_defaultvalue($defaultvalue)->
			filter_use_default();
		return $property;
	}
	public function init_messagelevel(): myp\property_list {
		$options = [
			'0' => gettext('None'),
			'1' => gettext('Error'),
			'2' => gettext('Info'),
			'3' => gettext('Debug')
		];
		$defaultvalue = '2';
		$description = sprintf(gettext('Set verbosity of transmission messages. The default message level is "%s"'),$options[$defaultvalue]);
		$property = parent::init_messagelevel();
		$property->
			set_id('messagelevel')->
			set_description($description)->
			set_options($options)->
			set_defaultvalue($defaultvalue)->
			filter_use_default();
		return $property;
	}
	public function init_extraoptions(): myp\property_text {
		$os_release = exec('uname -r | cut -d - -f1');
		$q = http_build_query([
			'query' => 'transmission-remote',
			'sektion' => '1',
			'manpath' => sprintf('FreeBSD+%s-RELEASE+and+ports',$os_release),
			'arch' => 'default',
			'format' => 'html'
		]);
		$description =
			gettext('Extra options to pass over RPC using transmission-remote.') .
			' ' .
			'<a' .
			sprintf(' href="https://www.freebsd.org/cgi/man.cgi?%s"',$q) .
			' target="_blank"' .
			' rel="noreferrer">' .
			gettext('Please check the documentation') .
			'</a>.';
		$placeholder = gettext('Extra options for transmission-remote');
		$property = parent::init_extraoptions();
		$property->
			set_id('extraoptions')->
			set_description($description)->
			set_placeholder($placeholder)->
			set_defaultvalue('')->
			set_size(60)->
			filter_use_default_or_empty();
		return $property;
	}
	public function init_port(): myp\property_int {
		$defaultvalue = 9091;
		$description = sprintf(gettext('Web interface port. The default port is %d.'),$defaultvalue);
		$placeholder = gettext($defaultvalue);
		$property = parent::init_port();
		$property->
			set_id('port')->
			set_description($description)->
			set_placeholder($placeholder)->
			set_placeholderv($placeholder)->
			set_defaultvalue($defaultvalue)->
			set_size(5)->
			set_min(1024)->
			set_max(65535)->
			filter_use_default_or_empty();
		return $property;
	}
	public function init_authrequired(): myp\property_bool {
		$caption = gettext('Require authentication.');
		$property = parent::init_authrequired();
		$property->
			set_id('authrequired')->
			set_caption($caption)->
			set_defaultvalue(false)->
			filter_use_default();
		return $property;
	}
	public function init_username(): myp\property_text {
		$description = gettext('Username to log into the transmission web interface.');
		$placeholder = gettext('Username');
		$property = parent::init_username();
		$property->
			set_id('username')->
			set_description($description)->
			set_placeholder($placeholder)->
			set_defaultvalue('')->
			set_size(20)->
			set_maxlength(1024)->
			set_filter()->
			filter_use_default_set_regexp('/^[[:alnum:]]*$/')->
			filter_use_default_or_empty();
		return $property;
	}
	public function init_password(): myp\property_text {
		$description = gettext('Password for the transmission web interface.');
		$placeholder = gettext('Password');
		$property = parent::init_password();
		$property->
			set_input_type($property::INPUT_TYPE_PASSWORD)->
			set_id('password')->
			set_description($description)->
			set_placeholder($placeholder)->
			set_defaultvalue('')->
			set_size(20)->
			set_maxlength(1024)->
			filter_use_default_set_regexp('/^[[:alnum:]]*$/')->
			filter_use_default_or_empty();
		return $property;
	}
	public function init_rpchostwhitelistenabled(): myp\property_list {
		$options = [
			'true' => gettext('Enable DNS rebind protection'),
			'false' => gettext('Disable DNS rebind protection')
		];
		$defaultvalue = 'true';
		$property = parent::init_rpchostwhitelistenabled();
		$property->
			set_id('rpcwhitelistenabled')->
			set_defaultvalue($defaultvalue)->
			set_options($options)->
			filter_use_default();
		return $property;
	}
	public function init_rpchostwhitelist(): myp\property_text {
		$defaultvalue = '';
		$description = gettext('List of allowed domain names.');
		$placeholder = $defaultvalue;
		$property = parent::init_rpchostwhitelist();
		$property->
			set_id('rpchostwhitelist')->
			set_description($description)->
			set_placeholder($placeholder)->
			set_defaultvalue($defaultvalue)->
			set_size(60)->
			set_maxlength(1024)->
			filter_use_default_or_empty();
		return $property;
	}
	public function init_startafterstart(): myp\property_bool {
		$caption = gettext('Start/resume previously stopped torrents after service has started.');
		$property = parent::init_startafterstart();
		$property->
			set_id('startafterstart')->
			set_caption($caption)->
			set_defaultvalue(false)->
			filter_use_default();
		return $property;
	}
	public function init_stopbeforestop(): myp\property_bool {
		$caption = gettext('Stop torrents before stopping service.');
		$property = parent::init_stopbeforestop();
		$property->
			set_id('stopbeforestop')->
			set_caption($caption)->
			set_defaultvalue(false)->
			filter_use_default();
		return $property;
	}
}
