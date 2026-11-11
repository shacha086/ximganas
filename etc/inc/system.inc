<?php
/*
	system.inc

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
require_once 'functions.inc';
require_once 'util.inc';
require_once 'rc.inc';
require_once 'zfs.inc';

use common\arr;
use common\lang;
use common\session;

/**
 *	Initiate halt. Everything will be done automatically
 *	in /etc/rc.shutdown by executing rc-init scripts in
 *	reverse order (the 'KEYWORD: shutdown' must be defined).
 */
function system_reboot() {
	mwexec('/sbin/shutdown -r now');
}
/**
 *	Initiate halt. Everything will be done automatically
 *	in /etc/rc.shutdown by executing rc-init scripts in
 *	reverse order (the 'KEYWORD: shutdown' must be defined).
 */
function system_halt() {
	mwexec('/sbin/shutdown -p now');
}
/**
 *  Get the codeset of the current configured language.
 *	@return string String containing codeset of current language.
 */
function system_get_language_codeset() {
	return 'UTF-8';
}
/**
 *	Reads and converts HTTP_ACCEPT_LANGUAGE of client browser into a key/value array,
 *	Key contains the language code and the value contains the q-factor.
 *	@return array List of accepted languages
 */
function get_http_accept_language(): array {
	$accepted_languages = [];
	$result = [];
	if(array_key_exists('HTTP_ACCEPT_LANGUAGE',$_SERVER) && is_string($_SERVER['HTTP_ACCEPT_LANGUAGE'])):
		$accepted_language_string = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
		unset($matches);
		preg_match_all('/([a-z]{1,8}(-[a-z]{1,8})?)\s*(;\s*q\s*=\s*(1|0\.[0-9]+))?/i',$accepted_language_string,$matches);
		$combined = array_combine($matches[1],$matches[4]);
		foreach($combined as $key => $val):
			$updated_key = strtolower(str_replace('-','_',$key));
			if($val == ''):
				$updated_val = 1.0;
			else:
				$updated_val = (float)$val;
			endif;
			$accepted_languages[$updated_key] = $updated_val;
		endforeach;
//		add languages with stripped country code to the list of accepted languages if necessary
		foreach($accepted_languages as $key => $q):
			$result[$key] = $q; // transfer original row
			if(strpos($key,'_') !== false): // check for underscore character
				$additional_key = strstr($key,'_',true); // get the good portion the string i.e. en-US -> en
				if(!isset($accepted_languages[$additional_key])): // don't process if the additional key already exists...
					if(!isset($result[$additional_key])): // in accepted_languages or result lists.
						$result[$additional_key] = $q; // no duplicate found, add with the same q factor
					endif;
				endif;
			endif;
		endforeach;
	endif;
	return $result;
}
/**
 *	Get the language from browser and return a supported language or null
 *	@return string|null
 */
function get_language_from_browser(): ?string {
	$language = null;
	$accepted_languages = get_http_accept_language();
	if(!empty($accepted_languages)):
		$available_languages = lang::get_list_lokey();
		$matching_languages = array_intersect_key($accepted_languages,$available_languages);
		$first_highest_q = 0.0;
		foreach($matching_languages as $key => $val):
			if($val > $first_highest_q):
				$language = $available_languages[$key];
				$first_highest_q = $val;
			endif;
		endforeach;
	endif;
	return $language;
}
/**
 *	Test the given language and return a supported language code
 *	@param string $language_to_verify
 *	@return string The supported language
 */
function get_supported_language_of(string $language_to_verify): string {
	$available_languages = lang::get_list_loval();
	$language = array_key_exists($language_to_verify,$available_languages) ? $language_to_verify : 'en_US';
	if($language === 'auto'):
		$language = get_language_from_browser() ?? 'en_US';
	endif;
	return $language;
}
/**
 *	Get the language from webgui configuration and return a supported language code
 *	@global array $config
 *	@return string
 */
function get_language_of_webgui(): string {
	global $config;

	arr::make_branch($config,'system');
	if(array_key_exists('language',$config['system']) && is_string($config['system']['language'])):
		$language = get_supported_language_of($config['system']['language']);
	endif;
	return $language;
}
/**
 *	Init language environment
 *	@param string $language_to_try
 *	Not all programs have translations for all languages. By default, an English message is shown
 *	in place of a nonexistent translation. If you understand other languages, you can set up a
 *	priority list of languages. This is done through a different environment variable, called LANGUAGE.
 *	GNU gettext gives preference to LANGUAGE over LC_ALL and LANG for the purpose of message handling,
 *	but you still need to have LANG (or LC_ALL) set to the primary language; this is required by other parts of the system libraries.
 *	For example, some Swedish users who would rather read translations in German than English for when Swedish is not available,
 *	set LANGUAGE to ‘sv:de’ while leaving LANG to ‘sv_SE’.
 *
 *	Special advice for Norwegian users: The language code for Norwegian bokmål changed from ‘no’ to ‘nb’ recently (in 2003).
 *	During the transition period, while some message catalogs for this language are installed under ‘nb’ and
 *	some older ones under ‘no’, it is recommended for Norwegian users to set LANGUAGE to ‘nb:no’ so that both
 *	newer and older translations are used.
 *
 *	In the LANGUAGE environment variable, but not in the other environment variables,
 *	‘ll_CC’ combinations can be abbreviated as ‘ll’ to denote the language’s main dialect.
 *	For example, ‘de’ is equivalent to ‘de_DE’ (German as spoken in Germany), and
 *	‘pt’ to ‘pt_PT’ (Portuguese as spoken in Portugal) in this context.
 *
 *	Note: The variable LANGUAGE is ignored if the locale is set to ‘C’.
 *	In other words, you have to first enable localization, by setting LANG (or LC_ALL) to a value other than ‘C’,
 *	before you can use a language priority list through the LANGUAGE variable.
 *
 */
function system_language_load(?string $language_to_try = null) {
	$directory = '/usr/local/share/locale';
	$domain = strtolower(get_product_name());
	$lang = 'en_US';
	$codeset = 'UTF-8';
	if(is_null($language_to_try)):
		$language = $_SESSION['userlang'] ?? get_language_of_webgui();
	else:
		$language = get_supported_language_of($language_to_try);
	endif;
	putenv(sprintf('LANG=%s.%s',$lang,$codeset));
	setlocale(LC_ALL,'');
	putenv(sprintf('LANGUAGE=%s',$language));
	bindtextdomain($domain,$directory);
	bind_textdomain_codeset($domain,$codeset);
	textdomain($domain);
	if(isset($_SESSION)):
		if(array_key_exists('userlang',$_SESSION)):
			if($_SESSION['userlang'] !== $language):
				$_SESSION['g']['headermenu'] = [];
				$_SESSION['userlang'] = $language;
			endif;
		else:
			$_SESSION['userlang'] = $language;
		endif;
	endif;
}
/**
 *	Get the entity of LanguageCode of the current configured language.
 *	@return string Language code of current laguage.
 */
