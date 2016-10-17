<?php
if (isset($facebookLoginUrl)) {
	echo '<div class="facebook-login-container">';
	echo $this->Html->link(__('Facebook Login'), $facebookLoginUrl, array('class'=>'facebook-login'));
	echo '</div>';
}
?>