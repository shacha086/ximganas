<?php
/*
	cs_scheduletime.php

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

declare(strict_types = 1);

/**
 *	Renders a scheduler
 *	@global array $g_months
 *	@global array $g_weekdays
 *	@param array $pconfig
 *	@param bool $with_minutes
 *	@return void
 */
function render_scheduler(array $pconfig,bool $with_minutes = true): void {
	global $g_months,$g_weekdays;

	$matrix = [];
	if($with_minutes):
		$matrix['minutes' ] = ['header' => gettext('Minutes' ),'all' => 'all_mins' ,'sel' => 'minute' ,'val_min' => 0,'val_steps' => 60,'val_break' => 15];
	endif;
	$matrix['hours' ] = ['header' => gettext('Hours' ),'all' => 'all_hours' ,'sel' => 'hour' ,'val_min' => 0,'val_steps' => 24,'val_break' => 6];
	$matrix['days' ] = ['header' => gettext('Days' ),'all' => 'all_days' ,'sel' => 'day' ,'val_min' => 1,'val_steps' => 31,'val_break' => 7];
	$matrix['months' ] = ['header' => gettext('Months' ),'all' => 'all_months' ,'sel' => 'month'];
	$matrix['weekdays'] = ['header' => gettext('Weekdays'),'all' => 'all_weekdays','sel' => 'weekday'];
	$document = new gui\document();
	$div = $document->addDIV(['class' => 'scheduler-container']);
	foreach($matrix as $matrix_key => $control):
		$attributes_1 = ['type' => 'radio','class' => 'rblo','name' => $control['all'],'id' => $control['all'] . '1','value' => '1'];
		$attributes_2 = ['type' => 'radio','class' => 'rblo dimassoctable','name' => $control['all'],'id' => $control['all'] . '2','value' => '0'];
		if(isset($pconfig[$control['all']]) && $pconfig[$control['all']] == 1):
			$attributes_1['checked'] = 'checked';
		else:
			$attributes_2['checked'] = 'checked';
		endif;
		$hook = $div->
			addDIV(['style' => 'flex: 0 0 auto;'])->
				insDIV(['class' => 'lhebl'],$control['header'])->
				addDIV(['class' => 'lcebl'])->
					push()->
					addDIV(['class' => 'rblo'])->
						addElement('label')->
							insINPUT($attributes_1)->
							insSPAN(['class' => 'rblo'],gettext('All'))->
					pop()->
					addDIV(['class' => 'rblo'])->
						addElement('label')->
							insINPUT($attributes_2)->
							insSPAN(['class' => 'rblo'],gettext('Selected...'))->
							addTABLE()->
								addTBODY(['class' => 'donothighlight'])->
									addTR();
		switch($matrix_key):
			case 'minutes':
			case 'hours':
			case 'days':
				$val_min = $key = $control['val_min'];
				$val_count = $control['val_steps'];
				$val_max = $val_min + $val_count - 1;
				$val_break = $control['val_break'];
				$outer_max = ceil($val_count / $val_break) - 1;
				$inner_max = $val_min + $val_break - 1;
				for($outer = 0;$outer <= $outer_max;$outer++):
					$td = $hook->addTDwC('lcefl');
					for($innerer = $val_min;$innerer <= $inner_max;$innerer++):
						if($key <= $val_max):
							$attributes_3 = [
								'type' => 'checkbox',
								'class' => 'cblo',
								'name' => $control['sel'] . '[]',
								'onchange' => sprintf('set_selected("%s")',$control['all']),
								'value' => $key
							];
							if(isset($pconfig[$control['sel']]) && is_array($pconfig[$control['sel']]) && in_array((string)$key,$pconfig[$control['sel']])):
								$attributes_3['checked'] = 'checked';
							endif;
							$td->
								addDIV(['class' => 'cblo'])->
									addElement('label')->
										insINPUT($attributes_3)->
										insSPAN(['class' => 'cblo'],sprintf('%02d',$key));
						else:
							break;
						endif;
						$key++;
					endfor;
				endfor;
				break;
			case 'months':
				$td = $hook->addTDwC('lcefl');
				foreach($g_months as $key => $val):
					$attributes_3 = [
						'type' => 'checkbox',
						'class' => 'cblo',
						'name' => $control['sel'] . '[]',
						'onchange' => sprintf('set_selected("%s")',$control['all']),
						'value' => $key
					];
					if(isset($pconfig[$control['sel']]) && is_array($pconfig[$control['sel']]) && in_array((string)$key,$pconfig[$control['sel']])):
						$attributes_3['checked'] = 'checked';
					endif;
					$td->
						addDIV(['class' => 'cblo'])->
							addElement('label')->
								insINPUT($attributes_3)->
								insSPAN(['class' => 'cblo'],$val);
				endforeach;
				break;
			case 'weekdays':
				$td = $hook->addTDwC('lcefl');
				foreach($g_weekdays as $key => $val):
					$attributes_3 = [
						'type' => 'checkbox',
						'class' => 'cblo',
						'name' => $control['sel'] . '[]',
						'onchange' => sprintf('set_selected("%s")',$control['all']),
						'value' => $key
					];
					if(isset($pconfig[$control['sel']]) && is_array($pconfig[$control['sel']])):
						if(in_array((string)$key,$pconfig[$control['sel']])):
							$attributes_3['checked'] = 'checked';
						elseif($key == 7):
//							compatibility for non-ISO day of week 0 for Sunday
							if(in_array('0',$pconfig[$control['sel']])):
								$attributes_3['checked'] = 'checked';
							endif;
						endif;
					endif;
					$td->
						addDIV(['class' => 'cblo'])->
							addElement('label')->
								insINPUT($attributes_3)->
								insSPAN(['class' => 'cblo'],$val);
				endforeach;
				break;
		endswitch;
	endforeach;
	$document->render();
}