function system_get_language_code() {
//	a language code, as per [RFC3066]
	$language = getenv('LANGUAGE');
	if($language === false):
		$language = getenv('LANG');
		if($language === false):
			$language = 'en_US.UTF-8';
		endif;
	endif;
	$parsed = locale_parse($language) ?? ['language' => 'en','region' => 'US'];
	if(isset($parsed['language'])):
		$code = $parsed['language'];
		if(isset($parsed['region'])):
			$code .= '-' . $parsed['region'];
		endif;
	else:
		$code = 'en-US';
	endif;
	return $code;
}
// Get list of available groups from /etc/group.
// Result:
//	Array (
//		[wheel] => 0
//		[sshd] => 22
//		[www] => 80
//		[nobody] => 65534
//		[admin] => 1000
//		...
//	)
function system_get_group_list() {
	// List of groups to filter from result list.
	$filterlist = ['_dhcp','_pflogd'];
	$grouplist = [];
	preg_match_all('/(\S+):\*:(\d+):.*\n/',@file_get_contents('/etc/group'),$matches,PREG_SET_ORDER);
	if(is_array($matches)):
		foreach($matches as $group):
			if(false === in_array($group[1],$filterlist)):
				$grouplist[$group[1]] = $group[2];
			endif;
		endforeach;
		ksort($grouplist);
	endif;
	return $grouplist;
}
// Get list of available users from /etc/master.passwd.
// Result:
//  Array (
//    [test] => Array ( [name] => test
//                      [password] => $1$yuQLaTPN$lkwYlZEB7B8n85flXPkHd0
//                      [uid] => 1001
//                      [gid] => 1001
//                      [class] =>
//                      [change] => 0
//                      [expire] => 0
//                      [gecos] => test
//                      [home_dir] => /mnt
//                      [shell] => /usr/local/bin/scponly
//                    )
//    [root] => ...
//    [toor] => ..
//    [daemon] => ...
//    [operator] => ...
//    ...
//  )
function system_get_user_list() {
	$userlist = [];
	foreach(explode("\n",@file_get_contents('/etc/master.passwd')) as $userinfov):
		if(empty($userinfov)):
			continue;
		else:
			// Extract user information
			$userinfo = explode(':',$userinfov);
			$user = [];
			$user['name'] = $userinfo[0];
			$user['password'] = $userinfo[1];
			$user['uid'] = $userinfo[2];
			$user['gid'] = $userinfo[3];
			$user['class'] = $userinfo[4];
			$user['change'] = $userinfo[5];
			$user['expire'] = $userinfo[6];
			$user['gecos'] = $userinfo[7];
			$user['home_dir'] = $userinfo[8];
			$user['shell'] = $userinfo[9];
			$userlist[$user['name']] = $user;
		endif;
	endforeach;
	return $userlist;
}

// SMBIOS get various info
function get_smbios_info() {
	$smbios = [
		'bios' => ['reldate' => null,'vendor' => null,'version' => null],
		'planar' => ['maker' => null,'product' => null],
		'system' => ['maker' => null,'product' => null]
	];
	$cmd = '/bin/kenv -q';
	unset($rawdata,$rc);
	mwexec2($cmd,$rawdata,$rc);
	if($rc != 0):
		return $smbios;
	endif;
	$smbios_bios_options = array_keys($smbios['bios']);
	$smbios_planar_options = array_keys($smbios['planar']);
	$smbios_system_options = array_keys($smbios['system']);
	foreach($rawdata as $line):
		if(preg_match('/^smbios\.(bios|planar|system)\.([^\.=]+)="(.*)"/',$line,$match)):
			switch($match[1]):
				case 'bios':
					if(in_array($match[2],$smbios_bios_options)):
						$smbios[$match[1]][$match[2]] = $match[3];
					endif;
					break;
				case 'planar':
					if(in_array($match[2],$smbios_planar_options)):
						$smbios[$match[1]][$match[2]] = $match[3];
					endif;
					break;
				case 'system':
					if(in_array($match[2],$smbios_system_options)):
						$smbios[$match[1]][$match[2]] = $match[3];
					endif;
					break;
			endswitch;
		endif;
	endforeach;
	if($smbios['bios']['vendor'] === 'BHYVE'):
		$smbios['bios']['reldate'] = '01/01/2015';
		$smbios['bios']['vendor'] = 'BHyVe';
		$smbios['bios']['version'] = '1.0';
		$smbios['planar']['maker'] = '';
		$smbios['planar']['product'] = 'BHyVe';
	endif;
	return $smbios;
}

// Get number of CPUs
function system_get_cpus() {
	$cpus = @exec("/sbin/sysctl -q -n kern.smp.cpus");
	if($cpus == '' || $cpus < 1):
		$cpus = 1;
	endif;
	return $cpus;
}
/**
 *	Calculate cpu usage and returns an array containing values between 0 and 100 for each core, representing 0% - 100% usage
 *	@param string $id usage identifier to avoid spikes.
 *	@return array
 */
function system_get_smp_cpu_usage(string $id = '0'): array {
	$cpus = is_string($test = shell_exec('sysctl -nq kern.smp.cpus')) ? rtrim($test) : '';
	$var = 'kern.cp_times';
	$key = sprintf('%s.%s',$var,$id);
	$cmd = sprintf('/sbin/sysctl -nq %s',$var);
//	get previously stored ticks from session variable, if it doesn't exist, use ticks since boot
	$c_ticks_1 = $_SESSION[$key] ?? null;
	$a_ticks_1 = isset($c_ticks_1) ? explode(' ',$c_ticks_1) : array_fill(0,$cpus * 5,0);
//	get current ticks and save them in the session variable
	$_SESSION[$key] = $c_ticks_2 = is_string($test = shell_exec($cmd)) ? rtrim($test) : '';
	$a_ticks_2 = explode(' ',$c_ticks_2);
	$a_ticks_all = array_map(fn($a,$b) => abs($b - $a),$a_ticks_1,$a_ticks_2);
	$usage = array_fill_keys(range(0,$cpus - 1),0);
	for($i = 0;$i < $cpus;$i++):
		$a_ticks = array_splice($a_ticks_all,0,5);
//		CPUSTATES: 0 => user, 1 => nice, 2 => system, 3 => interrupt, 4 => idle
		$usage[$i] = 100 * ( 1 - $a_ticks[4] / array_sum($a_ticks));
	endfor;
	return $usage;
}
/**
 *	Calculate the cpu usage and returns a value between 0 and 100, representing 0% - 100% usage
 *	@param string $id usage identifier to avoid spikes.
 *	@return float
 */
function system_get_cpu_usage(string $id = '0'): float {
	$var = 'kern.cp_time';
	$key = sprintf('%s.%s',$var,$id);
	$cmd = sprintf('/sbin/sysctl -nq %s',$var);
//	get previously stored ticks from session variable, if it doesn't exist, use ticks since boot
	$c_ticks_1 = $_SESSION[$key] ?? null;
	$a_ticks_1 = isset($c_ticks_1) ? explode(' ',$c_ticks_1) : array_fill(0,5,'0');
//	get current ticks and save them in the session variable
	$_SESSION[$key] = $c_ticks_2 = is_string($test = shell_exec($cmd)) ? rtrim($test) : '';
	$a_ticks_2 = explode(' ',$c_ticks_2);
	$a_ticks = array_map(fn($a,$b) => abs($b - $a),$a_ticks_1,$a_ticks_2);
//	CPUSTATES: 0 => user, 1 => nice, 2 => system, 3 => interrupt, 4 => idle
	$usage = 100 * ( 1 - $a_ticks[4] / array_sum($a_ticks));
	return $usage;
}
/**
 *	Get various CPU informations.
 *	@return array Array with CPU information
 */
