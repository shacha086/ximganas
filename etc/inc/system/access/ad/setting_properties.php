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

namespace system\access\ad;

use common\properties as myp;

class setting_properties extends grid_properties {
	public function init_enable(): myp\property_enable {
		$property = parent::init_enable();
		$property->set_defaultvalue(false);
		return $property;
	}
	public function init_domaincontrollername(): myp\property_text {
		$description = gettext('Enter AD or PDC name.');
		$placeholder = gettext('Controller Name');
		$property = parent::init_domaincontrollername();
		$property->
			set_id('domaincontrollername')->
			set_description($description)->
			set_defaultvalue('')->
			set_placeholder($placeholder)->
			set_size(60)->
			set_maxlength(63)->
			set_filter(FILTER_VALIDATE_DOMAIN)->
			set_filter_flags(FILTER_FLAG_HOSTNAME)->
			set_filter_options(['default' => null]);
		return $property;
	}
	public function init_domainname_dns(): myp\property_text {
		$description = gettext('Enter the domain name.');
		$placeholder = gettext('example.com');
		$property = parent::init_domainname_dns();
		$property->
			set_id('domainname_dns')->
			set_description($description)->
			set_defaultvalue('')->
			set_placeholder($placeholder)->
			set_size(60)->
			set_maxlength(256)->
			set_filter(FILTER_VALIDATE_DOMAIN)->
			set_filter_options(['default' => null]);
		return $property;
	}
/**
 *	https://learn.microsoft.com/en-gb/troubleshoot/windows-server/identity/naming-conventions-for-computer-domain-site-ou
 *	@param mixed $test
 *	@return ?string
 */
	public function test_domainname_netbios($test = ''): ?string {
//		must be of type string
		if(!is_string($test)):
			return null;
		endif;
//		NETBIOS name cannot have more than 15 characters
		if(mb_strlen(string: $test) > 15):
			return null;
		endif;
//		minimum name length is 1
		if(mb_strlen(string: $test) < 1):
			return null;
		endif;
//		NETBIOS name cannot contain digits only (DNS restriction).
		if(preg_match('/.*[^0-9].*/',$test) !== 1):
			return null;
		endif;
//		NETBIOS name cannot contain any of these characters: \/:*?"<>|
//		hostname cannot contain any of these characters: ,~:!@#$%^ &‘.(){}_
//		merging the above to \/:*?"<>|,~!@#$%^ &‘.(){}_
		$regex = sprintf('/%s/',preg_quote('\/:*?"<>|,~!@#$%^ &‘.(){}_','/'));
		if(preg_match($regex,$test) === 1):
			return null;
		endif;
//		the last character cannot be a minus sign or a period (period is already denied)
		if(preg_match('/\-$/',$test) === 1):
			return null;
		endif;
		$test_upper = strtoupper($test);
//		testing for reserved words
		$reserved_words = [
			'ANONYMOUS','AUTHENTICATED USER','BATCH','BUILTIN',
			'CREATOR GROUP','CREATOR GROUP SERVER','CREATOR OWNER','CREATOR OWNER SERVER',
			'DIALUP','DIGEST AUTH','INTERACTIVE','INTERNET',
			'LOCAL','LOCAL SYSTEM','NETWORK','NETWORK SERVICE',
			'NT AUTHORITY','NT DOMAIN','NTLM AUTH','NULL',
			'PROXY','REMOTE INTERACTIVE','RESTRICTED','SCHANNEL AUTH',
			'SELF','SERVER','SERVICE','SYSTEM',
			'TERMINAL SERVER','THIS ORGANIZATION','USERS','WORLD'
		];
//		mb_strtoupper is not needed
		if(in_array($test_upper,$reserved_words)):
			return null;
		endif;
//		testing SDDL SID strings
		$sddl_sid = [
			'AA','AC','AN','AO','AP','AU','BA','BG',
			'BO','BU','CA','CD','CG','CN','CO','CY',
			'DA','DC','DD','DG','DU','EA','ED','EK',
			'ER','ES','HA','HI','IS','IU','KA','LA',
			'LG','LS','LU','LW','ME','MP','MU','NO',
			'NS','NU','OW','PA','PO','PS','PU','RA',
			'RC','RD','RE','RM','RO','RS','RU','SA',
			'SI','SO','SS','SU','SY','UD','WD','WR'
		];
//		mb_strtoupper is not needed
		if(in_array($test_upper,$sddl_sid)):
			return null;
		endif;
//		test passed
		return $test;
	}
	public function init_domainname_netbios(): myp\property_text {
		$description = gettext('Enter NetBIOS name.');
		$placeholder = gettext('NETBIOS');
		$property = parent::init_domainname_netbios();
		$property->
			set_id('domainname_netbios')->
			set_description($description)->
			set_defaultvalue('')->
			set_placeholder($placeholder)->
			set_size(60)->
			set_maxlength(15)->
			set_filter(FILTER_CALLBACK)->
			set_filter_options([$this,'test_domainname_netbios']);
		return $property;
	}
	public function init_username(): myp\property_text {
		$description = gettext('Enter user name of a domain administrator account.');
		$placeholder = gettext('Admin');
		$regexp = '/^((?!["\':])\S){1,32}$/';
		$property = parent::init_username();
		$property->
			set_id('username')->
			set_description($description)->
			set_defaultvalue('')->
			set_placeholder($placeholder)->
			set_size(60)->
			set_maxlength(32)->
			set_filter(FILTER_VALIDATE_REGEXP)->
			set_filter_flags(FILTER_REQUIRE_SCALAR)->
			set_filter_options(['default' => null,'regexp' => $regexp]);
		return $property;
	}
	public function init_password_encoded(): myp\property_text {
		$description = gettext('Enter the password of the domain administrator account.');
		$placeholder = gettext('Password');
		$property = parent::init_password_encoded();
		$property->
			set_id('password_encoded')->
			set_description($description)->
			set_defaultvalue('')->
			set_input_type($property::INPUT_TYPE_PASSWORD)->
			set_placeholder($placeholder)->
			set_size(60)->
			set_maxlength(256)->
			set_filter(FILTER_DEFAULT);
		return $property;
	}
}
