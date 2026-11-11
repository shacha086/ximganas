<?php
/*
	property.php

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

namespace common\properties;

/**
 *	Adds additional characteristics to a variable
 */
abstract class property {
	const INPUT_TYPE_TEXT = 8469;
	const INPUT_TYPE_CHECKBOX = 6772;
	const INPUT_TYPE_TITLELINE_CHECKBOX = 8467;
	const INPUT_TYPE_TEXTAREA = 8465;
	const INPUT_TYPE_PASSWORD = 8065;
	const INPUT_TYPE_RADIO_GRID = 8271;
	const INPUT_TYPE_CHECKBOX_GRID = 6771;
	const INPUT_TYPE_SELECT = 8369;
	const INPUT_TYPE_FILECHOOSER = 7067;

	protected $x_owner = null;
	protected ?string $x_input_type = null;
	protected ?string $x_id = null;
	protected ?string $x_name = null;
	protected ?string $x_title = null;
	protected $x_description = null;
	protected ?string $x_caption = null;
	protected $x_defaultvalue = null;
	protected bool $x_editableonadd = true;
	protected bool $x_editableonmodify = true;
	protected array $x_filter = [];
	protected array $x_filter_group = [];
	protected ?string $x_message_error = null;
	protected ?string $x_message_info = null;
	protected ?string $x_message_warning = null;

