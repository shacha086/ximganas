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

namespace services\samba\share;

use common\properties as myp;

class grid_properties extends myp\container_row {
	protected $x_afpcompat;
	public function init_afpcompat(): myp\property_bool {
		$property = $this->x_afpcompat = new myp\property_bool($this);
		$property->
			set_name('afpcompat')->
			set_title(gettext('Enable AFP'));
		return $property;
	}
	final public function get_afpcompat(): myp\property_bool {
		return $this->x_afpcompat ?? $this->init_afpcompat();
	}
	protected $x_auxparam;
	public function init_auxparam(): myp\property_auxparam {
		$property = $this->x_auxparam = new myp\property_auxparam($this);
		return $property;
	}
	final public function get_auxparam(): myp\property_auxparam {
		return $this->x_auxparam ?? $this->init_auxparam();
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
	protected $x_shadowformat;
	public function init_shadowformat(): myp\property_text {
		$property = $this->x_shadowformat = new myp\property_text($this);
		$property->
			set_name('shadowformat')->
			set_title(gettext('Shadow Copy Format'));
		return $property;
	}
	final public function get_shadowformat(): myp\property_text {
		return $this->x_shadowformat ?? $this->init_shadowformat();
	}
	protected $x_vfs_fruit_encoding;
	public function init_vfs_fruit_encoding(): myp\property_list {
		$property = $this->x_vfs_fruit_encoding = new myp\property_list($this);
		$property->
			set_name('vfs_fruit_encoding')->
			set_title(gettext('VFS fruit:encoding'));
		return $property;
	}
	final public function get_vfs_fruit_encoding(): myp\property_list {
		return $this->x_vfs_fruit_encoding ?? $this->init_vfs_fruit_encoding();
	}
	protected $x_vfs_fruit_locking;
	public function init_vfs_fruit_locking(): myp\property_list {
		$property = $this->x_vfs_fruit_locking = new myp\property_list($this);
		$property->
			set_name('vfs_fruit_locking')->
			set_title(gettext('VFS fruit:locking'));
		return $property;
	}
	final public function get_vfs_fruit_locking(): myp\property_list {
		return $this->x_vfs_fruit_locking ?? $this->init_vfs_fruit_locking();
	}
	protected $x_vfs_fruit_metadata;
	public function init_vfs_fruit_metadata(): myp\property_list {
		$property = $this->x_vfs_fruit_metadata = new myp\property_list($this);
		$property->
			set_name('vfs_fruit_metadata')->
			set_title(gettext('VFS fruit:metadata'));
		return $property;
	}
	final public function get_vfs_fruit_metadata(): myp\property_list {
		return $this->x_vfs_fruit_metadata ?? $this->init_vfs_fruit_metadata();
	}
	protected $x_vfs_fruit_resource;
	public function init_vfs_fruit_resource(): myp\property_list {
		$property = $this->x_vfs_fruit_resource = new myp\property_list($this);
		$property->
			set_name('vfs_fruit_resource')->
			set_title(gettext('VFS fruit:resource'));
		return $property;
	}
	final public function get_vfs_fruit_resource(): myp\property_list {
		return $this->x_vfs_fruit_resource ?? $this->init_vfs_fruit_resource();
	}
	protected $x_vfs_fruit_time_machine;
	public function init_vfs_fruit_time_machine(): myp\property_list {
		$property = $this->x_vfs_fruit_time_machine = new myp\property_list($this);
		$property->
			set_name('vfs_fruit_time_machine')->
			set_title(gettext('VFS fruit:time machine'));
		return $property;
	}
	final public function get_vfs_fruit_time_machine(): myp\property_list {
		return $this->x_vfs_fruit_time_machine ?? $this->init_vfs_fruit_time_machine();
	}
	protected $x_zfsacl;
	public function init_zfsacl(): myp\property_bool {
		$property = $this->x_zfsacl = new myp\property_bool($this);
		$property->
			set_name('zfsacl')->
			set_title(gettext('ZFS ACL'));
		return $property;
	}
	final public function get_zfsacl(): myp\property_bool {
		return $this->x_zfsacl ?? $this->init_zfsacl();
	}
	protected $x_inheritacls;
	public function init_inheritacls(): myp\property_bool {
		$property = $this->x_inheritacls = new myp\property_bool($this);
		$property->
			set_name('inheritacls')->
			set_title(gettext('Inherit ACL'));
		return $property;
	}
	final public function get_inheritacls(): myp\property_bool {
		return $this->x_inheritacls ?? $this->init_inheritacls();
	}
	protected $x_storealternatedatastreams;
	public function init_storealternatedatastreams(): myp\property_bool {
		$property = $this->x_storealternatedatastreams = new myp\property_bool($this);
		$property->
			set_name('storealternatedatastreams')->
			set_title(gettext('Alternate Data Streams'));
		return $property;
	}
	final public function get_storealternatedatastreams(): myp\property_bool {
		return $this->x_storealternatedatastreams ?? $this->init_storealternatedatastreams();
	}
	protected $x_storentfsacls;
	public function init_storentfsacls(): myp\property_bool {
		$property = $this->x_storentfsacls = new myp\property_bool($this);
		$property->
			set_name('storentfsacls')->
			set_title(gettext('NTFS ACLs'));
		return $property;
	}
	final public function get_storentfsacls(): myp\property_bool {
		return $this->x_storentfsacls ?? $this->init_storentfsacls();
	}
	protected $x_hostsallow;
	public function init_hostsallow(): myp\property_text {
		$property = $this->x_hostsallow = new myp\property_text($this);
		$property->
			set_name('hostsallow')->
			set_title(gettext('Hosts Allow'));
		return $property;
	}
	final public function get_hostsallow(): myp\property_text {
		return $this->x_hostsallow ?? $this->init_hostsallow();
	}
	protected $x_hostsdeny;
	public function init_hostsdeny(): myp\property_text {
		$property = $this->x_hostsdeny = new myp\property_text($this);
		$property->
			set_name('hostsdeny')->
			set_title(gettext('Hosts Deny'));
		return $property;
	}
	final public function get_hostsdeny(): myp\property_text {
		return $this->x_hostsdeny ?? $this->init_hostsdeny();
	}
	protected $x_shadowcopy;
	public function init_shadowcopy(): myp\property_bool {
		$property = $this->x_shadowcopy = new myp\property_bool($this);
		$property->
			set_name('shadowcopy')->
			set_title(gettext('Shadow Copy'));
		return $property;
	}
	final public function get_shadowcopy(): myp\property_bool {
		return $this->x_shadowcopy ?? $this->init_shadowcopy();
	}
	protected $x_readonly;
	public function init_readonly(): myp\property_bool {
		$property = $this->x_readonly = new myp\property_bool($this);
		$property->
			set_name('readonly')->
			set_title(gettext('Read Only'));
		return $property;
	}
	final public function get_readonly(): myp\property_bool {
		return $this->x_readonly ?? $this->init_readonly();
	}
	protected $x_browseable;
	public function init_browseable(): myp\property_bool {
		$property = $this->x_browseable = new myp\property_bool($this);
		$property->
			set_name('browseable')->
			set_title(gettext('Browseable'));
		return $property;
	}
	final public function get_browseable(): myp\property_bool {
		return $this->x_browseable ?? $this->init_browseable();
	}
	protected $x_guest;
	public function init_guest(): myp\property_bool {
		$property = $this->x_guest = new myp\property_bool($this);
		$property->
			set_name('guest')->
			set_title(gettext('Guest'));
		return $property;
	}
	final public function get_guest(): myp\property_bool {
		return $this->x_guest ?? $this->init_guest();
	}
	protected $x_guestonly;
	public function init_guestonly(): myp\property_bool {
		$property = $this->x_guestonly = new myp\property_bool($this);
		$property->
			set_name('guestonly')->
			set_title(gettext('Guest Only'));
		return $property;
	}
	final public function get_guestonly(): myp\property_bool {
		return $this->x_guestonly ?? $this->init_guestonly();
	}
	protected $x_inheritpermissions;
	public function init_inheritpermissions(): myp\property_bool {
		$property = $this->x_inheritpermissions = new myp\property_bool($this);
		$property->
			set_name('inheritpermissions')->
			set_title(gettext('Inherit Permissions'));
		return $property;
	}
	final public function get_inheritpermissions(): myp\property_bool {
		return $this->x_inheritpermissions ?? $this->init_inheritpermissions();
	}
	protected $x_recyclebin;
	public function init_recyclebin(): myp\property_bool {
		$property = $this->x_recyclebin = new myp\property_bool($this);
		$property->
			set_name('recyclebin')->
			set_title(gettext('Recycle Bin'));
		return $property;
	}
	final public function get_recyclebin(): myp\property_bool {
		return $this->x_recyclebin ?? $this->init_recyclebin();
	}
	protected $x_hidedotfiles;
	public function init_hidedotfiles(): myp\property_bool {
		$property = $this->x_hidedotfiles = new myp\property_bool($this);
		$property->
			set_name('hidedotfiles')->
			set_title(gettext('Hide Dot Files'));
		return $property;
	}
	final public function get_hidedotfiles(): myp\property_bool {
		return $this->x_hidedotfiles ?? $this->init_hidedotfiles();
	}
	protected $x_name;
	public function init_name(): myp\property_text {
		$property = $this->x_name = new myp\property_text($this);
		$property->
			set_name('name')->
			set_title(gettext('Name'));
		return $property;
	}
	final public function get_name(): myp\property_text {
		return $this->x_name ?? $this->init_name();
	}
	protected $x_comment;
	public function init_comment(): myp\property_text {
		$property = $this->x_comment = new myp\property_text($this);
		$property->
			set_name('comment')->
			set_title(gettext('Comment'));
		return $property;
	}
	final public function get_comment(): myp\property_text {
		return $this->x_comment ?? $this->init_comment();
	}
	protected $x_path;
	public function init_path(): myp\property_text {
		$property = $this->x_path = new myp\property_text($this);
		$property->
			set_name('path')->
			set_title(gettext('Path'));
		return $property;
	}
	final public function get_path(): myp\property_text {
		return $this->x_path ?? $this->init_path();
	}
}
