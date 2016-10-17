<?php echo sprintf(__('There are new events awaiting moderation in %s'), Configure::read('evento_settings.appName')); ?>

<?php echo __('You can moderate them through this link:'); ?>

<?php echo Router::url(array('admin'=>'true', 'controller'=>'events', 'action'=>'index'), true); ?>