	public function __construct($owner = null) {
		$this->set_owner($owner);
	}
	abstract public function filter_use_default();
	public function set_owner($owner = null) {
		if(is_object($owner)):
			$this->x_owner = $owner;
		endif;
		return $this;
	}
	public function get_owner() {
		return $this->x_owner;
	}
/**
 *	indicates the input type for the gui
 *	@param int $input_type INPUT_TYPE constant from object
 *	@return $this
 */
	public function set_input_type(int $input_type) {
		$this->x_input_type = $input_type;
		return $this;
	}
	public function get_input_type() {
		return $this->x_input_type;
	}
	public function set_id(?string $id = null) {
		$this->x_id = $id;
		return $this;
	}
	public function get_id() {
		return $this->x_id;
	}
	public function set_name(?string $name = null) {
		$this->x_name = $name;
		return $this;
	}
	public function get_name() {
		return $this->x_name;
	}
	public function set_title(?string $title = null) {
		$this->x_title = $title;
		return $this;
	}
	public function get_title() {
		return $this->x_title;
	}
	public function set_description($description = null) {
		$this->x_description = $description;
		return $this;
	}
	public function get_description() {
		return $this->x_description;
	}
	public function set_caption(?string $caption = null) {
		$this->x_caption = $caption;
		return $this;
	}
	public function get_caption() {
		return $this->x_caption;
	}
	public function set_defaultvalue($defaultvalue = null) {
		$this->x_defaultvalue = $defaultvalue;
		return $this;
	}
	public function get_defaultvalue() {
		return $this->x_defaultvalue;
	}
	public function set_editableonadd(bool $editableonadd = true) {
		$this->x_editableonadd = $editableonadd;
		return $this;
	}
	public function get_editableonadd() {
		return $this->x_editableonadd;
	}
	public function set_editableonmodify(bool $editableonmodify = true) {
		$this->x_editableonmodify = $editableonmodify;
		return $this;
	}
	public function get_editableonmodify() {
		return $this->x_editableonmodify;
	}
	public function set_message_error(?string $message_error = null) {
		$this->x_message_error = $message_error;
		return $this;
	}
	public function get_message_error() {
		if(is_null($this->x_message_error)):
			return sprintf('%s: %s',$this->get_title() ?? gettext('Undefined'),gettext('The value is invalid.'));
		endif;
		return $this->x_message_error;
	}
	public function set_message_info(?string $message_info = null) {
		$this->x_message_info = $message_info;
		return $this;
	}
	public function get_message_info() {
		return $this->x_message_info;
	}
	public function set_message_warning(?string $message_warning = null) {
		$this->x_message_warning = $message_warning;
		return $this;
	}
	public function get_message_warning() {
		return $this->x_message_warning;
	}
/**
 *	Method to set filter type.
 *	@param int $value Filter type.
 *	@param string $filter_name Name of the filter, default is 'ui'.
 *	@return object Returns $this.
 */
	public function set_filter($value = null,string $filter_name = 'ui') {
//		create array element if it doesn't exist.
		if(array_key_exists($filter_name,$this->x_filter)):
			$this->x_filter[$filter_name]['filter'] = $value;
		else:
			$this->x_filter[$filter_name] = ['filter' => $value,'flags' => null,'options' => null];
		endif;
		return $this;
	}
/**
 *	Method to set filter flags.
 *	@param type $value Flags for filter.
 *	@param string $filter_name Name of the filter, default is 'ui'.
 *	@return object Returns $this.
 */
	public function set_filter_flags($value = null,string $filter_name = 'ui') {
//		create array element if it doesn't exist.
		if(array_key_exists($filter_name,$this->x_filter)):
			$this->x_filter[$filter_name]['flags'] = $value;
		else:
			$this->x_filter[$filter_name] = ['filter' => null,'flags' => $value,'options' => null];
		endif;
		return $this;
	}
/**
 *	Method to set filter options.
 *	@param array $value Filter options.
 *	@param string $filter_name Name of the filter, default is 'ui'.
 *	@return object Returns $this.
 */
	public function set_filter_options($value = null,string $filter_name = 'ui') {
//		create array element if it doesn't exist.
		if(array_key_exists($filter_name,$this->x_filter)):
			$this->x_filter[$filter_name]['options'] = $value;
		else:
			$this->x_filter[$filter_name] = ['filter' => null,'flags' => null,'options' => $value];
		endif;
		return $this;
	}
/**
 *	Method returns the filter settings of $filter_name:
 *	@param string $filter_name Name of the filter, default is 'ui'.
 *	@return array If $filter_name exists the filter configuration is returned, otherwise null is returned.
 */
	public function get_filter(string $filter_name = 'ui') {
		if(array_key_exists($filter_name,$this->x_filter)):
			return $this->x_filter[$filter_name];
		endif;
		return null;
	}
/**
 *	Method to set a filter group
 *	@param string $root_filter_name
 *	@param array $filter_names
 *	@return object Returns $this.
 */
	public function set_filter_group(string $root_filter_name = 'ui', array $filter_names = []) {
		$this->x_filter_group[$root_filter_name] = $filter_names;
		return $this;
	}
/**
 *	Method to get a previously defined filter group
 *	@param string $root_filter_name Name of the filter group
 *	@return array Array with group members
 */
	public function get_filter_group(string $root_filter_name = 'ui') {
		return array_key_exists($root_filter_name,$this->x_filter_group) ? $this->x_filter_group[$root_filter_name] : [$root_filter_name];
	}
/**
 *	Get a list of filter names og a group
 *	@param type $filter
 *	@return array Array of filter names
 */
	public function get_filter_names($filter = 'ui') {
		$filter_names = [];
		$filter_groups = is_array($filter) ? $filter : (is_string($filter) ? [$filter] : []);
		foreach($filter_groups as $filter_group):
			if(is_string($filter_group)):
				$filter_group_members = $this->get_filter_group($filter_group);
				foreach($filter_group_members as $filter_group_member):
					$filter_names[] = $filter_group_member;
				endforeach;
			endif;
		endforeach;
		return $filter_names;
	}
/**
 *	Method to apply filter to an input element.
 *	A list of filters will be processed until a filter does not return null.
 *	@param int $input_type Input type. Check the PHP manual for supported input types.
 *	@param mixed $filter Single filter name or list of filters names to validate, default is ['ui'].
 *	@return mixed Filter result.
 */
	public function validate_input(int $input_type = INPUT_POST,$filter = 'ui') {
		$result = null;
		$filter_names = $this->get_filter_names($filter);
		foreach($filter_names as $filter_name):
			if(is_string($filter_name)):
				$filter_parameter = $this->get_filter($filter_name);
				if(isset($filter_parameter)):
					$action = (isset($filter_parameter['flags']) ? 1 : 0) + (isset($filter_parameter['options']) ? 2 : 0);
					switch($action):
						case 3:
							$result = filter_input($input_type,$this->get_name(),$filter_parameter['filter'],['flags' => $filter_parameter['flags'],'options' => $filter_parameter['options']]);
							break;
						case 2:
							$result = filter_input($input_type,$this->get_name(),$filter_parameter['filter'],['options' => $filter_parameter['options']]);
							break;
						case 1:
							$result = filter_input($input_type,$this->get_name(),$filter_parameter['filter'],$filter_parameter['flags']);
							break;
						case 0:
							$result = filter_input($input_type,$this->get_name(),$filter_parameter['filter']);
							break;
					endswitch;
				endif;
				if(isset($result)):
					break; // foreach
				endif;
			endif;
		endforeach;
		return $result;
	}
/**
 *	Method to validate a value.
 *	A list of filters will be processed until a filter does not return null.
 *	If an array of values is provided all values must pass validation.
 *	@param mixed $value The value to be tested.
 *	@param mixed $filter Single filter name or list of filters names to validate, default is ['ui'].
 *	@return mixed Filter result.
 */
	public function validate_value($content,$filter = 'ui') {
		$result = null;
		$filter_names = $this->get_filter_names($filter);
		switch(true):
			case is_scalar($content):
				$value = $content;
				$row_results = null;
				foreach($filter_names as $filter_name):
					$filter_result = null;
					if(is_string($filter_name)):
						$filter_parameter = $this->get_filter($filter_name);
						if(isset($filter_parameter)):
							$action = (isset($filter_parameter['flags']) ? 1 : 0) + (isset($filter_parameter['options']) ? 2 : 0);
							switch($action):
								case 3:
									$filter_result = filter_var($value,$filter_parameter['filter'],['flags' => $filter_parameter['flags'],'options' => $filter_parameter['options']]);
									break;
								case 2:
									$filter_result = filter_var($value,$filter_parameter['filter'],['options' => $filter_parameter['options']]);
									break;
								case 1:
									$filter_result = filter_var($value,$filter_parameter['filter'],$filter_parameter['flags']);
									break;
								case 0:
									$filter_result = filter_var($value,$filter_parameter['filter']);
									break;
							endswitch;
						endif;
					endif;
					if(isset($filter_result)):
						$row_results = $filter_result;
						break;
					endif;
				endforeach;
				if(isset($row_results)):
					$result = $row_results;
				endif;
				break;
			case is_array($content):
				$row_results = [];
				foreach($content as $value):
					$row_results[] = null;
					end($row_results);
					$row_results_key = key($row_results);
					if(is_scalar($value)):
						foreach($filter_names as $filter_name):
							$filter_result = null;
							if(is_string($filter_name)):
								$filter_parameter = $this->get_filter($filter_name);
								if(isset($filter_parameter)):
									$action = (isset($filter_parameter['flags']) ? 1 : 0) + (isset($filter_parameter['options']) ? 2 : 0);
									switch($action):
										case 3:
											$filter_result = filter_var($value,$filter_parameter['filter'],['flags' => $filter_parameter['flags'],'options' => $filter_parameter['options']]);
											break;
										case 2:
											$filter_result = filter_var($value,$filter_parameter['filter'],['options' => $filter_parameter['options']]);
											break;
										case 1:
											$filter_result = filter_var($value,$filter_parameter['filter'],$filter_parameter['flags']);
											break;
										case 0:
											$filter_result = filter_var($value,$filter_parameter['filter']);
											break;
									endswitch;
								endif;
							endif;
							if(isset($filter_result)):
								$row_results[$row_results_key] = $filter_result;
								break;
							endif;
						endforeach;
					endif;
					if(is_null($row_results[$row_results_key])):
						$row_results = [];
						break;
					endif;
				endforeach;
				if(count($row_results) > 0):
					$result = $row_results;
				endif;
				break;
		endswitch;
		return $result;
	}
/**
 *	Method to validate an array value. Key/Index is the name property of $this.
 *	A list of filters will be processed until a filter does not return null.
 *	If an array of values is provided all values must pass validation.
 *	@param array $variable The variable to be tested.
 *	@param mixed $filter Single filter name or list of filters names to validate, default is ['ui'].
 *	@return mixed Filter result.
 */
	public function validate_array_element(array $variable,$filter = 'ui') {
		$result = null;
		$filter_names = $this->get_filter_names($filter);
		$key = $this->get_name();
//		fix for boolean
		if(!array_key_exists($key,$variable)):
			if(strpos(get_class($this),'property_bool') !== false):
				$variable[$key] = false;
			endif;
		endif;
		if(array_key_exists($key,$variable)):
			$content = $variable[$key];
			switch(true):
				case is_scalar($content):
					$value = $content;
					$row_results = null;
					foreach($filter_names as $filter_name): //	loop through filters
						$filter_result = null;
						if(is_string($filter_name)):
							$filter_parameter = $this->get_filter($filter_name);
							if(isset($filter_parameter)):
								$action = (isset($filter_parameter['flags']) ? 1 : 0) + (isset($filter_parameter['options']) ? 2 : 0);
								switch($action):
									case 3:
										$filter_result = filter_var($value,$filter_parameter['filter'],['flags' => $filter_parameter['flags'],'options' => $filter_parameter['options']]);
										break;
									case 2:
										$filter_result = filter_var($value,$filter_parameter['filter'],['options' => $filter_parameter['options']]);
										break;
									case 1:
										$filter_result = filter_var($value,$filter_parameter['filter'],$filter_parameter['flags']);
										break;
									case 0:
										$filter_result = filter_var($value,$filter_parameter['filter']);
										break;
								endswitch;
							endif;
						endif;
						if(isset($filter_result)):
							$row_results = $filter_result;
							break;
						endif;
					endforeach;
					if(isset($row_results)):
						$result = $row_results;
					endif;
					break;
				case is_array($content):
					$row_results = [];
					foreach($content as $value): // loop through array elements
						$row_results[] = null;
						end($row_results);
						$row_results_key = key($row_results);
						if(is_scalar($value)):
							foreach($filter_names as $filter_name): //	loop through filters
								$filter_result = null;
								if(is_string($filter_name)):
									$filter_parameter = $this->get_filter($filter_name);
									if(isset($filter_parameter)):
										$action = (isset($filter_parameter['flags']) ? 1 : 0) + (isset($filter_parameter['options']) ? 2 : 0);
										switch($action):
											case 3:
												$filter_result = filter_var($value,$filter_parameter['filter'],['flags' => $filter_parameter['flags'],'options' => $filter_parameter['options']]);
												break;
											case 2:
												$filter_result = filter_var($value,$filter_parameter['filter'],['options' => $filter_parameter['options']]);
												break;
											case 1:
												$filter_result = filter_var($value,$filter_parameter['filter'],$filter_parameter['flags']);
												break;
											case 0:
												$filter_result = filter_var($value,$filter_parameter['filter']);
												break;
										endswitch;
									endif;
								endif;
								if(isset($filter_result)):
									$row_results[$row_results_key] = $filter_result;
									break;
								endif;
							endforeach;
						endif;
						if(is_null($row_results[$row_results_key])):
							$row_results = [];
							break;
						endif;
					endforeach;
					if(count($row_results) > 0):
						$result = $row_results;
					endif;
					break;
			endswitch;
		endif;
		return $result;
	}
/**
 *	Returns the value if key exists and is not null, otherwise the default value is returned.
 *	@param array $source
 *	@return string
 */
	public function validate_config(array $source) {
		$name = $this->get_name();
		if(array_key_exists($name,$source)):
			$return_data = $source[$name];
		else:
			$return_data = $this->get_defaultvalue();
		endif;
		return $return_data;
	}
/**
 *	Calculate if field value is readonly
 *	@param bool $check_editableonadd true will evaluate editableonadd, false will evaluate editableonmodify
 *	@return bool true when readonly, false otherwise.
 */
	public function is_readonly_rowmode(bool $check_editableonadd): bool {
		return !($check_editableonadd ? $this->x_editableonadd : $this->x_editableonmodify);
	}
}
