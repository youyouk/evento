<?php
/*
 If events are being filtered by city or tag show the info-line and allow the user to clear
 individual filters.
*/
if(
	$country!=__d('url','all-countries')
	|| $city!=__d('url', 'all-cities')
	|| $category!=__d('url', 'all-categories')
	|| $current_tag!=__d('url', 'all-tags')
	|| $year || $month || $day
	) {
		echo '<div id="filter-block">';
	}

if($country!=__d('url','all-countries')) {
	echo '<div class="info-line">';
	echo '<span>'.sprintf(__('Events in %s'),h(__d('countries', $country_name))).'</span>';
	echo $this->Html->link('x',array('action'=>'index', __d('url','all-countries'), __d('url', 'all-cities'), __d('url', 'all-venues'), $category,
		$current_tag, $year, $month, $day));
	echo '</div>';
}

if($city!=__d('url', 'all-cities')) {
	echo '<div class="info-line">';
	echo '<span>'.sprintf(__('Events in %s'), h($city_name)).'</span>';
	echo $this->Html->link('x',array('action'=>'index', $country,
		__d('url', 'all-cities'), __d('url', 'all-venues'), $category, $current_tag, $year, $month, $day));
	echo '</div>';
}

if($venue!=__d('url', 'all-venues')) {
	echo '<div class="info-line">';
	echo '<span>'.sprintf(__('Events at %s'), h($venue_name)).'</span>';
	echo $this->Html->link('x',array('action'=>'index', $country,
		$city, __d('url', 'all-venues'), $category, $current_tag, $year, $month, $day));
	echo '</div>';
}

if($category!=__d('url', 'all-categories')) {
	echo "<div class=\"info-line\">";
	echo '<span>'.sprintf(__("Events in category %s"),h($category_name)).'</span>';
	echo $this->Html->link('x', array('action'=>'index', $country, $city, $venue, __d('url', 'all-categories'),
		$current_tag, $year, $month, $day));
	echo '</div>';
}

if($current_tag!=__d('url', 'all-tags')) {
	echo '<div class="info-line">';
	echo '<span>'.sprintf(__("Events tagged %s"), h($tag_name)).'</span>';
	echo $this->Html->link('x',array('action'=>'index', $country, $city, $venue, $category, __d('url', 'all-tags'), $year,
		$month, $day));
	echo '</div>';
}

if($year or $month or $day) {
	$str = '';
	if($month) {
		if (($key = array_search($month, $this->Calendar->month_list)) !== false) {
			$month_num = $key + 1;
		} else {
			$month_num = intval(date('m'));
		}

		$month = __(ucfirst($month));
		if(!$day && ($month_num == date('n') && $year == date('Y'))) $str = __('Events this month');
		if($day) {
			if($day == __d('url', 'week')) $str = __('Events this week');
			elseif($day == date('d', time()) && $month_num == date('n') && $year == date('Y')) {
				$str = __('Events today');
			}
			elseif($day == date('d', strtotime('+1 day')) && $month_num == date('n') && $year == date('Y')) {
				$str = __('Events tomorrow');
			}
		}
	}

	if($str=='') {
		if($day) {
			$str = sprintf(__('Events on %1$s %2$s %3$s'), __d('cake', $month), $day, $year);
		} else {
			$str = sprintf(__('Events in %1$s %2$s'), __d('cake', $month), $year);
		}
	}

	echo '<div class="info-line">';
	echo '<span>'.$str.'</span>';
	echo $this->Html->link('x',array('action'=>'index', $country, $city, $venue, $category, __d('url', 'all-tags')));
	echo '</div>';
}

if(
	$country!=__d('url','all-countries')
	|| $city!=__d('url', 'all-cities')
	|| $category!=__d('url', 'all-categories')
	|| $current_tag!=__d('url', 'all-tags')
	|| $year || $month || $day
	) {
		echo '</div>';
	}
?>