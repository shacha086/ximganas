<?php
/*
	session.php

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

namespace common;

/*
	$_SESSION

	@start

	admin -------------------> boolean True when logged in user has admin rights, false otherwise
	at ----------------------> int     Timestamp
	authtoken ---------------> string  The authentication token
	login -------------------> boolean True when user logged in successfully
	ts ----------------------> int     Timestamp
	tz ----------------------> string  Timezone
	uid ---------------------> string  User ID
	uname -------------------> string  Login name of the user

	@runtime

	access.hidesystemgroups -> boolean Hide/show system groups in Access > Groups
	access.hidesystemusers --> boolean Hide/Show system users in Access > Users
	cpu ---------------------> array
	dev ---------------------> array
	filter_time_id ----------> string  Filter for snapshot overview in disks_zfs_snapshot.php
	g -----------------------> array   Global array containing session related information
		headermenu ----------> array   Cache for header menu
	kern.cp_times.{id} ------> system_get_cpu_usage
	kern.cp_times.{id} ------> system_get_smp_cpu_usage
	{scriptname} ------------> int     Return value of 'Apply' action
	submit ------------------> string  Name of the script submitting the form
	userlang ----------------> string  Language
	statusgraphdukey --------> string  Script status_graph_disk_usage, reporting disk
	statusgraphmemorylevel---> string  Script status_graph_memory.php, reporting level 'memory' or 'memory-detailed'
	statusgraphnetworkif-----> string  Script status_graph_network.php, reporting interface
*/

class session {
/**
 *	Start session.
 */
	public static function start() {
		switch(session_status()):
//			case PHP_SESSION_DISABLED:
//				break;
			case PHP_SESSION_NONE:
				session_start();
				$_SESSION['at'] = time();
				break;
//			case PHP_SESSION_ACTIVE:
//				break;
		endswitch;
	}
	public static function commit() {
		switch(session_status()):
//			case PHP_SESSION_DISABLED:
//				break;
//			case PHP_SESSION_NONE:
//				break;
			case PHP_SESSION_ACTIVE:
				session_write_close();
				break;
		endswitch;
	}
/**
 *	Destroy session.
 */
	public static function destroy() {
		switch(session_status()):
//			case PHP_SESSION_DISABLED:
//				break;
			case PHP_SESSION_NONE:
				session_start();
				session_destroy();
				break;
			case PHP_SESSION_ACTIVE:
				session_destroy();
				break;
		endswitch;
	}
/**
 *	Initialize user.
 *	@param string $uid The user ID
 *	@param string $uname The user name
 *	@param bool $admin
 *	@param string $timezone
 */
	public static function init(string $uid,string $uname,bool $admin = false,?string $timezone = null) {
		switch(session_status()):
//			case PHP_SESSION_DISABLED:
//				break;
//			case PHP_SESSION_NONE:
//				break;
			case PHP_SESSION_ACTIVE:
				session_regenerate_id(true);
				$_SESSION['authtoken'] = bin2hex(random_bytes(32));
				$_SESSION['admin'] = $admin;
				$_SESSION['login'] = true;
				$_SESSION['ts'] = time();
				$_SESSION['uid'] = $uid;
				$_SESSION['uname'] = $uname;
				$_SESSION['tz'] = $timezone;
				$_SESSION['g'] = [
					'headermenu' => []
				];
				break;
		endswitch;
	}
/**
 *	Has the current user administration permissions?
 *	@return true if the current user has administration permissions, otherwise false.
 */
	public static function is_admin() {
		if(!isset($_SESSION['admin']) || !$_SESSION['admin']):
			return false;
		endif;
		return true;
	}
/**
 *	Is the login flag set?
 */
	public static function is_login() {
		if(!isset($_SESSION['login']) || !$_SESSION['login']):
			return false;
		endif;
	 	return $_SESSION['login'];
	}
/**
 *	Validate the given token.
 *	@param authtoken The token to be validated.
 *	@return true if the token is valid, otherwise false.
 */
	public static function is_valid_authtoken($authtoken) {
		if(!isset($_SESSION['authtoken']) || !$_SESSION['authtoken']):
			return false;
		endif;
		return hash_equals($_SESSION['authtoken'],$authtoken);
	}
/**
 *	Get the current authentication token.
 *	@return The current authentication token.
 */
	public static function get_authtoken() {
		return $_SESSION['authtoken'];
	}
/**
 *	Get the current user name.
 *	@return Returns the current user name, otherwise false.
 */
	public static function get_user_name() {
		if(!isset($_SESSION['uname']) || !$_SESSION['uname']):
			return false;
		endif;
	 	return $_SESSION['uname'];
	}
/**
 *	Get the current user ID.
 *	@return Returns the current user ID, otherwise false.
 */
	public static function get_user_id() {
		if(!isset($_SESSION['uid']) || !$_SESSION['uid']):
			return false;
		endif;
	 	return $_SESSION['uid'];
	}
}
