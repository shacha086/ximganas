<?php
/*
	fm_init.php

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

namespace filemanager;

trait fm_init {
	use fm_debug;
	use fm_login;

	public function init() {
		$this->_debug('Initializing ---------------------------------------------------');
/*
		$this->_debug('xxx3 action: ' . $_GET['action'] ?? '' . '/' . $_GET['do_action'] ?? '' . '/' . (isset($_GET['action']) ? 'true' : 'false'));
 */
		$this->action = $_GET['action'] ?? 'list';
		if($this->action == 'post' && isset($_POST['do_action'])):
			$this->action = $_POST['do_action'];
		endif;
		if($this->action == ''):
			$this->action = 'list';
		endif;
/*
		$this->_debug('xxx3 action: ' . $_GET['action'] ?? '' . '/' . $_GET['do_action'] ?? '' . '/' . (isset($_GET['action']) ? 'true' : 'false'));
 */
//		Get Item
		$this->item = $_GET['item'] ?? '';
//		Get Sort
		$this->order = filter_input(INPUT_GET,'order',FILTER_VALIDATE_REGEXP,['flags' => FILTER_REQUIRE_SCALAR,'options' => ['default' => 'name','regexp' => '/\S/']]);
//		Get Sortorder (yes==up)
		$this->srt = filter_input(INPUT_GET,'srt',FILTER_VALIDATE_REGEXP,['flags' => FILTER_REQUIRE_SCALAR,'options' => ['default' => 'yes','regexp' => '/^(yes|no)$/']]);
//		Necessary files
//		prevent unwanted output
		ob_start();
/*
		BEGIN XigmaNAS® CODE
 */
		if(function_exists('date_default_timezone_set')):
			if(function_exists('date_default_timezone_get')):
				@date_default_timezone_set(@date_default_timezone_get());
			else:
				@date_default_timezone_set('UTC');
			endif;
		endif;
/*
 *		END XigmaNAS® CODE
 */
/*
 *		ORIGINAL CODE
 *		date_default_timezone_set ( "UTC" );
 */
//		prevent unwanted output
		ob_start();
		$this->login();
//		get rid of cached unwanted output
		ob_end_clean();
	}
}