function system_get_cpu_info() {
	$cpuinfo = [];
	$cpuinfo['number'] = is_string($test = shell_exec('/sbin/sysctl -n hw.ncpu')) ? rtrim($test) : '';
	$cpuinfo['model'] = is_string($test = shell_exec('/sbin/sysctl -n hw.model')) ? rtrim($test) : '';
	$cpuinfo['clockrate'] = is_string($test =shell_exec('/sbin/sysctl -n hw.clockrate')) ? rtrim($test) : '';
	$cpuinfo['temperature'] = is_string($test = shell_exec('/sbin/sysctl -n hw.acpi.thermal.tz0.temperature')) ? rtrim($test) : '';
//	read temperatures of all cores (kern.smp.cpus) from sysctl
	$adjust_temp_by = 0;
	$cpu_temp_info = [];
	unset($exec_response,$exec_exitcode);
	mwexec2("sysctl dev.cpu | grep '^dev.cpu.[0-9]\+.temperature:'",$exec_response,$exec_exitcode);
	foreach($exec_response as $record_to_match):
		unset($matches);
		if(preg_match('/^dev\.cpu\.(?<core_id>[0-9]+)\.temperature:\s*(?<core_temp>\S+)c/i',$record_to_match,$matches) === 1):
			$cpu_temp_info[$matches['core_id']] = sprintf('%.1f',(float)$matches['core_temp'] + $adjust_temp_by);
		endif;
	endforeach;
	ksort($cpu_temp_info);
	$cpuinfo['temperature2'] = $cpu_temp_info;
	$cpuinfo['freq'] = is_string($test = shell_exec('/sbin/sysctl -n dev.cpu.0.freq')) ? rtrim($test) : '';
	$cpuinfo['freqlevels'] = is_string($test = shell_exec('/sbin/sysctl -n dev.cpu.0.freq_levels')) ? trim($test) : '';
	return $cpuinfo;
}
/**
 *	Get the system uptime (how long the system is already running).
 *	@return string The current uptime as string.
 */
function system_get_uptime() {
	exec("/sbin/sysctl -n kern.boottime",$boottime);
	preg_match("/sec = (\d+)/",$boottime[0],$matches);
	$boottime = $matches[1];
	$uptime = time() - $boottime;

	if($uptime > 60):
		$uptime += 30;
	endif;
	$updays = (int)($uptime / 86400);
	$uptime %= 86400;
	$uphours = (int)($uptime / 3600);
	$uptime %= 3600;
	$upmins = (int)($uptime / 60);
	$upsecs = $uptime % 60;

	$gt_seconds = (1 === $upsecs) ? gettext('Second') : gettext('Seconds');
	$gt_minutes = (1 === $upmins) ? gettext('Minute') : gettext('Minutes');
	$gt_hours = (1 === $uphours) ? gettext('Hour') : gettext('Hours');
	$gt_days = (1 === $updays) ? gettext('Day') : gettext('Days');

	if(($updays < 1) && ($uphours < 1)):
		$uptime = sprintf('%d %s %d %s',$upmins,$gt_minutes,$upsecs,$gt_seconds);
	elseif($updays < 1):
		$uptime = sprintf('%d %s %d %s %d %s',$uphours,$gt_hours,$upmins,$gt_minutes,$upsecs,$gt_seconds);
	else:
		$uptime = sprintf('%d %s %d %s %d %s %d %s',$updays,$gt_days,$uphours,$gt_hours,$upmins,$gt_minutes,$upsecs,$gt_seconds);
	endif;
	return htmlspecialchars($uptime);
}
/**
 * Get the system date/time for conversion strings.
 * @return string The current uptime as string.
 */
function get_datetime_locale_samples() {
	$retval = [];
	$sphere_array = [
		'D M d H:i:s T Y',    // Thu Feb 25 22:52:29 CET 2016
		'D d M Y H:i:s',      // Thu 25 Feb 2016 22:52:29
		'D d M Y H:i:s T',    // Thu 25 Feb 2016 22:52:29 CET
		'D d M Y H:i:s T O',  // Thu 25 Feb 2016 22:52:29 CET +0100
		'l F d H:i:s T Y',    // Thursday February 25 22:52:29 BST 2016
		'l d F Y H:i:s',      // Thursday 25 February 2016 22:52:29
		'l d F Y H:i:s T',    // Thursday 25 February 2016 22:52:29 BST
		'l d F Y H:i:s T O',  // Thursday 25 February 2016 22:52:29 BST +0100
		'D M d h:i:s A T Y',  // Thu Feb 25 10:52:29 PM PT 2016
		'l F d h:i:s A T Y',  // Thursday February 25 10:52:29 PM BST 2016
		'd.m.Y H:i:s',        // 25.02.2016 22:31:42
		'd/m/Y H:i:s',        // 25/02/2016 22:31:42
		'd/m/Y h:i:s A',      // 25/02/2016 10:31:42 PM
		'm/d/Y H:i:s',        // 02/25/2016 22:31:42
		'm/d/Y h:i:s A',      // 02/25/2016 10:31:42 PM
		'Y-m-d H:i:s'         // 2016-02-02 22:31:42
	];
	$now = (int)shell_exec('date "+%s"');
	foreach($sphere_array as $sphere_record):
		$retval[$sphere_record] = get_datetime_locale($now,$sphere_record);
	endforeach;
	return $retval;
}

function get_datetime_locale(?int $timestamp = null,?string $format = null) {
	global $config;

	$t_timestamp = $timestamp ?? time();
	$t_format = $format ?? $config['system']['datetimeformat'] ?? 'D M d H:i:s T Y';
	$tz = $_SESSION['tz'] ?? $config['system']['timezone'] ?? 'UTC';
	try {
        $dtz = new DateTimeZone($tz);
	} catch(Exception $e) {
        $dtz = new DateTimeZone('UTC');
	}
	$dto = new DateTime('',$dtz);
	$dto->setTimestamp($t_timestamp);
	$format_symbols = ['D','l','M','F'];
	$replacement_symbols = ['%1','%2','%3','%4'];
	$localised_names = [
		gtext($dto->format($format_symbols[0])),
		gtext($dto->format($format_symbols[1])),
		gtext($dto->format($format_symbols[2])),
		gtext($dto->format($format_symbols[3]))
	];
	$retval = str_replace($replacement_symbols,$localised_names,$dto->format(str_replace($format_symbols,$replacement_symbols,$t_format)));
	return $retval;
}
/*
 *	Array keys
 *	vt: value total
 *	vu: value used
 *	vf: value free
 *	pu: percentage used
 *	pf: percentage free
 *	vth: value total human readable
 *	vtu: value used human readable
 *	vtf: value free human readable
 *	puh: percentage used human readable
 *	pfh: percentage free human readable
 *	tu: text used
 *	tf: text free
 *	tt: text total
 */
/**
 *	Get the current RAM information.
 *	@return array Returns an array with memory status information.
 */
