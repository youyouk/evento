<div class="container content">
<?php
	if(isset($email_confirmation)) {
		$str = sprintf("You must confirm your new email address before we can start using it. An email has been sent to %s.", $email_confirmation);
	echo '<p>' . __($str) . '</p>';
} else {
?>
	<div class="generic-form">
		<h1><?php echo __('Edit user profile'); ?></h1>
		<div id="form-block">
<?php
	echo $this->Form->create('User', array('novalidate'=>true, 'url'=>$this->Html->url(array('action'=>'edit'), true)
		, 'type'=>'file', 'id'=>'UserEditForm', 'novalidate'=>true));
	echo '<div id="user-photo-box">';
	echo $this->Html->image('users/'.$user['User']['photo'], array('alt'=>__('photo')));
	echo '<div id="photo-input">';
	echo $this->Form->input('filedata', array('type'=>'file', 'label'=>__('Profile photo')));
	echo '</div><div class="clear"></div></div>';
	if($user['User']['photo']!='user_photo.jpg') {
		echo '<div id="delete-photo-block">';
		echo $this->Form->input('delete_photo', array('type'=>'checkbox', 'div'=>false
			, 'label'=>__('delete photo'), 'class'=>'checkbox-input'));
		echo '</div>';
	}
	echo $this->Form->label('City.country_id', __('Country'));
	echo $this->Form->select('City.country_id', $countries);
	echo $this->Form->error('City.country_id');
	echo $this->Form->input('City.name', array('id'=>'CityName', 'label'=>__('City'), 'autocomplete'=>'off'));
?>
			<div id="CityName_autoComplete" class="auto_complete"></div>
<?php
	echo $this->Form->input('email', array('autocomplete'=>'off'));
	echo $this->Form->error('alter_email');
	echo $this->Form->input('web', array('label'=>__('Website'), 'autocomplete'=>'off'));
	echo $this->Form->input('password', array('label'=>__('New password'),'required'=>false));
	echo $this->Form->label('password_confirm',__('Password confirmation'));
	echo $this->Form->password('password_confirm');
?>
			<div class="submit">
<?php
	echo $this->Form->submit(__('Save'), array('div'=>false, 'id'=>'submit-button', 'class'=>'submit-button'));
	echo $this->Html->link(__('Cancel'), array('controller'=>'users', 'action'=>'view'
		, $user['User']['username']), array('class'=>'back-button'));
?>
				<div class="clear"></div>
			</div>
<?php echo $this->Form->end(); ?>
			<p class="delete-button">
<?php
	echo $this->Html->link(__('Delete this account')
		, array('controller'=>'users', 'action'=>'delete_user'));
?>
			</p>
		</div>
	</div>
<?php } ?>
</div>