<?php
/*
	diag_log.inc

	Part of XigmaNAS® (https://www.xigmanas.com).
	Copyright © 2018-2025 XigmaNAS® <info@xigmanas.com>.
	All rights reserved.

	Redistribution and use in source and binary forms, with or without
	modification, are permitted provided that the following conditions are met:

	1. Redistributions of source code must retain the above copyright
	   notice, this list of conditions and the following disclaimer.

	2. Redistributions in binary form must reproduce the above copyright
	   notice, this list of conditions and the following disclaimer in the
	   documentation and/or other materials provided with the distribution.

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

require_once 'globals.inc';
require_once 'rc.inc';

// Get the location of the log files (may be modified via rc.conf variables).
$clogdir = rc_getenv_ex('clog_logdir',$g['varlog_path']);
$rsync_client_logfile = rc_getenv_ex('rsync_client_logfile',"{$g['varlog_path']}/rsync_client.log");
$rsync_local_logfile = rc_getenv_ex('rsync_local_logfile',"{$g['varlog_path']}/rsync_local.log");
$samba_logdir = rc_getenv_ex('samba_logdir',"{$g['varlog_path']}/samba4");
$samba_logfilename = rc_getenv_ex('samba_logfilename','samba.log');
$samba_logfile = sprintf('%s/%s',$samba_logdir,$samba_logfilename);
$minidlna_logdir = rc_getenv_ex('minidlna_logdir',"{$g['varlog_path']}/minidlna");
$minidlna_logfile = "{$minidlna_logdir}/minidlna.log";
$mtdaapd_logdir = rc_getenv_ex('mtdaapd_logdir',"{$g['varlog_path']}/mt-daapd");
$mtdaapd_logfile = "{$mtdaapd_logdir}/mt-daapd.log";

$proftpd_modban_logfile = rc_getenv_ex('proftpd_modban_banlog',"{$g['varlog_path']}/proftpd/ban.log");
//	the log configuration
$loginfo = [
	[
		'visible' => true,
		'desc' => gtext('System'),
		'logfile' => "{$clogdir}/system.log",
		'filename' => 'system.log',
		'type' => 'clog',
		'size' => '262144',
		'pattern' => '/^(?<timestamp>\S+\s+\d+\s+\S+)\s+(?<hostname>\S+)\s+(?<msg>.*)$/',
		'test.rfc5424' => true,
		'columns' => [
			['title' => gtext('Date & Time'),'width' => '15%','hdrclass' => 'lhell','class' => 'lcell','param' => 'style="white-space: nowrap;"','pmid' => 'timestamp'],
			['title' => gtext('Host'),'width' => '10%','hdrclass' => 'lhell','class' => 'lcell','param' => 'style="white-space: nowrap;"','pmid' => 'hostname'],
			['title' => gtext('Event'),'width' => '75%','hdrclass' => 'lhebl','class' => 'lcebl','param' => '','pmid' => 'msg']
		]],
	[
		'visible' => true,
		'desc' => gtext('SSH'),
		'logfile' => "{$clogdir}/sshd.log",
		'filename' => 'sshd.log',
		'type' => 'clog',
		"size" => '32768',
		'pattern' => '/^(?<timestamp>\S+\s+\d+\s+\S+)\s+(?<hostname>\S+)\s+(?<msg>.*)$/',
		'test.rfc5424' => true,
		'columns' => [
			['title' => gtext('Date & Time'),'width' => '15%','hdrclass' => 'lhell','class' => 'lcell','param' => 'style="white-space: nowrap;"','pmid' => 'timestamp'],
			['title' => gtext('Host'),'width' => '10%','hdrclass' => 'lhell','class' => 'lcell','param' => 'style="white-space: nowrap;"','pmid' => 'hostname'],
			['title' => gtext('Event'),'width' => '75%','hdrclass' => 'lhebl','class' => 'lcebl','param' => '','pmid' => 'msg']
		]],
	[
		'visible' => true,
		'desc' => gtext('Daemon'),
		'logfile' => "{$clogdir}/daemon.log",
		'filename' => 'daemon.log',
		'type' => 'clog',
		"size" => '32768',
		'pattern' => '/^(?<timestamp>\S+\s+\d+\s+\S+)\s+(?<hostname>\S+)\s+(?<msg>.*)$/',
		'test.rfc5424' => true,
		'columns' => [
			['title' => gtext('Date & Time'),'width' => '15%','hdrclass' => 'lhell','class' => 'lcell','param' => 'style="white-space: nowrap;"','pmid' => 'timestamp'],
			['title' => gtext('Host'),'width' => '10%','hdrclass' => 'lhell','class' => 'lcell','param' => 'style="white-space: nowrap;"','pmid' => 'hostname'],
			['title' => gtext('Event'),'width' => '75%','hdrclass' => 'lhebl','class' => 'lcebl','param' => '','pmid' => 'msg']
		]],
	[
		'visible' => true,
		'desc' => gtext('FTP'),
		'logfile' => "{$clogdir}/ftp.log",
		'filename' => 'ftp.log',
		'type' => 'clog',
		"size" => '32768',
		'pattern' => '/^(?<timestamp>\S+\s+\d+\s+\S+)\s+(?<hostname>\S+)\s+(?<msg>.*)$/',
		'test.rfc5424' => true,
		'columns' => [
			['title' => gtext('Date & Time'),'width' => '15%','hdrclass' => 'lhell','class' => 'lcell','param' => 'style="white-space: nowrap;"','pmid' => 'timestamp'],
			['title' => gtext('Host'),'width' => '10%','hdrclass' => 'lhell','class' => 'lcell','param' => 'style="white-space: nowrap;"','pmid' => 'hostname'],
			['title' => gtext('Event'),'width' => '75%','hdrclass' => 'lhebl','class' => 'lcebl','param' => '','pmid' => 'msg']
		]],
	[
		'visible' => isset($config['ftpd']['mod_ban']['enable']),
		'desc' => gtext('FTP - Ban Module'),
		'logfile' => $proftpd_modban_logfile,
		'filename' => 'ban.log',
		'type' => 'plain',
		'pattern' => '/^(?<timestamp>\S+\s+\S+)\s+(?<msg>.*)$/',
		'test.rfc5424' => false,
		'columns' => [
			['title' => gtext('Date & Time'),'width' => '15%','hdrclass' => 'lhell','class' => 'lcell','param' => 'style="white-space: nowrap;"','pmid' => 'timestamp'],
			['title' => gtext('Event'),'width' => '85%','hdrclass' => 'lhebl','class' => 'lcebl','param' => '','pmid' => 'msg']
		]],
	[
		'visible' => true,
		'desc' => gtext('FTP - File Transfer'),
		'logfile' => "{$g['varlog_path']}/xferlog",
		'filename' => 'xferlog',
		'type' => 'plain',
		'pattern' => '/^(?<timestamp>\S+\s+\S+\s+\d+\s+\S+\s+\d+) (?<msg>(\d+) (?<ip>\S+) (?<filesize>\d+) (?<filename>\S+) (a|b) (C|U|T|_) (o|i|d) (a|g|r) (?<username>\S+) (\S+) (0|1) (\S+) (c|i))$/',
		'test.rfc5424' => false,
		'columns' => [
			['title' => gtext('Date & Time'),'width' => '15%','hdrclass' => 'lhell','class' => 'lcell','param' => 'style="white-space: nowrap;"','pmid' => 'timestamp'],
			['title' => gtext('IP'),'width' => '10%','hdrclass' => 'lhell','class' => 'lcell','param' => '','pmid' => 'ip'],
			['title' => gtext('User'),'width' => '10%','hdrclass' => 'lhell','class' => 'lcell','param' => '','pmid' => 'username'],
//			['title' => gtext('File'),'hdrclass' => 'lhell','class' => 'lcell','param' => '','pmid' => 'filename'],
//			['title' => gtext('Filesize'),'hdrclass' => 'lhell','class' => 'lcell','param' => '','pmid' => 'filesize'],
			['title' => gtext('Event'),'width' => '65%','hdrclass' => 'lhebl','class' => 'lcebl','param' => '','pmid' => 'msg']
		]],
	[
		'visible' => true,
		'desc' => gtext('S.M.A.R.T.'),
		'logfile' => "{$clogdir}/smartd.log",
		'filename' => 'smartd.log',
		'type' => 'clog',
		"size" => '32768',
		'pattern' => '/^(?<timestamp>\S+\s+\d+\s+\S+)\s+(?<hostname>\S+)\s+(?<msg>.*)$/',
		'test.rfc5424' => true,
		'columns' => [
			['title' => gtext('Date & Time'),'width' => '15%','hdrclass' => 'lhell','class' => 'lcell','param' => 'style="white-space: nowrap;"','pmid' => 'timestamp'],
			['title' => gtext('Host'),'width' => '10%','hdrclass' => 'lhell','class' => 'lcell','param' => 'style="white-space: nowrap;"','pmid' => 'hostname'],
			['title' => gtext('Event'),'width' => '75%','hdrclass' => 'lhebl','class' => 'lcebl','param' => '','pmid' => 'msg']
		]],
	[
		'visible' => true,
		'desc' => gtext('SMB'),
		'logfile' => $samba_logfile,
		'filename' => $samba_logfilename,
		'type' => 'plain',
		'pattern' => '/^(?<msg>.*)$/',
		'test.rfc5424' => false,
		'columns' => [
			['title' => gtext('Event'),'width' => '100%','hdrclass' => 'lhebl','class' => 'lcebl','param' => '','pmid' => 'msg']
		]],
	[
		'visible' => true,
		'desc' => gtext('RSYNC - Server'),
		'logfile' => "{$clogdir}/rsyncd.log",
		'filename' => 'rsyncd.log',
		'type' => 'clog',
		"size" => '32768',
		'pattern' => '/^(?<timestamp>\S+\s+\d+\s+\S+)\s+(?<hostname>\S+)\s+(?<msg>.*)$/',
		'test.rfc5424' => true,
		'columns' => [
			['title' => gtext('Date & Time'),'width' => '15%','hdrclass' => 'lhell','class' => 'lcell','param' => 'style="white-space: nowrap;"','pmid' => 'timestamp'],
			['title' => gtext('Host'),'width' => '10%','hdrclass' => 'lhell','class' => 'lcell','param' => 'style="white-space: nowrap;"','pmid' => 'hostname'],
			['title' => gtext('Event'),'width' => '75%','hdrclass' => 'lhebl','class' => 'lcebl','param' => '','pmid' => 'msg']
		]],
	[
		'visible' => true,
		'desc' => gtext('RSYNC - Client'),
		'logfile' => $rsync_client_logfile,
		'filename' => 'rsync_client.log',
		'type' => 'plain',
		'pattern' => '/^(?<msg>.*)$/',
		'test.rfc5424' => false,
		'columns' => [
			['title' => gtext('Event'),'width' => '100%','hdrclass' => 'lhebl','class' => 'lcebl','param' => '','pmid' => 'msg']
		]],
	[
		'visible' => true,
		'desc' => gtext('RSYNC - Local'),
		'logfile' => $rsync_local_logfile,
		'filename' => 'rsync_local.log',
		'type' => 'plain',
		'pattern' => '/^(?<msg>.*)$/',
		'test.rfc5424' => false,
		'columns' => [
			['title' => gtext('Event'),'width' => '100%','hdrclass' => 'lhebl','class' => 'lcebl','param' => '','pmid' => 'msg']
		]],
	[
		'visible' => true,
		'desc' => gtext('UPnP/DLNA - MiniDLNA'),
		'logfile' =>  $minidlna_logfile,
		'filename' => 'minidlna.log',
		'type' => 'plain',
		'pattern' => '/^\[(?<timestamp>.*?)\]\s+(?<who>.*?)\..*?:\d+:\s+.*?:\s+(?<msg>.*)$/',
		'test.rfc5424' => false,
		'columns' => [
			['title' => gtext('Date & Time'),'width' => '15%','hdrclass' => 'lhell','class' => 'lcell','param' => 'style="white-space: nowrap;"','pmid' => 'timestamp'],
			['title' => gtext('Who'),'width' => '10%','hdrclass' => 'lhell','class' => 'lcell','param' => 'style="white-space: nowrap;"','pmid' => 'who'],
			['title' => gtext('Event'),'width' => '75%','hdrclass' => 'lhebl','class' => 'lcebl','param' => '','pmid' => 'msg']
		]],
	[
		'visible' => true,
		'desc' => gtext('iTunes/DAAP'),
		'logfile' => $mtdaapd_logfile,
		'filename' => 'mt-daapd.log',
		'type' => 'plain',
		'pattern' => '/^(?<timestamp>\S+\s+\S+)\s+\S+:\s+(?<msg>.*)$/',
		'test.rfc5424' => false,
		'columns' => [
			['title' => gtext('Date & Time'),'width' => '15%','hdrclass' => 'lhell','class' => 'lcell','param' => 'style="white-space: nowrap;"','pmid' => 'timestamp'],
			['title' => gtext('Event'),'width' => '85%','hdrclass' => 'lhebl','class' => 'lcebl','param' => '','pmid' => 'msg']
		]],
];
function log_get_contents($logfile,$type) {
	global $config;

	$content = [];
	$param = (isset($config['syslogd']['reverse']) ? '-r ' : '');
	$param .= '-n ' . $config['syslogd']['nentries'] ?? '50';
	switch($type):
		case 'clog':
			$searchlog = $_POST['searchlog'] ?? '';
		    $filter_str = ($searchlog == '') ? '' : "| grep -i \"".htmlspecialchars($searchlog)."\"";
			exec("/usr/sbin/clog {$logfile} | /usr/bin/tail {$param} {$filter_str}",$content);
			break;
		case 'plain':
			$searchlog = isset($_POST['searchlog']) ? $_POST['searchlog'] : '';
		    $filter_str = ($searchlog == '') ? '' : "| grep -i \"".htmlspecialchars($searchlog)."\"";
			exec("/bin/cat {$logfile} | /usr/bin/tail {$param} {$filter_str}",$content);
	endswitch;
	return $content;
}
function log_clear($loginfo) {
	if(!is_array($loginfo)):
		return;
	endif;
	switch($loginfo['type']):
		case 'clog':
			exec("/usr/sbin/clog -i -s {$loginfo['size']} {$loginfo['logfile']}");
			break;
		case 'plain':
			exec("/bin/cat /dev/null > {$loginfo['logfile']}");
			break;
	endswitch;
}
function log_download($loginfo) {
	$gt_logisempty = gtext('Log file is empty.');
	if(is_array($loginfo)):
		switch($loginfo['type']):
			case 'clog':
				if(file_exists($loginfo['logfile'])):
					exec(sprintf('/usr/sbin/clog %s',escapeshellarg($loginfo['logfile'])),$output);
					$content = implode("\n",$output);
				else:
					$content = $gt_logisempty;
				endif;
				header('Content-Type: application/octet-stream');
				header(sprintf('Content-Disposition: attachment; filename="%s"',$loginfo['filename']));
				header(sprintf('Content-Length: %d',strlen($content)));
				header('Pragma: hack');
				echo $content;
				break;
			case 'plain':
				if(file_exists($loginfo['logfile'])):
					$content = file_get_contents($loginfo['logfile']);
					if($content === false):
						$content = $gt_logisempty;
					endif;
				else:
					$content = $gt_logisempty;
				endif;
				if(empty($content)):
					$content = $gt_logisempty;
				endif;
				header('Content-Type: application/octet-stream');
				header(sprintf('Content-Disposition: attachment; filename="%s"',$loginfo['filename']));
				header(sprintf('Content-Length: %d',strlen($content)));
				header('Pragma: hack');
				echo $content;
				break;
		endswitch;
	endif;
}
