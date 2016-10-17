<p><?php echo __('hi' ).' '.$user['User']['username']; ?>,</p>

<p><?php echo __('If you need to change your user password login in this url:'); ?></p>

<p><?php echo $this->Html->link(Router::url(array('controller'=>'users', 'action'=>'code_login', $code),true),
	Router::url(array('controller'=>'users', 'action'=>'code_login', $code),true)); ?></p>

<p><?php echo __("If you don't need to change your password just ignore this email."); ?></p>
