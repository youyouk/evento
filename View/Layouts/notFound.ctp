<!DOCTYPE html>
<html>
<head>
<?php
	echo $this->Html->charset();
	echo $this->Html->meta('icon');
	echo $this->Html->meta(array('name'=>'viewport', 'content'=>'width=device-width, initial-scale=1.0'));
	echo '<title>';
	echo ucwords($title_for_layout) . ' - ' . Configure::read('evento_settings.appName');
	echo '</title>';

	$this->Html->css('style', array('block' => 'css'));

	// CakePHP view blocks
	echo $this->fetch('meta');
	echo $this->fetch('css');
	echo $this->fetch('script');
?>
</head>
<body class="notFound">
	<div>
	<?php
		// main content is inserted here
		echo $this->fetch('content');
	?>
	</div>
</body>
</html>