<div id="admin-content">
	<div class="admin-content-wrap">
		<div class="header">
<?php
	echo $this->Html->link(__('Back to event')
		, array('controller'=>'events', 'action'=>'edit', $event['Event']['id']), array('class'=>'button'));
	echo $this->Html->link(__('Export as CSV'), array('controller'=>'events', 'action'=>'export_attendees'
		, $event['Event']['id']), array('class'=>'button'));
?>
			<div class="clear"></div>
		</div>
<?php
	if(!empty($event['Attendees'])) {
		echo '<table id="admin">';
		echo '<thead>';
		echo '<tr>';
		echo '<th class="name">';
		echo $event['Event']['name'] . ' ' . strtolower(__('Attendees'));
		echo '</th>';
		echo '</tr>';
		echo '</thead>';
		echo '<tbody>';

		foreach($event['Attendees'] as $user) {
			echo '<tr>';
			echo '<td class="name">';
			echo $this->Html->image('users/small/' . $user['photo']
				, array('alt'=>h($user['username']))) . ' ';
			echo $user['username'];
			echo '</td>';
			echo '</tr>';
		}
		echo '</tbody>';
		echo '</table>';
} else {
	echo '<div class="content-box"><p class="center empty">' . __('There are no users') . '</p></div>';
}
?>
	</div>
</div>