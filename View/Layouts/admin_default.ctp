<!DOCTYPE html>
<html>
<head>
<?php
	echo $this->Html->charset();
	echo $this->Html->meta('icon');
	echo $this->Html->meta(array('name'=>'viewport', 'content'=>'width=device-width, initial-scale=1.0'));
	echo $this->Html->tag('title', Configure::read('evento_settings.appName') . ' | ' . ucwords($title_for_layout));

	$this->Html->css('admin', array('block' => 'css'));
	$this->Html->css('font-awesome.min', array('block' => 'css'));

	// CakePHP view blocks
	echo $this->fetch('meta');
	echo $this->fetch('css');
	echo $this->fetch('script');
?>
</head>
<body>
	<?php if (Configure::read('debug') > 0) {
		echo '<div id="debug-mode-enabled">DEBUG MODE ENABLED</div>';
	} ?>
	<header>
		<span><?php echo Configure::read('evento_settings.appName'); ?></span>
<?php
	echo $this->Html->link('<i class="fa fa-power-off"></i> ' . __('Exit'),
		array('admin'=>0, 'controller'=>'events', 'action'=>'index'),
		array('id'=>'menu-exit', 'escape'=>false));
?>
	</header>
	<div id="container">
<?php
	// admin menu is inserted here
	echo $this->Element('admin_menu');

	// content is inserted here
	echo $this->fetch('content');
?>
		<div class="clear"></div>
	</div>
<?php 
	$this->Html->script('https://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js'
		, array('block' => 'scriptBottom'));
	$this->Html->script('evento', array('block' => 'scriptBottom'));
	$this->Html->script('admin', array('block' => 'scriptBottom'));

	echo $this->fetch('scriptBottom');

	// CakePHP SQL dump
	echo $this->element('sql_dump');
?>
</body>
</html>