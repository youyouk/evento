<div class="container content">
	<div class="login-form">
		<h1><?php echo __('Login'); ?></h1>
		<div id="form-block">
<?php
	echo $this->element('facebook_login');
	echo $this->Session->flash('auth');
	echo $this->Form->create('User', array('novalidate'=>true, 'url'=>array('controller'=>'users', 'action' => 'login')
		, 'id'=>'UserLoginForm'));
	echo $this->Form->input('email', array('div'=>false, 'autofocus'=>true, 'autocomplete'=>'off'));
	echo $this->Form->input('password', array('div'=>false));
	echo $this->Html->link(__('Forgot your password?'), array('controller'=>'users', 'action'=>'recover'));
	echo $this->Form->end(array('label'=>__('Login')));
?>
			<div class="clear"></div>
		</div>
	</div>
</div>