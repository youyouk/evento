<div class="container content">
<?php
if(isset($code)) {
	echo '<p>'.__('We sent you an email with instructions to confirm your email address.').'</p>';
} else {
?>
	<div class="registration-form">
		<h1><?php echo __('Registration form'); ?></h1>
		<div id="form-block">
			<?php echo $this->element('facebook_login'); ?>
			<?php echo $this->element('register_form'); ?>
			<div class="clear"></div>
		</div>
	</div>
<?php } ?>
</div>