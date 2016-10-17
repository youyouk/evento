<?php

/**
* Calendar Helper for CakePHP
*
*	Copyright 2008 John Elliott
* Licensed under The MIT License
* Redistributions of files must retain the above copyright notice.
*
*
* @author John Elliott
* @copyright 2008 John Elliott
* @link http://www.flipflops.org More Information
* @license			http://www.opensource.org/licenses/mit-license.php The MIT License
*
* Modified for Evento
* remove base_url and use html helper to create links.
* choose monday or sunday as start day for the week
*/

class CalendarHelper extends Helper {

	var $helpers = array('Html', 'Form');

	/**
	 * use the constructor to translate the month names in the url
	 */
	public function __construct(View $View, $settings = array()) {
		$this->month_list = array(
			__d('url', 'january'),
			__d('url', 'february'),
			__d('url', 'march'),
			__d('url', 'april'),
			__d('url', 'may'),
			__d('url', 'june'),
			__d('url', 'july'),
			__d('url', 'august'),
			__d('url', 'september'),
			__d('url', 'october'),
			__d('url', 'november'),
			__d('url', 'december')
		);
		parent::__construct($View, $settings);
	}

/**
* Generates a Calendar for the specified by the month and year params and populates it with the content
* of the data array
*
* @param $year string
* @param $month string
* @param $data array
* @param $base_url
* @return string HTML code to display calendar in view
*
*/

	function calendar($year = '', $month = '', $data = '', $country, $city, $venue, $category, $tag, $weekStart, $selectedDay = null) {
		$month_list = $this->month_list;
		$str = '';
		if($weekStart=='monday')
			$day_list = array('Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun');
		else if ($weekStart == 'saturday')
			$day_list = array('Sat', 'Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri');
		else
			$day_list = array('Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat');


		$day = 1;
		$today = 0;
		$month_num = intval(date('m'));

		if($year == '' || $month == '') {
			$year = date('Y');
			$month = strtolower(date('F'));
		} else {
			if (($key = array_search($month, $month_list)) !== false) {
				$month_num = $key + 1;
			}
		}

		$next_year = $year;
		$prev_year = $year;

		$next_month = intval($month_num) + 1;
		$prev_month = intval($month_num) - 1;

		if($next_month == 13) {
			$next_month = 'january';
			$next_year = intval($year) + 1;
		}
		else {
			$next_month = $month_list[$next_month -1];
		}

		if($prev_month == 0) {
			$prev_month = 'december';
			$prev_year = intval($year) - 1;
		}
		else {
			$prev_month = $month_list[$prev_month - 1];
		}

		if($year == date('Y') && strtolower($month) == strtolower(date('F'))){
		// set the flag that shows todays date but only in the current month - not past or future...
			$today = date('j');
		}

		$days_in_month = date("t", mktime(0, 0, 0, $month_num, 1, $year));
		$first_day_in_month = date('D', mktime(0,0,0, $month_num, 1, $year));

		$str .= '<div id="calendar-container"><table class="calendar">';
		$str .= '<thead>';
		$str .= '<tr><th class="cell-prev">';
		$str .= $this->Html->link(__('<<'),
			array('controller'=>'events', 'action'=>'index', $country, $city, $venue, $category, $tag,
			$prev_year, __d('url', $prev_month)),array('rel'=>'nofollow'));
		$str .= '</th><th colspan="5">' 
			. __d('cake', ucfirst($month)) . ' ' . $year
			. '</th><th class="cell-next">';
		$str .= $this->Html->link(__('>>'), array('controller'=>'events', 'action'=>'index', $country,	$city, $venue, $category, $tag, $next_year, __d('url', $next_month)),array('rel'=>'nofollow'));
		$str .= '</th></tr>';
		$str .= '<tr>';

		for($i = 0; $i < 7;$i++) {
			$str .= '<th class="cell-header">' . __($day_list[$i]) . '</th>';
		}

		$str .= '</tr>';
		$str .= '</thead>';
		$str .= '<tbody>';

		while($day <= $days_in_month) {
			$str .= '<tr>';
			for($i = 0; $i < 7; $i ++) {
				$cell = '';
				if(isset($data[$day])) {
					$image = 'active_event.png';
					if(date('Y-m-d', strtotime($year . '-' . $month_num . '-' . $day)) < date('Y-m-d')) {
						$image = 'past_event.png';
					}
					$cell = $this->Html->link($this->Html->image($image,
					array('alt'=>__('event'))), array('controller'=>'events',
					'action'=>'index', $country, $city, $venue, $category, $tag, $year, __d('url', $month), $day),
						array('title'=> $data[$day].' '.__n('Event', 'Events',$data[$day]),
						'escape'=>false));
				}
				$class = '';
				if($i > 4) {
					$class .= ' cell-weekend ';
				}

				if($day == $today) {
					if($first_day_in_month == $day_list[$i] || $day > 1) {
						$class .= ' cell-today ';
					}
				}

				if( ($first_day_in_month == $day_list[$i] || $day > 1) && $selectedDay == $day) {
					$class .= ' cell-selected-day ';
				}

				$class = ' class="' . $class . '"';

				$str.= '<td '.$class.'><div class="calendar-cell-container">';
				if(($first_day_in_month == $day_list[$i] || $day > 1) && ($day <= $days_in_month)) {
					$str.='<div class="cell-number">'.$day.'</div><div class="cell-data">'.$cell.'</div>';
					$day++;
				}
				$str.= '</div></td>';
			}
			$str .= '</tr>';
		}
		$str .= '</tbody>';
		$str .= '</table></div>';
		return $str;
	}
}
?>