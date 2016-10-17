<div id="admin-content">
	<div class="admin-content-wrap">
		<div class="notice">
			<p>
<?php
	echo sprintf(__n('There is one event in this category, deleting it will also delete the event.', 'There are %d events in this category, deleting it will also delete the events.', $events), $events);
 ?>
			</p>
			<p><?php echo __('Do you want to continue?'); ?></p>
			<p>
<?php
	echo $this->Html->link(__('Yes, delete the category and all its events'), 
		array('controller'=>'categories', 'action'=>'delete', $id, 'confirmation'),
		array('class'=>'button'));
	echo $this->Html->link(__("No, I don't want to delete this category"), 
		array('controller'=>'categories', 'action'=>'index'), 
		array('class'=>'back-button'));
?>
			</p>
		</div>
	</div>
</div>