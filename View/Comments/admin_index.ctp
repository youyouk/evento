<div id="admin-content">
	<div class="admin-content-wrap">
		<div class="header">
<?php
	if(!isset($event)) $event = null;
	if($event !== null) {
		echo $this->Html->link(__('Back to event')
			, array('controller'=>'events', 'action'=>'edit', $event)
			, array('class'=>'button'), false, false);
	}
	echo $this->Form->create('Search', array('url'=>array('controller'=>'comments', 'action'=>'search', $event)));
	echo $this->Form->input('Search.comment', array('class'=>'user-search', 'type'=>'text', 'label'=>false
		, 'div'=>false, 'placeholder'=>__('Search term')));
	echo $this->Form->submit(__('Search'), array('class'=>'user-search-button', 'div'=>false));
	echo $this->Form->end();
?>
		</div>
<?php
if(!empty($comments)) {
	echo $this->Form->create('Comment', array('url'=>array('action'=>'bulk')));	
	
	// comments table
	echo '<table id="admin">';
	echo '<thead>';
	echo '<tr>';
   	echo '<th class="checkbox-column"><input type="checkbox" id="select-all"></th>';
	echo '<th class="name">' . __('Comments') . '</th>';
	echo '<th class="icon"><i class="fa fa-pencil"></i></th>';
	echo '<th class="icon"><i class="fa fa-trash-o"></i></th>';
	echo '</tr>';
	echo '</thead>';
	echo '<tbody>';
	foreach($comments as $comment) {
		echo '<tr>';
		echo '<td class="checkbox-column">';
		echo $this->Form->input('Comment.id.' . $comment['Comment']['id']
			, array('type'=>'checkbox', 'div'=>false, 'label'=>false, 'class'=>'checkbox'));
		echo '</td>';
		echo '<td class="name">';
		echo '<div class="comment-info">';
		echo $this->Html->image('users/small/'.$comment['User']['photo']).' ';  
		echo sprintf(__('Posted by %1$s in %2$s'), $this->Html->link(ucfirst($comment['User']['username'])
			, array('admin'=>false, 'controller'=>'users', 'action'=>'view', $comment['User']['slug']))
			, $this->Html->link($comment['Event']['name'], array('admin'=>false, 'controller'=>'events'
			, 'action'=>'view', $comment['Country']['slug'], $comment['City']['slug']
			, $comment['Venue']['slug'], $comment['Event']['slug'])));
		echo '<br/>';
		echo '</div>';
		echo '<div class="comment">';
		echo nl2br(h($comment['Comment']['comment']));
		echo '</div>';
		echo '</td>';
		echo '<td class="icon">' . $this->Html->link('<i class="fa fa-pencil"></i>'
			, array('controller'=>'comments', 'action'=>'edit', 'admin'=>1, $comment['Comment']['id']
			, 'page'=> isset($this->request->params['named']['page'])? 
				$this->request->params['named']['page']:null)
			, array('escape'=>false));
		echo '</td>';
		echo '<td class="icon">' . $this->Html->link('<i class="fa fa-trash-o"></i>'
			, array('controller'=>'comments', 'action'=>'delete', 'admin'=>1, $comment['Comment']['id']
			, 'page'=> isset($this->request->params['named']['page'])?
				$this->request->params['named']['page']:null)
			, array('escape'=>false));
		echo '</td>';
		echo '</tr>';
		echo '</div>';
	}
	echo '</tbody>';
	echo '</table>';

	// bulk options
	echo '<div id="bulk-options">';
	echo '<div id="bulk-options-container">';	
	echo '<span>' . __('With selected:') . '</span>';
	echo '<input type="radio" class="radio-button" id="delete-radio" name="data[Comment][option]" value="delete" checked>';
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
	echo '<div class="content-box"><p class="center empty">' . __('There are no comments') . '</p></div>';
}
?>
	</div>
</div>