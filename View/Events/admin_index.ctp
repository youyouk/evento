<div id="admin-content">
	<div class="admin-content-wrap">
		<div class="header">
<?php
	echo $this->Html->link('<i class="fa fa-plus"></i> ' . __('Add an event'), array('action'=>'add')
		, array('class'=>'button', 'escape'=>false));

	echo $this->Form->create('Search', array('url'=>array('controller'=>'events', 'action'=>'search')));
	echo $this->Form->input('Search.term', array('class'=>'user-search', 'type'=>'text'
		, 'label'=>false, 'div'=>false, 'autocomplete'=>'off'
		, 'placeholder'=>ucfirst(strtolower(__('Event Name')))));
	echo $this->Form->submit(__('Search'), array('class'=>'user-search-button', 'div'=>false));
	echo $this->Form->end();
?>
		</div>
<?php
	if(!empty($events)) {
		echo $this->Form->create('Event', array('url'=>array('action'=>'bulk')));
		echo '<table id="admin">';
		echo '<thead>';
		echo '<tr>';
		echo '<th class="checkbox-column"><input type="checkbox" id="select-all"></th>';
		echo '<th class="name">' . __('Event') . '</th>';
		echo '<th class="published"></th>';
		echo '<th class="icon"><i class="fa fa-trash-o"></i></th>';
		echo '<th class="icon"><i class="fa fa-pencil"></i></th>';
		echo '</tr>';
		echo '</thead>';
		echo '<tbody>';
		foreach($events as $event) {
			echo '<tr>';
			echo '<td class="checkbox-column">';
			echo $this->Form->input('Event.id.'.$event['Event']['id']
				, array('type'=>'checkbox', 'div'=>false, 'label'=>false, 'class'=>'checkbox'));
			echo '</td>';
			echo '<td class="name">';
			echo $this->Html->link(ucfirst($event['Event']['name'])
				, array('admin'=>0, 'controller'=>'events', 'action'=>'view'
				, $event['Country']['slug'], $event['City']['slug']
				, $event['Venue']['slug'], $event['Event']['slug']));
            echo $this->Element('event_full_date', array('event'=>$event));
			echo '</td>';
			echo '<td class="published">';

			if(!$event['Event']['published']) {
				echo '<span class="table-notification">' . __('not published') . '</span>';
			}

			echo '</td>';
			echo '<td class="icon"> ';
			echo $this->Html->link('<i class="fa fa-trash-o"></i>'
				, array('controller'=>'events', 'action'=>'delete', $event['Event']['id']
				, 'page'=>isset($this->request->params['named']['page'])?
					$this->request->params['named']['page'] : null)
				, array('escape'=>false));
			echo '</td>';
			echo '<td class="icon">';
			echo $this->Html->link('<i class="fa fa-pencil"></i>'
				, array('controller'=>'events', 'action'=>'edit', $event['Event']['id']
				, 'page'=>isset($this->request->params['named']['page'])?
					$this->request->params['named']['page'] : null)
				, array('escape'=>false));
			echo '</td>';
			echo '</tr>';
		}

		echo '</tbody>';
		echo '</table>';

		echo '<div id="bulk-options">';
		echo '<div id="bulk-options-container">';
		echo '<span>' . __('With selected:') . '</span>';
		echo '<input type="radio" class="radio-button" id="publish-radio" name="data[Event][option]" value="publish" checked="checked" />';
		echo '<label for="publish-radio">' . __('publish') . '</label>';
		echo '<input type="radio" class="radio-button" id="unpublish-radio" name="data[Event][option]" value="unpublish" />';
		echo '<label for="unpublish-radio">' . __('unpublish') . '</label>';
		echo '<input type="radio" class="radio-button" id="delete-radio" name="data[Event][option]" value="delete" />';
		echo '<label for="delete-radio">' . __('delete') . '</label>';
		echo '</div>';
		echo $this->Form->submit(__('Submit'), array('div'=>false, 'class'=>'user-search-button'
			, 'id'=>'bulk-submit'));
		echo '<div class="clear"></div>';
		echo '</div>';
		echo $this->Form->end();
		echo $this->Element('paginator');
	} else {
		echo '<div class="content-box"><p class="center empty">' . __('There are no events.') . '</p></div>';
	}
?>
	</div>
</div>