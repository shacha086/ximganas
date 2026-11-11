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

namespace services\torrent;

use common\properties as myp;

class grid_properties extends myp\container {
	protected $x_enable;
	public function init_enable(): myp\property_enable {
		$property = $this->x_enable = new myp\property_enable($this);
		return $property;
	}
	final public function get_enable(): myp\property_enable {
		return $this->x_enable ?? $this->init_enable();
	}
	protected $x_peerport;
	public function init_peerport(): myp\property_int {
		$title = gettext('Peer Port');
		$property = $this->x_peerport = new myp\property_int($this);
		$property->
			set_name('peerport')->
			set_title($title);
		return $property;
	}
	final public function get_peerport(): myp\property_int {
		return $this->x_peerport ?? $this->init_peerport();
	}
	protected $x_configdir;
	public function init_configdir(): myp\property_text {
		$title = gettext('Configuration Folder');
		$property = $this->x_configdir = new myp\property_text($this);
		$property->
			set_name('configdir')->
			set_title($title);
		return $property;
	}
	final public function get_configdir(): myp\property_text {
		return $this->x_configdir ?? $this->init_configdir();
	}
	protected $x_incompletedir;
	public function init_incompletedir(): myp\property_text {
		$title = gettext('Incomplete Folder');
		$property = $this->x_incompletedir = new myp\property_text($this);
		$property->
			set_name('incompletedir')->
			set_title($title);
		return $property;
	}
	final public function get_incompletedir(): myp\property_text {
		return $this->x_incompletedir ?? $this->init_incompletedir();
	}
	protected $x_downloaddir;
	public function init_downloaddir(): myp\property_text {
		$title = gettext('Download Folder');
		$property = $this->x_downloaddir = new myp\property_text($this);
		$property->
			set_name('downloaddir')->
			set_title($title);
		return $property;
	}
	final public function get_downloaddir(): myp\property_text {
		return $this->x_downloaddir ?? $this->init_downloaddir();
	}
	protected $x_watchdir;
	public function init_watchdir(): myp\property_text {
		$title = gettext('Watch Folder');
		$property = $this->x_watchdir = new myp\property_text($this);
		$property->
			set_name('watchdir')->
			set_title($title);
		return $property;
	}
	final public function get_watchdir(): myp\property_text {
		return $this->x_watchdir ?? $this->init_watchdir();
	}
	protected $x_portforwarding;
	public function init_portforwarding(): myp\property_bool {
		$title = gettext('Port Forwarding');
		$property = $this->x_portforwarding = new myp\property_bool($this);
		$property->
			set_name('portforwarding')->
			set_title($title);
		return $property;
	}
	final public function get_portforwarding(): myp\property_bool {
		return $this->x_portforwarding ?? $this->init_portforwarding();
	}
	protected $x_pex;
	public function init_pex(): myp\property_bool {
		$title = gettext('Peer Exchange');
		$property = $this->x_pex = new myp\property_bool($this);
		$property->
			set_name('pex')->
			set_title($title);
		return $property;
	}
	final public function get_pex(): myp\property_bool {
		return $this->x_pex ?? $this->init_pex();
	}
	protected $x_dht;
	public function init_dht(): myp\property_bool {
		$title = gettext('Distributed Hash Table');
		$property = $this->x_dht = new myp\property_bool($this);
		$property->
			set_name('dht')->
			set_title($title);
		return $property;
	}
	final public function get_dht(): myp\property_bool {
		return $this->x_dht ?? $this->init_dht();
	}
	protected $x_lpd;
	public function init_lpd(): myp\property_bool {
		$title = gettext('Local Peer Discovery');
		$property = $this->x_lpd = new myp\property_bool($this);
		$property->
			set_name('lpd')->
			set_title($title);
		return $property;
	}
	final public function get_lpd(): myp\property_bool {
		return $this->x_lpd ?? $this->init_lpd();
	}
	protected $x_utp;
	public function init_utp(): myp\property_bool {
		$title = gettext('uTP');
		$property = $this->x_utp = new myp\property_bool($this);
		$property->
			set_name('utp')->
			set_title($title);
		return $property;
	}
	final public function get_utp(): myp\property_bool {
		return $this->x_utp ?? $this->init_utp();
	}
	protected $x_uplimit;
	public function init_uplimit(): myp\property_int {
		$title = gettext('Upload Bandwidth');
		$property = $this->x_uplimit = new myp\property_int($this);
		$property->
			set_name('uplimit')->
			set_title($title);
		return $property;
	}
	final public function get_uplimit(): myp\property_int {
		return $this->x_uplimit ?? $this->init_uplimit();
	}
	protected $x_downlimit;
	public function init_downlimit(): myp\property_int {
		$title = gettext('Download Bandwidth');
		$property = $this->x_downlimit = new myp\property_int($this);
		$property->
			set_name('downlimit')->
			set_title($title);
		return $property;
	}
	final public function get_downlimit(): myp\property_int {
		return $this->x_downlimit ?? $this->init_downlimit();
	}
	protected $x_umask;
	public function init_umask(): myp\property_octal {
		$title = gettext('User Mask');
		$property = $this->x_umask = new myp\property_octal($this);
		$property->
			set_name('umask')->
			set_title($title);
		return $property;
	}
	final public function get_umask(): myp\property_octal {
		return $this->x_umask ?? $this->init_umask();
	}
	protected $x_preallocation;
	public function init_preallocation(): myp\property_list {
		$title = gettext('Preallocation');
		$property = $this->x_preallocation = new myp\property_list($this);
		$property->
			set_name('preallocation')->
			set_title($title);
		return $property;
	}
	final public function get_preallocation(): myp\property_list {
		return $this->x_preallocation ?? $this->init_preallocation();
	}
	protected $x_encryption;
	public function init_encryption(): myp\property_list {
		$title = gettext('Encryption');
		$property = $this->x_encryption = new myp\property_list($this);
		$property->
			set_name('encryption')->
			set_title($title);
		return $property;
	}
	final public function get_encryption(): myp\property_list {
		return $this->x_encryption ?? $this->init_encryption();
	}
	protected $x_messagelevel;
	public function init_messagelevel(): myp\property_list {
		$title = gettext('Message Level');
		$property = $this->x_messagelevel = new myp\property_list($this);
		$property->
			set_name('messagelevel')->
			set_title($title);
		return $property;
	}
	final public function get_messagelevel(): myp\property_list {
		return $this->x_messagelevel ?? $this->init_messagelevel();
	}
	protected $x_extraoptions;
	public function init_extraoptions(): myp\property_text {
		$title = gettext('Extra Options');
		$property = $this->x_extraoptions = new myp\property_text($this);
		$property->
			set_name('extraoptions')->
			set_title($title);
		return $property;
	}
	final public function get_extraoptions(): myp\property_text {
		return $this->x_extraoptions ?? $this->init_extraoptions();
	}
	protected $x_port;
	public function init_port(): myp\property_int {
		$title = gettext('Port');
		$property = $this->x_port = new myp\property_int($this);
		$property->
			set_name('port')->
			set_title($title);
		return $property;
	}
	final public function get_port(): myp\property_int {
		return $this->x_port ?? $this->init_port();
	}
	protected $x_authrequired;
	public function init_authrequired(): myp\property_bool {
		$title = gettext('Authentication');
		$property = $this->x_authrequired = new myp\property_bool($this);
		$property->
			set_name('authrequired')->
			set_title($title);
		return $property;
	}
	final public function get_authrequired(): myp\property_bool {
		return $this->x_authrequired ?? $this->init_authrequired();
	}
	protected $x_username;
	public function init_username(): myp\property_text {
		$title = gettext('Username');
		$property = $this->x_username = new myp\property_text($this);
		$property->
			set_name('username')->
			set_title($title);
		return $property;
	}
	final public function get_username(): myp\property_text {
		return $this->x_username ?? $this->init_username();
	}
	protected $x_password;
	public function init_password(): myp\property_text {
		$title = gettext('Password');
		$property = $this->x_password = new myp\property_text($this);
		$property->
			set_name('password')->
			set_title($title);
		return $property;
	}
	final public function get_password(): myp\property_text {
		return $this->x_password ?? $this->init_password();
	}
	protected $x_rpchostwhitelistenabled;
	public function init_rpchostwhitelistenabled(): myp\property_list {
		$title = gettext('DNS Rebind Protection');
		$property = $this->x_rpchostwhitelistenabled = new myp\property_list($this);
		$property->
			set_name('rpchostwhitelistenabled')->
			set_title($title);
		return $property;
	}
	final public function get_rpchostwhitelistenabled(): myp\property_list {
		return $this->x_rpchostwhitelistenabled ?? $this->init_rpchostwhitelistenabled();
	}
	protected $x_rpchostwhitelist;
	public function init_rpchostwhitelist(): myp\property_text {
		$title = gettext('Domain Names');
		$property = $this->x_rpchostwhitelist = new myp\property_text($this);
		$property->
			set_name('rpchostwhitelist')->
			set_title($title);
		return $property;
	}
	final public function get_rpchostwhitelist(): myp\property_text {
		return $this->x_rpchostwhitelist ?? $this->init_rpchostwhitelist();
	}
	protected $x_startafterstart;
	public function init_startafterstart(): myp\property_bool {
		$title = gettext('Start Torrents');
		$property = $this->x_startafterstart = new myp\property_bool($this);
		$property->
			set_name('startafterstart')->
			set_title($title);
		return $property;
	}
	final public function get_startafterstart(): myp\property_bool {
		return $this->x_startafterstart ?? $this->init_startafterstart();
	}
	protected $x_stopbeforestop;
	public function init_stopbeforestop(): myp\property_bool {
		$title = gettext('Stop Torrents');
		$property = $this->x_stopbeforestop = new myp\property_bool($this);
		$property->
			set_name('stopbeforestop')->
			set_title($title);
		return $property;
	}
	final public function get_stopbeforestop(): myp\property_bool {
		return $this->x_stopbeforestop ?? $this->init_stopbeforestop();
	}
/*	json "alt-speed-down": <integer>
	protected $x_altspeeddown;
	public function init_altspeeddown(): myp\property_int {
		$title = gettext('Alternative Download Speed');
		$property = $this->x_altspeeddown = new myp\property_int($this);
		$property->
			set_name('altspeeddown')->
			set_title($title);
		return $property;
	}
	final public function get_altspeeddown(): myp\property_int {
		return $this->x_altspeeddown ?? $this->init_altspeeddown();
	}
 */
/*	json "alt-speed-enabled": <boolean>
	protected $x_altspeedenabled;
	public function init_altspeedenabled(): myp\property_bool {
		$title = gettext('Alternative Speed');
		$property = $this->x_altspeedenabled = new myp\property_bool($this);
		$property->
			set_name('altspeedenabled')->
			set_title($title);
		return $property;
	}
	final public function get_altspeedenabled(): myp\property_bool {
		return $this->x_altspeedenabled ?? $this->init_altspeedenabled();
	}
 */
/*	json "alt-speed-time-begin": <integer>
	protected $x_altspeedtimebegin;
	public function init_altspeedtimebegin(): myp\property_int {
		$title = gettext('Alternative Speed Start Time');
		$property = $this->x_altspeedtimebegin = new myp\property_int($this);
		$property->
			set_name('altspeedtimebegin')->
			set_title($title);
		return $property;
	}
	final public function get_altspeedtimebegin(): myp\property_int {
		return $this->x_altspeedtimebegin ?? $this->init_altspeedtimebegin();
	}
 */
/*	json "alt-speed-time-day": <integer>
	protected $x_altspeedtimeday;
	public function init_altspeedtimeday(): myp\property_int {
		$title = gettext('Alternative Speed Days');
		$property = $this->x_altspeedtimeday = new myp\property_int($this);
		$property->
			set_name('altspeedtimeday')->
			set_title($title);
		return $property;
	}
	final public function get_altspeedtimeday(): myp\property_int {
		return $this->x_altspeedtimeday ?? $this->init_altspeedtimeday();
	}
 */
/*	json "alt-speed-time-enabled": <boolean>
	protected $x_altspeedtimeenabled;
	public function init_altspeedtimeenabled(): myp\property_bool {
		$title = gettext('Alternative Speed Schedule');
		$property = $this->x_altspeedtimeenabled = new myp\property_bool($this);
		$property->
			set_name('altspeedtimeenabled')->
			set_title($title);
		return $property;
	}
	final public function get_altspeedtimeenabled(): myp\property_bool {
		return $this->x_altspeedtimeenabled ?? $this->init_altspeedtimeenabled();
	}
 */
/*	json "alt-speed-time-end": <integer>
	protected $x_altspeedtimeend;
	public function init_altspeedtimeend(): myp\property_int {
		$title = gettext('Alternative Speed End Time');
		$property = $this->x_altspeedtimeend = new myp\property_int($this);
		$property->
			set_name('altspeedtimeend')->
			set_title($title);
		return $property;
	}
	final public function get_altspeedtimeend(): myp\property_int {
		return $this->x_altspeedtimeend ?? $this->init_altspeedtimeend();
	}
 */
/*	json "alt-speed-up": <integer>
	protected $x_altspeedup;
	public function init_altspeedup(): myp\property_int {
		$title = gettext('Alternative Upload Speed');
		$property = $this->x_altspeedup = new myp\property_int($this);
		$property->
			set_name('altspeedup')->
			set_title($title);
		return $property;
	}
	final public function get_altspeedup(): myp\property_int {
		return $this->x_altspeedup ?? $this->init_altspeedup();
	}
 */
/*	json "bind-address-ipv4": <string> */
/*	json "bind-address-ipv6": <string> */
/*	json "blocklist-enabled": <boolean>
	protected $x_blocklistenabled;
	public function init_blocklistenabled(): myp\property_bool {
		$title = gettext('Enable Blocklist');
		$property = $this->x_blocklistenabled = new myp\property_bool($this);
		$property->
			set_name('blocklistenabled')->
			set_title($title);
		return $property;
	}
	final public function get_blocklistenabled(): myp\property_bool {
		return $this->x_blocklistenabled ?? $this->init_blocklistenabled();
	}
 */
/*	json "blocklist-url": <string> */
/*	json "cache-size-mb": <integer> */
/*	json "download-queue-enabled": <boolean>
	protected $x_downloadqueueenabled;
	public function init_downloadqueueenabled(): myp\property_bool {
		$title = gettext('Download Queue');
		$property = $this->x_downloadqueueenabled = new myp\property_bool($this);
		$property->
			set_name('downloadqueueenabled')->
			set_title($title);
		return $property;
	}
	final public function get_downloadqueueenabled(): myp\property_bool {
		return $this->x_downloadqueueenabled ?? $this->init_downloadqueueenabled();
	}
 */
/*	json "download-queue-size": <integer> */
/*	json "idle-seeding-limit": <integer> */
/*	json "idle-seeding-limit-enabled": <boolean>
	protected $x_idleseedinglimitenabled;
	public function init_idleseedinglimitenabled(): myp\property_bool {
		$title = gettext('Enable Idle Seeding Limit');
		$property = $this->x_idleseedinglimitenabled = new myp\property_bool($this);
		$property->
			set_name('idleseedinglimitenabled')->
			set_title($title);
		return $property;
	}
	final public function get_idleseedinglimitenabled(): myp\property_bool {
		return $this->x_idleseedinglimitenabled ?? $this->init_idleseedinglimitenabled();
	}
 */
/*	json "incomplete-dir-enabled": <boolean>
	protected $x_incompletedirenabled;
	public function init_incompletedirenabled(): myp\property_bool {
		$title = gettext('Enable Incomplete Folder');
		$property = $this->x_incompletedirenabled = new myp\property_bool($this);
		$property->
			set_name('incompletedirenabled')->
			set_title($title);
		return $property;
	}
	final public function get_incompletedirenabled(): myp\property_bool {
		return $this->x_incompletedirenabled ?? $this->init_incompletedirenabled();
	}
 */
/*	json "peer-congestion-algorithm": <string> */
/*	json "peer-id-ttl-hours": <integer> */
/*	json "peer-limit-global": <integer> */
/*	json "peer-limit-per-torrent": <integer> */
/*	json "peer-port-random-high": <integer> */
/*	json "peer-port-random-low": <integer> */
/*	json "peer-port-random-on-start": <boolean>
	protected $x_peerportrandomonstart;
	public function init_peerportrandomonstart(): myp\property_bool {
		$title = gettext('Random Peer Port');
		$property = $this->x_peerportrandomonstart = new myp\property_bool($this);
		$property->
			set_name('peerportrandomonstart')->
			set_title($title);
		return $property;
	}
	final public function get_peerportrandomonstart(): myp\property_bool {
		return $this->x_peerportrandomonstart ?? $this->init_peerportrandomonstart();
	}
 */
/*	json "peer-socket-tos": <string> */
/*	json "prefetch-enabled": <boolean>
	protected $x_prefetchenabled;
	public function init_prefetchenabled(): myp\property_bool {
		$title = gettext('Prefetch');
		$property = $this->x_prefetchenabled = new myp\property_bool($this);
		$property->
			set_name('prefetchenabled')->
			set_title($title);
		return $property;
	}
	final public function get_prefetchenabled(): myp\property_bool {
		return $this->x_prefetchenabled ?? $this->init_prefetchenabled();
	}
 */
/*	json "queue-stalled-enabled": <boolean>
	protected $x_queuestalledenabled;
	public function init_queuestalledenabled(): myp\property_bool {
		$title = gettext('Queue Stall');
		$property = $this->x_queuestalledenabled = new myp\property_bool($this);
		$property->
			set_name('queuestalledenabled')->
			set_title($title);
		return $property;
	}
	final public function get_queuestalledenabled(): myp\property_bool {
		return $this->x_queuestalledenabled ?? $this->init_queuestalledenabled();
	}
 */
/*	json "queue-stalled-minutes": <integer> */
/*	json "ratio-limit": <float> */
/*	json "ratio-limit-enabled": <boolean>
	protected $x_ratiolimitenabled;
	public function init_ratiolimitenabled(): myp\property_bool {
		$title = gettext('Ratio Limit');
		$property = $this->x_ratiolimitenabled = new myp\property_bool($this);
		$property->
			set_name('ratiolimitenabled')->
			set_title($title);
		return $property;
	}
	final public function get_ratiolimitenabled(): myp\property_bool {
		return $this->x_ratiolimitenabled ?? $this->init_ratiolimitenabled();
	}
 */
/*	json "rename-partial-files": <boolean>
	protected $x_renamepartialfiles;
	public function init_renamepartialfiles(): myp\property_bool {
		$title = gettext('Rename Partial Files');
		$property = $this->x_renamepartialfiles = new myp\property_bool($this);
		$property->
			set_name('renamepartialfiles')->
			set_title($title);
		return $property;
	}
	final public function get_renamepartialfiles(): myp\property_bool {
		return $this->x_renamepartialfiles ?? $this->init_renamepartialfiles();
	}
 */
/*	json "rpc-bind-address": <string> */
/*	json "rpc-enabled": <boolean>
	protected $x_rpcenabled;
	public function init_rpcenabled(): myp\property_bool {
		$title = gettext('Enable RPC');
		$property = $this->x_rpcenabled = new myp\property_bool($this);
		$property->
			set_name('rpcenabled')->
			set_title($title);
		return $property;
	}
	final public function get_rpcenabled(): myp\property_bool {
		return $this->x_rpcenabled ?? $this->init_rpcenabled();
	}
 */
/*	json "rpc-url": <string> */
/*	json "rpc-whitelist": <string> */
/*	json "rpc-whitelist-enabled": <boolean>
	protected $x_rpcwhitelistenabled;
	public function init_rpcwhitelistenabled(): myp\property_bool {
		$title = gettext('Enable RPC Whitelist');
		$property = $this->x_rpcwhitelistenabled = new myp\property_bool($this);
		$property->
			set_name('rpcwhitelistenabled')->
			set_title($title);
		return $property;
	}
	final public function get_rpcwhitelistenabled(): myp\property_bool {
		return $this->x_rpcwhitelistenabled ?? $this->init_rpcwhitelistenabled();
	}
 */
/*	json "scrape-paused-torrents-enabled": <boolean>
	protected $x_scrapepausedtorrentsenabled;
	public function init_scrapepausedtorrentsenabled(): myp\property_bool {
		$title = gettext('Scrape Paused Torrents');
		$property = $this->x_scrapepausedtorrentsenabled = new myp\property_bool($this);
		$property->
			set_name('scrapepausedtorrentsenabled')->
			set_title($title);
		return $property;
	}
	final public function get_scrapepausedtorrentsenabled(): myp\property_bool {
		return $this->x_scrapepausedtorrentsenabled ?? $this->init_scrapepausedtorrentsenabled();
	}
 */
/*	json "script-torrent-done-enabled": <boolean>
	protected $x_scripttorrentdoneenabled;
	public function init_scripttorrentdoneenabled(): myp\property_bool {
		$title = gettext('Script After Completion');
		$property = $this->x_scripttorrentdoneenabled = new myp\property_bool($this);
		$property->
			set_name('scripttorrentdoneenabled')->
			set_title($title);
		return $property;
	}
	final public function get_scripttorrentdoneenabled(): myp\property_bool {
		return $this->x_scripttorrentdoneenabled ?? $this->init_scripttorrentdoneenabled();
	}
 */
/*	json "script-torrent-done-filename": <string> */
/*	json "seed-queue-enabled": <boolean> */
/*	json "seed-queue-size": <integer> */
/*	json "speed-limit-down-enabled": <boolean>
	protected $x_speedlimitdownenabled;
	public function init_speedlimitdownenabled(): myp\property_bool {
		$title = gettext('Limit Download Speed');
		$property = $this->x_speedlimitdownenabled = new myp\property_bool($this);
		$property->
			set_name('speedlimitdownenabled')->
			set_title($title);
		return $property;
	}
	final public function get_speedlimitdownenabled(): myp\property_bool {
		return $this->x_speedlimitdownenabled ?? $this->init_speedlimitdownenabled();
	}
 */
/*	json "speed-limit-up-enabled": <boolean>
	protected $x_speedlimitupenabled;
	public function init_speedlimitupenabled(): myp\property_bool {
		$title = gettext('Limit Upload Speed');
		$property = $this->x_speedlimitupenabled = new myp\property_bool($this);
		$property->
			set_name('speedlimitupenabled')->
			set_title($title);
		return $property;
	}
	final public function get_speedlimitupenabled(): myp\property_bool {
		return $this->x_speedlimitupenabled ?? $this->init_speedlimitupenabled();
	}
 */
/*	json "start-added-torrents": <boolean>
	protected $x_startaddedtorrents;
	public function init_startaddedtorrents(): myp\property_bool {
		$title = gettext('Start Added Torrents');
		$property = $this->x_startaddedtorrents = new myp\property_bool($this);
		$property->
			set_name('startaddedtorrents')->
			set_title($title);
		return $property;
	}
	final public function get_startaddedtorrents(): myp\property_bool {
		return $this->x_startaddedtorrents ?? $this->init_startaddedtorrents();
	}
 */
/*	json "trash-original-torrent-files": <boolean> */
/*	json "upload-slots-per-torrent": <integer> */
/*	json "watch-dir-enabled": <boolean>
	protected $x_watchdirenabled;
	public function init_watchdirenabled(): myp\property_bool {
		$title = gettext('Enable Watch Folder');
		$property = $this->x_watchdirenabled = new myp\property_bool($this);
		$property->
			set_name('watchdirenabled')->
			set_title($title);
		return $property;
	}
	final public function get_watchdirenabled(): myp\property_bool {
		return $this->x_watchdirenabled ?? $this->init_watchdirenabled();
	}
 */
}
