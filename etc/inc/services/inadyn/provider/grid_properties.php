<?php
/*
	grid_properties.php

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

namespace services\inadyn\provider;

use common\properties as myp;

class grid_properties extends myp\container_row {
	protected $x_recordtype;
	public function init_recordtype(): myp\property_list {
		$options = [
			'provider' => gettext('Standard Provider'),
			'custom' => gettext('Custom Provider')
		];
		$title = gettext('Record Type');
		$property = $this->x_recordtype = new myp\property_list($this);
		$property->
			set_name('recordtype')->
			set_options($options)->
			set_title($title);
		return $property;
	}
	final public function get_recordtype(): myp\property_list {
		return $this->x_recordtype ?? $this->init_recordtype();
	}
	protected $x_provider;
	public function init_provider(): myp\property_list {
		$options = [
			'default@freedns.afraid.org' => 'freedns.afraid.org',
			'ipv4@nsupdate.info' => 'nsupdate.info',
			'default@duckdns.org' => 'duckdns.org',
			'default@freemyip.com' => 'freemyip.com',
			'default@loopia.com' => 'www.loopia.com',
			'default@dyndns.org' => 'www.dyndns.org',
			'default@noip.com' => 'www.noip.com',
			'default@no-ip.com' => 'www.no-ip.com',
			'default@easydns.com' => 'www.easydns.com',
			'default@dnsomatic.com' => 'www.dnsomatic.com',
			'dyndns@he.net' => 'dns.he.net',
			'default@tunnelbroker.netIPv6 ' => 'www.tunnelbroker.net',
			'default@sitelutions.com' => 'www.sitelutions.com',
			'default@dnsexit.com' => 'www.dnsexit.com',
			'default@zoneedit.com' => 'zoneedit.com',
			'default@changeip.com' => 'www.changeip.com',
			'default@dhis.org' => 'www.dhis.org',
			'default@domains.google.com' => 'domains.google',
			'default@ovh.com' => 'www.ovh.com',
			'default@gira.de' => 'giradns.com',
			'default@duiadns.net' => 'www.duiadns.net',
			'default@ddnss.de' => 'ddnss.de',
			'default@dynv6.com' => 'dynv6.com',
			'default@ipv4.dynv6.com' => 'ipv4.dynv6.com',
			'default@spdyn.de' => 'spdyn.de',
			'default@strato.com' => 'www.strato.com',
			'default@cloudxns.net' => 'www.cloudxns.net',
			'dyndns@3322.org' => 'www.3322.org',
			'default@dnspod.cn' => 'www.dnspod.cn',
			'default@dynu.com' => 'www.dynu.com',
			'default@selfhost.de' => 'www.selfhost.de',
			'default@pdd.yandex.ru' => 'connect.yandex.ru',
			'default@cloudflare.com' => 'www.cloudflare.com'
		];
		$title = gettext('Provider');
		$property = $this->x_provider = new myp\property_list($this);
		$property->
			set_name('provider')->
			set_options($options)->
			set_title($title);
		return $property;
	}
	final public function get_provider(): myp\property_list {
		return $this->x_provider ?? $this->init_provider();
	}
	protected $x_customprovider;
	public function init_customprovider(): myp\property_text {
		$title = gettext('Provider Name');
		$property = $this->x_customprovider = new myp\property_text($this);
		$property->
			set_name('customprovider')->
			set_title($title);
		return $property;
	}
	final public function get_customprovider(): myp\property_text {
		return $this->x_customprovider ?? $this->init_customprovider();
	}
	protected $x_identifier;
	public function init_identifier(): myp\property_text {
		$title = gettext('Identifier');
		$property = $this->x_identifier = new myp\property_text($this);
		$property->
			set_name('identifier')->
			set_title($title);
		return $property;
	}
	final public function get_identifier(): myp\property_text {
		return $this->x_identifier ?? $this->init_identifier();
	}
	protected $x_hostnames;
	public function init_hostnames(): myp\property_text {
		$title = gettext('Host Names');
		$property = $this->x_hostnames = new myp\property_text($this);
		$property->
			set_name('hostnames')->
			set_title($title);
		return $property;
	}
	final public function get_hostnames(): myp\property_text {
		return $this->x_hostnames ?? $this->init_hostnames();
	}
	protected $x_username;
	public function init_username(): myp\property_text {
		$title = gettext('Username');
		$property = $this->x_username = new myp\property_text($this);
		$property->
			set_name('username')->
			set_title($title);
		return $property;
	}
	final public function get_username(): myp\property_text {
		return $this->x_username ?? $this->init_username();
	}
	protected $x_password;
	public function init_password(): myp\property_text {
		$title = gettext('Password');
		$property = $this->x_password = new myp\property_text($this);
		$property->
			set_name('password')->
			set_title($title);
		return $property;
	}
	final public function get_password(): myp\property_text {
		return $this->x_password ?? $this->init_password();
	}
	protected $x_wildcard;
	public function init_wildcard(): myp\property_bool {
		$title = gettext('Wildcard Domain');
		$property = $this->x_wildcard = new myp\property_bool($this);
		$property->
			set_name('wildcard')->
			set_title($title);
		return $property;
	}
	final public function get_wildcard(): myp\property_bool {
		return $this->x_wildcard ?? $this->init_wildcard();
	}
	protected $x_usessl;
	public function init_usessl(): myp\property_bool {
		$title = gettext('Use HTTPS');
		$property = $this->x_usessl = new myp\property_bool($this);
		$property->
			set_name('usessl')->
			set_title($title);
		return $property;
	}
	final public function get_usessl(): myp\property_bool {
		return $this->x_usessl ?? $this->init_usessl();
	}
	protected $x_checkip_server;
	public function init_checkip_server(): myp\property_text {
		$title = gettext('Check IP Server');
		$property = $this->x_checkip_server = new myp\property_text($this);
		$property->
			set_name('checkipserver')->
			set_title($title);
		return $property;
	}
	final public function get_checkip_server(): myp\property_text {
		return $this->x_checkip_server ?? $this->init_checkip_server();
	}
	protected $x_checkip_path;
	public function init_checkip_path(): myp\property_text {
		$title = gettext('Check IP Path');
		$property = $this->x_checkip_path = new myp\property_text($this);
		$property->
			set_name('checkippath')->
			set_title($title);
		return $property;
	}
	final public function get_checkip_path(): myp\property_text {
		return $this->x_checkip_path ?? $this->init_checkip_path();
	}
	protected $x_checkip_ssl;
	public function init_checkip_ssl(): myp\property_bool {
		$title = gettext('Check IP using HTTPS');
		$property = $this->x_checkip_ssl = new myp\property_bool($this);
		$property->
			set_name('checkipssl')->
			set_title($title);
		return $property;
	}
	final public function get_checkip_ssl(): myp\property_bool {
		return $this->x_checkip_ssl ?? $this->init_checkip_ssl();
	}
	protected $x_checkip_command;
	public function init_checkip_command(): myp\property_text {
		$title = gettext('Check IP Command');
		$property = $this->x_checkip_command = new myp\property_text($this);
		$property->
			set_name('checkipcommand')->
			set_title($title);
		return $property;
	}
	final public function get_checkip_command(): myp\property_text {
		return $this->x_checkip_command ?? $this->init_checkip_command();
	}
	protected $x_useragent;
	public function init_useragent(): myp\property_text {
		$title = gettext('User Agent');
		$property = $this->x_useragent = new myp\property_text($this);
		$property->
			set_name('useragent')->
			set_title($title);
		return $property;
	}
	final public function get_useragent(): myp\property_text {
		return $this->x_useragent ?? $this->init_useragent();
	}
	protected $x_ddns_server;
	public function init_ddns_server(): myp\property_text {
		$title = gettext('DDNS-Server');
		$property = $this->x_ddns_server = new myp\property_text($this);
		$property->
			set_name('ddnsserver')->
			set_title($title);
		return $property;
	}
	final public function get_ddns_server(): myp\property_text {
		return $this->x_ddns_server ?? $this->init_ddns_server();
	}
	protected $x_ddns_path;
	public function init_ddns_path(): myp\property_text {
		$title = gettext('DDNS-Path');
		$property = $this->x_ddns_path = new myp\property_text($this);
		$property->
			set_name('ddnspath')->
			set_title($title);
		return $property;
	}
	final public function get_ddns_path(): myp\property_text {
		return $this->x_ddns_path ?? $this->init_ddns_path();
	}
	protected $x_append_myip;
	public function init_append_myip(): myp\property_bool {
		$title = gettext('Append my IP');
		$property = $this->x_append_myip = new myp\property_bool($this);
		$property->
			set_name('appendmyip')->
			set_title($title);
		return $property;
	}
	final public function get_append_myip(): myp\property_bool {
		return $this->x_append_myip ?? $this->init_append_myip();
	}
	protected $x_auxparam;
	public function init_auxparam(): myp\property_auxparam {
		$property = $this->x_auxparam = new myp\property_auxparam($this);
		return $property;
	}
	final public function get_auxparam(): myp\property_auxparam {
		return $this->x_auxparam ?? $this->init_auxparam();
	}
}
