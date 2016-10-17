<div id="admin-content">
	<div class="admin-content-wrap form-wrap">
		<h1 class="form-box-title"><?php echo __('Edit venue'); ?></h1>
		<div id="form-block" class="form-box"><?php echo $this->Element('venue_form'); ?></div>
		<h1 class="form-box-title"><?php echo __('Merge venue'); ?></h1>
		<div class="form-box">
<?php
	echo '<p class="admin-button">';
	echo $this->Html->link('<i class="fa fa-magnet"></i> ' . __('Merge venue')
		, array('action'=>'merge', $this->request->data['Venue']['id']), array('escape'=>false));
	echo '</p>';
?>
		</div>
		<div class="clear"></div>
	</div>
</div>