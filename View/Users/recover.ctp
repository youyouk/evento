<div class="container content">
<?php
if(isset($recover)) {
	echo __('An email should be sent to your email address. For user privacy, we cannot confirm if it exists.');
} else {
?>
	<div class="recover-form">
		<h1><?php echo __('Password recovery'); ?></h1>
		<div id="form-block">
<?php
	echo $this->Form->create('User',array('novalidate'=>true, 'action'=>'recover'));
	echo $this->Form->input('email', array('autofocus'=>true));
	echo $this->Form->end(__('Submit'));
?>
			<div class="clear"></div>
		</div>
	</div>
<?php } ?>
</div>