function system_get_ram_info() {
	$sphere = [];
	unset($memory);
	exec('/sbin/sysctl -n vm.stats.vm.v_inactive_count vm.stats.vm.v_free_count vm.stats.vm.v_page_count vm.stats.vm.v_page_size hw.physmem hw.realmem',$memory);
	$sphere['pagesize'] = $memory[3];
	$sphere['physical'] = $memory[4];
	$sphere['real'] = $memory[5];
	$sphere['vt'] = $memory[3] * $memory[2];
	$sphere['vf'] = $memory[3] * ($memory[1] + $memory[0]);
	$sphere['vu'] = $sphere['vt'] - $sphere['vf'];
	$sphere['vth'] = format_bytes($sphere['vt'],2,false,false);
	$sphere['vuh'] = format_bytes($sphere['vu'],2,false,false);
	$sphere['vfh'] = format_bytes($sphere['vf'],2,false,false);
	$sphere['pf'] = round($sphere['vf'] * 100 / $sphere['vt'],0);
	$sphere['pu'] = 100 - $sphere['pf'];
	$sphere['pfh'] = sprintf('%u%%',$sphere['pf']);
	$sphere['puh'] = sprintf('%u%%',$sphere['pu']);
	$sphere['tu'] = sprintf('%s',$sphere['puh']);
	$sphere['tf'] = sprintf('%s',$sphere['pfh']);
	$sphere['tt'] = sprintf(gettext('%1$s managed, %2$s used, %3$s available'),$sphere['vth'],$sphere['vuh'],$sphere['vfh']);
	return $sphere;
}
// Get the current swap information.
// Result:
//  Array (
//    [device] => "/dev/ad0s2b"
//    [total] => "20M"
//    [used] => "0B"
//    [avail] => "20M"
//    [capacity] => "0%"
//  )
function system_get_swap_info() {
	$use_si = is_sidisksizevalues();
	exec('/usr/sbin/swapinfo -k',$swap);
	//	blocks
	$val = 512;
	if(preg_match("/(?:Device)(?:\s+)(\d+)(?:K-blocks|-blocks)(?:\s+)(?:Used)(?:\s+)(?:Avail)(?:\s)(?:Capacity)/",$swap[0],$matches)):
		if($matches[1] == 1):
			$val = $matches[1] * 1024;
		else:
			$val = $matches[1];
		endif;
	endif;
	array_shift($swap);
	$swapinfo = [];
	foreach($swap as $swapv):
		if(preg_match("/(\/dev\/.*)(?:\s+)(\d+)(?:\s+)(\d+)(?:\s+)(\d+)(?:\s)(.+)/",$swapv,$matches)):
			$devswap = [];
			$devswap['device'] = trim($matches[1]);
			$devswap['vt'] = $matches[2] * $val;
			$devswap['vu'] = $matches[3] * $val;
			$devswap['vf'] = $matches[4] * $val;
			$devswap['total'] = format_bytes($devswap['vt'],2,false,$use_si);
			$devswap['used'] = format_bytes($devswap['vu'],2,false,$use_si);
			$devswap['avail'] = format_bytes($devswap['vf'],2,false,$use_si);
			$devswap['capacity'] = trim($matches[5]);
			$swapinfo[] = $devswap;
		endif;
	endforeach;
	return $swapinfo;
}

/**
 * Get the system hostname.
 * @return string The hostname
 */
function system_get_hostname() {
	return @exec("hostname");
}

/**
 * Get device I/O statistics.
 * @param[in] device The device name, e.g. ad1
 * @return	array An array containing the I/O statistics kpt (kilobytes per transfer),
 *					tps (transfers per second) and mps (megabytes per second). On
 *					failure, false will be returned.
 * @code
 * array(
 *   [kpt] => 11.93
 *   [tps] => 61
 *   [mps] => 0.71
 * @endcode
 */
function system_get_device_iostat($device) {
	$result = false;
	mwexec2("iostat -d {$device}",$rawdata);
	if(preg_match("/^\s*(\S+)\s*(\S+)\s*(\S+)/",$rawdata[2],$matches)):
		$result = [];
		$result['kpt'] = $matches[1];
		$result['tps'] = $matches[2];
		$result['mps'] = $matches[3];
	endif;
	return $result;
}
/**
 *	Get device temperature, using grep.
 *	@global array $config
 *	@param string $devicespecialfile The device name
 *	@return	string|null The temperature if available, otherwise null.
 */
function system_get_device_temp_grep(string $devicespecialfile): ?string {
	global $config;

	$result = null;
//	define the timeout in seconds for caching smartctl queries
	$timeout = 120;
//	check first with ataidle if S.M.A.R.T. is disabled
	$params = ['/usr/local/sbin/ataidle'];
	$params[] = escapeshellarg($devicespecialfile);
	$params[] = '2>/dev/null | /usr/bin/grep "SMART Enabled"';
	$cmd = implode(' ',$params);
	unset($raw_ataidle);
	mwexec2($cmd,$raw_ataidle);
	if(!empty($raw_ataidle) && (preg_match('/^SMART Enabled:\s+(\S+)/',$raw_ataidle[0],$matches) === 1)):
		if(strcasecmp($matches[1],'no') == 0):
//			nothing to do when S.M.A.R.T. is disabled
			return $result;
		endif;
	endif;
	$timestamp = time();
	if(empty($_SESSION['dev'][$devicespecialfile]['temp']['value'])):
//		devicespecialfile hasn't been queried yet
		$run_cmd = true;
	elseif(($timestamp - $_SESSION['dev'][$devicespecialfile]['temp']['timestamp']) >= $timeout):
//		devicespecialfile last query has expired
		$run_cmd = true;
	else:
//		report cached temperature
		$run_cmd = false;
		$result = $_SESSION['dev'][$devicespecialfile]['temp']['value'];
	endif;
	if($run_cmd):
		$cfg_disks = arr::make_branch($config,'disks','disk');
		$index = arr::search_ex($devicespecialfile,$cfg_disks,'devicespecialfile');
		if($index !== false):
			$cfg_disk = $cfg_disks[$index];
//			compile smartctl command
			$params = ['/usr/local/sbin/smartctl'];
			$smartd_drivedb_arg = trim(get_smartmontools_drivedb_arg());
			if(!empty($smartd_drivedb_arg)):
				$params[] = $smartd_drivedb_arg;
			endif;
			$params[] = '-A';
			$params[] = '-T';
			$params[] = 'permissive';
			if(!empty($cfg_disk['smart']['devicetypearg'])):
				$params[] = '-d';
				$params[] = $cfg_disk['smart']['devicetypearg'];
			endif;
			$params[] = escapeshellarg($cfg_disk['smart']['devicefilepath']);
			$params[] = '| /usr/bin/grep -i temperature';
			$cmd = implode(' ',$params);
			unset($raw_smartctl);
			mwexec2($cmd,$raw_smartctl);
			foreach($raw_smartctl as $raw_smartctl_row):
				$arawdatav = preg_split('/\s+/',$raw_smartctl_row);
				if(!empty($arawdatav[1]) && ((strncmp(trim($arawdatav[1]),'Temperature_',12) == 0) || (strncmp(trim($arawdatav[1]),'Airflow_Temperature_',20) == 0)) && ((strcmp(trim($arawdatav[0]),'190') != 0) || (strcmp(trim($arawdatav[0]),'194') != 0))):
					$result = rtrim($arawdatav[9]);
					break;
				elseif(preg_match('/Current Drive Temperature:\s+(\d+) C.*/',$raw_smartctl_row,$matches) === 1):
					$result = $matches[1];
					break;
				endif;
			endforeach;
			$_SESSION['dev'][$devicespecialfile]['temp']['value'] = $result;
			$_SESSION['dev'][$devicespecialfile]['temp']['timestamp'] = $timestamp;
		else:
//			orphaned, kill it
			unset($_SESSION['dev'][$devicespecialfile]);
		endif;
	endif;
	return $result;
}
/**
 *	Get device temperature, using json method.
 *	@global array $config
 *	@param string $devicespecialfile The device name
 *	@return	string|null The temperature if available, otherwise null.
 */
