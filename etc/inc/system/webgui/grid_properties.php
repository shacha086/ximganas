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

namespace system\webgui;

use common\properties as myp;

class grid_properties extends myp\container {
	protected $x_adddivsubmittodataframe;
	public function init_adddivsubmittodataframe(): myp\property_bool {
		$property = $this->x_adddivsubmittodataframe = new myp\property_bool($this);
		$property->
			set_name('adddivsubmittodataframe')->
			set_title(gettext('Button Location'));
		return $property;
	}
	final public function get_adddivsubmittodataframe(): myp\property_bool {
		return $this->x_adddivsubmittodataframe ?? $this->init_adddivsubmittodataframe();
	}
	protected $x_cssfcfile;
	public function init_cssfcfile(): myp\property_text {
		$property = $this->x_cssfcfile = new myp\property_text($this);
		$property->
			set_name('cssfcfile')->
			set_title(gettext('File Chooser'));
		return $property;
	}
	final public function get_cssfcfile(): myp\property_text {
		return $this->x_cssfcfile ?? $this->init_cssfcfile();
	}
	protected $x_cssfcfilemode;
	public function init_cssfcfilemode(): myp\property_list {
		$property = $this->x_cssfcfilemode = new myp\property_list($this);
		$property->
			set_name('cssfcfilemode')->
			set_title(gettext('File Mode'));
		return $property;
	}
	final public function get_cssfcfilemode(): myp\property_list {
		return $this->x_cssfcfilemode ?? $this->init_cssfcfilemode();
	}
	protected $x_cssguifile;
	public function init_cssguifile(): myp\property_text {
		$property = $this->x_cssguifile = new myp\property_text($this);
		$property->
			set_name('cssguifile')->
			set_title(gettext('GUI'));
		return $property;
	}
	final public function get_cssguifile(): myp\property_text {
		return $this->x_cssguifile ?? $this->init_cssguifile();
	}
	protected $x_cssguifilemode;
	public function init_cssguifilemode(): myp\property_list {
		$property = $this->x_cssguifilemode = new myp\property_list($this);
		$property->
			set_name('cssguifilemode')->
			set_title(gettext('File Mode'));
		return $property;
	}
	final public function get_cssguifilemode(): myp\property_list {
		return $this->x_cssguifilemode ?? $this->init_cssguifilemode();
	}
	protected $x_cssnavbarfile;
	public function init_cssnavbarfile(): myp\property_text {
		$property = $this->x_cssnavbarfile = new myp\property_text($this);
		$property->
			set_name('cssnavbarfile')->
			set_title(gettext('Navigation Bar'));
		return $property;
	}
	final public function get_cssnavbarfile(): myp\property_text {
		return $this->x_cssnavbarfile ?? $this->init_cssnavbarfile();
	}
	protected $x_cssnavbarfilemode;
	public function init_cssnavbarfilemode(): myp\property_list {
		$property = $this->x_cssnavbarfilemode = new myp\property_list($this);
		$property->
			set_name('cssnavbarfilemode')->
			set_title(gettext('File Mode'));
		return $property;
	}
	final public function get_cssnavbarfilemode(): myp\property_list {
		return $this->x_cssnavbarfilemode ?? $this->init_cssnavbarfilemode();
	}
	protected $x_csstabsfile;
	public function init_csstabsfile(): myp\property_text {
		$property = $this->x_csstabsfile = new myp\property_text($this);
		$property->
			set_name('csstabsfile')->
			set_title(gettext('Tabs'));
		return $property;
	}
	final public function get_csstabsfile(): myp\property_text {
		return $this->x_csstabsfile ?? $this->init_csstabsfile();
	}
	protected $x_csstabsfilemode;
	public function init_csstabsfilemode(): myp\property_list {
		$property = $this->x_csstabsfilemode = new myp\property_list($this);
		$property->
			set_name('csstabsfilemode')->
			set_title(gettext('File Mode'));
		return $property;
	}
	final public function get_csstabsfilemode(): myp\property_list {
		return $this->x_csstabsfilemode ?? $this->init_csstabsfilemode();
	}
	protected $x_enabletogglemode;
	public function init_enabletogglemode(): myp\property_bool {
		$property = $this->x_enabletogglemode = new myp\property_bool($this);
		$property->
			set_name('enabletogglemode')->
			set_title(gettext('Toggle Mode'));
		return $property;
	}
	final public function get_enabletogglemode(): myp\property_bool {
		return $this->x_enabletogglemode ?? $this->init_enabletogglemode();
	}
	protected $x_navbartoplevelstyle;
	public function init_navbartoplevelstyle(): myp\property_list {
		$property = $this->x_navbartoplevelstyle = new myp\property_list($this);
		$property->
			set_name('navbartoplevelstyle')->
			set_title(gettext('Navbar Style'));
		return $property;
	}
	final public function get_navbartoplevelstyle(): myp\property_list {
		return $this->x_navbartoplevelstyle ?? $this->init_navbartoplevelstyle();
	}
	protected $x_nonsidisksizevalues;
	public function init_nonsidisksizevalues(): myp\property_bool {
		$property = $this->x_nonsidisksizevalues = new myp\property_bool($this);
		$property->
			set_name('nonsidisksizevalues')->
			set_title(gettext('Binary Prefix'));
		return $property;
	}
	final public function get_nonsidisksizevalues(): myp\property_bool {
		return $this->x_nonsidisksizevalues ?? $this->init_nonsidisksizevalues();
	}
	protected $x_showcolorfulmeter;
	public function init_showcolorfulmeter(): myp\property_bool {
		$property = $this->x_showcolorfulmeter = new myp\property_bool($this);
		$property->
			set_name('showcolorfulmeter')->
			set_title(gettext('Colorful Meters'));
		return $property;
	}
	final public function get_showcolorfulmeter(): myp\property_bool {
		return $this->x_showcolorfulmeter ?? $this->init_showcolorfulmeter();
	}
	protected $x_skipviewmode;
	public function init_skipviewmode(): myp\property_bool {
		$property = $this->x_skipviewmode = new myp\property_bool($this);
		$property->
			set_name('skipviewmode')->
			set_title(gettext('Skip View Mode'));
		return $property;
	}
	final public function get_skipviewmode(): myp\property_bool {
		return $this->x_skipviewmode ?? $this->init_skipviewmode();
	}
	protected $x_showmaxcpus;
	public function init_showmaxcpus(): myp\property_int {
		$title = gettext('Show Max CPUs');
		$property = $this->x_showmaxcpus = new myp\property_int();
		$property->
			set_name('showmaxcpus')->
			set_title($title);
		return $property;
	}
	final public function get_showmaxcpus(): myp\property_int {
		return $this->x_showmaxcpus ?? $this->init_showmaxcpus();
	}
	protected $x_lp_diag_infos;
	public function init_lp_diag_infos(): myp\property_list {
		$property = $this->x_lp_diag_infos = new myp\property_list($this);
		$property->
			set_name('lp_diag_infos')->
			set_title(gettext('Diagnostics Information'));
		return $property;
	}
	final public function get_lp_diag_infos(): myp\property_list {
		return $this->x_lp_diag_infos ?? $this->init_lp_diag_infos();
	}
}
