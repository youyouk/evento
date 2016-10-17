<div class="container content">
	<div class="generic-form">
		<h1><?php echo __('You are going to delete your account'); ?></h1>
		<p>
			<?php echo __('You are going to delete your user account, all your data will be deleted.'); ?>
		</p>
		<p><?php echo __('Do you want to continue?'); ?></p>
		<br/>
<?php
	echo $this->Html->link(__('Yes, Delete My Account')
		, array('controller'=>'users', 'action'=>'delete_user', 'confirmation')
		, array('class'=>'btn-danger')) . ' ';
	echo $this->Html->link(__('No, I Don\'t Want To delete My Account')
		, array('controller'=>'events', 'action'=>'index'), array('class'=>'button btn-success'));
?>
	</div>
</div>