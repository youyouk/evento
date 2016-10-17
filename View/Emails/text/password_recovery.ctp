<?php echo __('hi' ).' '.$user['User']['username']; ?>,

<?php echo __('If you need to change your user password login in this url:'); ?>

<?php echo Router::url(array('controller'=>'users', 'action'=>'code_login', $code),true); ?>


<?php echo __("If you don't need to change your password just ignore this email."); ?>

