<div id="admin-content">
	<div class="admin-content-wrap form-wrap">
		<h1 class="form-box-title"><?php echo __('Edit event'); ?></h1>
		<div id="form-block" class="form-box"><?php echo $this->Element('event_form'); ?></div>
		<h1 class="form-box-title"><?php echo __('Related data') ?></h1>
		<div class="form-box">
<?php
	echo '<p class="admin-button">';
	echo $this->Html->link('<i class="fa fa-comments-o"></i> ' . __('View comments')
		, array('controller'=>'comments', 'action'=>'index'
		, $this->request->data['Event']['id']), array('escape'=>false));
	echo '</p>';
	echo '<p class="admin-button">';
	echo $this->Html->link('<i class="fa fa-picture-o"></i> ' . __('View photos')
		, array('controller'=>'photos', 'action'=>'index'
		, $this->request->data['Event']['id']), array('escape'=>false));
	echo '</p>';
	echo '<p class="admin-button">';
	echo $this->Html->link('<i class="fa fa-users"></i> ' . __('View attendees')
		, array('controller'=>'events', 'action'=>'attendees'
		, $this->request->data['Event']['id']), array('escape'=>false));
	echo '</p>';
?>
		</div>
		<h1 class="form-box-title"><?php echo __('Merge event') ?></h1>
		<div class="form-box">
<?php
	echo '<p class="admin-button">';
	echo $this->Html->link('<i class="fa fa-magnet"></i> ' . __('Merge event'), array('action'=>'merge'
		, $this->request->data['Event']['id']), array('escape'=>false));
	echo '</p>';
?>
		</div>
	</div>
</div>