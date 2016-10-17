<p>
<?php echo sprintf(__('There are new events awaiting moderation in %s'), Configure::read('evento_settings.appName')); ?>
</p>

<p><?php echo __('You can moderate them through this link:'); ?></p>

<p><?php echo $this->Html->link(Router::url(array('admin'=>'true', 'controller'=>'events', 'action'=>'index'), true),
	Router::url(array('admin'=>'true', 'controller'=>'events', 'action'=>'index'), true)); ?></p>