function system_get_device_temp_json(string $devicespecialfile): ?string {
	global $config;

	$result = null;
//	define the timeout in seconds for caching smartctl queries
	$timeout = 60;
	$timestamp = time();
	if(empty($_SESSION['dev'][$devicespecialfile]['smartctl']['result'])):
//		devicespecialfile hasn't been queried yet
		$run_cmd = true;
	elseif(($timestamp - $_SESSION['dev'][$devicespecialfile]['smartctl']['timestamp']) >= $timeout):
//		devicespecialfile last query has expired
		$run_cmd = true;
	else:
//		report cached temperature
		$run_cmd = false;
	endif;
	if($run_cmd):
		$cfg_disks = arr::make_branch($config,'disks','disk');
		$index = arr::search_ex($devicespecialfile,$cfg_disks,'devicespecialfile');
		if($index !== false):
			$cfg_disk = $cfg_disks[$index];
//			compile smartctl command
			$params = ['/usr/local/sbin/smartctl'];
			$smartd_drivedb_arg = trim(get_smartmontools_drivedb_arg());
			if(!empty($smartd_drivedb_arg)):
				$params[] = $smartd_drivedb_arg;
			endif;
			$params[] = '--json=c';
			$params[] = '-a';
			$params[] = '-T';
			$params[] = 'verypermissive';
			if(!empty($cfg_disk['smart']['devicetypearg'])):
				$params[] = '-d';
				$params[] = $cfg_disk['smart']['devicetypearg'];
			endif;
			$params[] = escapeshellarg($cfg_disk['smart']['devicefilepath']);
			$cmd = implode(' ',$params);
			$json_string = shell_exec($cmd);
			$_SESSION['dev'][$devicespecialfile]['smartctl']['result'] = json_decode($json_string,true) ?? [];
			$_SESSION['dev'][$devicespecialfile]['smartctl']['timestamp'] = $timestamp;
		else:
//			orphaned, kill it
			unset($_SESSION['dev'][$devicespecialfile]);
		endif;
	endif;
	if(!empty($_SESSION['dev'][$devicespecialfile]['smartctl']['result']['temperature']['current'])):
		$result = $_SESSION['dev'][$devicespecialfile]['smartctl']['result']['temperature']['current'];
	endif;
	return $result;
}
/**
 *	Get device temperature via smartctl.
 *	@param string $devicespecialfile The device name
 *	@param int $use_version
 *	@return	string|null
 */
function system_get_device_temp(string $devicespecialfile,int $use_version = 2): ?string {
	switch($use_version):
		default:
			return system_get_device_temp_grep($devicespecialfile);
			break;
		case 2:
			return system_get_device_temp_json($devicespecialfile);
			break;
	endswitch;
}
/**
 * Get controller
 * @param[in] device The device name, e.g. /dev/ad1
 * @return string The disk device serial number.
 */
