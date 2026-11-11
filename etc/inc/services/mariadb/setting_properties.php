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

namespace services\mariadb;

use common\properties as myp;

class setting_properties extends grid_properties {
	public function init_enable(): myp\property_enable {
		$property = parent::init_enable();
		$property->set_defaultvalue(false);
		return $property;
	}
/**
 *	Test if home folder exists and is not a folder on a memory disk
 *	@param string $test
 *	@return string|null
 */
	public function test_homedir($test = ''): ?string {
//		only strings starting with / are allowed
		if(is_string($test) && preg_match('~^/~',$test) === 1):
//			it must be a folder
			if(is_dir($test)):
//				get the name from df
				$cmd = sprintf('df --libxo:J %s',escapeshellarg($test));
				$json_string = shell_exec($cmd);
				$rawdata = json_decode($json_string,true) ?? [];
				if(is_array($rawdata)):
					foreach($rawdata['storage-system-information']['filesystem'] as $element):
//						don't allow xmd, md and tmpfs
						if(preg_match('~^(/dev/x?md[0-9]|tmpfs)~',$element['name']) === 1):
							return null;
						endif;
					endforeach;
//					test passed
					return $test;
				endif;
			endif;
		endif;
		return null;
	}
	public function init_homedir(): myp\property_text {
		$description =
			gettext('Enter the path of the home directory for databases and configuration files.')
			. '<br />'
			. gettext('The server will be started with the minimum required parameters.')
			. '<br />'
			. gettext("In this directory, the configuration file 'my.cnf' will be created.")
			. '  '
			. '<a href="https://mariadb.com/kb/en/mariadb/configuring-mariadb-with-mycnf" target="_blank" rel="noreferrer">' . gettext('Please read the documentation') . '</a>.';
		$placeholder = $placeholderv = gettext('Path');
		$property = parent::init_homedir();
		$property->
			set_id('homedir')->
			set_input_type($property::INPUT_TYPE_FILECHOOSER)->
			set_description($description)->
			set_placeholder($placeholder)->
			set_placeholderv($placeholderv)->
			set_defaultvalue('')->
			set_filter(FILTER_CALLBACK)->
			set_filter_options([$this,'test_homedir']);
		return $property;
	}
	public function init_auxparam(): myp\property_auxparam {
		$description = gettext('These parameters will be added to my.cnf');
		$property = parent::init_auxparam();
		$property->set_description($description);
		return $property;
	}
	public function init_phrasecookieauth(): myp\property_text {
		$description = gettext("The cookie-based auth_type uses AES algorithm to encrypt the password. Enter a random passphrase of your choice. It will be used internally by the AES algorithm - you won't be prompted for this passphrase. The secret should be at least 32 or more characters long");
		$placeholder = $placeholderv = gettext('Passphrase');
		$regexp = '/^(|\S{32,128})$/';
		$property = parent::init_phrasecookieauth();
		$property->
			set_id('phrasecookieauth')->
			set_input_type($property::INPUT_TYPE_PASSWORD)->
			set_description($description)->
			set_placeholder($placeholder)->
			set_placeholderv($placeholderv)->
			set_defaultvalue('')->
			set_size(60)->
			set_maxlength(128)->
			set_filter(FILTER_VALIDATE_REGEXP)->
			set_filter_flags(FILTER_REQUIRE_SCALAR)->
			set_filter_options(['default' => null,'regexp' => $regexp]);
		return $property;
	}
}
