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

namespace services\samba;

use common\properties as myp;

class setting_properties extends grid_properties {
	public function init_enable(): myp\property_enable {
		$property = parent::init_enable();
		$property->
			set_defaultvalue(false);
		return $property;
	}
	public function init_security(): myp\property_list {
		$description = gettext('This option affects how clients respond to Samba and is one of the most important settings in the smb.conf file.');
		$options = [
			'user' => gettext('Local User'),
			'ads' => gettext('Active Directory')
		];
		$property = parent::init_security();
		$property->
			set_defaultvalue('')->
			set_description($description)->
			set_id('security')->
			set_options($options)->
			filter_use_default();
		return $property;
	}
	protected function get_protocol_options(): array {
		$options = [
			'' => gettext('Default'),
			'SMB3' => gettext('SMB3'),
			'SMB3_11' => gettext('SMB3 (Windows 10)'),
//			'SMB3_10' => gettext('SMB3 (Early technical preview of Windows 10'),
			'SMB3_02' => gettext('SMB3 (Windows 8.1)'),
			'SMB3_00' => gettext('SMB3 (Windows 8)'),
			'SMB2' => gettext('SMB2'),
//			'SMB2_24' => gettext('SMB2 (Windows 8 beta SMB2 version)'),
//			'SMB2_22' => gettext('SMB2 (Early Windows 8 SMB2 version)'),
//			'SMB2_10' => gettext('SMB2 (Windows 7 SMB2 version)'),
//			'SMB2_02' => gettext('SMB2 (The earliest SMB2 version)'),
			'NT1' => gettext('NT1 (CIFS)')
//			'LANMAN2' => gettext('LANMAN2 (Updates to Lanman1 protocol)'),
//			'LANMAN1' => gettext('LANMAN1 (First modern version of the protocol. Long filename support)'),
//			'COREPLUS' => gettext('COREPLUS (Slight improvements on CORE for efficiency)'),
//			'CORE' => gettext('CORE (Earliest version. No concept of user names)')
		];
		return $options;
	}
	protected function get_protocol_description(): string {
		$description = gettext('Normally this option should not be set as the automatic negotiation phase in the SMB protocol takes care of choosing the appropriate protocol.');
		return $description;
	}
	public function init_maxprotocol(): myp\property_list {
		$description = sprintf('%s %s',
			gettext('This parameter sets the highest protocol level that will be supported by the server.'),
			$this->get_protocol_description()
		);
		$property = parent::init_maxprotocol();
		$property->
			set_defaultvalue('')->
			set_description($description)->
			set_id('maxprotocol')->
			set_input_type($property::INPUT_TYPE_SELECT)->
			set_options($this->get_protocol_options())->
			filter_use_default();
		return $property;
	}
	public function init_minprotocol(): myp\property_list {
		$description = sprintf('%s %s',
			gettext('This setting controls the minimum protocol version that the server will allow the client to use.'),
			$this->get_protocol_description()
		);
		$property = parent::init_minprotocol();
		$property->
			set_defaultvalue('')->
			set_description($description)->
			set_id('minprotocol')->
			set_input_type($property::INPUT_TYPE_SELECT)->
			set_options($this->get_protocol_options())->
			filter_use_default();
		return $property;
	}
	public function init_clientmaxprotocol(): myp\property_list {
		$description = sprintf('%s %s',
			gettext('This parameter sets the highest protocol level that will be supported by the client.'),
			$this->get_protocol_description()
		);
		$property = parent::init_clientmaxprotocol();
		$property->
			set_defaultvalue('')->
			set_description($description)->
			set_id('clientmaxprotocol')->
			set_input_type($property::INPUT_TYPE_SELECT)->
			set_options($this->get_protocol_options())->
			filter_use_default();
		return $property;
	}
	public function init_clientminprotocol(): myp\property_list {
		$description = sprintf('%s %s',
			gettext('This setting controls the minimum protocol version that the client will attempt to use.'),
			$this->get_protocol_description()
		);
		$property = parent::init_clientminprotocol();
		$property->
			set_defaultvalue('')->
			set_description($description)->
			set_id('clientminprotocol')->
			set_input_type($property::INPUT_TYPE_SELECT)->
			set_options($this->get_protocol_options())->
			filter_use_default();
		return $property;
	}
	public function init_serversigning(): myp\property_list {
		$description = gettext('This controls whether the client is allowed or required to use SMB1 and SMB2 signing.');
		$options = [
			'default' => gettext('Default'),
			'auto' => gettext('Auto'),
			'mandatory' => gettext('Mandatory'),
			'disabled' => gettext('Disabled')
		];
		$property = parent::init_serversigning();
		$property->
			set_defaultvalue('')->
			set_description($description)->
			set_id('serversigning')->
			set_input_type($property::INPUT_TYPE_SELECT)->
			set_options($options)->
			filter_use_default();
		return $property;
	}
	public function init_serversmbencrypt(): myp\property_list {
		$description = gettext('This parameter controls whether a remote client is allowed or required to use SMB encryption. (Effects for SMB3 and newer).');
		$options = [
			'default' => gettext('Default'),
			'desired' => gettext('Desired'),
			'required' => gettext('Required '),
			'off' => gettext('Disabled')
		];
		$property = parent::init_serversmbencrypt();
		$property->
			set_defaultvalue('')->
			set_description($description)->
			set_id('serversmbencrypt')->
			set_input_type($property::INPUT_TYPE_SELECT)->
			set_options($options)->
			filter_use_default();
		return $property;
	}
	public function init_clientsmbencrypt(): myp\property_list {
		$description = gettext('This parameter controls whether a client should try or is required to use SMB encryption. (Effects for SMB3 and newer).');
		$options = [
			'default' => gettext('Default'),
			'desired' => gettext('Desired'),
			'required' => gettext('Required '),
			'off' => gettext('Disabled')
		];
		$property = parent::init_clientsmbencrypt();
		$property->
			set_defaultvalue('')->
			set_description($description)->
			set_id('clientsmbencrypt')->
			set_input_type($property::INPUT_TYPE_SELECT)->
			set_options($options)->
			filter_use_default();
		return $property;
	}
	public function init_netbiosname(): myp\property_text {
		$description = gettext('The NetBIOS name of this Samba server.');
		$regexp = '/^([a-z0-9_\-]+\.?)*$/i';
		$placeholder = gettext('Name');
		$property = parent::init_netbiosname();
		$property->
			set_description($description)->
			set_id('netbiosname')->
			set_maxlength(15)->
			set_placeholder($placeholder)->
			set_size(20)->
			filter_use_default()->
			set_filter(FILTER_VALIDATE_REGEXP)->
			set_filter_flags(FILTER_REQUIRE_SCALAR)->
			set_filter_options(['default' => null,'regexp' => $regexp]);
		return $property;
	}
	public function init_workgroup(): myp\property_text {
		$description = gettext('The workgroup in which the server will appear when queried by Windows or SMB clients. (maximum 15 characters).');
		$placeholder = gettext('WORKGROUP');
		$regexp = '/^[\w0-9][\w0-9\!\@\#\$\%\^\&\(\)\_\-\;\:\'\"\,\.]{0,14}$/';
		$property = parent::init_workgroup();
		$property->
			set_defaultvalue('')->
			set_description($description)->
			set_id('workgroup')->
			set_maxlength(15)->
			set_placeholder($placeholder)->
			set_size(20)->
			filter_use_default()->
			set_filter(FILTER_VALIDATE_REGEXP)->
			set_filter_flags(FILTER_REQUIRE_SCALAR)->
			set_filter_options(['default' => null,'regexp' => $regexp]);
		return $property;
	}
	public function init_if(): myp\property_list {
		$description = '';
		$options = [
			'' => gettext("All Interfaces"),
			'lan' => gettext("LAN Only"),
			'opt' => gettext("OPT Only"),
			'carp' => gettext("CARP only")
		];
		$property = parent::init_if();
		$property->
			set_defaultvalue('')->
			set_description($description)->
			set_id('if')->
			set_input_type($property::INPUT_TYPE_SELECT)->
			set_options($options)->
			filter_use_default();
		return $property;
	}
	public function init_serverdesc(): myp\property_text {
		$description = gettext('Server description. This can usually be left blank.');
		$placeholder = gettext('Description');
		$property = parent::init_serverdesc();
		$property->
			set_defaultvalue('')->
			set_description($description)->
			set_id('serverdesc')->
			set_placeholder($placeholder)->
			filter_use_default_or_empty();
		return $property;
	}
	public function init_doscharset(): myp\property_list {
		$description = gettext('DOS SMB clients assume the server has the same charset as they do. This option specifies which charset Samba should talk to DOS clients.');
		$options = [
			'' => gettext('Default'),
			'CP437' => gettext('CP437 (Latin US)'),
			'CP850' => gettext('CP850 (Latin 1)'),
			'CP852' => gettext('CP852 (Latin 2)'),
			'CP866' => gettext('CP866 (Cyrillic CIS 1)'),
			'CP932' => gettext('CP932 (Japanese Shift-JIS)'),
			'CP936' => gettext('CP936 (Simplified Chinese GBK)'),
			'CP949' => gettext('CP949 (Korean)'),
			'CP950' => gettext('CP950 (Traditional Chinese Big5)'),
			'CP1251' => gettext('CP1251 (Cyrillic)'),
			'CP1252' => gettext('CP1252 (Latin 1)'),
			'ASCII' => gettext('ASCII')
		];
		$property = parent::init_doscharset();
		$property->
			set_defaultvalue('')->
			set_description($description)->
			set_id('doscharset')->
			set_input_type($property::INPUT_TYPE_SELECT)->
			set_options($options)->
			filter_use_default();
		return $property;
	}
	public function init_unixcharset(): myp\property_list {
		$description = gettext('Specifies the charset the unix machine Samba runs on uses.');
		$options = [
			'' => gettext('Default'),
			'UTF-8' => 'UTF-8',
			'iso-8859-1' => 'ISO-8859-1',
			'iso-8859-15' => 'ISO-8859-15',
			'gb2312' => 'GB2312',
			'EUC-JP' => 'EUC-JP',
			'ASCII' => 'ASCII'
		];
		$property = parent::init_unixcharset();
		$property->
			set_defaultvalue('')->
			set_description($description)->
			set_id('unixcharset')->
			set_input_type($property::INPUT_TYPE_SELECT)->
			set_options($options)->
			filter_use_default();
		return $property;
	}
	public function init_loglevel(): myp\property_list {
		$description = gettext('The value of the parameter allows the debug level (logging level) to be specified.');
		$options = [
			'0' => gettext('Disabled'),
			'1' => gettext('Minimum'),
			'2' => gettext('Normal'),
			'3' => gettext('Full'),
			'10' => gettext('Debug')
		];
		$property = parent::init_loglevel();
		$property->
			set_defaultvalue('')->
			set_description($description)->
			set_id('loglevel')->
			set_input_type($property::INPUT_TYPE_SELECT)->
			set_options($options)->
			filter_use_default();
		return $property;
	}
	public function init_localmaster(): myp\property_list {
		$description = gettext('Allow the server to try and become a local master browser.');
		$options = [
			'yes' => gettext('Yes'),
			'no' => gettext('No')
		];
		$property = parent::init_localmaster();
		$property->
			set_defaultvalue('yes')->
			set_description($description)->
			set_id('localmaster')->
			set_input_type($property::INPUT_TYPE_SELECT)->
			set_options($options)->
			filter_use_default();
		return $property;
	}
	public function init_timesrv(): myp\property_list {
		$description = gettext('The server advertises itself as a time server to Windows clients.');
		$options = [
			'yes' => gettext('Yes'),
			'no' => gettext('No')
		];
		$property = parent::init_timesrv();
		$property->
			set_defaultvalue('yes')->
			set_description($description)->
			set_id('timesrv')->
			set_input_type($property::INPUT_TYPE_SELECT)->
			set_options($options)->
			filter_use_default();
		return $property;
	}
	public function init_pwdsrv(): myp\property_text {
		$description = gettext('Password server name or IP address (e.g. Active Directory domain controller).');
		$property = parent::init_pwdsrv();
		$property->
			set_defaultvalue('')->
			set_description($description)->
			set_id('pwdsrv')->
			filter_use_default_or_empty();
		return $property;
	}
	public function init_winssrv(): myp\property_text {
		$description = gettext('WINS server IP address (e.g. from MS Active Directory server).');
		$property = parent::init_winssrv();
		$property->
			set_defaultvalue('')->
			set_description($description)->
			set_id('winssrv')->
			set_size(60)->
			set_maxlength(1024)->
			filter_use_default_or_empty();
		return $property;
	}
	public function init_trusteddomains(): myp\property_bool {
		$caption = gettext('Allow trusted domains.');
		$description = gettext('If allowed, a user of the trusted domains can access the share.');
		$property = parent::init_trusteddomains();
		$property->
			set_caption($caption)->
			set_description($description)->
			set_id('trusteddomains')->
			filter_use_default();
		return $property;
	}
	public function init_forcegroup(): myp\property_list {
		$description = gettext('This specifies a UNIX group name that will be assigned as the default primary group for all users connecting to this service. This is useful for sharing files by ensuring that all access to files on service will use the named group for their permissions checking.');
		$property = parent::init_forcegroup();
		$property->
			set_defaultvalue('')->
			set_description($description)->
			set_id('forcegroup')->
			set_input_type($property::INPUT_TYPE_SELECT)->
			filter_use_default();
		return $property;
	}
	public function init_forceuser(): myp\property_list {
		$description = gettext('This specifies a UNIX user name that will be assigned as the default user for all users connecting to this service. This is useful for sharing files. You should also use it carefully as using it incorrectly can cause security problems.');
		$property = parent::init_forceuser();
		$property->
			set_defaultvalue('')->
			set_description($description)->
			set_id('forceuser')->
			set_input_type($property::INPUT_TYPE_SELECT)->
			filter_use_default();
		return $property;
	}
	public function init_guestaccount(): myp\property_list {
		$description = gettext('Use this option to override the username ("ftp" by default) which will be used for access to services which are specified as guest. Whatever privileges this user has will be available to any client connecting to the guest service. This user must exist in the password file, but does not require a valid login.');
		$property = parent::init_guestaccount();
		$property->
		set_defaultvalue('ftp')->
			set_description($description)->
			set_id('guestaccount')->
			set_input_type($property::INPUT_TYPE_SELECT)->
			filter_use_default();
		return $property;
	}
	public function init_maptoguest(): myp\property_list {
		$description = '';
		$options = [
			'Never' => gettext('Never - (Default)'),
			'Bad User' => gettext('Bad User - (Non Existing Users)')
		];
		$property = parent::init_maptoguest();
		$property->
			set_defaultvalue('Never')->
			set_description($description)->
			set_id('maptoguest')->
			set_input_type($property::INPUT_TYPE_SELECT)->
			set_options($options)->
			filter_use_default();
		return $property;
	}
	public function init_createmask(): myp\property_text {
		$description = gettext('Use this option to override the file creation mask (0666 by default).');
		$property = parent::init_createmask();
		$property->
			set_defaultvalue('')->
			set_description($description)->
			set_id('createmask')->
			set_maxlength(4)->
			set_size(5)->
			filter_use_default_or_empty()->
			set_filter(FILTER_VALIDATE_REGEXP)->
			set_filter_flags(FILTER_REQUIRE_SCALAR)->
			set_filter_options(['default' => null,'regexp' => '/^(|0[0-7]{3})$/']);
		return $property;
	}
	public function init_directorymask(): myp\property_text {
		$description = gettext('Use this option to override the directory creation mask (0777 by default).');
		$property = parent::init_directorymask();
		$property->
			set_defaultvalue('')->
			set_description($description)->
			set_id('directorymask')->
			set_maxlength(4)->
			set_size(5)->
			filter_use_default_or_empty()->
			set_filter(FILTER_VALIDATE_REGEXP)->
			set_filter_flags(FILTER_REQUIRE_SCALAR)->
			set_filter_options(['default' => null,'regexp' => '/^(|0[0-7]{3})$/']);
		return $property;
	}
	public function init_sndbuf(): myp\property_int {
		$description = sprintf(gettext('Size of send buffer (%d by default).'),65536);
		$property = parent::init_sndbuf();
		$property->
			set_defaultvalue('')->
			set_description($description)->
			set_id('sndbuf')->
			set_min(1)->
			set_size(20)->
			filter_use_default_or_empty();
		return $property;
	}
	public function init_rcvbuf(): myp\property_int {
		$description = sprintf(gettext('Size of receive buffer (%d by default).'),65536);
		$property = parent::init_rcvbuf();
		$property->
			set_description($description)->
			set_id('rcvbuf')->
			set_min(1)->
			set_size(20)->
			filter_use_default_or_empty();
		return $property;
	}
	public function init_largereadwrite(): myp\property_bool {
		$caption = gettext('Enable large read/write.');
		$description = gettext('Use the new 64k streaming read and write variant SMB requests introduced with Windows 2000.');
		$property = parent::init_largereadwrite();
		$property->
			set_caption($caption)->
			set_description($description)->
			set_id('largereadwrite')->
			filter_use_default();
		return $property;
	}
	public function init_easupport(): myp\property_bool {
		$caption = gettext('Enable extended attribute support.');
		$description = gettext('Allow clients to attempt to store OS/2 style extended attributes on a share.');
		$property = parent::init_easupport();
		$property->
			set_caption($caption)->
			set_description($description)->
			set_id('easupport')->
			filter_use_default();
		return $property;
	}
	public function init_storedosattributes(): myp\property_bool {
		$caption = gettext('Enable store DOS attributes.');
		$description = gettext('If this parameter is set, Samba attempts to first read DOS attributes (SYSTEM, HIDDEN, ARCHIVE or READ-ONLY) from a filesystem extended attribute, before mapping DOS attributes to UNIX permission bits. When set, DOS attributes will be stored onto an extended attribute in the UNIX filesystem, associated with the file or directory.');
		$property = parent::init_storedosattributes();
		$property->
			set_caption($caption)->
			set_description($description)->
			set_id('storedosattributes')->
			filter_use_default();
		return $property;
	}
	public function init_mapdosattributes(): myp\property_bool {
		$caption = gettext('Enable mapping DOS attributes.');
		$description = gettext('Convert DOS attributes to UNIX execution bits when Store DOS attributes is disabled.');
		$property = parent::init_mapdosattributes();
		$property->
			set_caption($caption)->
			set_description($description)->
			set_id('mapdosattributes')->
			filter_use_default();
		return $property;
	}
	public function init_aio(): myp\property_bool {
		$caption = gettext('Enable Asynchronous I/O.');
		$property = parent::init_aio();
		$property->
			set_caption($caption)->
			set_id('aio')->
			filter_use_default();
		return $property;
	}
	public function init_aiorsize(): myp\property_int {
		$description = sprintf(gettext('Samba will read from file asynchronously when size of request is bigger than this value. (%d by default).'),1024);
		$property = parent::init_aiorsize();
		$property->
			set_description($description)->
			set_id('aiorsize')->
			set_size(20)->
			filter_use_default();
		return $property;
	}
	public function init_aiowsize(): myp\property_int {
		$description = sprintf(gettext('Samba will write to file asynchronously when size of request is bigger than this value. (%d by default).'),1024);
		$property = parent::init_aiowsize();
		$property->
			set_description($description)->
			set_id('aiowsize')->
			set_size(20)->
			filter_use_default();
		return $property;
	}
	public function init_auxparam(): myp\property_auxparam {
		$helpinghand = '<a href="https://www.samba.org/samba/docs/current/man-html/smb.conf.5.html" target="_blank">'
			. gettext('Please check the documentation')
			. '</a>.';
		$description = sprintf('%s %s',sprintf(gettext('These parameters are added to [Global] section of %s.'),'smb4.conf'),$helpinghand);
		$property = parent::init_auxparam();
		$property->
			set_description($description);
		return $property;
	}
}
