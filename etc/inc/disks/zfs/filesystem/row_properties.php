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

declare(strict_types = 1);

namespace disks\zfs\filesystem;

use common\properties as myp;

class row_properties extends grid_properties {
//	essential
	public function init_dataset_name(): myp\property_text {
		$description = gettext('The name of the filesystem.');
		$placeholder = gettext('Enter filesystem name');
		$regexp = sprintf('/^[a-z\d][a-z\d%1$s]*(?:\/[a-z\d][a-z\d%1$s]*)*$/i',preg_quote('.:-_','/'));
		$property = parent::init_dataset_name();
		$property->
			set_id('name')->
			set_description($description)->
			set_defaultvalue('')->
			set_placeholder($placeholder)->
			set_editableonmodify(false)->
			set_filter(FILTER_VALIDATE_REGEXP)->
			set_filter_flags(FILTER_REQUIRE_SCALAR)->
			set_filter_options(['default' => null,'regexp' => $regexp]);
		return $property;
	}
	public function init_dataset_type(): myp\property_list {
		$description = gettext('Controls the type of the ZFS dataset.');
		$property = parent::init_dataset_type();
		$property->
			set_id('type')->
			set_description($description)->
			set_defaultvalue('filesystem')->
			set_editableonmodify(false)->
			filter_use_default();
		return $property;
	}
	public function init_pool_name(): myp\property_list {
		$description = gettext('Pool Name');
		$property = parent::init_pool_name();
		$property->
			set_id('pool_name')->
			set_defaultvalue('')->
			set_description($description)->
			set_editableonmodify(false)->
			set_input_type($property::INPUT_TYPE_SELECT)->
			filter_use_default();
		return $property;
	}
//	applicable in add mode
	public function init_casesensitivity(): myp\property_list {
		$description = gettext('Indicates whether the file name matching algorithm used by the filesystem should be case-sensitive, case-insensitive, or allow a combination of both styles of matching.');
		$options = [
			'' => gettext('Default'),
			'sensitive' => gettext('Sensitive'),
			'insensitive' => gettext('Insensitive'),
			'mixed' => gettext('Mixed'),
		];
		$property = parent::init_casesensitivity();
		$property->
			set_id('casesensitivity')->
			set_defaultvalue('sensitive')->
			set_description($description)->
			set_editableonmodify(false)->
			set_options($options)->
			filter_use_default();
		return $property;
	}
	public function init_encryption(): myp\property_list_offon {
		$description = gettext('Controls the encryption cipher suite (block cipher, key length, and mode) used for this dataset.');
		$options = [
			'aes-128-ccm' => 'aes-128-ccm',
			'aes-192-ccm' => 'aes-192-ccm',
			'aes-256-ccm' => 'aes-256-ccm',
			'aes-128-gcm' => 'aes-128-gcm',
			'aes-192-gcm' => 'aes-192-gcm',
			'aes-256-gcm' => 'aes-256-gcm'
		];
		$property = parent::init_encryption();
		$property->
			set_id('encryption')->
			set_defaultvalue('off')->
			set_description($description)->
			set_editableonmodify(false)->
			set_input_type($property::INPUT_TYPE_SELECT)->
			upsert_options($options);
		return $property;
	}
	public function init_normalization(): myp\property_list {
		$description = gettext('Indicates whether the file system should perform a unicode normalization of file names whenever two file names are compared, and which normalization algorithm should be used.');
		$options = [
			'' => gettext('Default'),
			'none' => gettext('None'),
			'formC' => gettext('Normalization Form C (NFC) - Canonical Decomposition, followed by Canonical Composition'),
			'formD' => gettext('Normalization Form D (NFD) - Canonical Decomposition'),
			'formKC' => gettext('Normalization Form KC (NFKC) - Compatibility Decomposition, followed by Canonical Composition'),
			'formKD' => gettext('Normalization Form KD (NFKD) - Compatibility Decomposition'),
		];
		$property = parent::init_normalization();
		$property->
			set_id('normalization')->
			set_defaultvalue('')->
			set_description($description)->
			set_editableonmodify(false)->
			set_input_type($property::INPUT_TYPE_SELECT)->
			set_options($options)->
			filter_use_default();
		return $property;
	}
	public function init_utf8only(): myp\property_list_offon {
		$description = gettext('Indicates whether the file system should reject file names that include characters that are not present in the UTF-8 character code set.');
		$property = parent::init_utf8only();
		$property->
			set_id('utf8only')->
			set_defaultvalue('off')->
			set_description($description)->
			set_editableonmodify(false);
		return $property;
	}
	public function init_volblocksize(): myp\property_list {
		$description = gettext('ZFS volume block size. This value can not be changed after creation.');
		$options = [
			'' => gettext('Default'),
			'512B' => gettext('512B'),
			'1K' => gettext('1K'),
			'2K' => gettext('2K'),
			'4K' => gettext('4K'),
			'8K' => gettext('8K'),
			'16K' => gettext('16K'),
			'32K' => gettext('32K'),
			'64K' => gettext('64K'),
			'128K' => gettext('128K')
		];
		$property = parent::init_volblocksize();
		$property->
			set_id('volblocksize')->
			set_defaultvalue('')->
			set_description($description)->
			set_editableonmodify(false)->
			set_input_type($property::INPUT_TYPE_SELECT)->
			set_options($options)->
			filter_use_default();
		return $property;
	}
//	applicable in add and edit mode
	public function init_aclinherit(): myp\property_list {
		$description = gettext('This attribute determines the behavior of Access Control List inheritance.');
		$options = [
			'' => gettext('Default'),
			'discard' => gettext('Discard - Do not inherit any ACEs'),
			'noallow' => gettext('Noallow - Only inherit inheritable ACEs that specify deny permissions'),
			'restricted' => gettext('Restricted - Inherit ACEs except "write ACL" and "write owner" permissions'),
			'passthrough' => gettext('Passthrough - Inherit all inheritable ACEs without any modifications'),
			'passthrough-x' => gettext('Passthrough-X - Inherit all except execute permission when not specified')
		];
		$property = parent::init_aclinherit();
		$property->
			set_id('aclinherit')->
			set_defaultvalue('')->
			set_description($description)->
			set_options($options)->
			filter_use_default();
		return $property;
	}
	public function init_aclmode(): myp\property_list {
		$description = gettext('This attribute controls the ACL behavior when a file is created or whenever the mode of a file or a directory is modified.');
		$options = [
			'' => gettext('Default'),
			'discard' => gettext('Discard - Discard ACEs'),
			'groupmask' => gettext('Groupmask - Mask ACL with group permissions'),
			'passthrough' => gettext('Passthrough - Do not change ACL'),
			'restricted' => gettext('Restricted')
		];
		$property = parent::init_aclmode();
		$property->
			set_id('aclmode')->
			set_defaultvalue('discard')->
			set_description($description)->
			set_options($options)->
			filter_use_default();
		return $property;
	}
	public function init_acltype(): myp\property_list {
		$description = gettext('Controls whether ACLs are enabled and if so what type of ACL to use.');
		$options = [
			'' => gettext('Default'),
			'off' => gettext('Do not use ACLs'),
			'nfsv4' => gettext('Use NFSv4-style ZFS ACLs'),
			'posix' => gettext('Use POSIX ACLs')
		];
		$property = parent::init_acltype();
		$property->
			set_id('acltype')->
			set_defaultvalue('')->
			set_description($description)->
			set_options($options)->
			filter_use_default();
		return $property;
	}
	public function init_atime(): myp\property_list_onoff {
		$description = gettext('Controls whether the access time for files is updated when they are read.');
		$property = parent::init_atime();
		$property->
			set_id('atime')->
			set_defaultvalue('on')->
			set_description($description);
		return $property;
	}
	public function init_canmount(): myp\property_list_onoff {
		$description = gettext('If this property is set to off, the file system cannot be mounted.');
		$options = [
			'noauto' => gettext('Noauto'),
		];
		$property = parent::init_canmount();
		$property->
			set_id('canmount')->
			set_defaultvalue('on')->
			set_description($description)->
			upsert_options($options);
		return $property;
	}
	public function init_checksum(): myp\property_list_onoff {
		$description = gettext('Defines the checksum algorithm.');
		$options = [
			'fletcher2' => 'Fletcher 2',
			'fletcher4' => 'Fletcher 4',
			'sha256' => 'SHA-256',
			'noparity' => gettext('No Parity'),
			'sha512' => 'SHA-512',
			'skein' => 'Skein',
//			'edonr' => 'Edon-R',
		];
		$property = parent::init_checksum();
		$property->
			set_id('checksum')->
			set_description($description)->
			set_defaultvalue('on')->
			upsert_options($options);
		return $property;
	}
	public function init_compression(): myp\property_list_onoff {
		$description = gettext("Controls the compression algorithm. LZ4 is the recommended compression algorithm. Setting compression to 'On' uses the LZ4 compression algorithm if the feature flag lz4_compress is active, otherwise LZJB is used.");
		$options = [
			'gzip' => 'gzip',
			'lz4' => 'lz4',
			'lzjb' => 'lzjb',
			'zle' => 'zle',
			'zstd' => gettext('Zstandard Compression'),
			'zstd-fast' => gettext('Fast Zstandard Compression')
		];
		$property = parent::init_compression();
		$property->
			set_id('compression')->
			set_description($description)->
			set_defaultvalue('on')->
			upsert_options($options);
		return $property;
	}
	public function init_compression_level_gzip(): myp\property_list {
		$description = gettext('Controls the level of the gzip compression algorithm');
		$options = [
			'gzip' => gettext('Default compression level'),
			'gzip-9' => gettext('Compression level 9 - best compression ratio'),
			'gzip-8' => gettext('Compression level 8'),
			'gzip-7' => gettext('Compression level 7'),
			'gzip-6' => gettext('Compression level 6'),
			'gzip-5' => gettext('Compression level 5'),
			'gzip-4' => gettext('Compression level 4'),
			'gzip-3' => gettext('Compression level 3'),
			'gzip-2' => gettext('Compression level 2'),
			'gzip-1' => gettext('Compression level 1 - fastest compression')
		];
		$property = parent::init_compression_level_gzip();
		$property->
			set_id('comression_gzip')->
			set_defaultvalue('gzip')->
			set_description($description)->
			set_options($options)->
			set_input_type($property::INPUT_TYPE_SELECT)->
			filter_use_default();
		return $property;
	}
	public function init_compression_level_zstd(): myp\property_list {
		$description = gettext('Controls the level of the Zstandard compression level');
		$options = [
			'zstd' => gettext('Default compression level'),
			'zstd-19' => gettext('Compression level 19 - best compression ratio'),
			'zstd-18' => gettext('Compression level 18'),
			'zstd-17' => gettext('Compression level 17'),
			'zstd-16' => gettext('Compression level 16'),
			'zstd-15' => gettext('Compression level 15'),
			'zstd-14' => gettext('Compression level 14'),
			'zstd-13' => gettext('Compression level 13'),
			'zstd-12' => gettext('Compression level 12'),
			'zstd-11' => gettext('Compression level 11'),
			'zstd-10' => gettext('Compression level 10'),
			'zstd-9' => gettext('Compression level 9'),
			'zstd-8' => gettext('Compression level 8'),
			'zstd-7' => gettext('Compression level 7'),
			'zstd-6' => gettext('Compression level 6'),
			'zstd-5' => gettext('Compression level 5'),
			'zstd-4' => gettext('Compression level 4'),
			'zstd-3' => gettext('Compression level 3'),
			'zstd-2' => gettext('Compression level 2'),
			'zstd-1' => gettext('Compression level 1 - fastest compression')
		];
		$property = parent::init_compression_level_zstd();
		$property->
			set_id('compression_zstd')->
			set_defaultvalue('zstd')->
			set_description($description)->
			set_options($options)->
			set_input_type($property::INPUT_TYPE_SELECT)->
			filter_use_default();
		return $property;
	}
	public function init_compression_level_zstd_fast(): myp\property_list {
		$description = gettext('Controls the level of the Fast Zstandard compression level');
		$options = [
			'zstd-fast' => gettext('Default compression level'),
			'zstd-fast-1' => gettext('Compression level 1 - best compression ratio'),
			'zstd-fast-2' => gettext('Compression level 2'),
			'zstd-fast-3' => gettext('Compression level 3'),
			'zstd-fast-4' => gettext('Compression level 4'),
			'zstd-fast-5' => gettext('Compression level 5'),
			'zstd-fast-6' => gettext('Compression level 6'),
			'zstd-fast-7' => gettext('Compression level 7'),
			'zstd-fast-8' => gettext('Compression level 8'),
			'zstd-fast-9' => gettext('Compression level 9'),
			'zstd-fast-10' => gettext('Compression level 10'),
			'zstd-fast-20' => gettext('Compression level 20'),
			'zstd-fast-30' => gettext('Compression level 30'),
			'zstd-fast-40' => gettext('Compression level 40'),
			'zstd-fast-50' => gettext('Compression level 50'),
			'zstd-fast-60' => gettext('Compression level 60'),
			'zstd-fast-70' => gettext('Compression level 70'),
			'zstd-fast-80' => gettext('Compression level 80'),
			'zstd-fast-90' => gettext('Compression level 90'),
			'zstd-fast-100' => gettext('Compression level 100'),
			'zstd-fast-500' => gettext('Compression level 500'),
			'zstd-fast-1000' => gettext('Compression level 1000 - fastest compression'),
		];
		$property = parent::init_compression_level_zstd_fast();
		$property->
			set_id('compression_zstd_fast')->
			set_description($description)->
			set_defaultvalue('zstd-fast')->
			set_options($options)->
			set_input_type($property::INPUT_TYPE_SELECT)->
			filter_use_default();
		return $property;
	}
	public function init_copies(): myp\property_list {
		$description = gettext('Controls the number of copies of data stored for this dataset.');
		$options = [
			'' => gettext('Default'),
			'1' => gettext('1 - one copy'),
			'2' => gettext('2 - two copies'),
			'3' => gettext('3 - three copies - not applicable when encryption is enabled')
		];
		$property = parent::init_copies();
		$property->
			set_id('copies')->
			set_description($description)->
			set_defaultvalue('')->
			set_options($options)->
			filter_use_default();
		return $property;
	}
	public function init_dedup(): myp\property_list_offon {
		$description = '<div>' . gettext('Controls the dedup method.') . '</div>'
			. '<div><b>'
			. '<font color="red">' . gettext('WARNING') . '</font>' . ': '
			. '<a href="https://www.xigmanas.com/wiki/doku.php?id=documentation:setup_and_user_guide:disks_zfs_datasets_dataset" target="_blank">'
			. gettext('See ZFS datasets & deduplication wiki article BEFORE using this feature.')
			. '</a>'
			. '</b></div>';
		$options = [
			'verify' => gettext('Verify'),
			'sha256' => 'SHA-256',
			'sha256,verify' => gettext('SHA-256, Verify'),
			'sha512' => 'SHA-512',
			'sha512,verify' => gettext('SHA-512, Verify'),
			'skein' => 'Skein',
			'skein,verify' => gettext('Skein, Verify'),
			'edonr,verify' => gettext('Edon-R, Verify')
		];
		$property = parent::init_dedup();
		$property->
			set_id('dedup')->
			set_description($description)->
			set_defaultvalue('off')->
			upsert_options($options);
		return $property;
	}
	public function init_devices(): myp\property_list_onoff {
		$description = gettext(' Controls whether device nodes can be opened on this file system. The default value is on. The values on and off are equivalent to the dev and nodev mount options.');
		$property = parent::init_devices();
		$property->
			set_id('devices')->
			set_description($description)->
			set_defaultvalue('on');
		return $property;
	}
	public function init_dnodesize(): myp\property_list {
		$description = gettext('Specifies a compatibility mode or literal value for the size of dnodes in the file system.');
		$options = [
			'' => gettext('Default'),
			'legacy' => gettext('Legacy'),
			'auto' => gettext('Auto'),
			'1k' => gettext('1k'),
			'2k' => gettext('2k'),
			'4k' => gettext('4k'),
			'8k' => gettext('8k'),
			'16k' => gettext('16k'),
		];
		$property = parent::init_dnodesize();
		$property->
			set_id('dnodesize')->
			set_defaultvalue('legacy')->
			set_description($description)->
			set_options($options)->
			filter_use_default();
		return $property;
 }
	public function init_exec(): myp\property_list_onoff {
		$description = gettext('Controls whether processes can be executed from within this file system. The default value is on. The values on and off are equivalent to the exec and noexec mount options.');
		$property = parent::init_exec();
		$property->
			set_id('exec')->
			set_description($description)->
			set_defaultvalue('on');
		return $property;
	}
	public function init_filesystem_limit(): myp\property_int {
		$property = parent::init_filesystem_limit();
		return $property;
	}
	public function init_jailed(): myp\property_list_offon {
		$description = gettext('Controls whether the dataset is managed from a jail.');
		$property = parent::init_jailed();
		$property->
			set_id('jailed')->
			set_description($description)->
			set_defaultvalue('off');
		return $property;
	}
	public function init_keyformat(): myp\property_list {
		$description = gettext('Controls the format of the provided encryption key.');
		$options = [
			'raw' => gettext('Raw'),
			'hex' => gettext('Hex'),
			'passphrase' => gettext('Passphrase')
		];
		$property = parent::init_keyformat();
		$property->
			set_id('keyformat')->
			set_description($description)->
			set_defaultvalue('passphrase')->
			set_options($options)->
			filter_use_default();
		return $property;
	}
	public function init_keylocation(): myp\property_text {
		$property = parent::init_keylocation();
		return $property;
	}
	public function init_logbias(): myp\property_list {
		$description = gettext('Provide a hint to ZFS about handling of synchronous requests in this dataset.');
		$options = [
			'latency' => gettext('Latency'),
			'throughput' => gettext('Throughput')
		];
		$property = parent::init_logbias();
		$property->
			set_id('logbias')->
			set_description($description)->
			set_defaultvalue('latency')->
			set_options($options)->
			filter_use_default();
		return $property;
	}
	public function init_mountpoint(): myp\property_text {
		$property = parent::init_mountpoint();
		return $property;
	}
	public function init_mountpoint_type(): myp\property_list {
		$description = gettext('Controls the mount point type used for this file system.');
		$options = [
			'' => gettext('Auto mount'),
			'path' => gettext('User defined mount point'),
			'none' => gettext('Do not mount'),
			'legacy' => gettext('Admin controlled mount')
		];
		$property = parent::init_mountpoint_type();
		$property->
			set_defaultvalue('')->
			set_description($description)->
			set_id('mountpoint_type')->
			set_options($options)->
			filter_use_default();
		return $property;
	}
	public function init_nbmand(): myp\property_list_offon {
		$description = gettext('Controls whether the file system should be mounted with non-blocking mandatory locks.');
		$property = parent::init_nbmand();
		$property->
			set_id('nbmand')->
			set_defaultvalue('off')->
			set_description($description);
		return $property;
	}
	public function init_overlay(): myp\property_list_onoff {
		$description = gettext('Allow mounting on a busy directory or a directory which already contains files or directories.');
		$property = parent::init_overlay();
		$property->
			set_id('overlay')->
			set_defaultvalue('on')->
			set_description($description);
		return $property;
	}
	public function init_pbkdf2iters(): myp\property_int {
		$property = parent::init_pbkdf2iters();
		return  $property;
	}
	public function init_primarycache(): myp\property_list {
		$description = gettext('Controls what is cached in the primary cache (ARC).');
		$options = [
			'all' => gettext('Both user data and metadata will be cached in ARC.'),
			'metadata' => gettext('Only metadata will be cached in ARC.'),
			'none' => gettext('Neither user data nor metadata will be cached in ARC.')
		];
		$property = parent::init_primarycache();
		$property->
			set_id('primarycache')->
			set_description($description)->
			set_defaultvalue('all')->
			set_options($options)->
			filter_use_default();
		return $property;
	}
	public function init_quota(): myp\property_text {
		$description = gettext('Limits the amount of space a dataset and its descendents can consume.');
		$property = parent::init_quota();
		$property->
			set_defaultvalue('')->
			set_description($description)->
			set_filter(FILTER_VALIDATE_REGEXP)->
			set_filter_flags(FILTER_REQUIRE_SCALAR)->
			set_filter_options(['default' => null,'regexp' => $this::REGEXP_SIZEORNONEORNOTHING])->
			set_id('quota');
		return $property;
	}
	public function init_readonly(): myp\property_list_offon {
		$description = gettext('Controls whether this dataset can be modified.');
		$property = parent::init_readonly();
		$property->
			set_id('readonly')->
			set_description($description)->
			set_defaultvalue('off');
		return $property;
	}
	public function init_recordsize(): myp\property_list {
		$description = gettext('Specifies a suggested block size for files in the file system. This property is designed solely for use with database workloads that access files in fixed-size records.');
		$options = [
			'' => gettext('Default'),
			'512B' => gettext('512B'),
			'1K' => gettext('1K'),
			'2K' => gettext('2K'),
			'4K' => gettext('4K'),
			'8K' => gettext('8K'),
			'16K' => gettext('16K'),
			'32K' => gettext('32K'),
			'64K' => gettext('64K'),
			'128K' => gettext('128K'),
			'256k' => gettext('256K'),
			'512K' => gettext('512K'),
			'1M' => gettext('1M')
		];
		$property = parent::init_recordsize();
		$property->
			set_id('recordsize')->
			set_description($description)->
			set_defaultvalue('')->
			set_options($options)->
			filter_use_default();
		return  $property;
	}
	public function init_redundant_metadata(): myp\property_list {
		$description = gettext('Controls what types of metadata are stored redundantly.');
		$options = [
			'all' => gettext('All'),
			'most' => gettext('Most')
		];
		$property = parent::init_redundant_metadata();
		$property->
			set_id('redundant_metadata')->
			set_description($description)->
			set_defaultvalue('all')->
			set_options($options)->
			filter_use_default();
		return $property;
	}
	public function init_refquota(): myp\property_text {
		$description = gettext('Limits the amount of space a dataset can consume.');
		$property = parent::init_refquota();
		$property->
			set_id('refquota')->
			set_description($description)->
			set_defaultvalue('')->
			set_filter(FILTER_VALIDATE_REGEXP)->
			set_filter_flags(FILTER_REQUIRE_SCALAR)->
			set_filter_options(['default' => null,'regexp' => $this::REGEXP_SIZEORNONEORNOTHING]);
		return $property;
	}
	public function init_refquota_type(): myp\property_list {
		$description = gettext('Refquota type.');
		$options = [
			'none' => gettext('None'),
			'size' => gettext('User defined refquota')
		];
		$property = parent::init_refquota_type();
		$property->
			set_id('refquota')->
			set_description($description)->
			set_defaultvalue('none')->
			set_options($options)->
			filter_use_default();
		return  $property;
	}
	public function init_refreservation(): myp\property_text {
		$description = gettext('Defines the minimum amount of space guaranteed to a dataset, not including its descendents.');
		$property = parent::init_refreservation();
		$property->
			set_id('refreservation')->
			set_description($description)->
			set_defaultvalue('')->
			set_filter(FILTER_VALIDATE_REGEXP)->
			set_filter_flags(FILTER_REQUIRE_SCALAR)->
			set_filter_options(['default' => null,'regexp' => $this::REGEXP_SIZEORNONEORNOTHING]);
		return $property;
	}
	public function init_refreservation_type(): myp\property_list {
		$description = gettext('Refreservation type.');
		$options = [
			'none' => gettext('None'),
			'auto' => gettext('Auto'),
			'size' => gettext('User defined refreservation')
		];
		$property = parent::init_refreservation_type();
		$property->
			set_id('refreservation_type')->
			set_description($description)->
			set_defaultvalue('none')->
			set_options($options)->
			filter_use_default();
		return $property;
	}
	public function init_relatime(): myp\property_list_offon {
		$description = gettext('Turning this property on causes the access time to be updated relative to the modify or change time. The default value is off');
		$property = parent::init_relatime();
		$property->
			set_id('relatime')->
			set_description($description)->
			set_defaultvalue('off');
		return $property;
	}
	public function init_reservation(): myp\property_text {
		$description = gettext('The minimum amount of space guaranteed to a dataset and its descendents.');
		$property = parent::init_reservation();
		$property->
			set_id('reservation')->
			set_description($description)->
			set_defaultvalue('')->
			set_filter(FILTER_VALIDATE_REGEXP)->
			set_filter_flags(FILTER_REQUIRE_SCALAR)->
			set_filter_options(['default' => null,'regexp' => $this::REGEXP_SIZEORNONEORNOTHING]);
		return $property;
	}
	public function init_reservation_type(): myp\property_list {
		$description = gettext('Reservation type.');
		$options = [
			'none' => gettext('None'),
			'size' => gettext('User defined reservation')
		];
		$property = parent::init_reservation_type();
		$property->
			set_id('reservation_type')->
			set_description($description)->
			set_defaultvalue('none')->
			set_options($options)->
			filter_use_default();
		return $property;
	}
	public function init_secondarycache(): myp\property_list {
		$description = gettext('Controls what is cached in the secondary cache (L2ARC).');
		$options = [
			'all' => gettext('Both user data and metadata will be cached in L2ARC.'),
			'metadata' => gettext('Only metadata will be cached in L2ARC.'),
			'none' => gettext('Neither user data nor metadata will be cached in L2ARC.')
		];
		$property = parent::init_secondarycache();
		$property->
			set_id('secondarycache')->
			set_description($description)->
			set_defaultvalue('all')->
			set_options($options)->
			filter_use_default();
		return $property;
	}
	public function init_setuid(): myp\property_list_onoff {
		$description = gettext('Controls whether the set-UID bit is respected for the file system.');
		$property = parent::init_setuid();
		$property->
			set_id('setuid')->
			set_description($description)->
			set_defaultvalue('on');
		return $property;
	}
	public function init_sharenfs(): myp\property_text {
		$description = gettext('Share NFS options.');
		$placeholder = gettext('Options');
		$property = parent::init_sharenfs();
		$property->
			set_id('sharenfs')->
			set_description($description)->
			set_defaultvalue('')->
			set_placeholder($placeholder)->
			filter_use_default_or_empty();
		return $property;
	}
	public function init_sharenfs_type(): myp\property_list {
		$description = gettext('Controls whether the file system is shared via NFS.');
		$options = [
			'off' => gettext('Off - do not share'),
			'on' => gettext('On - share with default permissions'),
			'custom' => gettext('Custom sharing')
		];
		$property = parent::init_sharenfs_type();
		$property->
			set_id('sharenfs_type')->
			set_description($description)->
			set_defaultvalue('off')->
			set_options($options)->
			filter_use_default();
		return $property;
	}
	public function init_sharesmb(): myp\property_text {
		$description = gettext('Share SMB options.');
		$placeholder = gettext('Options');
		$property = parent::init_sharesmb();
		$property->
			set_id('sharesmb')->
			set_description($description)->
			set_defaultvalue('')->
			set_placeholder($placeholder)->
			filter_use_default_or_empty();
		return $property;
	}
	public function init_sharesmb_type(): myp\property_list_offon {
		$description = gettext('Controls whether the file system is shared via SMB.');
		$options = [
			'options' => gettext('Samba sharing')
		];
		$property = parent::init_sharesmb_type();
		$property->
			set_id('sharesmb_type')->
			set_description($description)->
			set_defaultvalue('off')->
			upsert_options($options);
		return $property;
	}
	public function init_snapdev(): myp\property_list {
		$description = gettext('Controls whether the volume snapshot devices under /dev/zvol/<pool> are hidden or visible.');
		$options = [
			'hidden' => gettext('Hidden'),
			'visible' => gettext('Visible'),
		];
		$property = parent::init_snapdev();
		$property->
			set_id('snapdev')->
			set_description($description)->
			set_defaultvalue('hidden')->
			set_options($options)->
			filter_use_default();
		return $property;
	}
	public function init_snapdir(): myp\property_list {
		$description = gettext('Controls whether the .zfs directory is hidden or visible in the root of the file system.');
		$options = [
			'hidden' => gettext('Hidden'),
			'visible' => gettext('Visible'),
		];
		$property = parent::init_snapdir();
		$property->
			set_id('snapdir')->
			set_description($description)->
			set_defaultvalue('hidden')->
			set_options($options)->
			filter_use_default();
		return $property;
	}
	public function init_snapshot_limit(): myp\property_int {
		$description = gettext('Snapshot limit');
		$property = parent::init_snapshot_limit();
		$property->
			set_id('snapshot_limit')->
			set_description($description)->
			set_min(1)->
			set_defaultvalue('')->
			filter_use_default();
		return $property;
	}
	public function init_snapshot_limit_type(): myp\property_list {
		$description = gettext('Snapshot limit');
		$options = [
			'none' => gettext('None'),
			'size' => gettext('User defined limit')
		];
		$property = parent::init_snapshot_limit_type();
		$property->
			set_id('snapshot_limit_type')->
			set_description($description)->
			set_defaultvalue('none')->
			set_options($options)->
			filter_use_default();
		return $property;
	}
	public function init_special_small_blocks(): myp\property_list {
		$description = gettext('This value represents the threshold block size for including small file blocks into the special allocation class.');
		$options = [
			'' => gettext('Default'),
			'512B' => gettext('512 bytes'),
			'1K' => gettext('1K'),
			'2K' => gettext('2K'),
			'4K' => gettext('4K'),
			'8K' => gettext('8K'),
			'16K' => gettext('16K'),
			'32K' => gettext('32K'),
			'64K' => gettext('64K'),
			'128K' => gettext('128K'),
			'256K' => gettext('256K'),
			'512K' => gettext('512K'),
			'1M' => gettext('1M')
		];
		$property = parent::init_special_small_blocks();
		$property->
			set_id('special_small_blocks')->
			set_description($description)->
			set_defaultvalue('')->
			set_options($options)->
			set_input_type($property::INPUT_TYPE_SELECT)->
			filter_use_default();
		return $property;
	}
	public function init_sync(): myp\property_list {
		$description = gettext('Controls the behavior of synchronous requests.');
		$options = [
			'standard' => gettext('Standard'),
			'always' => gettext('Always'),
			'disabled' => gettext('Disabled')
		];
		$property = parent::init_sync();
		$property->
			set_id('sync')->
			set_description($description)->
			set_defaultvalue('standard')->
			set_options($options)->
			filter_use_default();
		return $property;
	}
	public function init_volmode(): myp\property_list {
		$description = gettext('Specifies how the volume should be exposed to the OS.');
		$options = [
			'default' => gettext('Default'),
			'full' => gettext('full'),
			'dev' => gettext('dev'),
			'none' => gettext('none')
		];
		$property = parent::init_volmode();
		$property->
			set_id('volmode')->
			set_description($description)->
			set_defaultvalue('default')->
			set_options($options)->
			filter_use_default();
		return $property;
	}
	public function init_volsize(): myp\property_text {
		$description = gettext('ZFS volume size. You can use human-readable suffixes like K, KB, M, GB.');
		$property = parent::init_volsize();
		$property->
			set_id('volsize')->
			set_description($description)->
			set_defaultvalue('')->
			set_size(20)->
			set_maxlength(20)->
			set_filter(FILTER_VALIDATE_REGEXP)->
			set_filter_flags(FILTER_REQUIRE_SCALAR)->
			set_filter_options(['default' => null,'regexp' => $this::REGEXP_SIZE]);
		return $property;
	}
	public function init_vscan(): myp\property_list_offon {
		$description = gettext('Controls whether regular files should be scanned for viruses when a file is opened and closed. In addition to enabling this property, the virus scan service must also be enabled for virus scanning to occur.');
		$property = parent::init_vscan();
		$property->
			set_id('vscan')->
			set_description($description)->
			set_defaultvalue('off');
		return $property;
	}
	public function init_xattr(): myp\property_list_onoff {
		$description = gettext('Controls whether extended attributes are enabled for this file system.');
		$options = [
			'sa' => gettext('System attribute based xattrs')
		];
		$property = parent::init_xattr();
		$property->
			set_defaultvalue('on')->
			set_description($description)->
			set_id('xattr')->
			upsert_options($options);
		return $property;
	}
}
