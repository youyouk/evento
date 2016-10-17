<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<title><?php echo __('Installation'); ?></title>
	<style type="text/css" media="screen">
	#url-rewriting-warning {
		position: absolute; 
		top:0;
		bottom:0;
		left:0;
		right:0;
		z-index:5;
		background-color:#f1f1f1;
		font-family: 'helvetica neue', sans-serif;
		font-size: 18px
	}
	#error-msg-block {
		width: 600px;
		margin: 100px auto;
		background-color: #a30006;
		color:#fff;
		padding: 15px 50px;
		-moz-border-radius: 5px;
		-webkit-border-radius: 5px;
		border-radius: 5px;
	}
	</style>
	<?php echo $this->Html->css('/install/css/install.css'); ?>
</head>
<body>
	<div id="header">
    Evento Web Installer
	</div>
	<div id="main">
		<div id="url-rewriting-warning">
			<div id="error-msg-block">
			<h1>mod_rewrite message</h1>
			<p>URL rewriting is not enabled or not properly configured on your server.</p>
			<p>See the installation instructions and the <i>mod_rewrite</i> and <i>htaccess</i> sections in the documentation to learn more about this issue and how to avoid it.</p>
			</div>
		</div>
		<?php echo $content_for_layout; ?>
	</div>
</body>
</html>