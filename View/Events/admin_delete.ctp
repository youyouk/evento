<div id="admin-content">
	<div class="admin-content-wrap">
		<div class="header">
<?php
	echo $this->Html->link(__('Back to the events index')
		, array('controller'=>'events', 'action'=>'index'), array('class'=>'button'))
?>
		</div>
		<div class="notice">
			<p>
<?php
	echo __('This event is a repeat event, do you want to delete all repeat events too?');
 ?>
			</p>
<?php
	echo $this->Html->link(__('Yes, delete this event and the repeat events')
		, array('controller'=>'events', 'action'=>'delete', $eventId, 2, 'page'=>$page)
		, array('class'=>'button'));
	echo $this->Html->link(__('No, delete this event only')
		, array('controller'=>'events', 'action'=>'delete', $eventId, 1, 'page'=>$page)
		, array('class'=>'back-button'));
?>
		</div>
	</div>
</div>