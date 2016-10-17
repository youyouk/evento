<?php
	$class = '';
	if(isset($new) && $new == true) $class = 'comment-new';

	echo '<div class="comment ' . $class . '">';
	echo '<div class="user-photo">';
	if(empty($comment['User']['photo']) || !$comment['User']['active']) {
		$photo = $this->Html->image('users/user_photo.jpg');
	} else {
		$photo = $this->Html->link($this->Html->image('users/' . $comment['User']['photo']
			, array('alt'=>h($comment['User']['username'])))
			, array('controller'=>'users', 'action'=>'view', $comment['User']['slug'])
			, array('escape'=>false));
	}

	echo $photo . ' ';
	echo '</div><div class="comment-body">';
	echo '<div class="comment-info">';
	echo sprintf(__('Posted by %s'), $this->Html->link($comment['User']['username']
		, array('controller'=>'users', 'action'=>'view', $comment['User']['slug'])));

	echo ' ' . $this->Time->timeAgoInWords($comment['Comment']['created']
	, array('format'=>Configure::read('evento_settings.dateFormat'))) . '.';

	echo '</div>';
	echo '<p>' . nl2br(h($comment['Comment']['comment'])) . '</p>';
	echo '</div><div class="clear"></div></div>';
?>