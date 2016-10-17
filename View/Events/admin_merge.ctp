<div id="admin-content">
	<div class="admin-content-wrap">
<?php if(!empty($events)){ ?>
		<div class="header">
<?php
	echo $this->Form->create('Search', array('url'=>array('controller'=>'events', 'action'=>'merge'
		, $event_merge['Event']['id'])));
	echo $this->Form->input('Search.term', array('class'=>'user-search', 'type'=>'text'
		, 'label'=>false, 'div'=>false));
	echo $this->Form->submit(__('Search'), array('class'=>'user-search-button', 'div'=>false));
	echo $this->Form->end();
?>
		</div>
		<div id="merge-event">
			<p><?php echo __('Merge event') . ' ' . h($event_merge['Event']['name']); ?></p>
		</div>
<?php
		echo '<table id="admin">';
		echo '<thead>';
		echo '<tr>';
		echo '<th class="name">' . __('Event') . '</th>';
		echo '<th class="icon">' . ucfirst(__('merge')) . '</th>';
		echo '</tr>';
		echo '</thead>';
		echo '<tbody>';
		foreach($events as $event) {
			echo '<tr>';
			echo '<td class="name">' . h($event['Event']['name']) . '</td>';
			echo '<td class="icon">';
			echo $this->Html->link(__('merge'), array('controller'=>'events', 'action'=>'merge'
				, $event_merge['Event']['id'], $event['Event']['id']), array('escape'=>false));
			echo '</td>';
			echo '</tr>';
		}
		echo '</tbody>';
		echo '</table>';
		echo $this->Element('paginator');
	 } else {
		echo '<div class="content-box"><p class="center empty">' . __('There are no events.') . '</p></div>';
	}
?>
</div>