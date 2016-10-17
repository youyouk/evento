<?php
$error = false;

// check if PCRE has Unicode support
App::uses('Validation', 'Utility');
if (!Validation::alphaNumeric('cakephp')) {
	echo '<p class="error"><b>PCRE has not been compiled with Unicode support.</b></p>';
	$error = true;
}

$error_str = '';

// check permissions for writable paths
foreach($is_writable as $path) {
	if(!is_writable($path)) {
		$error_str .= '<p class="error">';
		$error_str .= '<b>Path:</b><br><i>' . $path . '</i><br>';
		$error_str .=  '&#8692; Must be writable.';
		if($path == TMP){
			$error_str .= '<br><b>NOTE:</b> All subfolders in this path must be writable too.';
		}
		$error_str .= '</p>';
	}
}

// show the database settings form
if($error_str === '') {
	echo '<div id="install">';
	echo '<h1> Database Settings </h1>';

	if ($this->Session->check('Message.flash')) {
		echo '<div class="error">';
			echo $this->Session->flash();
		echo '</div>';
	}

	echo $this->Form->create('Install', array(
		'url' => array(
			'plugin' => 'install',
			'controller' => 'install',
			'action' => 'index')));
	echo $this->Form->input('Install.host', array('label' => 'Host name'));
	echo $this->Form->input('Install.database', array('label' => 'Database name'));
	echo $this->Form->input('Install.login', array('label' => 'Database user name', 'autofocus'=>true));
	echo $this->Form->input('Install.password', array('label' => 'Database user password'));
	echo $this->Form->end('Submit', array('class'=>'btn'));
	echo '</div>';
}
else {
	echo '<div id="error-msg"><h1>Write permissions</h1>' . $error_str
	. '<div id="error-info">You must address the folder permissions before using the web installer.<br> Please see the installation section in the documentation for more information.</div></div>';
}
?>