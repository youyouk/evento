<div id="admin-content">
	<div class="admin-content-wrap">
		<div class="notice">
			<p>
<?php
	echo sprintf(__n('There is one event in this venue, deleting it will also delete the event.'
		, 'There are %d events in this venue, deleting it will also delete the events.', $events), $events);
	echo ' <br/>';
	echo __("If you don't want to delete any event you may need to edit this venue or merge it with an existing one.");
?>
			</p>
			<p><?php echo __('Do you want to continue?'); ?></p>
			<br/>
<?php
	echo $this->Html->link(__('Yes, delete the venue and all its events')
		, array('controller'=>'venues', 'action'=>'delete', $id, 'confirmation'
		, 'page'=> isset($this->request->params['named']['page'])?
			$this->request->params['named']['page'] : null)
		, array('class'=>'button'));
	echo $this->Html->link(__("No, I don't want to delete this venue")
		, array('controller'=>'venues', 'action'=>'index'
		, 'page'=> isset($this->request->params['named']['page'])?
			$this->request->params['named']['page'] : null)
		, array('class'=>'back-button'));
?>
		</div>
	</div>
</div>