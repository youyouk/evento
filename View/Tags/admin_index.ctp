<div id="admin-content">
	<div class="admin-content-wrap">
		<div class="header">
<?php
	echo $this->Form->create('Search', array('url'=>array('controller'=>'tags', 'action'=>'search')));
	echo $this->Form->input('Search.tag', array('class'=>'user-search', 'type'=>'text', 'label'=>false
		, 'div'=>false, 'placeholder'=>__('Tag')));
	echo $this->Form->submit(__('Search'), array('class'=>'user-search-button', 'div'=>false
		, 'autocomplete'=>'off'));
	echo $this->Form->end();
?>
		</div>
<?php
if(!empty($tags)) {
	echo $this->Form->create('Tag', array('url'=>array('action'=>'bulk')));	
	echo '<table id="admin">';
	echo '<thead>';
	echo '<tr>';
	echo '<th class="checkbox-column"><input type="checkbox" id="select-all" /></th>';
	echo '<th class="name">' . __('Tag') . '</th>';
	echo '<th class="icon"><i class="fa fa-trash-o"></i></th>';
	echo '<th class="icon"><i class="fa fa-pencil"></i></th>';
	echo '</tr>';
	echo '</thead>';
	echo '<tbody>';
	foreach($tags as $tag) {
		echo '<tr>';
		echo '<td class="checkbox-column">';
		echo $this->Form->input('Tag.id.' . $tag['Tag']['id']
			, array('type'=>'checkbox', 'div'=>false, 'label'=>false, 'class'=>'checkbox'));
		echo '</td>';
		echo '<td class="name">' . h($tag['Tag']['name']) . '</td>';
		echo '<td class="icon">';
		echo $this->Html->link('<i class="fa fa-trash-o"></i>'
			, array('controller'=>'tags', 'action'=>'delete', $tag['Tag']['id']
			, 'page'=> isset($this->request->params['named']['page'])?
				 $this->request->params['named']['page'] : null)
			, array('escape'=>false));
		echo '</td>';
		echo '<td class="icon">';
		echo $this->Html->link('<i class="fa fa-pencil"></i>'
			, array('controller'=>'tags', 'action'=>'edit', $tag['Tag']['id']
			, 'page'=> isset($this->request->params['named']['page'])?
				 $this->request->params['named']['page'] : null)
			, array('escape'=>false));
		echo '</td>';
		echo '</tr>';
	}
	echo '</tbody>';
	echo '</table>';

	// bulk options
	echo '<div id="bulk-options">';
	echo '<div id="bulk-options-container">';
	echo '<span>'.__('With selected:').'</span>';
	echo '<input type="radio" class="radio-button" id="delete-radio" name="data[Tag][option]" value="delete" checked="checked" />';
	echo '<label for="delete-radio">' . __('delete') . '</label>';
	echo '</div>';
	echo $this->Form->submit(__('Submit'), array('div'=>false, 'class'=>'user-search-button'
		, 'id'=>'bulk-submit'));
	echo '<div class="clear"></div>';
	echo '</div>';
	echo $this->Form->end();
	echo $this->Element('paginator');
}
else {
	echo '<div class="content-box"><p class="center empty">' . __('There are no tags') . '.</p></div>';
}
?>
	</div>
</div>