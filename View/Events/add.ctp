<div class="container content">
<?php
	if(isset($moderation)) {
		echo '<p>' . __('Events must be moderated and are not published immediately.') . '</p>';
	} else {
?>
	<div class="generic-form">
		<h1><?php echo __('Add an event');?></h1>
		<div id="form-block" class="events">
<?php echo $this->Element('event_form'); ?>
		</div>
<?php } ?>
	</div>
</div>