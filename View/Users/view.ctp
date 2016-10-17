<div class="container content">
	<div id="left_column_user">
		<div class="user-photo">
<?php echo $this->Html->image('users/'.$user['User']['photo'], array('alt'=>$user['User']['username'])); ?>
		</div>
		<div class="user-info">
			<h1><?php echo ucfirst($user['User']['username']); ?></h1>
<?php
	$memberDate = __d('cake', $this->Time->format('F', $user['User']['created'])) . ' ';
	$memberDate .= $this->Time->format('Y', $user['User']['created']) . '. ';

	echo sprintf(__('Member since %s'), $memberDate);

	if(isset($user['City']['name'])) {
		echo '<br />';
		echo $this->Html->link(ucfirst($user['City']['name'])
			, array('controller'=>'events', 'action'=>'index', $user['Country']['slug'], $user['City']['slug']));
		echo ', ';
		echo $this->Html->link(__d('countries', $user['Country']['name'])
			, array('controller'=>'events', 'action'=>'index', $user['Country']['slug']));
		echo '.';
	}

	if($user['User']['web']) {
		echo '<br />';
		echo $this->Html->link(__('Visit website'),
			'http://' . ereg_replace('^http://','', $user['User']['web']), array('rel'=>'nofollow'));
	}
?>
		</div>
		<div class="clear"></div>
		<div class="user-links">
<?php
	if($homepage) {
		echo $this->Html->link(__('Edit my profile'),array('controller'=>'users', 'action'=>'edit')
			, array('class'=>'button'));
	}
?>
		</div>
	</div>
	<div id="right_column_user">
		<div class="user-events">
<?php
	if($showAddEventsButton == true || Configure::read('evento_settings.disableAttendees') != 1) {
?>
			<h2><?php echo __("My events"); ?></h2>
			<div id="user-events-browser">
<?php
	$selected = array('class'=>'selected');
	$upcoming = (!isset($mode) || !$mode)? $selected : null;
	$past = (isset($mode) && $mode == 'past')? $selected : null;
	$posted = (isset($mode) && $mode == 'posted')? $selected : null;

	if(Configure::read('evento_settings.disableAttendees') != 1) {
		echo $this->Html->link(__('Upcoming'), array('controller'=>'users', 'action'=>'view'
			, $user['User']['slug']), $upcoming);
		echo $this->Html->link(__('Past'), array('controller'=>'users', 'action'=>'view'
			, $user['User']['slug'], 'past'), $past);
	}

	if($showAddEventsButton == true) {
		echo $this->Html->link(__('Posted by me'), array('controller'=>'users', 'action'=>'view'
			, $user['User']['slug'], 'posted'), $posted);
	}
?>
			</div>
<?php
	if(!empty($user['Attendee'])) {
		echo '<table id="user-events-table">';
		foreach($user['Attendee'] as $event) {
			echo '<tr>';
			echo '<td class="date">';
			echo $this->Timeformat->getFormattedDate($event['Event']['start_date'], false);
			echo '</td>';
			echo '<td>';
			echo ' <span>';
			echo $this->Html->link(ucfirst($event['Event']['name'])
				, array('controller'=>'events', 'action'=>'view', $event['Country']['slug']
				, $event['City']['slug'], $event['Venue']['slug'], $event['Event']['slug']));
			echo '</span> <br>';
			echo h($event['Venue']['address'] . ', ' . $event['City']['name'] 
				. '. ' . $event['Country']['name'] . '.');
			echo '</td>';
			echo '</tr>';
		}
		echo '</table>';
		echo $this->Element('paginator');
	} else {
		echo '<p class="empty-message user-profile-events">' . __('There are no events.') . '</p>';
	}
}
?>
		</div>
	</div>
	<div class="clear"></div>
</div>