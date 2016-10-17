<div id="admin-content">
	<div class="admin-content-wrap">
		<div class="header">
<?php 
	echo $this->Html->link(__('Add user'), array('action'=>'register'), array('class'=>'button'));
	echo $this->Html->link(__('Export as CSV'), array('action'=>'export'), array('class'=>'button'));

	echo $this->Form->create('Search', array('url'=>array('controller'=>'users', 'action'=>'search')));
	echo $this->Form->input('Search.user', array('class'=>'user-search', 'type'=>'text', 'label'=>false
		, 'div'=>false, 'autocomplete'=>'off', 'placeholder'=>__('Username')));
	echo $this->Form->submit(__('Search'), array('class'=>'user-search-button', 'div'=>false));
	echo $this->Form->end();
?>
			<div class="clear"></div>
		</div>
<?php
	if(!empty($users)) {
		echo $this->Form->create('User', array('url'=>array('action'=>'bulk')));
		echo '<table id="admin">';
		echo '<thead>';
		echo '<tr>';
		echo '<th class="checkbox-column"><input type="checkbox" id="select-all"></th>';
		echo '<th class="name">' . __('Username') . '</th>';
		echo '<th class="active"></th>';
		echo '<th class="icon"><i class="fa fa-trash-o"></i></th>';
		echo '<th class="icon"><i class="fa fa-pencil"></i></th>';
		echo '</tr>';
		echo '</thead>';
		echo '<tbody>';

		foreach($users as $user) {
			echo '<tr>';
			echo '<td class="checkbox-column">';
			echo $this->Form->input('User.id.'.$user['User']['id']
				, array('type'=>'checkbox', 'div'=>false, 'label'=>false, 'class'=>'checkbox'));
			echo '</td>';
			echo '<td class="name">';
			echo $this->Html->image('users/small/'.$user['User']['photo']
				, array('alt'=>h($user['User']['username']))) . ' ';

			if(!$user['User']['active']) {
				echo $this->Html->link($user['User']['username'], 
					array('admin'=>0, 'controller'=>'users', 'action'=>'view', $user['User']['slug']));
			} else {
				echo $user['User']['username'];
			}

			echo '</td>';
			echo '<td class="active">';

			if(!$user['User']['active']) {
				echo '<span class="table-notification">' . __('deactivated') . '</span>';
			}

			echo '</td>';
			echo '<td class="icon">';
			echo $this->Html->link('<i class="fa fa-trash-o"></i>'
				, array('controller'=>'users', 'action'=>'delete_user', 'admin'=>1
				, $user['User']['id'], 'page'=> isset($this->request->params['named']['page'])?
					 $this->request->params['named']['page'] : null)
				, array('escape'=>false));
			echo '</td>';
			echo '<td class="icon">';
			echo $this->Html->link('<i class="fa fa-pencil"></i>'
				, array('controller'=>'users', 'action'=>'edit', 'admin'=>1, $user['User']['id']
				, 'page'=> isset($this->request->params['named']['page'])?
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
		echo '<input type="radio" class="radio-button" id="activate-radio" name="data[User][option]" value="activate" checked>';
		echo '<label for="activate-radio">' . __('activate') . '</label>';
		echo '<input type="radio" class="radio-button" id="deactivate-radio" name="data[User][option]" value="deactivate">';
		echo '<label for="deactivate-radio">' . __('deactivate') . '</label>';
		echo '<input type="radio" class="radio-button" id="delete-radio" name="data[User][option]" value="delete">';
		echo '<label for="delete-radio">' . __('delete') . '</label>';
		echo '</div>';
		echo $this->Form->submit(__('Submit'), array('div'=>false, 'class'=>'user-search-button'
			, 'id'=>'bulk-submit'));
		echo '<div class="clear"></div>';
		echo '</div>';
		echo $this->Form->end();
		echo $this->Element('paginator'); 
} else {
	echo '<div class="content-box"><p class="center empty">' . __('There are no users') . '</p></div>';
}
?>
	</div>
</div>