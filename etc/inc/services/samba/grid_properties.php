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

namespace services\samba;

use common\properties as myp;

class grid_properties extends myp\container {
	protected $x_enable;
	public function init_enable(): myp\property_enable {
		$property = $this->x_enable = new myp\property_enable($this);
		return $property;
	}
	final public function get_enable(): myp\property_enable {
		return $this->x_enable ?? $this->init_enable();
	}
	protected $x_security;
	public function init_security(): myp\property_list {
		$property = $this->x_security = new myp\property_list($this);
		$property->
			set_name('security')->
			set_title(gettext('Authentication'));
		return $property;
	}
	final public function get_security(): myp\property_list {
		return $this->x_security ?? $this->init_security();
	}
	protected $x_netbiosname;
	public function init_netbiosname(): myp\property_text {
		$property = $this->x_netbiosname = new myp\property_text($this);
		$property->
			set_name('netbiosname')->
			set_title(gettext('NetBIOS Name'));
		return $property;
	}
	final public function get_netbiosname(): myp\property_text {
		return $this->x_netbiosname ?? $this->init_netbiosname();
	}
	protected $x_workgroup;
	public function init_workgroup(): myp\property_text {
		$property = $this->x_workgroup = new myp\property_text($this);
		$property->
			set_name('workgroup')->
			set_title(gettext('Workgroup'));
		return $property;
	}
	final public function get_workgroup(): myp\property_text {
		return $this->x_workgroup ?? $this->init_workgroup();
	}
	protected $x_if;
	public function init_if(): myp\property_list {
		$property = $this->x_if = new myp\property_list($this);
		$property->
			set_name('if')->
			set_title(gettext('Interface Selection'));
		return $property;
	}
	final public function get_if(): myp\property_list {
		return $this->x_if ?? $this->init_if();
	}
	protected $x_serverdesc;
	public function init_serverdesc(): myp\property_text {
		$property = $this->x_serverdesc = new myp\property_text($this);
		$property->
			set_name('serverdesc')->
			set_title(gettext('Description'));
		return $property;
	}
	final public function get_serverdesc(): myp\property_text {
		return $this->x_serverdesc ?? $this->init_serverdesc();
	}
	protected $x_doscharset;
	public function init_doscharset(): myp\property_list {
		$property = $this->x_doscharset = new myp\property_list($this);
		$property->
			set_name('doscharset')->
			set_title(gettext('DOS Charset'));
		return $property;
	}
	final public function get_doscharset(): myp\property_list {
		return $this->x_doscharset ?? $this->init_doscharset();
	}
	protected $x_unixcharset;
	public function init_unixcharset(): myp\property_list {
		$property = $this->x_unixcharset = new myp\property_list($this);
		$property->
			set_name('unixcharset')->
			set_title(gettext('Unix Charset'));
		return $property;
	}
	final public function get_unixcharset(): myp\property_list {
		return $this->x_unixcharset ?? $this->init_unixcharset();
	}
	protected $x_loglevel;
	public function init_loglevel(): myp\property_list {
		$property = $this->x_loglevel = new myp\property_list($this);
		$property->
			set_name('loglevel')->
			set_title(gettext('Log Level'));
		return $property;
	}
	final public function get_loglevel(): myp\property_list {
		return $this->x_loglevel ?? $this->init_loglevel();
	}
	protected $x_localmaster;
	public function init_localmaster(): myp\property_list {
		$property = $this->x_localmaster = new myp\property_list($this);
		$property->
			set_name('localmaster')->
			set_title(gettext('Local Master Browser'));
		return $property;
	}
	final public function get_localmaster(): myp\property_list {
		return $this->x_localmaster ?? $this->init_localmaster();
	}
	protected $x_timesrv;
	public function init_timesrv(): myp\property_list {
		$property = $this->x_timesrv = new myp\property_list($this);
		$property->
			set_name('timesrv')->
			set_title(gettext('Time Server'));
		return $property;
	}
	final public function get_timesrv(): myp\property_list {
		return $this->x_timesrv ?? $this->init_timesrv();
	}
	protected $x_pwdsrv;
	public function init_pwdsrv(): myp\property_text {
		$property = $this->x_pwdsrv = new myp\property_text($this);
		$property->
			set_name('pwdsrv')->
			set_title(gettext('Password Server'));
		return $property;
	}
	final public function get_pwdsrv(): myp\property_text {
		return $this->x_pwdsrv ?? $this->init_pwdsrv();
	}
	protected $x_winssrv;
	public function init_winssrv(): myp\property_text {
		$property = $this->x_winssrv = new myp\property_text($this);
		$property->
			set_name('winssrv')->
			set_title(gettext('WINS Server'));
		return $property;
	}
	final public function get_winssrv(): myp\property_text {
		return $this->x_winssrv ?? $this->init_winssrv();
	}
	protected $x_trusteddomains;
	public function init_trusteddomains(): myp\property_bool {
		$property = $this->x_trusteddomains = new myp\property_bool($this);
		$property->
			set_name('trusteddomains')->
			set_title(gettext('Trusted Domains'));
		return $property;
	}
	final public function get_trusteddomains(): myp\property_bool {
		return $this->x_trusteddomains ?? $this->init_trusteddomains();
	}
	protected $x_forcegroup;
	public function init_forcegroup(): myp\property_list {
		$property = $this->x_forcegroup = new myp\property_list($this);
		$property->
			set_name('forcegroup')->
			set_title(gettext('Force Group'));
		return $property;
	}
	final public function get_forcegroup(): myp\property_list {
		return $this->x_forcegroup ?? $this->init_forcegroup();
	}
	protected $x_forceuser;
	public function init_forceuser(): myp\property_list {
		$property = $this->x_forceuser = new myp\property_list($this);
		$property->
			set_name('forceuser')->
			set_title(gettext('Force User'));
		return $property;
	}
	final public function get_forceuser(): myp\property_list {
		return $this->x_forceuser ?? $this->init_forceuser();
	}
	protected $x_guestaccount;
	public function init_guestaccount(): myp\property_list {
		$property = $this->x_guestaccount = new myp\property_list($this);
		$property->
			set_name('guestaccount')->
			set_title(gettext('Guest Account'));
		return $property;
	}
	final public function get_guestaccount(): myp\property_list {
		return $this->x_guestaccount ?? $this->init_guestaccount();
	}
	protected $x_maptoguest;
	public function init_maptoguest(): myp\property_list {
		$property = $this->x_maptoguest = new myp\property_list($this);
		$property->
			set_name('maptoguest')->
			set_title(gettext('Map to Guest'));
		return $property;
	}
	final public function get_maptoguest(): myp\property_list {
		return $this->x_maptoguest ?? $this->init_maptoguest();
	}
	protected $x_createmask;
	public function init_createmask(): myp\property_text {
		$property = $this->x_createmask = new myp\property_text($this);
		$property->
			set_name('createmask')->
			set_title(gettext('Create Mask'));
		return $property;
	}
	final public function get_createmask(): myp\property_text {
		return $this->x_createmask ?? $this->init_createmask();
	}
	protected $x_directorymask;
	public function init_directorymask(): myp\property_text {
		$property = $this->x_directorymask = new myp\property_text($this);
		$property->
			set_name('directorymask')->
			set_title(gettext('Directory Mask'));
		return $property;
	}
	final public function get_directorymask(): myp\property_text {
		return $this->x_directorymask ?? $this->init_directorymask();
	}
	protected $x_sndbuf;
	public function init_sndbuf(): myp\property_int {
		$property = $this->x_sndbuf = new myp\property_int($this);
		$property->
			set_name('sndbuf')->
			set_title(gettext('Send Buffer'));
		return $property;
	}
	final public function get_sndbuf(): myp\property_int {
		return $this->x_sndbuf ?? $this->init_sndbuf();
	}
	protected $x_rcvbuf;
	public function init_rcvbuf(): myp\property_int {
		$property = $this->x_rcvbuf = new myp\property_int($this);
		$property->
			set_name('rcvbuf')->
			set_title(gettext('Receive Buffer'));
		return $property;
	}
	final public function get_rcvbuf(): myp\property_int {
		return $this->x_rcvbuf ?? $this->init_rcvbuf();
	}
	protected $x_largereadwrite;
	public function init_largereadwrite(): myp\property_bool {
		$property = $this->x_largereadwrite = new myp\property_bool($this);
		$property->
			set_name('largereadwrite')->
			set_title(gettext('Large Read/Write'));
		return $property;
	}
	final public function get_largereadwrite(): myp\property_bool {
		return $this->x_largereadwrite ?? $this->init_largereadwrite();
	}
	protected $x_easupport;
	public function init_easupport(): myp\property_bool {
		$property = $this->x_easupport = new myp\property_bool($this);
		$property->
			set_name('easupport')->
			set_title(gettext('EA Support'));
		return $property;
	}
	final public function get_easupport(): myp\property_bool {
		return $this->x_easupport ?? $this->init_easupport();
	}
	protected $x_storedosattributes;
	public function init_storedosattributes(): myp\property_bool {
		$property = $this->x_storedosattributes = new myp\property_bool($this);
		$property->
			set_name('storedosattributes')->
			set_title(gettext('Store DOS Attributes'));
		return $property;
	}
	final public function get_storedosattributes(): myp\property_bool {
		return $this->x_storedosattributes ?? $this->init_storedosattributes();
	}
	protected $x_mapdosattributes;
	public function init_mapdosattributes(): myp\property_bool {
		$property = $this->x_mapdosattributes = new myp\property_bool($this);
		$property->
			set_name('mapdosattributes')->
			set_title(gettext('Mapping DOS Attributes'));
		return $property;
	}
	final public function get_mapdosattributes(): myp\property_bool {
		return $this->x_mapdosattributes ?? $this->init_mapdosattributes();
	}
	protected $x_aio;
	public function init_aio(): myp\property_bool {
		$property = $this->x_aio = new myp\property_bool($this);
		$property->
			set_name('aio')->
			set_title(gettext('Asynchronous I/O'));
		return $property;
	}
	final public function get_aio(): myp\property_bool {
		return $this->x_aio ?? $this->init_aio();
	}
	protected $x_aiorsize;
	public function init_aiorsize(): myp\property_int {
		$property = $this->x_aiorsize = new myp\property_int($this);
		$property->
			set_name('aiorsize')->
			set_title(gettext('AIO Read Size'));
		return $property;
	}
	final public function get_aiorsize(): myp\property_int {
		return $this->x_aiorsize ?? $this->init_aiorsize();
	}
	protected $x_aiowsize;
	public function init_aiowsize(): myp\property_int {
		$property = $this->x_aiowsize = new myp\property_int($this);
		$property->
			set_name('aiowsize')->
			set_title(gettext('AIO Write Size'));
		return $property;
	}
	final public function get_aiowsize(): myp\property_int {
		return $this->x_aiowsize ?? $this->init_aiowsize();
	}
	protected $x_maxprotocol;
	public function init_maxprotocol(): myp\property_list {
		$property = $this->x_maxprotocol = new myp\property_list($this);
		$property->
			set_name('maxprotocol')->
			set_title(gettext('Server Max. Protocol'));
		return $property;
	}
	final public function get_maxprotocol(): myp\property_list {
		return $this->x_maxprotocol ?? $this->init_maxprotocol();
	}
	protected $x_minprotocol;
	public function init_minprotocol(): myp\property_list {
		$property = $this->x_minprotocol = new myp\property_list($this);
		$property->
			set_name('minprotocol')->
			set_title(gettext('Server Min. Protocol'));
		return $property;
	}
	final public function get_minprotocol(): myp\property_list {
		return $this->x_minprotocol ?? $this->init_minprotocol();
	}
	protected $x_clientmaxprotocol;
	public function init_clientmaxprotocol(): myp\property_list {
		$property = $this->x_clientmaxprotocol = new myp\property_list($this);
		$property->
			set_name('clientmaxprotocol')->
			set_title(gettext('Client Max. Protocol'));
		return $property;
	}
	final public function get_clientmaxprotocol(): myp\property_list {
		return $this->x_clientmaxprotocol ?? $this->init_clientmaxprotocol();
	}
	protected $x_clientminprotocol;
	public function init_clientminprotocol(): myp\property_list {
		$property = $this->x_clientminprotocol = new myp\property_list($this);
		$property->
			set_name('clientminprotocol')->
			set_title(gettext('Client Min. Protocol'));
		return $property;
	}
	final public function get_clientminprotocol(): myp\property_list {
		return $this->x_clientminprotocol ?? $this->init_clientminprotocol();
	}
	protected $x_serversigning;
	public function init_serversigning(): myp\property_list {
		$property = $this->x_serversigning = new myp\property_list($this);
		$property->
			set_name('serversigning')->
			set_title(gettext('Server Signing'));
		return $property;
	}
	final public function get_serversigning(): myp\property_list {
		return $this->x_serversigning ?? $this->init_serversigning();
	}
	protected $x_serversmbencrypt;
	public function init_serversmbencrypt(): myp\property_list {
		$property = $this->x_serversmbencrypt = new myp\property_list($this);
		$property->
			set_name('serversmbencrypt')->
			set_title(gettext('Server SMB Encryption'));
		return $property;
	}
	final public function get_serversmbencrypt(): myp\property_list {
		return $this->x_serversmbencrypt ?? $this->init_serversmbencrypt();
	}
	protected $x_clientsmbencrypt;
	public function init_clientsmbencrypt(): myp\property_list {
		$property = $this->x_clientsmbencrypt = new myp\property_list($this);
		$property->
			set_name('clientsmbencrypt')->
			set_title(gettext('Client SMB Encryption'));
		return $property;
	}
	final public function get_clientsmbencrypt(): myp\property_list {
		return $this->x_clientsmbencrypt ?? $this->init_clientsmbencrypt();
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
