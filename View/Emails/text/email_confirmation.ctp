<?php 
echo __('Hello').' '.$user.",\n\r"; 
echo __("Follow this link to confirm your email address:\n\r");
echo Router::url(array('controller'=>'users', 'action'=>'activation', $code),true); 
?>