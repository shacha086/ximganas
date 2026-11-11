<?php
/*
	cfg_toolbox.php

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

namespace system\proxy\http;

use common\arr;

/**
 *	Wrapper class for autoloading functions
 */
class cfg_toolbox {
/**
 *	Read http proxy information from config and set environment variables for request
 *	@global type $config
 */
	public static function putenv_http_proxy() {
		global $config;

		$cfg_http_proxy = arr::make_branch($config,'system','proxy','http');
		$http_proxy_enabled = $cfg_http_proxy['enable'] ?? false;
		if(is_bool($http_proxy_enabled) ? $http_proxy_enabled : true):
			$cfg_http_proxy_address = $cfg_http_proxy['address'] ?? null;
			if(!is_null($cfg_http_proxy_address)):
				$cfg_http_proxy_port = $cfg_http_proxy['port'] ?? null;
				if(is_null($cfg_http_proxy_port)):
					$http_proxy_string = sprintf('HTTP_PROXY="%s"',$cfg_http_proxy_address);
				else:
					$http_proxy_string = sprintf('HTTP_PROXY="%s:%s"',$cfg_http_proxy_address,$cfg_http_proxy_port);
				endif;
				putenv($http_proxy_string);
			endif;
			$http_proxy_auth_enabled = $cfg_http_proxy['auth'] ?? false;
			if(is_bool($http_proxy_auth_enabled) ? $http_proxy_auth_enabled : true):
				$cfg_http_proxy_username = $cfg_http_proxy['username'] ?? null;
				if(!(is_null($cfg_http_proxy_username))):
					$cfg_http_proxy_password = $cfg_http_proxy['password'] ?? null;
					if(!(is_null($cfg_http_proxy_password))):
						$http_proxy_auth_string = sprintf('HTTP_PROXY_AUTH="%s:%s:%s:%s"','basic','*',$cfg_http_proxy_username,$cfg_http_proxy_password);
						putenv($http_proxy_auth_string);
					endif;
				endif;
			endif;
		endif;
	}
}
