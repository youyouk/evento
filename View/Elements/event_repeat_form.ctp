<?php
/*
 * display the repeat option only if it is an add form
 */
if($this->request->params['action']=='add' || $this->request->params['action']=='admin_add') {
	// repeat select field
	echo $this->Form->label('Event.repeat', __('Repeat',true));
	echo $this->Form->select('Event.repeat', array(
		'does_not_repeat'=>__('Does not repeat', true),	// default option
		'daily'=>__('Daily', true),
		'weekly'=>__('Weekly', true),
		'monthly'=>__('Monthly', true)
	), array('empty'=>false));
	
	echo '<div id="repeat-options">';

	// repeat daily options panel
	echo '<div id="repeat-daily-form">';
	echo $this->Form->label('Event.repeat_daily_freq', __('Repeats every', true));
	echo '<div class="block">';
	echo $this->Form->select('Event.repeat_daily_freq', range(1,7), array('empty'=>false));
	echo ' '.__('Day(s)', true);
	echo '</div>';
	echo $this->Form->input('Event.daily_weekdays', array('type'=>'checkbox', 'label'=>__('Weekdays only', true)));
	echo '</div>';
	
	// repeat weekly options panel
	echo '<div id="repeat-weekly-form">';
	echo $this->Form->label('Event.repeat_weeks', __('Repeats every', true));
	echo '<div class="block">';
	echo $this->Form->select('Event.repeat_weeks', range(1, 3), array('empty'=>false));
	echo ' '.__('Week(s)', true);
	echo '</div>';
	echo '<p class="block">'.__('Repeats on', true).'</p>';
	echo '<div class="days_block">';
	echo $this->Form->input('Event.weekly.1', array('type'=>'checkbox', 'label'=>__('Monday', true)));
	echo $this->Form->input('Event.weekly.2', array('type'=>'checkbox', 'label'=>__('Tuesday', true)));
	echo $this->Form->input('Event.weekly.3', array('type'=>'checkbox', 'label'=>__('Wednesday', true)));
	echo $this->Form->input('Event.weekly.4', array('type'=>'checkbox', 'label'=>__('Thursday', true)));
	echo '</div><div class="days_block">';                                                     
	echo $this->Form->input('Event.weekly.5', array('type'=>'checkbox', 'label'=>__('Friday', true)));
	echo $this->Form->input('Event.weekly.6', array('type'=>'checkbox', 'label'=>__('Saturday', true)));
	echo $this->Form->input('Event.weekly.7', array('type'=>'checkbox', 'label'=>__('Sunday', true)));
	echo '</div>';
	echo '<div class="clear"></div>';
	echo '</div>';
	
	// repeat monthly options panel
	echo '<div id="repeat-monthly-form">';
	echo $this->Form->label('Event.repeat_months', __('Repeats every', true));
	echo '<div class="block">';
	echo $this->Form->select('Event.repeat_months', range(1, 12), array('empty'=>false));
	echo ' '.__('Month(s)', true);
	echo '</div>';
	echo '<input type="radio" id="same_day" class="radio-button" name="data[Event][day]" value="same_day" checked>';
	echo '<label for="same_day" class="inline">' . __('Same day', true).'</label><br/>';
	echo $this->Form->input('Event.monthly_weekdays', array('type'=>'checkbox', 'class'=>'checkbox', 'id'=>'EventMonthlyWeekdays', 'label'=>__('Weekdays only', true)));	
	echo '<input type="radio" id="specific_day" class="radio-button" name="data[Event][day]" value="specific_day">';
	echo '<label for="specific_day" class="inline">'. __('Specific day', true).'</label><br/>';
	echo '<div class="block">';
	echo $this->Form->select('Event.direction', array(
		'first'=>__('First', true),
		'last' =>__('Last', true)
		), array('empty'=>false));
	echo $this->Form->select('Event.month_day', array(
		1 => __('Monday', true),
		2 => __('Tuesday', true),
		3 => __('Wednesday', true),
		4 => __('Thursday', true),
		5 => __('Friday', true),
		6 => __('Saturday', true),
		7 => __('Sunday', true)
		), array('empty'=>false));
	echo '</div></div>';

	echo '<div id="repeat-general">';
	// general repeat options used in all repeat panels
	// repeat until this date
	echo '<input type="radio" id="repeat_until" class="radio-button" name="data[Event][repeat_times]" value="date" checked>';	
	echo '<label for="repeat_until" class="inline">'. __('Repeat until', true) .'</label>';
	echo $this->Form->input('Event.repeat_until',array('type'=>'datetime', 'dateFormat'=>'DMY',
	    'timeFormat'=>Configure::read('evento_settings.timeFormat'),
		'minYear' => date('Y'),
		'maxYear' => date('Y')+1,
		'separator' => "",
		'label' => false
		));

	echo '<input type="radio" id="repeat_occurences" class="radio-button" name="data[Event][repeat_times]" value="occurrences">';
	echo '<label for="repeat_occurences" class="inline">' .  __('End after', true) . '</label> ';
	echo $this->Form->select('Event.repeat_occurrences', range(1, 20), array('empty'=>false));
	echo ' '.__('occurrences', true);
	echo '</div>';
	echo '</div>';
}
?>