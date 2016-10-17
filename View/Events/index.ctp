<div class="container content">
	<div id="center_column" class="events-index">
<?php
	// show the promoted events at the top
	if(!empty($promoted)) {
		echo '<div id="promoted">';
		foreach($promoted as $promoted_event) {
			echo $this->Element('event_post', array('event'=>$promoted_event));
		}
		echo '</div>';
	}

	// show events filters
	echo $this->Element('index_filter');

	// load events
	if(!empty($events)) {
		foreach($events as $event) {
			echo $this->Element('event_post', array('event'=>$event));
		}

		echo $this->Element('paginator');
	} else {
		echo '<p class="empty-message">'.__('There are no events.').'</p>';
	}
?>
	</div>
	<div id="right_column">
<?php
	// calendar is inserted here
	echo $this->Calendar->calendar($year, $month, $data, $country, $city, $venue, $category
		, $current_tag, $weekStart, $day);
?>
		<div class="event-browser">
<?php
	$tomorrow_year = date('Y', strtotime('+1 day'));
	$tomorrow_month = strtolower(date('F', strtotime('+1 day')));
	$tomorrow_day = date('d', strtotime('+1 day'));
	echo $this->Html->link(__('Tomorrow'), array('controller'=>'events', 'action'=>'index', $country
		, $city, $venue, $category, $current_tag, $tomorrow_year, __d('url', $tomorrow_month), $tomorrow_day));
	echo $this->Html->link(__('This week'),array('controller'=>'events', 'action'=>'index', $country
		, $city, $venue, $category, $current_tag, date('Y'), __d('url', strtolower(date('F'))), __d('url', 'week')));
	echo $this->Html->link(__('This month'), array('controller'=>'events', 'action'=>'index'
		, $country, $city, $venue, $category, $current_tag, date('Y'), __d('url', strtolower(date('F')))));
?>
		</div>
<?php
	echo $this->Element('search_form');
	if(!empty($countries)) { 
?>
		<div id="cities_column">
			<h2><?php echo __('Cities'); ?></h2>
			<?php echo $this->Element('cities_list'); ?>
		</div>
<?php 
	}
	echo $this->Element('categories_block');
	echo $this->Element('toptags_block');
?>
	</div>
	<div class="clear"></div>
</div>