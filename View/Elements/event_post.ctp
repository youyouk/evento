<div class="post">
	<div class="event-logo">
<?php

	if($event['Event']['logo'] != '') {
		$logo = $this->Html->image('logos/' . $event['Event']['logo'], array('class'=>'photo'));
	} else {
		$logo = '';
	}

	echo $this->Html->link($logo
		, array('controller'=>'events', 'action'=>'view', $event['Country']['slug']
		, $event['City']['slug']
		, $event['Venue']['slug'], $event['Event']['slug'])
		, array('escape'=>false, 'class'=>'event-index-logo'));

	if($event['Event']['promoted']) {
		echo '<div class="promoted-bar">' . __('Promoted') . '</div>';
	}
?>
	</div>
	<div class="vevent">
		<h2>
<?php
	echo $this->Html->link($this->Text->truncate(ucfirst($event['Event']['name']), 80)
		, array('controller'=>'events', 'action'=>'view'
		, $event['Country']['slug'], $event['City']['slug']
		, $event['Venue']['slug'], $event['Event']['slug']), array('class'=>'url summary'));
?>
		</h2>
		<div class="post-info">
<?php
	echo $this->Html->link($event['Category']['name'], array('action'=>'index'
		, $country, $city,$venue, $event['Category']['slug'], $current_tag, $year, $month, $day)
		, array('class'=>'category'));
	echo '. ';

	$venue = '<span class="location vcard">';
	$venue .= $this->Html->link(ucfirst($event['Venue']['name']), array('action'=>'index'
		, $event['Country']['slug'], $event['City']['slug'], $event['Venue']['slug']
		, $category, $current_tag, $year, $month, $day), array('class'=>'fn org')) . ', ';
	$venue .= '</span><span class="adr">';
	$venue .= $this->Html->link(ucfirst($event['City']['name'])
		, array('action'=>'index', $event['Country']['slug'], $event['City']['slug'], __d('url', 'all-venues')
		, $category, $current_tag, $year, $month, $day), array('escape'=>false, 'class'=>'locality')) . ', ';
	$venue .= $this->Html->link(__d('countries', $event['Country']['name']), array('action'=>'index'
		, $event['Country']['slug'], __d('url','all-cities'), __d('url', 'all-venues'), $category, $current_tag, $year, $month, $day)
		, array('class'=>'country-name')) . '.';
	$venue .= '</span>';

	echo sprintf(__('At %s'), $venue);
?>
			<br>
			<abbr class="dtstart" title="<?php echo $event['Event']['start_date']?>">
<?php
	$indexDay = ($day)? sprintf('%02d', $day) : '01';
	$indexMonth = ($month)? $monthNum : date('m');
	if($year) {
		$indexYear = $year;
	}
	else {
		$indexYear = date('Y');
		$indexDay = date('d');
	}
	$selectedDate = strtotime($indexYear . '-' . $indexMonth . '-' . $indexDay);
	$indexDate = strtotime($event['Event']['start_date']);
	if(date('Y-m-d', $indexDate) < date('Y-m-d', $selectedDate)){
		$indexDate = strtotime(date('Y-m-d', $selectedDate));
	}

	echo $this->Timeformat->getFormattedDate($indexDate);
?>
			</abbr>
		</div>
		<p class="description"><?php echo h($this->Text->truncate($event['Event']['notes'], 120)); ?></p>
	</div>
	<div class="clear"></div>
</div>