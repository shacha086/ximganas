<?php
/*
	row_properties.php

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

class row_properties extends grid_properties {
	public function init_afpcompat(): myp\property_bool {
		$caption = gettext('Enable');
		$property = parent::init_afpcompat();
		$property->
			set_caption($caption)->
			set_defaultvalue(false)->
			set_id('afpcompat')->
			set_input_type($property::INPUT_TYPE_TITLELINE_CHECKBOX)->
			filter_use_default();
		return $property;
	}
	public function init_auxparam(): myp\property_auxparam {
		$description =
			sprintf(gettext('These parameters are added to the [Share] section of %s.'),'smb4.conf') .
			' ' .
			sprintf('<a href="%s" target="_blank" rel="noreferrer">%s</a>.','https://www.samba.org/samba/docs/current/man-html/smb.conf.5.html',gettext('Please check the documentation'));
		$property = parent::init_auxparam();
		$property->
			set_description($description);
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
	public function init_shadowformat(): myp\property_text {
		$default_shadowformat = "auto-%Y%m%d-%H%M%S";
		$description = sprintf(gettext('The custom format of the snapshot for shadow copy service can be specified. The default format is %s used for ZFS auto snapshot.'),$default_shadowformat);
		$property = parent::init_shadowformat();
		$property->
			set_id('shadowformat')->
			set_description($description)->
			set_size(60)->
			set_maxlength(1024)->
			set_defaultvalue($default_shadowformat)->
			filter_use_default();
		return $property;
	}
	public function init_vfs_fruit_encoding(): myp\property_list {
		$description = gettext('Controls how the set of illegal NTFS ASCII character are stored in the filesystem, commonly used by OS X clients.');
		$options = [
			'native' => gettext('Native - store characters with their native ASCII value.'),
			'private' => gettext('Private (default) - store characters as encoded by the OS X client: mapped to the Unicode private range.')
		];
		$property = parent::init_vfs_fruit_encoding();
		$property->
			set_id('vfs_fruit_encoding')->
			set_options($options)->
			set_description($description)->
			set_defaultvalue('native')->
			filter_use_default();
		return $property;
	}
	public function init_vfs_fruit_locking(): myp\property_list {
		$description = '';
		$options = [
			'netatalk' => gettext('Netatalk - use cross protocol locking with Netatalk.'),
			'none' => gettext('None (default) - no cross protocol locking.')
		];
		$property = parent::init_vfs_fruit_locking();
		$property->
			set_id('vfs_fruit_locking')->
			set_options($options)->
			set_description($description)->
			set_defaultvalue('netatalk')->
			filter_use_default();
		return $property;
	}
	public function init_vfs_fruit_metadata(): myp\property_list {
		$description = gettext('Controls where the OS X metadata stream is stored.');
		$options = [
			'netatalk' => gettext('Netatalk (default) - use Netatalk compatible xattr.'),
			'stream' => gettext('Stream - pass the stream on to the next module in the VFS stack.')
		];
		$property = parent::init_vfs_fruit_metadata();
		$property->
			set_id('vfs_fruit_metadata')->
			set_options($options)->
			set_description($description)->
			set_defaultvalue('netatalk')->
			filter_use_default();
		return $property;
	}
	public function init_vfs_fruit_resource(): myp\property_list {
		$description = gettext('Controls where the OS X resource fork is stored.');
		$options = [
			'file' => gettext('File (default) - use a ._ AppleDouble file compatible with OS X and Netatalk.'),
			'xattr' => gettext('Extended Attributes - use a xattr.'),
			'stream' => gettext('Stream (experimental) - pass the stream on to the next module in the VFS stack.')
		];
		$property = parent::init_vfs_fruit_resource();
		$property->
			set_id('vfs_fruit_resource')->
			set_options($options)->
			set_description($description)->
			set_defaultvalue('file')->
			filter_use_default();
		return $property;
	}
	public function init_vfs_fruit_time_machine(): myp\property_list {
		$description = gettext('Controls if Time Machine support via the FULLSYNC volume capability is advertised to clients.');
		$options = [
			'yes' => gettext('Yes - Enables Time Machine support for this share.'),
			'no' => gettext('No (default) - Disables advertising Time Machine support.')
		];
		$property = parent::init_vfs_fruit_time_machine();
		$property->
			set_id('vfs_fruit_time_machine')->
			set_options($options)->
			set_description($description)->
			set_defaultvalue('no')->
			filter_use_default();
		return $property;
	}
	public function init_zfsacl(): myp\property_bool {
		$caption = gettext('Enable ZFS ACL.');
		$description = gettext('This will provide ZFS ACL support. (ZFS only).');
		$property = parent::init_zfsacl();
		$property->
			set_id('zfsacl')->
			set_caption($caption)->
			set_description($description)->
			set_defaultvalue(false)->
			filter_use_default();
		return $property;
	}
	public function init_inheritacls(): myp\property_bool {
		$caption = gettext('Enable ACL inheritance.');
		$description = '';
		$property = parent::init_inheritacls();
		$property->
			set_id('inheritacls')->
			set_caption($caption)->
			set_description($description)->
			set_defaultvalue(true)->
			filter_use_default();
		return $property;
	}
	public function init_storealternatedatastreams(): myp\property_bool {
		$caption = gettext('Store alternate data streams in Extended Attributes.');
		$description = '';
		$property = parent::init_storealternatedatastreams();
		$property->
			set_id('storealternatedatastreams')->
			set_caption($caption)->
			set_description($description)->
			set_defaultvalue(false)->
			filter_use_default();
		return $property;
	}
	public function init_storentfsacls(): myp\property_bool {
		$caption = gettext('Store NTFS ACLs in Extended Attributes.');
		$description = gettext('This will provide NTFS ACLs without ZFS ACL support such as UFS.');
		$property = parent::init_storentfsacls();
		$property->
			set_id('storentfsacls')->
			set_caption($caption)->
			set_description($description)->
			set_defaultvalue(false)->
			filter_use_default();
		return $property;
	}
	public function init_hostsallow(): myp\property_text {
		$description = gettext('This option is a comma, space, or tab delimited set of hosts which are permitted to access this share. You can specify the hosts by name or IP number. Leave this field empty to use default settings.');
		$property = parent::init_hostsallow();
		$property->
			set_id('hostsallow')->
			set_size(60)->
			set_maxlength(1024)->
			set_description($description)->
			set_defaultvalue('')->
			set_filter(FILTER_VALIDATE_REGEXP)->
			set_filter_flags(FILTER_REQUIRE_SCALAR)->
			set_filter_options(['default' => null,'regexp' => '/(^$|\S)/']);
		return $property;
	}
	public function init_hostsdeny(): myp\property_text {
		$description = gettext('This option is a comma, space, or tab delimited set of host which are NOT permitted to access this share. Where the lists conflict, the allow list takes precedence. In the event that it is necessary to deny all by default, use the keyword ALL (or the netmask 0.0.0.0/0) and then explicitly specify to the hosts allow parameter those hosts that should be permitted access. Leave this field empty to use default settings.');
		$property = parent::init_hostsdeny();
		$property->
			set_id('hostsdeny')->
			set_size(60)->
			set_maxlength(1024)->
			set_description($description)->
			set_defaultvalue('')->
			set_filter(FILTER_VALIDATE_REGEXP)->
			set_filter_flags(FILTER_REQUIRE_SCALAR)->
			set_filter_options(['default' => null,'regexp' => '/(^$|\S)/']);
		return $property;
	}
	public function init_shadowcopy(): myp\property_bool {
		$caption = gettext('Enable shadow copy.');
		$description = gettext('This will provide shadow copy created by auto snapshot. (ZFS only).');
		$property = parent::init_shadowcopy();
		$property->
			set_id('shadowcopy')->
			set_caption($caption)->
			set_description($description)->
			set_defaultvalue(true)->
			filter_use_default();
		return $property;
	}
	public function init_readonly(): myp\property_bool {
		$caption = gettext('Set read only.');
		$description = gettext('If this parameter is set, then users may not create or modify files in the share.');
		$property = parent::init_readonly();
		$property->
			set_id('readonly')->
			set_caption($caption)->
			set_description($description)->
			set_defaultvalue(false)->
			filter_use_default();
		return $property;
	}
	public function init_browseable(): myp\property_bool {
		$caption = gettext('Set browseable.');
		$description = gettext('This controls whether this share is seen in the list of available shares in a net view and in the browse list.');
		$property = parent::init_browseable();
		$property->
			set_id('browseable')->
			set_caption($caption)->
			set_description($description)->
			set_defaultvalue(true)->
			filter_use_default();
		return $property;
	}
	public function init_guest(): myp\property_bool {
		$caption = gettext('Enable guest access.');
//		$description = gettext('This controls whether this share is accessible by the guest account.');
		$property = parent::init_guest();
		$property->
			set_id('guest')->
			set_caption($caption)->
//			set_description($description)->
			set_defaultvalue(true)->
			filter_use_default();
		return $property;
	}
	public function init_guestonly(): myp\property_bool {
		$caption = gettext('Permit guest connections only.');
//		$description = gettext('This controls whether this share is accessible by the guest account only.');
		$property = parent::init_guestonly();
		$property->
			set_id('guestonly')->
			set_caption($caption)->
//			set_description($description)->
			set_defaultvalue(true)->
			filter_use_default();
		return $property;
	}
	public function init_inheritpermissions(): myp\property_bool {
		$caption = gettext('Enable permission inheritance.');
		$description = gettext('The permissions on new files and directories are normally governed by create mask and directory mask but the inherit permissions parameter overrides this. This can be particularly useful on systems with many users to allow a single share to be used flexibly by each user.');
		$property = parent::init_inheritpermissions();
		$property->
			set_id('inheritpermissions')->
			set_caption($caption)->
			set_description($description)->
			set_defaultvalue(true)->
			filter_use_default();
		return $property;
	}
	public function init_recyclebin(): myp\property_bool {
		$caption = gettext('Enable recycle bin.');
		$description = gettext('This will create a recycle bin on the share.');
		$property = parent::init_recyclebin();
		$property->
			set_id('recyclebin')->
			set_caption($caption)->
			set_description($description)->
			set_defaultvalue(true)->
			filter_use_default();
		return $property;
	}
	public function init_hidedotfiles(): myp\property_bool {
		$caption = gettext('This parameter controls whether files starting with a dot appear as hidden files.');
		$description = '';
		$property = parent::init_hidedotfiles();
		$property->
			set_id('hidedotfiles')->
			set_caption($caption)->
			set_description($description)->
			set_defaultvalue(true)->
			filter_use_default();
		return $property;
	}
	public function init_name(): myp\property_text {
		$description = '';
		$placeholder = gettext('Enter the name of the share');
		$property = parent::init_name();
		$property->
			set_id('name')->
			set_size(60)->
			set_maxlength(1024)->
			set_description($description)->
			set_defaultvalue('')->
			set_placeholder($placeholder)->
			filter_use_default()->
			set_message_error(sprintf('%s: %s',$property->get_title(),gettext('Must not be empty.')));
		return $property;
	}
	public function init_comment(): myp\property_text {
		$description = '';
		$placeholder = gettext('Enter comment for the share');
		$property = parent::init_comment();
		$property->
			set_id('comment')->
			set_size(60)->
			set_maxlength(1024)->
			set_description($description)->
			set_defaultvalue('')->
			set_placeholder($placeholder)->
			filter_use_default()->
			set_message_error(sprintf('%s: %s',$property->get_title(),gettext('Must not be empty.')));
		return $property;
	}
	public function init_path(): myp\property_text {
		$description = gettext('Path to be shared.');
		$placeholder = gettext('Enter the location of the share');
		$property = parent::init_path();
		$property->
			set_id('path')->
			set_size(60)->
			set_maxlength(1024)->
			set_description($description)->
			set_defaultvalue('')->
			set_input_type($property::INPUT_TYPE_FILECHOOSER)->
			set_placeholder($placeholder)->
			filter_use_default()->
			set_message_error(sprintf('%s: %s',$property->get_title(),gettext('Must not be empty.')));
		return $property;
	}
}
