<p><?php echo __('Hello').' '.$user.","; ?></p>
<p><?php echo __("Follow this link to confirm your email address:"); ?> </p>
<p><?php echo $this->Html->link(Router::url(array('controller'=>'users', 'action'=>'activation', $code),true),
	Router::url(array('controller'=>'users', 'action'=>'activation', $code),true)); ?></p>