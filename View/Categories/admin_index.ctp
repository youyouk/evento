<div id="admin-content">
	<div class="admin-content-wrap">
		<div class="header">
<?php
	// category search form
	echo '<span>' . $this->Html->link(__('Add category'), array('action'=>'add')
		, array('class'=>'button')) . '</span>'; 
	echo $this->Form->create('Search', array('url'=>array('controller'=>'categories', 'action'=>'search')));
	echo $this->Form->input('Search.category', array('class'=>'user-search'
		, 'type'=>'text', 'label'=>false, 'div'=>false, 'placeholder'=>__('Category')));
	echo $this->Form->submit(__('Search'), array('class'=>'user-search-button'
		, 'div'=>false, 'autocomplete'=>'off'));
	echo $this->Form->end();
?>
		</div>
<?php
if(!empty($categories)) {
	echo $this->Form->create('Category', array('url'=>array('action'=>'bulk')));
	// categories table
	echo '<table id="admin">';
	echo '<thead>';
	echo '<tr>';
	echo '<th class="checkbox-column"><input type="checkbox" id="select-all"></th>';
	echo '<th class="name">' . __('Category') . '</th>';
	echo '<th class="icon"><i class="fa fa-trash-o"></i></th>';
	echo '<th class="icon"><i class="fa fa-pencil"></i></th>';
	echo '</tr>';
	echo '</thead>';
	echo '<tbody>';
	foreach($categories as $category):
		echo '<tr>';
		echo '<td class="checkbox-column">';
		echo $this->Form->input('Category.id.' . $category['Category']['id']
			, array('type'=>'checkbox', 'div'=>false, 'label'=>false, 'class'=>'checkbox'));
		echo '</td>'; 
		echo '<td class="name">' . h($category['Category']['name']) . '</td>';
		echo '<td class="icon">';
		echo $this->Html->link('<i class="fa fa-trash-o"></i>'
			, array('controller'=>'categories', 'action'=>'delete', $category['Category']['id'])
			, array('escape'=>false));
		echo '</td>';
		echo '<td class="icon">';
		echo $this->Html->link('<i class="fa fa-pencil"></i>'
			, array('controller'=>'categories', 'action'=>'edit', $category['Category']['id'])
			, array('escape'=>false));
		echo '</td>';
		echo '</tr>';
	endforeach;
	echo '</tbody>';
	echo '</table>';

	// bulk options
	echo '<div id="bulk-options">';
	echo '<div id="bulk-options-container">';
	echo '<span>' . __('With selected:') . '</span>';
	echo '<input type="radio" class="radio-button" id="delete-radio" name="data[Category][option]" value="delete" checked="checked">';
	echo '<label for="delete-radio">' . __('delete') . '</label>';
	echo '</div>';
	echo $this->Form->submit(__('Submit')
		, array('div'=>false, 'class'=>'user-search-button', 'id'=>'bulk-submit'));
	echo '<div class="clear"></div>';
	echo '</div>';
	echo $this->Form->end();
}
else {
	echo '<div class="content-box"><p class="center empty">' . __('There are no categories') . '.</p></div>';
}
?>
	</div>
</div>