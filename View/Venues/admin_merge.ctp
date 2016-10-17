<div id="admin-content">
	<div class="admin-content-wrap">
<?php
if(!empty($venues)) {
	echo '<table id="admin">';
	echo '<thead>';
	echo '<tr>';
	echo '<th class="name">'.__('Venue').'</th>';
	echo '<th class="icon">'.ucfirst(__('merge')).'</th>';
	echo '</tr>';
	echo '</thead>';
	echo '<tbody>';
	foreach($venues as $venue) {
		echo '<tr>';
		echo '<td class="name">';
		echo $venue['Venue']['name'];
		echo '<span class="venue-address">';
		echo '(' . $venue['Venue']['address'] . ', ' . $venue['City']['name']
			. ', ' . __($venue['City']['Country']['name']) . ')';
		echo '</span>';
		echo '</td>';
		echo '<td class="icon">';
		echo $this->Html->link(__('merge')
			, array('controller'=>'venues', 'action'=>'merge', $merge_id, $venue['Venue']['id']));
		echo '</td>';
		echo '</tr>';
	}
	echo '</tbody>';
	echo '</table>';
	echo $this->Element('paginator');
}
else {
	echo '<div class="content-box"><p class="center">'.__('There are no venues').'.</p></div>';
}
?>
	</div>
</div>