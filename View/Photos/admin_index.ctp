<div id="admin-content">
	<div class="admin-content-wrap">
<?php
	if(!isset($event)) $event = null;
	if($event!==null) {
		echo '<div class="header">';
		echo $this->Html->link(__('Back to event')
			, array('controller'=>'events', 'action'=>'edit', $event), array('class'=>'button'), false, false);
		echo '</div>';
	}

	if(empty($photos)) {
		echo '<div class="content-box"><p class="center empty">' . __('There are no photos') . '.</p></div>';
	} else {
		echo '<table id="admin">';
		echo '<thead>';
		echo '<tr>';
		echo '<th class="table-photo name">'.__('Photo').'</th>';
		echo '<th class="photo-info"></th>';
		echo "<th class=\"icon\">".'<i class="fa fa-trash-o"></i>'."</th>";
		echo '</tr>';
		echo '</thead>';
		echo '<tbody>';
		foreach($photos as $photo) {
			echo '<tr>';
			echo '<td class="table-photo">';
			echo $this->Html->image('events/small/' . $photo['Photo']['file']) . ' ';
			echo '</td><td class="photo-info">';
			echo __('Uploaded to %s', $this->Html->link($photo['Event']['name']
				, array('admin'=>false, 'controller'=>'events', 'action'=>'view'
				, $photo['Event']['Venue']['City']['Country']['slug'], $photo['Event']['Venue']['City']['slug']
				, $photo['Event']['Venue']['slug'], $photo['Event']['slug']))) . '.';
			echo '</td>';
			echo '<td class="icon">';
			echo $this->Html->link('<i class="fa fa-trash-o"></i>'
				, array('controller'=>'photos', 'action'=>'delete', $photo['Photo']['id']
				, 'page'=> isset($this->request->params['named']['page'])?
					$this->request->params['named']['page'] : null)
				, array('escape'=>false));
			echo '</td>';
			echo '</tr>';
		}
		echo '</tbody>';
		echo '</table>';
		echo $this->Element('paginator');
	}
?>
	</div>
</div>