<div class="container content">
	<div id="center_column">
<?php
	if(empty($events)) {
		echo __('No events found');
	} else {
		$date = '';
		foreach($events as $event) {
			$indexDate = strtotime($event['Event']['start_date']);
			if(date('Y-m-d', $indexDate) < date('Y-m-d')){
				$indexDate = strtotime(date('Y-m-d'));
			}

			if($date!=date('d/m/Y', $indexDate)) {
				$date = date('d/m/Y', $indexDate);
				echo '<p class="search-date">';
				echo $this->Timeformat->getFormattedDate($indexDate, false);
				echo '</p>';
			}
?>
		<div class="search-event">
			<h2 class="summary">
<?php
	echo $this->Html->link(ucfirst($event['Event']['name'])
		, array('controller'=>'events', 'action'=>'view', $event['Country']['slug']
		, $event['City']['slug'], $event['Venue']['slug'], $event['Event']['slug'])); 
?>
			</h2>
			<div class="post-info">
<?php
	echo $this->Time->format($this->Timeformat->getTimeFormat(), $indexDate) . ' ';
	$venue = $this->Html->link(ucfirst($event['Venue']['name']), array('action'=>'index'
		, $event['Country']['slug'], $event['City']['slug'], $event['Venue']['slug'])) . ', ';
	$venue .= $this->Html->link(ucfirst($event['City']['name'])
		, array('action'=>'index', $event['Country']['slug'], $event['City']['slug'])) . ', ';
	$venue .= $this->Html->link(__d('countries', $event['Country']['name']), array('action'=>'index'
		, $event['Country']['slug'])) . '.';
	echo sprintf(__('At %s'), $venue);
?>
			</div>
			<p><?php echo h($this->Text->truncate($event['Event']['notes'], 150)); ?></p>
		</div>
<?php
	}
	echo $this->Element('paginator'); 
}
?>
	</div>
	<div id="right_column"><?php  echo $this->Element('search_form'); ?></div>
	<div class="clear"></div>
</div>