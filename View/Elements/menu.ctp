<div id="menu">
	<div id="menu-container">
<?php 
if ($showAddEventsButton) {  // has permissions to add events
	echo $this->Html->link(__('Add an event'), array(
		'plugin'=>null,
		'controller'=>'events',
		'action'=>'add'
	), array('rel'=>'nofollow', 'id'=>"add-event-box")); 
}

if ($this->Session->read('Auth.User')) { // registered user
	echo $this->Html->link(__('My Profile'), array(
		'admin'=>false,
		'plugin'=>null,
		'controller'=>'users',
		'action'=>'view',
		$this->Session->read('Auth.User.slug')
	));

	if (isset($showAdminButton) && $showAdminButton == true) { // has administrator permissions
		echo $this->Html->link(__('Admin'), array(
			'plugin'=>null,
			'controller'=>'events', 
			'action'=>'index',
			'admin'=>true
		));
	}

	echo $this->Html->link(__('Logout'), array(
		'admin'=>false,
		'plugin'=>null,
		'controller'=>'users',
		'action'=>'logout'
	));
} else { // anonymous user
	echo $this->Html->link(__('Login'), array(
		'admin'=>false,
		'plugin'=>null,
		'controller'=>'users',
		'action'=>'login'
	));

	if (Configure::read('evento_settings.adminAddsUsers')==false) { // users registration is allowed
		echo $this->Html->link(__('Register'), array(
			'admin'=>false,
			'plugin'=>null,
			'controller'=>'users',
			'action'=>'register'
		));
	}
}
echo '</div>';
?>
</div>