function system_get_controller($devicespecialfile) {
	$devlist = [];
	$device = '';
	$controller = '';
	$controller_id = '';
	$target_id = '';
	$lun_id = '';
	mwexec2("/sbin/camcontrol devlist -v 2> /dev/null",$rawdata);
	foreach($rawdata as $line):
		// Get controller
		if(preg_match("/(?<= on )[-A-z0-9]*(?= bus )+/",$line,$matches)):
			$controller = preg_split( '/([-A-z]+)/',$matches[0],-1,PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
			$device = '';
		elseif(!strncmp($line,'<>' ,2) == 0):
			// Get device name
			// Parse substring between parentheses: (pass0,da0) or (da0,pass0)
			preg_match("/.*\(([^>]*)\).*/",$line,$match);
			$temp = preg_split("/,/",$match[1]);
			// Check whether device name is at the first (da0,pass0) or the second (pass0,da0) arguement
			$device = preg_split( '/([-A-z]+)/',$temp[1],-1,PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
			if(strncmp($device[0],'pass',4) == 0):
				$device = preg_split( '/([-A-z]+)/',$temp[0],-1,PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
			endif;
			if(strcmp($devicespecialfile,'/dev/'.$device[0].$device[1]) == 0):
				return $controller[0].$controller[1];
			endif;
		endif;
	endforeach;
	return gtext('n/a');
}
/**
 *	Get volume serial number.
 *	@global array $config
 *	@global array $config_disks
 *	@param string $devicespecialfile The device name, e.g. /dev/da1.
 *	@return string The disk device serial number.
 */
function system_get_volume_serial($devicespecialfile) {
	global $config;
	global $config_disks;

//	try to get serial from S.M.A.R.T.
	if(is_array($config_disks) && array_key_exists($devicespecialfile,$config_disks)):
		$smart_arg = get_smartctl_info($config_disks[$devicespecialfile]);
		if(!empty($smart_arg['serial'])):
			return $smart_arg['serial'];
		endif;
	endif;
//	try to get serial from ataidle
	$cmd1 = sprintf("ataidle %s 2>/dev/null | grep 'Serial'",escapeshellarg($devicespecialfile));
	mwexec2($cmd1,$rawdata1);
	if(!empty($rawdata1) && (preg_match("/^Serial:\s+(.+)$/",$rawdata1[0],$matches1) === 1)):
		$serial = preg_replace("/[\x01-\x1f\x7f]/","",$matches1[1]);
		if(!empty($serial)):
			return $serial;
		endif;
	endif;
//	try to get serial from camcontrol
	$cmd2 = sprintf('camcontrol inquiry %s -S 2>/dev/null',escapeshellarg($devicespecialfile));
	mwexec2($cmd2,$rawdata2);
	if(!empty($rawdata2) && !empty($rawdata2[0])):
		$serial = preg_replace("/[\x01-\x1f\x7f]/","",$rawdata2[0]);
		if(!empty($serial)):
			return $serial;
		endif;
	endif;
	return gtext('n/a');
}
/**
 * Get volume device model.
 * @param[in] device The device name, e.g. /dev/ad1
 * @return string The disk device model.
 */
function system_get_volume_model($devicespecialfile) {
	global $config;

	// get device model from config
	global $config_disks;
	if(!empty($config_disks[$devicespecialfile]['model'])):
		return $config_disks[$devicespecialfile]['model'];
	else:
		/* get device model from S.M.A.R.T. */
		$smart_arg = get_smartctl_info($config_disks[$devicespecialfile]);
		if(!empty($smart_arg['model'])):
			return $smart_arg['model'];
		endif;
	endif;
	/* without smart */
	mwexec2("ataidle {$devicespecialfile} 2>/dev/null | grep 'Model'",$rawdata);
	if(!empty($rawdata) && 1 === preg_match("/^Model:\s+(.+)$/",$rawdata[0],$matches)):
		$model = preg_replace("/[\x01-\x1f\x7f]/","",$matches[1]);
		if(!empty($model)):
			return $model;
		endif;
	endif;
	/* get device model from CAM */
	mwexec2("camcontrol inquiry {$devicespecialfile} -D 2>/dev/null",$rawdata2);
	if(!empty($rawdata2) && 1 === preg_match("/<([^>]+)>/",$rawdata2[0],$matches)):
		$model = preg_replace("/[\x01-\x1f\x7f]/","",$matches[1]);
		if(!empty($model)):
			return $model;
		endif;
	endif;
	return gtext("n/a");
}

/**
 * Get mount point usage information.
 * @return array An array containg the requested informations:
 * @code
 * [mountpoint] => [
 *   [mountpoint] => /mnt/xyz
 *   [name] => xyz
 *   [filesystem] => /dev/ad0a
 *   [capacity] => 48%
 *   [used] => 2.4G
 *   [avail] => 2.6G
 *   [size] => 5.4G]
 * @endcode
 */
function system_get_mount_usage() {
	global $config,$g;

	$use_si = is_sidisksizevalues();
	$sphere = [];
	$a_mount = arr::make_branch($config,'mounts','mount');
	if(count($a_mount) > 0):
		foreach($a_mount as &$r_mount):
			$r_mount['fqmn'] = sprintf('%s/%s',$g['media_path'],$r_mount['sharename']);
//			$r_mount['fsid'] = '';
		endforeach;
		unset($r_mount);
//		query df with 512B block counts, retrieve json
		$cmd = '/bin/df --libxo:J -P';
		$json_string = shell_exec($cmd);
		$rawdata = json_decode($json_string,true) ?? [];
		if(is_array($rawdata)):
			foreach($rawdata['storage-system-information']['filesystem'] as $r_rawdata):
				foreach($a_mount as $r_mount):
					if(0 == strcmp($r_rawdata['mounted-on'],$r_mount['fqmn'])):
						$row = [];
//						$row['id'] = $r_mount['fsid'];
						$row['mountpoint'] = $r_rawdata['mounted-on'];
						$row['name'] = $r_mount['sharename'];
						$row['filesystem'] = $r_rawdata['name'];
						$row['vt'] = $r_rawdata['total-blocks'] << 9;
						$row['vu'] = $r_rawdata['used-blocks'] << 9;
						$row['vf'] = $r_rawdata['available-blocks'] << 9;
						$row['pu'] = $r_rawdata['used-percent'];
						$row['pf'] = 100 - $row['pu'];
						$row['vth'] = format_bytes($row['vt'],2,false,$use_si);
						$row['vfh'] = format_bytes($row['vf'],2,false,$use_si);
						$row['vuh'] = format_bytes($row['vu'],2,false,$use_si);
						$row['puh'] = sprintf('%u%%',$row['pu']);
						$row['pfh'] = sprintf('%u%%',$row['pf']);
						$row['tu'] = $row['puh'];
						$row['tf'] = $row['pfh'];
						$row['tt'] = sprintf(gettext('Total: %1$s | Used: %2$s | Available: %3$s'),$row['vth'],$row['vuh'],$row['vfh']);
						$sphere[$r_rawdata['mounted-on']] = $row;
					endif;
				endforeach;
			endforeach;
		endif;
	endif;
	return $sphere;
}
/*
 *	supporting functions for index.php
 */
function get_vip_status() {
	global $config;

	if(empty($config['vinterfaces']['carp'])):
		return '';
	endif;
	$a_vipaddrs = [];
	foreach($config['vinterfaces']['carp'] as $carp):
		$ifinfo = get_carp_info($carp['if']);
		//$a_vipaddrs[] = $carp['vipaddr']." ({$ifinfo['state']},{$ifinfo['advskew']})";
		$a_vipaddrs[] = $carp['vipaddr']." ({$ifinfo['state']})";
	endforeach;
	return implode(', ',$a_vipaddrs);
}
function get_vbox_vminfo($user,$uuid) {
	$vminfo = [];
	unset($rawdata);
	mwexec2("/usr/local/bin/sudo -u {$user} /usr/local/bin/VBoxManage showvminfo --machinereadable {$uuid}",$rawdata);
	foreach($rawdata as $line):
		if(preg_match("/^([^=]+)=(\"([^\"]+)\"|[^\"]+)/",$line,$match)):
			$a = [];
			$a['raw'] = $match[0];
			$a['key'] = $match[1];
			$a['value'] = isset($match[3]) ? $match[3] : $match[2];
			$vminfo[$a['key']] = $a;
		endif;
	endforeach;
	return $vminfo;
}
function get_xen_info() {
	$info = [];
	unset($rawdata);
	mwexec2("/usr/local/sbin/xl info",$rawdata);
	foreach($rawdata as $line):
		if(preg_match("/^([^:]+)\s+:\s+(.+)\s*$/",$line,$match)):
			$a = [];
			$a['raw'] = $match[0];
			$a['key'] = trim($match[1]);
			$a['value'] = trim($match[2]);
			$info[$a['key']] = $a;
		endif;
	endforeach;
	return $info;
}
function get_xen_console($domid) {
	$info = [];
	unset($rawdata);
	mwexec2("/usr/local/bin/xenstore-ls /local/domain/{$domid}/console",$rawdata);
	foreach($rawdata as $line):
		if(preg_match("/^([^=]+)\s+=\s+\"(.+)\"$/",$line,$match)):
			$a = [];
			$a['raw'] = $match[0];
			$a['key'] = trim($match[1]);
			$a['value'] = trim($match[2]);
			$info[$a['key']] = $a;
		endif;
	endforeach;
	return $info;
}
/**
 *	Translates the UPS status code into a human readable string
 *	@param type $ups_status
 *	@return string
 */
function get_ups_disp_status($ups_status) {
	if(empty($ups_status)):
		return '';
	endif;
	$upstodisplay = [
		'WAIT' => gtext('UPS Waiting'),
		'OFF' => gtext('UPS Off Line'),
		'OL' => gtext('UPS On Line'),
		'OB' => gtext('UPS On Battery'),
		'TRIM' => gtext('SmartTrim'),
		'BOOST' => gtext('SmartBoost'),
		'OVER' => gtext('Overload'),
		'LB' => gtext('Battery Low'),
		'RB' => gtext('Replace Battery UPS'),
		'CAL' => gtext('Calibration Battery'),
		'CHRG' => gtext('Charging Battery')
	];
	$status = explode(' ',$ups_status);
	$disp_status = [];
	foreach($status as $condition):
		$disp_status[] = $upstodisplay[$condition] ?? $condition;
	endforeach;
	return implode(', ',$disp_status);
}
/**
 *	Collects UPS status information from UPS 1 and UPS 2
 *	@global array $config
 *	@return array Array with UPS information
 */
function get_upsinfo() {
	global $config;

	arr::make_branch($config,'ups');
	$sphere = [];
	foreach([1,2] as $ups_index):
		$row = [];
		$ups_fqn = null;
		switch($ups_index):
			case 1:
				if(isset($config['ups']['enable'])):
					$ups_name = $config['ups']['upsname'];
					$ups_fqn = sprintf('%s@%s',$ups_name,$config['ups']['ip']);
				endif;
				break;
			case 2:
				if(isset($config['ups']['enable']) && isset($config['ups']['ups2'])):
					$ups_name = $config['ups']['ups2_upsname'];
					$ups_fqn = sprintf('%s@%s',$ups_name,$config['ups']['ip']);
				endif;
				break;
		endswitch;
		if(is_null($ups_fqn)):
		else:
			$row['id'] = $ups_index;
			$row['name'] = $ups_name;
			$ups_query = [];
			$cmd = sprintf('/usr/local/bin/upsc %s',escapeshellarg($ups_fqn));
			unset($rawdata);
			exec($cmd,$rawdata);
			foreach($rawdata as $line):
				$line = explode(':',$line);
				$ups_query[$line[0]] = trim($line[1]);
			endforeach;
//			collect load status
			if(array_key_exists('ups.load',$ups_query) && !empty($ups_query['ups.load'])):
				$load_pct = $ups_query['ups.load'];
			else:
				$load_pct = 0;
			endif;
			$row_load = [];
			$row_load['vt'] = 100;
			$row_load['vu'] = $load_pct;
			$row_load['vf'] = 100 - $row_load['vu'];
			$row_load['vth'] = sprintf('%u%%',$row_load['vt']);
			$row_load['vuh'] = sprintf('%u%%',$row_load['vu']);
			$row_load['vfh'] = sprintf('%u%%',$row_load['vf']);
			$row_load['pu'] = round($row_load['vu'] * 100 / $row_load['vt'],0);;
			$row_load['pf'] = 100 - $row_load['pu'];
			$row_load['puh'] = sprintf('%u%%',$row_load['pu']);
			$row_load['pfh'] = sprintf('%u%%',$row_load['pf']);
			$row_load['tu'] = sprintf('%s',$row_load['puh']);
			$row_load['tf'] = sprintf('%s',$row_load['pfh']);
			$row_load['tt'] = sprintf(gettext('%2$s used, %3$s available'),$row_load['vth'],$row_load['vuh'],$row_load['vfh']);
			$row['load'] = $row_load;
			unset($row_load);
//			collect battery status
			if(array_key_exists('battery.charge',$ups_query) && !empty($ups_query['battery.charge'])):
				$battery_charge_pct = $ups_query['battery.charge'];
			else:
				$battery_charge_pct = 0;
			endif;
			$row_battery = [];
			$row_battery['vt'] = 100;
			$row_battery['vu'] = $battery_charge_pct;
			$row_battery['vf'] = $row_battery['vt'] - $row_battery['vu'];
			$row_battery['vth'] = sprintf('%u%%',$row_battery['vt']);
			$row_battery['vuh'] = sprintf('%u%%',$row_battery['vu']);
			$row_battery['vfh'] = sprintf('%u%%',$row_battery['vf']);
			$row_battery['pu'] = round($row_battery['vu'] * 100 / $row_battery['vt'],0);
			$row_battery['pf'] = 100 - $row_battery['pu'];
			$row_battery['puh'] = sprintf('%u%%',$row_battery['pu']);
			$row_battery['pfh'] = sprintf('%u%%',$row_battery['pf']);
			$row_battery['tu'] = sprintf('%s',$row_battery['puh']);
			$row_battery['tf'] = sprintf('%s',$row_battery['pfh']);
			$row_battery['tt'] = sprintf(gettext('%2$s used, %3$s available'),$row_battery['vth'],$row_battery['vuh'],$row_battery['vfh']);
			$row['battery'] = $row_battery;
			unset($row_battery);
//			collect UPS status
			if(array_key_exists('ups.status',$ups_query)):
				$disp_status = get_ups_disp_status($ups_query['ups.status']);
			else:
				$disp_status = gtext('Communication Error');
			endif;
			$row['disp_status'] = $disp_status;
			unset($disp_status);
//			collect remaining runtime
			if(array_key_exists('battery.runtime',$ups_query)):
				$battery_runtime_seconds = $ups_query['battery.runtime'];
				if($battery_runtime_seconds < 0):
					$battery_runtime_seconds = 0;
				endif;
			else:
				$battery_runtime_seconds = 0;
			endif;
			if($battery_runtime_seconds > 86400):
				$juice_left = gtext('More than a day');
			elseif($battery_runtime_seconds === 0):
				$juice_left = gtext('No data');
			else:
//				convert seconds to hours, minutes and seconds
				$processor = [
					'h' => ['eq1' => gtext('%u hour'),'gt1' => gtext('%u hours'),'div' => 3600],
					'm' => ['eq1' => gtext('%u minute'),'gt1' => gtext('%u minutes'),'div' => 60],
					's' => ['eq1' => gtext('%u second'),'gt1' => gtext('%u seconds'),'div' => 1]
				];
				$result = [];
				foreach($processor as $data):
					$value = intdiv($battery_runtime_seconds,$data['div']);
					$battery_runtime_seconds %= $data['div'];
					if($value > 1):
						$result[] = sprintf($data['gt1'],$value);
					elseif($value === 1):
						$result[] = sprintf($data['eq1'],$value);
					endif;
				endforeach;
				unset($data,$processor,$value,$battery_runtime_seconds);
				if(count($result) > 1):
					$last = array_pop($result);
					$juice_left = sprintf('%s %s %s',implode(', ',$result),gtext('and'),$last);
					unset($last);
				else:
					$juice_left = implode(' ',$result);
				endif;
				unset($result);
			endif;
			$row['juice_left'] = $juice_left;
			unset($juice_left);
			$sphere[] = $row;
			unset($row);
		endif;
	endforeach;
	return $sphere;
}
/**
 *	Collect disk usage information
 *	@return array Array with disk usage information
 */
function get_disk_usage() {
	$sphere = [];
	$a_diskinfo = system_get_mount_usage();
	ksort($a_diskinfo);
	foreach($a_diskinfo as $k_diskinfo => $r_diskinfo):
		$id = get_mount_fsid($r_diskinfo['filesystem'],$k_diskinfo);
		$row = $r_diskinfo;
		$row['id'] = $id;
		$sphere[] = $row;
		unset($row);
	endforeach;
	return $sphere;
}
/**
 *	Collect pool usage information
 *	@global array $config
 *	@return array Array containing pool usage information
 */
function get_pool_usage() {
	global $config;

	$sphere = [];
	$use_si = is_sidisksizevalues();
	$a_poolinfo = zfs_get_pool_list();
	if(!empty($a_poolinfo)):
		foreach($a_poolinfo as $r_poolinfo):
			$row = [];
			$row['id'] = $r_poolinfo['guid'];
			$row['name'] = $r_poolinfo['name'];
			$row['pu'] = round($r_poolinfo['cap'],0,PHP_ROUND_HALF_UP);
			$row['pf'] = 100 - $row['pu'];
			$row['puh'] = sprintf('%u%%',$row['pu']);
			$row['pfh'] = sprintf('%u%%',$row['pf']);
			$row['tu'] = $row['puh'];
			$row['tf'] = $row['pfh'];
			if(isset($config['zfs']['settings']['showusedavail'])):
				if(($r_poolinfo['used'] < 0) && ($r_poolinfo['avail'] < 0)):
					$row['vu'] = 0;
					$row['vf'] = 0;
					$row['vt'] = $row['vu'] + $row['vf'];
					$row['vth'] = $row['vuh'] = $row['vfh'] = gettext('Unknown');
					$row['tt'] = sprintf(gettext('Total: %1$s | Used: %2$s | Available: %3$s'),$row['vth'],$row['vuh'],$row['vfh']);
				else:
					$row['vu'] = $r_poolinfo['used'];
					$row['vf'] = $r_poolinfo['avail'];
					$row['vt'] = $row['vu'] + $row['vf'];
					$row['vth'] = format_bytes($row['vt'],2,false,$use_si);
					$row['vuh'] = format_bytes($row['vu'],2,false,$use_si);
					$row['vfh'] = format_bytes($row['vf'],2,false,$use_si);
					$row['tt'] = sprintf(gettext('Total: %1$s | Used: %2$s | Available: %3$s'),$row['vth'],$row['vuh'],$row['vfh']);
				endif;
			else: // this is the default setting
				$row['vu'] = $r_poolinfo['alloc'];
				$row['vf'] = $r_poolinfo['free'];
				$row['vt'] = $r_poolinfo['size'];
				$row['vth'] = format_bytes($row['vt'],2,false,$use_si);
				$row['vuh'] = format_bytes($row['vu'],2,false,$use_si);
				$row['vfh'] = format_bytes($row['vf'],2,false,$use_si);
				$row['tt'] = sprintf(gettext('Total: %1$s | Allocated: %2$s | Free: %3$s'),$row['vth'],$row['vuh'],$row['vfh']);
			endif;
			$row['health'] = $r_poolinfo['health'];
			$sphere[] = $row;
			unset($row);
		endforeach;
		arr::sort_key($sphere,'name');
	endif;
	return $sphere;
}
/**
 *	Collect swap usage information
 *	@return array Array containing swap usage information
 */
function get_swap_usage() {
	$sphere = [];
	$a_swapinfo= system_get_swap_info();
	if(!empty($a_swapinfo)):
		$id = 0;
		foreach($a_swapinfo as $r_swapinfo):
			$row = [];
			$row['id'] = $id++;
			$row['name'] = $r_swapinfo['device'];
			$row['vt'] = $r_swapinfo['vt'];
			$row['vu'] = $r_swapinfo['vu'];
			$row['vf'] = $r_swapinfo['vf'];
			$row['pu'] = round($row['vu'] * 100 / $row['vt'],0);
			$row['pf'] = 100 - $row['pu'];
			$row['vth'] = $r_swapinfo['total'];
			$row['vuh'] = $r_swapinfo['used'];
			$row['vfh'] = $r_swapinfo['avail'];
			$row['puh'] = sprintf('%u%%',$row['pu']);
			$row['pfh'] = sprintf('%u%%',$row['pf']);
			$row['tu'] = $row['puh'];
			$row['tf'] = $row['pfh'];
			$row['tt'] = sprintf(gettext('Total: %1$s | Used: %2$s | Available: %3$s'),$row['vth'],$row['vuh'],$row['vfh']);
			$sphere[] = $row;
			unset($row);
		endforeach;
		arr::sort_key($sphere,'name');
	endif;
	return $sphere;
}
/**
 *	Collect various system information.
 *	@return array Array containing system information.
 */
function system_get_sysinfo() {
	global $config;

	$sysinfo = [];
	$sysinfo['vipstatus'] = get_vip_status();
	$sysinfo['hostname'] = system_get_hostname();
	$sysinfo['builddate'] = get_datetime_locale((int)get_product_buildtimestamp());
	$sysinfo['date'] = get_datetime_locale();
	$sysinfo['uptime'] = system_get_uptime();
	$sysinfo['lastchange'] = get_datetime_locale((int)($config['lastchange'] ?? 0));
	$cpuinfo = system_get_cpu_info();
	$sysinfo['cpumodel'] = $cpuinfo['model'];
	if(session::is_admin()):
//		get number of CPU's
		$sysinfo['cpus'] = system_get_cpus();
//		get RAM usage
		$sysinfo['memusage'] = system_get_ram_info();
//		get load average
		$sysinfo['loadaverage'] = implode(' ',array_map(fn($e) => sprintf('%.2f',$e),sys_getloadavg()));
//		get up-to-date CPU informations.
		$sysinfo['cpufreq'] = $cpuinfo['freq'];
//		get CPU temperatures
		$cpu_t = $cpuinfo['temperature'];
		if(empty($cpuinfo['temperature2'])):
			$a_cpu_t = $sysinfo['cputemp2'] = [0 => $cpuinfo['temperature']];
		else:
			$a_cpu_t = $sysinfo['cputemp2'] = $cpuinfo['temperature2'];
		endif;
		$row = [];
		$row['vu'] = $cpu_t;
		$row['vuh'] = sprintf('%u°C',$row['vu']);
		$row['tu'] = $row['vuh'];
		$sysinfo['cputemp'] = $row;
		unset($row);
		$sysinfo['cputemp2'] = [];
		foreach($a_cpu_t as $k_cpu_t => $r_cpu_t):
			$row = [];
			$row['vu'] = $r_cpu_t;
			$row['vuh'] = sprintf('%u°C',$row['vu']);
			$row['tu'] = $row['vuh'];
			$sysinfo['cputemp2'][$k_cpu_t] = $row;
			unset($row);
		endforeach;
//		get CPU usage
		if($sysinfo['cpus'] > 1):
			$a_cpu_u = system_get_smp_cpu_usage('system_get_sysinfo');
			$cpu_u = floor(array_sum($a_cpu_u) / $sysinfo['cpus']);
		else:
			$cpu_u = system_get_cpu_usage('system_get_sysinfo');
			$a_cpu_u = [$cpu_u];
		endif;
		$row = [];
		$row['pu'] = round($cpu_u,0,PHP_ROUND_HALF_UP);
		$row['pf'] = 100 - $row['pu'];
		$row['puh'] = sprintf('%u%%',$row['pu']);
		$row['pfh'] = sprintf('%u%%',$row['pf']);
		$row['tu'] = $row['puh'];
		$row['tf'] = $row['pfh'];
		$row['tt'] = $row['puh'];
		$sysinfo['cpuusage'] = $row;
		unset($row);
		$sysinfo['cpuusage2'] = [];
		foreach($a_cpu_u as $r_cpu_u):
			$row = [];
			$row['pu'] = round($r_cpu_u,0,PHP_ROUND_HALF_UP);
			$row['pf'] = 100 - $row['pu'];
			$row['puh'] = sprintf('%u%%',$row['pu']);
			$row['pfh'] = sprintf('%u%%',$row['pf']);
			$row['tu'] = $row['puh'];
			$row['tf'] = $row['pfh'];
			$row['tt'] = $row['tu'];
			$sysinfo['cpuusage2'][] = $row;
			unset($row);
		endforeach;
//		get storage usage
		$sysinfo['diskusage'] = get_disk_usage();
		$sysinfo['poolusage'] = get_pool_usage();
		$sysinfo['swapusage'] = get_swap_usage();
//		get UPS status
		$sysinfo['upsinfo'] = get_upsinfo();
	endif;
	return $sysinfo;
}
