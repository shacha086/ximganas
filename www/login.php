<?php
/*
	login.php

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

require_once 'autoload.php';

use common\arr;
use common\session;

session::start();

require_once 'guiconfig.inc';

unset($input_errors);
$rm_name = 'REQUEST_METHOD';
$rm_activities = ['POST' => 'POST'];
if(array_key_exists($rm_name,$_SERVER)):
	$rm_value = filter_var($_SERVER[$rm_name],FILTER_CALLBACK,['options' =>
		function(string $value) use ($rm_activities) { return array_key_exists($value,$rm_activities) ? $value : null; }
	]);
else:
	$rm_value = null;
endif;
if(isset($rm_value)):
	switch($rm_value):
		case 'POST':
//			FreeBSD reference for regular expression: usr.sbin/pw/pw_user.c -> pw_checkname
			$regexp = '/^[0-9A-Za-z\.;\[\]_\{\}][0-9A-Za-z\-\.;\[\]_\{\}]*\$?$/';
			$username = filter_input(INPUT_POST,'username',FILTER_VALIDATE_REGEXP,['flags' => FILTER_REQUIRE_SCALAR,'options' => ['default' => null,'regexp' => $regexp]]);
			$remote_addr = (isset($_SERVER['REMOTE_ADDR']) && is_string($_SERVER['REMOTE_ADDR'])) ? $_SERVER['REMOTE_ADDR'] : '';
			if(isset($username)):
				arr::make_branch($config,'system');
				$authentication_successful = false;
				$continue_checking = true;
				if(isset($config['system']['username']) && is_string($config['system']['username']) && ($username === $config['system']['username'])):
					if($continue_checking):
						$continue_checking = false;
						$password = (isset($_POST['password']) && is_string($_POST['password'])) ? $_POST['password'] : null;
						if(isset($password)):
							$continue_checking = true;
						else:
							write_log(sprintf('AUTH: No password provided for username %s from IP address %s',$username,$remote_addr));
						endif;
					endif;
					if($continue_checking):
						$continue_checking = false;
						if(isset($config['system']['password']) && is_string($config['system']['password'])):
							$continue_checking = true;
						else:
							write_log(sprintf('AUTH: No password configured for username %s from IP address %s',$username,$remote_addr));
						endif;
					endif;
					if($continue_checking):
						$continue_checking = false;
//						verify password
						if(password_verify($password,$config['system']['password'])):
							$authentication_successful = true;
						else:
							write_log(sprintf('AUTH: Invalid password entererd for username %s from IP address %s',$username,$remote_addr));
						endif;
					endif;
					if($authentication_successful):
						write_log(sprintf('AUTH: %s logged in from IP address %s',$username,$remote_addr));
						session::init(0,$username,true);
						header('Location: index.php');
						exit;
					endif;
				else:
					if($continue_checking):
						$continue_checking = false;
//						check if username is listed as a system user
						$users = system_get_user_list();
						$system_user_row_id = arr::search_ex($username,$users,'name');
						if($system_user_row_id !== false):
							$system_user = $users[$system_user_row_id];
							$continue_checking = true;
						else:
							write_log(sprintf('AUTH: Username %s not found from IP address %s',$username,$remote_addr));
						endif;
					endif;
					if($continue_checking):
						$continue_checking = false;
//						check if UID column exists
						if(array_key_exists('uid',$system_user)):
							$continue_checking = true;
						else:
							write_log(sprintf('AUTH: UID for username %s not found from IP address %s',$username,$remote_addr));
						endif;
					endif;
					if($continue_checking):
						$continue_checking = false;
//						check if it is a local user
						arr::make_branch($config,'access','user');
						$portal_user_row_id = arr::search_ex($system_user['uid'],$config['access']['user'],'id');
						if($portal_user_row_id !== false):
							$portal_user = $config['access']['user'][$portal_user_row_id];
							$continue_checking = true;
						else:
							write_log(sprintf('AUTH: Username %s not found in portal configuration from IP address %s',$username,$remote_addr));
						endif;
					endif;
					if($continue_checking):
						$continue_checking = false;
//						check if a password has been received
						$password = (isset($_POST['password']) && is_string($_POST['password'])) ? $_POST['password'] : null;
						if(isset($password)):
							$continue_checking = true;
						else:
							write_log(sprintf('AUTH: No password provided for username %s from IP address %s',$username,$remote_addr));
						endif;
					endif;
					if($continue_checking):
						$continue_checking = false;
//						check if password has been configured for user
						if(isset($system_user['password']) && is_string($system_user['password'])):
							$continue_checking = true;
						else:
							write_log(sprintf('AUTH: No password configured for username %s from IP address %s',$username,$remote_addr));
						endif;
					endif;
					if($continue_checking):
						$continue_checking = false;
//						verify password
						if(password_verify($password,$system_user['password'])):
							$authentication_successful = true;
						else:
							write_log(sprintf('AUTH: Invalid password entererd for username %s from IP address %s',$username,$remote_addr));
						endif;
					endif;
					if($authentication_successful):
						if(isset($portal_user['userportal']) && is_string($portal_user['userportal'])):
							switch($portal_user['userportal']):
								case 'admin':
//									user has admin access permission
									$id = $system_user['uid'];
									$name = $system_user['name'];
									$has_admin_rights = true;
									$language = $portal_user['language'] ?? null;
									$timezone = $portal_user['timezone'] ?? $config['system']['timezone'] ?? 'UTC';
									write_log(sprintf('AUTH: %s logged in from IP address %s',$username,$remote_addr));
									session::init($id,$name,$has_admin_rights,$timezone);
									system_language_load($language);
									header('Location: index.php');
									exit;
									break;
								case '1':
//									user has user portal permission
									$id = $system_user['uid'];
									$name = $system_user['name'];
									$has_admin_rights = false;
									$language = $portal_user['language'] ?? null;
									$timezone = $portal_user['timezone'] ?? $config['system']['timezone'] ?? 'UTC';
									write_log(sprintf('AUTH: %s logged in from IP address %s',$username,$remote_addr));
									session::init($id,$name,$has_admin_rights,$timezone);
									system_language_load($language);
									header('Location: index.php');
									exit;
									break;
								default:
									write_log(sprintf('AUTH: No portal access for username %s from IP address %s',$username,$remote_addr));
									$language = null;
									break;
							endswitch;
						else:
							write_log(sprintf('AUTH: No portal access configured for username %s from IP address %s',$username,$remote_addr));
						endif;
					endif;
				endif;
				$input_errors = gettext('Invalid login credentials.');
			else:
				write_log(sprintf('AUTH: Empty or invalid username %s from IP address %s',$username,$remote_addr));
				$input_errors = gettext('Invalid login credentials.');
			endif;
			break;
	endswitch;
endif;
$document = new_page([],'login.php','login');
$pagecontent = $document->getElementById('pagecontent');
$loginpagedata = $pagecontent->
	addDIV(['class' => 'loginpage-workspace'])->
		addDIV(['class' => 'loginpage-frame'])->
			addDIV(['class' => 'loginpage-data']);
$loginpagedata->
	addElement('header',['class' => 'loginpage-header'])->
		push()->
		addDIV(['class' => 'loginpage-header-logo'])->
			push()->
			addDIV(['class' => 'loginpage-header-logo-left'])->
				insIMG(['src' => '/images/lock.png','alt' => ''])->
			pop()->
			addDIV(['class' => 'loginpage-header-logo-right'])->
				addA(['title' => sprintf('www.%s',get_product_url()),'href' => sprintf('https://www.%s',get_product_url()),'target' => '_blank','rel' => "noreferrer"])->
					insIMG(['src' => '/images/login_logo.png','alt' => 'logo'])->
		pop()->
		addDIV(['class' => 'loginpage-header-hostname'])->
			insDIV(['class' => 'loginpage-hostname'],system_get_hostname());
$loginpagedata->
	addDIV(['class' => 'loginpage-body'])->
		insINPUT(['type' => 'text','id' => 'username','class' => 'loginpage-body-item','name' => 'username','placeholder' => gettext('Username'),'autofocus' => 'autofocus','autocomplete' => 'username'])->
		insINPUT(['type' => 'password','id' => 'password','class' => 'loginpage-body-item','name' => 'password','placeholder' => gettext('Password'),'autocomplete' => 'current-password'])->
		insINPUT(['type' => 'submit','value' => gettext('Login')]);
$loginpagedata->
	addElement('footer',['class' => 'loginpage-footer'])->
		push()->addDIV(['class' => 'loginpage-footer-item'])->
			insA(['target' => '_blank','rel' => 'noreferrer','href' => 'https://www.xigmanas.com/forums/'],gettext('Forum'))->
		last()->addDIV(['class' => 'loginpage-footer-item'])->
			insA(['target' => '_blank','rel' => 'noreferrer','href' => 'https://www.xigmanas.com/wiki/doku.php'],gettext('Information & Manuals'))->
		last()->addDIV(['class' => 'loginpage-footer-item'])->
			insA(['target' => '_blank','rel' => 'noreferrer','href' => 'https://web.libera.chat/#xigmanas'],gettext('IRC XigmaNAS'))->
		pop()->addDIV(['class' => 'loginpage-footer-item'])->
			insA(['target' => '_blank','rel' => 'noreferrer','href' => 'https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=info%40xigmanas%2ecom&lc=US&item_name=XigmaNAS&currency_code=USD&bn=PP%2dDonationsBF%3abtn_donateCC_LG%2egif%3aNonHosted'],gettext('Donate'));
if(!empty($input_errors)):
	$loginpagedata->
		insDIV(['class' => 'loginpage-error'],$input_errors);
endif;
$document->add_js_document_ready(sprintf('document.getElementById("%s").focus();','username'));
$document->render();
