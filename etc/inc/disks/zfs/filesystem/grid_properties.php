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

declare(strict_types = 1);

namespace disks\zfs\filesystem;

use common\properties as myp;

class grid_properties extends myp\container_row {
	const REGEXP_SIZE = '/^(0*[1-9][\d]*(\.\d*)?|0*\.0*[1-9]\d*)[kmgtpezy]?[b]?$/i';
	const REGEXP_SIZEORNONE = '/^((0*[1-9]\d*(\.\d*)?|0*\.0*[1-9]\d*)[kmgtpezy]?b?|none)$/i';
	const REGEXP_SIZEORNONEORNOTHING = '/^((0*[1-9][\d]*(\.\d*)?|0*\.0*[1-9]\d*)[kmgtpezy]?b?|none|^$)$/i';

//	essential
	protected $x_dataset_name;
	public function init_dataset_name(): myp\property_text {
		$property = $this->x_dataset_name = new myp\property_text($this);
		$property->
			set_title(gettext('Dataset Name'))->
			set_name('name');
		return $property;
	}
	final public function get_dataset_name(): myp\property_text {
		return $this->x_dataset_name ?? $this->init_dataset_name();
	}
	protected $x_dataset_type;
	public function init_dataset_type(): myp\property_list {
		$options = [
			'filesystem' => gettext('File System - can be mounted within the standard system namespace and behaves like other file systems.'),
			'volume' => gettext('Volume - A logical volume. Can be exported as a raw or block device.'),
			'snapshot' => gettext('Snapshot - A read-only version of a file system or volume at a given point in time.'),
			'bookmark' => gettext('Bookmark - Creates a bookmark of a given snapshot.')
		];
		$property = $this->x_dataset_type = new myp\property_list($this);
		$property->
			set_name('type')->
			set_options($options)->
			set_title(gettext('Dataset Type'));
		return $property;
	}
	final public function get_dataset_type(): myp\property_list {
		return $this->x_dataset_type ?? $this->init_dataset_type();
	}
	protected $x_pool_name;
	public function init_pool_name(): myp\property_list {
		$property = $this->x_pool_name = new myp\property_list($this);
		$property->
			set_title(gettext('Pool Name'))->
			set_name('pool_name');
		return $property;
	}
	final public function get_pool_name(): myp\property_list {
		return $this->x_pool_name ?? $this->init_pool_name();
	}
//	read-only
	protected $x_available;
	public function init_available(): myp\property_text {
		$property = $this->x_available = new myp\property_text($this);
		$property->
			set_name('available')->
			set_title(gettext('Available'))->
			set_editableonadd(false)->
			set_editableonmodify(false);
		return $property;
	}
	final public function get_available(): myp\property_text {
		return $this->x_available ?? $this->init_available();
	}
	protected $x_clones;
	public function init_clones(): myp\property_text {
		$property = $this->x_clones = new myp\property_text($this);
		$property->
			set_name('clones')->
			set_title(gettext('Clones'))->
			set_editableonadd(false)->
			set_editableonmodify(false);
		return $property;
	}
	final public function get_clones(): myp\property_text {
		return $this->x_clones ?? $this->init_clones();
	}
	protected $x_compressratio;
	public function init_compressratio(): myp\property_text {
		$property = $this->x_compressratio = new myp\property_text($this);
		$property->
			set_name('compressratio')->
			set_title(gettext('Compression Ratio'))->
			set_editableonadd(false)->
			set_editableonmodify(false);
		return $property;
	}
	final public function get_compressratio(): myp\property_text {
		return $this->x_compressratio ?? $this->init_compressratio();
	}
	protected $x_defer_destroy;
	public function init_defer_destroy(): myp\property_text {
		$property = $this->x_defer_destroy = new myp\property_text($this);
		$property->
			set_name('defer_destroy')->
			set_title(gettext('Defer Destroy'))->
			set_editableonadd(false)->
			set_editableonmodify(false);
		return $property;
	}
	final public function get_defer_destroy(): myp\property_text {
		return $this->x_defer_destroy ?? $this->init_defer_destroy();
	}
	protected $x_encryptionroot;
	public function init_encryptionroot(): myp\property_text {
		$property = $this->x_encryptionroot = new myp\property_text($this);
		$property->
			set_name('encryptionroot')->
			set_title(gettext('Encryption Root'))->
			set_editableonadd(false)->
			set_editableonmodify(false);
		return $property;
	}
	final public function get_encryptionroot(): myp\property_text {
		return $this->x_encryptionroot ?? $this->init_encryptionroot();
	}
	protected $x_filesystem_count;
	public function init_filesystem_count(): myp\property_text {
		$property = $this->x_filesystem_count = new myp\property_text($this);
		$property->
			set_name('filesystem_count')->
			set_title(gettext('Filesystem Count'))->
			set_editableonadd(false)->
			set_editableonmodify(false);
		return $property;
	}
	final public function get_filesystem_count(): myp\property_text {
		return $this->x_filesystem_count ?? $this->init_filesystem_count();
	}
	protected $x_guid;
	public function init_guid(): myp\property_text {
		$property = $this->x_guid = new myp\property_text($this);
		$property->
			set_name('guid')->
			set_title(gettext('GUID'))->
			set_editableonadd(false)->
			set_editableonmodify(false);
		return $property;
	}
	final public function get_guid(): myp\property_text {
		return $this->x_guid ?? $this->init_guid();
	}
	protected $x_keystatus;
	public function init_keystatus(): myp\property_text {
		$property = $this->x_keystatus = new myp\property_text($this);
		$property->
			set_name('keystatus')->
			set_title(gettext('Key Status'))->
			set_editableonadd(false)->
			set_editableonmodify(false);
		return $property;
	}
	final public function get_keystatus(): myp\property_text {
		return $this->x_keystatus ?? $this->init_keystatus();
	}
	protected $x_logicalreferenced;
	public function init_logicalreferenced(): myp\property_text  {
		$property = $this->x_logicalreferenced = new myp\property_text($this);
		$property->
			set_name('logicalreferenced')->
			set_title(gettext('Logical Referenced'))->
			set_editableonadd(false)->
			set_editableonmodify(false);
		return $property;
	}
	final public function get_logicalreferenced(): myp\property_text {
		return $this->x_logicalreferenced ?? $this->init_logicalreferenced();
	}
	protected $x_logicalused;
	public function init_logicalused(): myp\property_text  {
		$property = $this->x_logicalused = new myp\property_text($this);
		$property->
			set_name('logicalused')->
			set_title(gettext('Logical Used'))->
			set_editableonadd(false)->
			set_editableonmodify(false);
		return $property;
	}
	final public function get_logicalused(): myp\property_text {
		return $this->x_logicalused ?? $this->init_logicalused();
	}
	protected $x_mounted;
	public function init_mounted(): myp\property_text  {
		$property = $this->x_mounted = new myp\property_text($this);
		$property->
			set_name('mounted')->
			set_title(gettext('Mounted'))->
			set_editableonadd(false)->
			set_editableonmodify(false);
		return $property;
	}
	final public function get_mounted(): myp\property_text {
		return $this->x_mounted ?? $this->init_mounted();
	}
	protected $x_objsetid;
	public function init_objsetid(): myp\property_text {
		$property = $this->x_objsetid = new myp\property_text($this);
		$property->
			set_name('objsetid')->
			set_title(gettext('Obj Set ID'))->
			set_editableonadd(false)->
			set_editableonmodify(false);
		return $property;
	}
	final public function get_objsetid(): myp\property_text {
		return $this->x_objsetid ?? $this->init_objsetid();
	}
	protected $x_origin;
	public function init_origin(): myp\property_text {
		$property = $this->x_origin = new myp\property_text($this);
		$property->
			set_name('origin')->
			set_title(gettext('Origin'))->
			set_editableonadd(false)->
			set_editableonmodify(false);
		return $property;
	}
	final public function get_origin(): myp\property_text {
		return $this->x_origin ?? $this->init_origin();
	}
	protected $x_receive_resume_token;
	public function init_receive_resume_token(): myp\property_text {
		$property = $this->x_receive_resume_token = new myp\property_text($this);
		$property->
			set_name('receive_resume_token')->
			set_title(gettext('Receive Resume Token'))->
			set_editableonadd(false)->
			set_editableonmodify(false);
		return $property;
	}
	final public function get_receive_resume_token(): myp\property_text {
		return $this->x_receive_resume_token ?? $this->init_receive_resume_token();
	}
	protected $x_redact_snaps;
	public function init_redact_snaps(): myp\property_text {
		$property = $this->x_redact_snaps = new myp\property_text($this);
		$property->
			set_name('redact_snaps')->
			set_title(gettext('Redact Snaps'))->
			set_editableonadd(false)->
			set_editableonmodify(false);
		return $property;
	}
	final public function get_redact_snaps(): myp\property_text {
		return $this->x_redact_snaps ?? $this->init_redact_snaps();
	}
	protected $x_refcompressratio;
	public function init_refcompressratio(): myp\property_text {
		$property = $this->x_refcompressratio = new myp\property_text($this);
		$property->
			set_name('refcompressratio')->
			set_title(gettext('Refcompressratio'))->
			set_editableonadd(false)->
			set_editableonmodify(false);
		return $property;
	}
	final public function get_refcompressratio(): myp\property_text {
		return $this->x_refcompressratio ?? $this->init_refcompressratio();
	}
	protected $x_referenced;
	public function init_referenced(): myp\property_text {
		$property = $this->x_referenced = new myp\property_text($this);
		$property->
			set_name('referenced')->
			set_title(gettext('Referenced'))->
			set_editableonadd(false)->
			set_editableonmodify(false);
		return $property;
	}
	final public function get_referenced(): myp\property_text {
		return $this->x_referenced ?? $this->init_referenced();
	}
	protected $x_snapshot_count;
	public function init_snapshot_count(): myp\property_text {
		$property = $this->x_snapshot_count = new myp\property_text($this);
		$property->
			set_name('snapshot_count')->
			set_title(gettext('Snapshot Count'))->
			set_editableonadd(false)->
			set_editableonmodify(false);
		return $property;
	}
	final public function get_snapshot_count(): myp\property_text {
		return $this->x_snapshot_count ?? $this->init_snapshot_count();
	}
	protected $x_used;
	public function init_used(): myp\property_text  {
		$property = $this->x_used = new myp\property_text($this);
		$property->
			set_name('used')->
			set_title(gettext('Used'))->
			set_editableonadd(false)->
			set_editableonmodify(false);
		return $property;
	}
	final public function get_used(): myp\property_text {
		return $this->x_used ?? $this->init_used();
	}
	protected $x_usedbychildren;
	public function init_usedbychildren(): myp\property_text {
		$property = $this->x_usedbychildren = new myp\property_text($this);
		$property->
			set_name('usedbychildren')->
			set_title(gettext('Used by Children'))->
			set_editableonadd(false)->
			set_editableonmodify(false);
		return $property;
	}
	final public function get_usedbychildren(): myp\property_text {
		return $this->x_usedbychildren ?? $this->init_usedbychildren();
	}
	protected $x_usedbydataset;
	public function init_usedbydataset(): myp\property_text {
		$property = $this->x_usedbydataset = new myp\property_text($this);
		$property->
			set_name('usedbydataset')->
			set_title(gettext('Used by Dataset'))->
			set_editableonadd(false)->
			set_editableonmodify(false);
		return $property;
	}
	final public function get_usedbydataset(): myp\property_text {
		return $this->x_usedbydataset ?? $this->init_usedbydataset();
	}
	protected $x_usedbyrefreservation;
	public function init_usedbyrefreservation(): myp\property_text {
		$property = $this->x_usedbyrefreservation = new myp\property_text($this);
		$property->
			set_name('usedbyrefreservation')->
			set_title(gettext('Used by Refreservation'))->
			set_editableonadd(false)->
			set_editableonmodify(false);
		return $property;
	}
	final public function get_usedbyrefreservation(): myp\property_text {
		return $this->x_usedbyrefreservation ?? $this->init_usedbyrefreservation();
	}
	protected $x_usedbysnapshots;
	public function init_usedbysnapshots(): myp\property_text {
		$property = $this->x_usedbysnapshots = new myp\property_text($this);
		$property->
			set_name('usedbysnapshots')->
			set_title(gettext('Used by Snapshots'))->
			set_editableonadd(false)->
			set_editableonmodify(false);
		return $property;
	}
	final public function get_usedbysnapshots(): myp\property_text {
		return $this->x_usedbysnapshots ?? $this->init_usedbysnapshots();
	}
	protected $x_userrefs;
	public function init_userrefs(): myp\property_text {
		$property = $this->x_userrefs = new myp\property_text($this);
		$property->
			set_name('userrefs')->
			set_title(gettext('Userrefs'))->
			set_editableonadd(false)->
			set_editableonmodify(false);
		return $property;
	}
	final public function get_userrefs(): myp\property_text {
		return $this->x_userrefs ?? $this->init_userrefs();
	}
	protected $x_written;
	public function init_written(): myp\property_text {
		$property = $this->x_written = new myp\property_text($this);
		$property->
			set_name('written')->
			set_title(gettext('Written'))->
			set_editableonadd(false)->
			set_editableonmodify(false);
		return $property;
	}
	final public function get_written(): myp\property_text {
		return $this->x_written ?? $this->init_written();
	}
//	applicable in add mode
	protected $x_casesensitivity;
	public function init_casesensitivity(): myp\property_list {
		$property = $this->x_casesensitivity = new myp\property_list($this);
		$property->
			set_title(gettext('Case Sensitivity'))->
			set_name('casesensitivity');
		return $property;
	}
	final public function get_casesensitivity(): myp\property_list {
		return $this->x_casesensitivity ?? $this->init_casesensitivity();
	}
	protected $x_encryption;
	public function init_encryption(): myp\property_list_offon {
		$property = $this->x_encryption = new myp\property_list_offon($this);
		$property->
			set_title(gettext('Encryption'))->
			set_name('encryption');
		return $property;
	}
	final public function get_encryption(): myp\property_list_offon {
		return $this->x_encryption ?? $this->init_encryption();
	}
	protected $x_normalization;
	public function init_normalization(): myp\property_list {
		$property = $this->x_normalization = new myp\property_list($this);
		$property->
			set_title(gettext('Normalization'))->
			set_name('normalization');
		return $property;
	}
	final public function get_normalization(): myp\property_list {
		return $this->x_normalization ?? $this->init_normalization();
	}
	protected $x_utf8only;
	public function init_utf8only(): myp\property_list_offon {
		$property = $this->x_utf8only = new myp\property_list_offon($this);
		$property->
			set_title(gettext('UTF-8 File Names'))->
			set_name('utf8only');
		return $property;
	}
	final public function get_utf8only(): myp\property_list_offon {
		return $this->x_utf8only ?? $this->init_utf8only();
	}
	protected $x_volblocksize;
	public function init_volblocksize(): myp\property_list {
		$property = $this->x_volblocksize = new myp\property_list($this);
		$property->
			set_name('volblocksize')->
			set_title(gettext('Block Size'));
		return $property;
	}
	final public function get_volblocksize(): myp\property_list {
		return $this->x_volblocksize ?? $this->init_volblocksize();
	}
//	applicable in add and edit mode
	protected $x_aclinherit;
	public function init_aclinherit(): myp\property_list {
		$property = $this->x_aclinherit = new myp\property_list($this);
		$property->
			set_title(gettext('ACL Inherit'))->
			set_name('aclinherit');
		return $property;
	}
	final public function get_aclinherit(): myp\property_list {
		return $this->x_aclinherit ?? $this->init_aclinherit();
	}
	protected $x_aclmode;
	public function init_aclmode(): myp\property_list {
		$property = $this->x_aclmode = new myp\property_list($this);
		$property->
			set_title(gettext('ACL Mode'))->
			set_name('aclmode');
		return $property;
	}
	final public function get_aclmode(): myp\property_list {
		return $this->x_aclmode ?? $this->init_aclmode();
	}
	protected $x_acltype;
	public function init_acltype(): myp\property_list {
		$property = $this->x_acltype = new myp\property_list($this);
		$property->
			set_title(gettext('ACL Type'))->
			set_name('acltype');
		return $property;
	}
	final public function get_acltype(): myp\property_list {
		return $this->x_acltype ?? $this->init_acltype();
	}
	protected $x_atime;
	public function init_atime(): myp\property_list_onoff {
		$property = $this->x_atime = new myp\property_list_onoff($this);
		$property->
			set_title(gettext('Access Time'))->
			set_name('atime');
		return $property;
	}
	final public function get_atime(): myp\property_list_onoff {
		return $this->x_atime ?? $this->init_atime();
	}
	protected $x_canmount;
	public function init_canmount(): myp\property_list_onoff {
		$property = $this->x_canmount = new myp\property_list_onoff($this);
		$property->
			set_title(gettext('Can Mount'))->
			set_name('canmount');
		return $property;
	}
	final public function get_canmount(): myp\property_list {
		return $this->x_canmount ?? $this->init_canmount();
	}
	protected $x_checksum;
	public function init_checksum(): myp\property_list {
		$property = $this->x_checksum = new myp\property_list($this);
		$property->
			set_title(gettext('Checksum'))->
			set_name('checksum');
		return $property;
	}
	final public function get_checksum(): myp\property_list {
		return $this->x_checksum ?? $this->init_checksum();
	}
	protected $x_compression;
	public function init_compression(): myp\property_list_onoff {
		$property = $this->x_compression = new myp\property_list_onoff($this);
		$property->
			set_title(gettext('Compression'))->
			set_name('compression');
		return $property;
	}
	final public function get_compression(): myp\property_list_onoff {
		return $this->x_compression ?? $this->init_compression();
	}
	protected $x_compression_level_gzip;
	public function init_compression_level_gzip(): myp\property_list {
		$property = $this->x_compression_level_gzip = new myp\property_list($this);
		$property->
			set_title(gettext('GZIP Compression Level'))->
			set_name('compression_level_gzip');
		return $property;
	}
	final public function get_compression_level_gzip(): myp\property_list {
		return $this->x_compression_level_gzip ?? $this->init_compression_level_gzip();
	}
	protected $x_compression_level_zstd;
	public function init_compression_level_zstd(): myp\property_list {
		$property = $this->x_compression_level_zstd = new myp\property_list($this);
		$property->
			set_title(gettext('Zstandard Compression Level'))->
			set_name('compression_level_zstd');
		return $property;
	}
	final public function get_compression_level_zstd(): myp\property_list {
		return $this->x_compression_level_zstd ?? $this->init_compression_level_zstd();
	}
	protected $x_compression_level_zstd_fast;
	public function init_compression_level_zstd_fast(): myp\property_list {
		$property = $this->x_compression_level_zstd_fast = new myp\property_list($this);
		$property->
			set_title(gettext('Fast Zstandard Compression Level'))->
			set_name('compression_level_zstd_fast');
		return $property;
	}
	final public function get_compression_level_zstd_fast(): myp\property_list {
		return $this->x_compression_level_zstd_fast ?? $this->init_compression_level_zstd_fast();
	}
/*
	protected $x_context;
	public function init_context(): myp\property_list {
		$property = $this->x_context = new myp\property_list($this);
		$property->
			set_title(gettext('Context'))->
			set_name('context');
		return $property;
	}
	final public function get_context(): myp\property_list {
		return $this->x_context ?? $this->init_context();
	}
 */
	protected $x_copies;
	public function init_copies(): myp\property_list {
		$property = $this->x_copies = new myp\property_list($this);
		$property->
			set_title(gettext('Copies'))->
			set_name('copies');
		return $property;
	}
	final public function get_copies(): myp\property_list {
		return $this->x_copies ?? $this->init_copies();
	}
	protected $x_dedup;
	public function init_dedup(): myp\property_list_offon {
		$property = $this->x_dedup = new myp\property_list_offon($this);
		$property->
			set_title(gettext('Dedup Method'))->
			set_name('dedup');
		return $property;
	}
	final public function get_dedup(): myp\property_list_offon {
		return $this->x_dedup ?? $this->init_dedup();
	}
/*
	protected $x_defcontext;
	public function init_defcontext(): myp\property_list {
		$property = $this->x_defcontext = new myp\property_list($this);
		$property->
			set_title(gettext('Defcontext'))->
			set_name('defcontext');
		return $property;
	}
	final public function get_defcontext(): myp\property_list {
		return $this->x_defcontext ?? $this->init_defcontext();
	}
 */
	protected $x_devices;
	public function init_devices(): myp\property_list_onoff {
		$property = $this->x_devices = new myp\property_list_onoff($this);
		$property->
			set_title(gettext('Devices'))->
			set_name('devices');
		return $property;
	}
	final public function get_devices(): myp\property_list_onoff {
		return $this->x_devices ?? $this->init_devices();
	}
	protected $x_dnodesize;
	public function init_dnodesize(): myp\property_list {
		$property = $this->x_dnodesize = new myp\property_list($this);
		$property->
			set_title(gettext('Dnode Size'))->
			set_name('dnodesize');
		return $property;
	}
	final public function get_dnodesize(): myp\property_list {
		return $this->x_dnodesize ?? $this->init_dnodesize();
	}
	protected $x_exec;
	public function init_exec(): myp\property_list_onoff {
		$property = $this->x_exec = new myp\property_list_onoff($this);
		$property->
			set_title(gettext('Execute Processes'))->
			set_name('exec');
		return $property;
	}
	final public function get_exec(): myp\property_list_onoff {
		return $this->x_exec ?? $this->init_exec();
	}
	protected $x_filesystem_limit;
	public function init_filesystem_limit(): myp\property_int {
		$property = $this->x_filesystem_limit = new myp\property_int($this);
		$property->
			set_title(gettext('Filesystem Limit'))->
			set_name('filesystem_limit');
		return $property;
	}
	final public function get_filesystem_limit(): myp\property_int {
		return $this->x_filesystem_limit ?? $this->init_filesystem_limit();
	}
/*
	protected $x_fscontext;
	public function init_fscontext(): myp\property_list {
		$property = $this->x_fscontext = new myp\property_list($this);
		$property->
			set_title(gettext('FS Context'))->
			set_name('fscontext');
		return $property;
	}
	final public function get_fscontext(): myp\property_list {
		return $this->x_fscontext ?? $this->init_fscontext();
	}
 */
	protected $x_jailed;
	public function init_jailed(): myp\property_list_offon {
		$property = $this->x_jailed = new myp\property_list_offon($this);
		$property->
			set_name('jailed')->
			set_title(gettext('Jailed'));
		return $property;
	}
	final public function get_jailed(): myp\properties_list_offon {
		return $this->x_jailed ?? $this->init_jailed();
	}
	protected $x_keyformat;
	public function init_keyformat(): myp\property_list {
		$property = $this->x_keyformat = new myp\property_list($this);
		$property->
			set_title(gettext('Key Format'))->
			set_name('keyformat');
		return $property;
	}
	final public function get_keyformat(): myp\property_list {
		return $this->x_keyformat ?? $this->init_keyformat();
	}
	protected $x_keylocation;
	public function init_keylocation(): myp\property_text {
		$property = $this->x_keylocation = new myp\property_text($this);
		$property->
			set_title(gettext('Key Location'))->
			set_name('keylocation');
		return $property;
	}
	final public function get_keylocation(): myp\property_text {
		return $this->x_keylocation ?? $this->init_keylocation();
	}
	protected $x_logbias;
	public function init_logbias(): myp\property_list {
		$property = $this->x_logbias = new myp\property_list($this);
		$property->
			set_title(gettext('Logbias'))->
			set_name('logbias');
		return $property;
	}
	final public function get_logbias(): myp\property_list {
		return $this->x_logbias ?? $this->init_logbias();
	}
	protected $x_mountpoint;
	public function init_mountpoint(): myp\property_text {
		$property = $this->x_mountpoint = new myp\property_text($this);
		$property->
			set_title(gettext('Mount Point'))->
			set_name('mountpoint');
		return $property;
	}
	final public function get_mountpoint(): myp\property_text {
		return $this->x_mountpoint ?? $this->init_mountpoint();
	}
	protected $x_mountpoint_type;
	public function init_mountpoint_type(): myp\property_list {
		$property = $this->x_mountpoint_type = new myp\property_list($this);
		$property->
			set_title(gettext('Mountpoint Type'))->
			set_name('mountpoint_type');
		return $property;
	}
	final public function get_mountpoint_type(): myp\property_list {
		return $this->x_mountpoint_type ?? $this->init_mountpoint_type();
	}
	protected $x_nbmand;
	public function init_nbmand(): myp\property_list_offon {
		$property = $this->x_nbmand = new myp\property_list_offon($this);
		$property->
			set_title(gettext('Non-blocking Mandatory Locks'))->
			set_name('nbmand');
		return $property;
	}
	final public function get_nbmand(): myp\property_list_offon {
		return $this->x_nbmand ?? $this->init_nbmand();
	}
	protected $x_overlay;
	public function init_overlay(): myp\property_list_onoff {
		$property = $this->x_overlay = new myp\property_list_onoff($this);
		$property->
			set_title(gettext('Overlay Mount'))->
			set_name('overlay');
		return $property;
	}
	final public function get_overlay(): myp\property_list {
		return $this->x_overlay ?? $this->init_overlay();
	}
	protected $x_pbkdf2iters;
	public function init_pbkdf2iters(): myp\property_int {
		$property = $this->x_pbkdf2iters = new myp\property_int($this);
		$property->
			set_title(gettext('PBKDF2 Iterations'))->
			set_name('pbkdf2iters');
		return $property;
	}
	final public function get_pbkdf2iters(): myp\property_int {
		return $this->x_pbkdf2iters ?? $this->init_pbkdf2iters();
	}
	protected $x_primarycache;
	public function init_primarycache(): myp\property_list {
		$property = $this->x_primarycache = new myp\property_list($this);
		$property->
			set_name('primarycache')->
			set_title(gettext('Primary Cache'));
		return $property;
	}
	final public function get_primarycache(): myp\property_list {
		return $this->x_primarycache ?? $this->init_primarycache();
	}
	protected $x_quota;
	public function init_quota(): myp\property_text {
		$property = $this->x_quota = new myp\property_text($this);
		$property->
			set_name('quota')->
			set_title(gettext('Quota'));
		return $property;
	}
	final public function get_quota(): myp\property_text {
		return $this->x_quota ?? $this->init_quota();
	}
	protected $x_readonly;
	public function init_readonly(): myp\property_list_offon {
		$property = $this->x_readonly = new myp\property_list_offon($this);
		$property->
			set_name('readonly')->
			set_title(gettext('Readonly'));
		return $property;
	}
	final public function get_readonly(): myp\property_list_offon {
		return $this->x_readonly ?? $this->init_readonly();
	}
	protected $x_recordsize;
	public function init_recordsize(): myp\property_list {
		$property = $this->x_recordsize = new myp\property_list($this);
		$property->
			set_name('recordsize')->
			set_title(gettext('Record Size'));
		return $property;
	}
	final public function get_recordsize(): myp\property_list {
		return $this->x_recordsize ?? $this->init_recordsize();
	}
	protected $x_redundant_metadata;
	public function init_redundant_metadata(): myp\property_list {
		$property = $this->x_redundant_metadata = new myp\property_list($this);
		$property->
			set_name('redundant_metadata')->
			set_title(gettext('Redundant Metadata'));
		return $property;
	}
	final public function get_redundant_metadata(): myp\property_list {
		return $this->x_redundant_metadata ?? $this->init_redundant_metadata();
	}
	protected $x_refquota;
	public function init_refquota(): myp\property_text {
		$property = $this->x_refquota = new myp\property_text($this);
		$property->
			set_name('refquota')->
			set_title(gettext('Refquota'));
		return $property;
	}
	final public function get_refquota(): myp\property_text {
		return $this->x_refquota ?? $this->init_refquota();
	}
	protected $x_refquota_type;
	public function init_refquota_type(): myp\property_list {
		$property = $this->x_refquota_type = new myp\property_list($this);
		$property->
			set_name('refquota_type')->
			set_title(gettext('Refquota Type'));
		return $property;
	}
	final public function get_refquota_type(): myp\property_list {
		return $this->x_refquota_type ?? $this->init_refquota_type();
	}
	protected $x_refreservation;
	public function init_refreservation(): myp\property_text {
		$property = $this->x_refreservation = new myp\property_text($this);
		$property->
			set_name('refreservation')->
			set_title(gettext('Refreservation'));
		return $property;
	}
	final public function get_refreservation(): myp\property_text {
		return $this->x_refreservation ?? $this->init_refreservation();
	}
	protected $x_refreservation_type;
	public function init_refreservation_type(): myp\property_list {
		$property = $this->x_refreservation_type = new myp\property_list($this);
		$property->
			set_name('refreservation_type')->
			set_title(gettext('Refreservation Type'));
		return $property;
	}
	final public function get_refreservation_type(): myp\property_list {
		return $this->x_refreservation_type ?? $this->init_refreservation_type();
	}
	protected $x_relatime;
	public function init_relatime(): myp\property_list_offon {
		$property = $this->x_relatime = new myp\property_list_offon($this);
		$property->
			set_name('relatime')->
			set_title(gettext('Relatime'));
		return $property;
	}
	final public function get_relatime(): myp\property_list_offon {
		return $this->x_relatime ?? $this->init_relatime();
	}
	protected $x_reservation;
	public function init_reservation(): myp\property_text {
		$property = $this->x_reservation = new myp\property_text($this);
		$property->
			set_name('reservation')->
			set_title(gettext('Reservation'));
		return $property;
	}
	final public function get_reservation(): myp\property_text {
		return $this->x_reservation ?? $this->init_reservation();
	}
	protected $x_reservation_type;
	public function init_reservation_type(): myp\property_list {
		$property = $this->x_reservation_type = new myp\property_list($this);
		$property->
			set_name('reservation_type')->
			set_title(gettext('Reservation Type'));
		return $property;
	}
	final public function get_reservation_type(): myp\property_list {
		return $this->x_reservation_type ?? $this->init_reservation_type();
	}
/*
	protected $x_rootcontext;
	public function init_rootcontext(): myp\property_list {
		$property = $this->x_rootcontext = new myp\property_list($this);
		$property->
			set_title(gettext('Root Context'))->
			set_name('rootcontext');
		return $property;
	}
	final public function get_rootcontext(): myp\property_list {
		return $this->x_rootcontext ?? $this->init_rootcontext();
	}
 */
	protected $x_secondarycache;
	public function init_secondarycache(): myp\property_list {
		$property = $this->x_secondarycache = new myp\property_list($this);
		$property->
			set_name('secondarycache')->
			set_title(gettext('Secondary Cache'));
		return $property;
	}
	final public function get_secondarycache(): myp\property_list {
		return $this->x_secondarycache ?? $this->init_secondarycache();
	}
	protected $x_setuid;
	public function init_setuid(): myp\property_list_onoff {
		$property = $this->x_setuid = new myp\property_list_onoff($this);
		$property->
			set_name('setuid')->
			set_title(gettext('Setuid'));
		return $property;
	}
	final public function get_setuid(): myp\property_list_onoff {
		return $this->x_setuid ?? $this->init_setuid();
	}
	protected $x_sharenfs;
	public function init_sharenfs(): myp\property_text {
		$property = $this->x_sharenfs = new myp\property_text($this);
		$property->
			set_name('sharenfs')->
			set_title(gettext('Share NFS Options'));
		return $property;
	}
	final public function get_sharenfs(): myp\property_text {
		return $this->x_sharenfs ?? $this->init_sharenfs();
	}
	protected $x_sharenfs_type;
	public function init_sharenfs_type(): myp\property_list {
		$property = $this->x_sharenfs_type = new myp\property_list($this);
		$property->
			set_name('sharenfs_type')->
			set_title(gettext('Share NFS Type'));
		return $property;
	}
	final public function get_sharenfs_type(): myp\property_list {
		return $this->x_sharenfs_type ?? $this->init_sharenfs_type();
	}
	protected $x_sharesmb;
	public function init_sharesmb(): myp\property_text {
		$property = $this->x_sharesmb = new myp\property_text($this);
		$property->
			set_name('sharesmb')->
			set_title(gettext('Share SMB Options'));
		return $property;
	}
	final public function get_sharesmb(): myp\property_text {
		return $this->x_sharesmb ?? $this->init_sharesmb();
	}
	protected $x_sharesmb_type;
	public function init_sharesmb_type(): myp\property_list_offon {
		$property = $this->x_sharesmb_type = new myp\property_list_offon($this);
		$property->
			set_name('sharesmb_type')->
			set_title(gettext('Share SMB Type'));
		return $property;
	}
	final public function get_sharesmb_type(): myp\property_list_offon {
		return $this->x_sharesmb_type ?? $this->init_sharesmb_type();
	}
	protected $x_snapdev;
	public function init_snapdev(): myp\property_list {
		$property = $this->x_snapdev = new myp\property_list($this);
		$property->
			set_name('snapdev')->
			set_title(gettext('Snapshot Device Visibility'));
		return $property;
	}
	final public function get_snapdev(): myp\property_list {
		return $this->x_snapdev ?? $this->init_snapdev();
	}
	protected $x_snapdir;
	public function init_snapdir(): myp\property_list {
		$property = $this->x_snapdir = new myp\property_list($this);
		$property->
			set_name('snapdir')->
			set_title(gettext('Snapshot Folder Visibility'));
		return $property;
	}
	final public function get_snapdir(): myp\property_list {
		return $this->x_snapdir ?? $this->init_snapdir();
	}
	protected $x_snapshot_limit;
	public function init_snapshot_limit(): myp\property_int {
		$property = $this->x_snapshot_limit = new myp\property_int($this);
		$property->
			set_name('snapshot_limit')->
			set_title(gettext('Snapshot Limit'));
		return $property;
	}
	final public function get_snapshot_limit(): myp\property_int {
		return $this->x_snapshot_limit ?? $this->init_snapshot_limit();
	}
	protected $x_snapshot_limit_type;
	public function init_snapshot_limit_type(): myp\property_list {
		$property = $this->x_snapshot_limit_type = new myp\property_list($this);
		$property->
			set_name('snapshot_limit_type')->
			set_title(gettext('Snapshot Limit'));
		return $property;
	}
	final public function get_snapshot_limit_type(): myp\property_list {
		return $this->x_snapshot_limit_type ?? $this->init_snapshot_limit_type();
	}
	protected $x_special_small_blocks;
	public function init_special_small_blocks(): myp\property_list {
		$property = $this->x_special_small_blocks = new myp\property_list($this);
		$property->
			set_title(gettext('Threshold Small Block Size'))->
			set_name('special_small_blocks');
		return $property;
	}
	final public function get_special_small_blocks(): myp\property_list {
		return $this->x_special_small_blocks ?? $this->init_special_small_blocks();
	}
	protected $x_sync;
	public function init_sync(): myp\property_list {
		$property = $this->x_sync = new myp\property_list($this);
		$property->
			set_name('sync')->
			set_title(gettext('Synchronous Requests Behaviour'));
		return $property;
	}
	final public function get_sync(): myp\property_list {
		return $this->x_sync ?? $this->init_sync();
	}
/*
	protected $x_version_type;
	public function init_version_type(): myp\property_list {
		$property = $this->x_version_type = new myp\property_list($this);
		$property->
			set_name('version_type')->
			set_title(gettext('Version'));
		return $property;
	}
	final public function get_version_type(): myp\property_list {
		return $this->x_version_type ?? $this->init_version_type();
	}
	protected $x_version;
	public function init_version(): myp\property_int {
		$property = $this->x_version = new myp\property_int($this);
		$property->
			set_name('version')->
			set_title(gettext('Version'));
		return $property;
	}
	final public function get_version(): myp\property_int {
		return $this->x_version ?? $this->init_version();
	}
 */
	protected $x_volmode;
	public function init_volmode(): myp\property_list {
		$property = $this->x_volmode = new myp\property_list($this);
		$property->
			set_name('volmode')->
			set_title(gettext('Volume Mode'));
		return $property;
	}
	final public function get_volmode(): myp\property_list {
		return $this->x_volmode ?? $this->init_volmode();
	}
	protected $x_volsize;
	public function init_volsize(): myp\property_text {
		$property = $this->x_volsize = new myp\property_text($this);
		$property->
			set_name('volsize')->
			set_title(gettext('Volume Size'));
		return $property;
	}
	final public function get_volsize(): myp\property_text {
		return $this->x_volsize ?? $this->init_volsize();
	}
	protected $x_vscan;
	public function init_vscan(): myp\property_list_offon {
		$property = $this->x_vscan = new myp\property_list_offon($this);
		$property->
			set_title(gettext('Virus Scan'))->
			set_name('vscan');
		return $property;
	}
	final public function get_vscan(): myp\property_list_offon {
		return $this->x_vscan ?? $this->init_vscan();
	}
	protected $x_xattr;
	public function init_xattr(): myp\property_list_onoff {
		$property = $this->x_attr = new myp\property_list_onoff($this);
		$property->
			set_name('xattr')->
			set_title(gettext('Extended Attributes'));
		return $property;
	}
	final public function get_xattr(): myp\property_list_onoff {
		return $this->x_xattr ?? $this->init_xattr();
	}
/*
	protected $x_zoned;
	public function init_zoned(): myp\property_list_offon {
		$property = $this->x_zoned = new myp\property_list_offon($this);
		$property->
			set_title(gettext('Zoned Dataset'))->
			set_name('zoned');
		return $property;
	}
	final public function get_zoned(): myp\property_list_offon {
		return $this->x_zoned ?? $this->init_zoned();
	}
 */
}
