<!DOCTYPE html>
<html>
<head>
<?php
	echo $this->Html->charset();
	echo $this->Html->meta('icon');
	echo $this->Html->meta(array('name'=>'viewport', 'content'=>'width=device-width, initial-scale=1.0'));
	echo $this->Html->tag('title', ucwords($title_for_layout) . ' - ' . Configure::read('evento_settings.appName'));
	$this->Html->css('style', array('block' => 'css'));

	// see default/elements/
	echo $this->Element('xml_feed_link');

	// Description meta tags. See View/Elements/meta_description.ctp
	echo $this->Element('meta_description');

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
<div id="header">
	<div class="container">
		<div class="logo">
<?php
	echo $this->Html->link(Configure::read('evento_settings.appName'), Router::url('/', true)
		, array('rel'=>'home', 'escape'=>false));
?>
		</div>
<?php
	// Navigation menu is inserted here
	// see default/elements/menu.ctp if you need to edit the menu options
	echo $this->Element('menu');
?>
		<div class="clear"></div>
	</div>
</div>

<?php
$htmlTop = Configure::read('evento_settings.htmlTop');
if (!empty($htmlTop)) {
?>
	<div class="container html-top">
	<?php
		// htmlTop is the custom header added by admin
		echo $htmlTop;
	?>
	</div>
<?php } ?>

<div id="main">
<?php
	// main content is inserted here
	echo $this->fetch('content');

?>
</div>
<?php
$htmlBottom = Configure::read('evento_settings.htmlBottom');
if (!empty($htmlBottom)) {
?>
	<div class="container html-bottom">
		<?php
			// htmlBottom is the custom footer added by admin
			echo $htmlBottom;
		?>
	</div>
<?php } ?>
<div id="footer">

	<div class="container">
<?php
	echo $this->Html->link(sprintf(__('About %s'), Configure::read('evento_settings.appName'))
		, array('plugin' => null, 'controller' => 'pages', 'action' => 'display', 'about'));
	echo $this->Html->link(__('Terms and conditions')
		, array('plugin'=>null, 'controller'=>'pages', 'action'=>'display', 'terms'));
	echo $this->Html->link(__('Contact us'), array('plugin'=>null, 'controller'=>'contact', 'action'=>'index'));

	if(isset($feed)) {
		echo $this->Html->link(__('Xml feed'), '/feeds/'.$feed.'.xml').' ';
	}

	/*
	// You can allow users to change they language settings by creating a link to the /lang/ page along with the
	// language code.
	// ex. http://example.com/lang/spa --> set language to Spanish
	// The language settings are stored in a cookie so when the user comes back it gets the page in his desired
	// language.
	// you can use the following code to build your language links, this links are created using the CakePHP HTML
	// helper and also add the nofollow attribute.

	// if current language is English show the link to change it to Italian
	if(Configure::read('evento_settings.language') == 'eng') {
		echo $this->Html->link('Italiano', array('controller'=>'settings', 'action'=>'lang', 'ita'),
			array('rel'=>'nofollow'));
	}
	else {
		echo $this->Html->link('English', array('controller'=>'settings', 'action'=>'lang', 'eng'),
			array('rel'=>'nofollow'));
	}
	*/
?>
	</div>
</div>
<?php
	$this->Html->script('https://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js'
		, array('block' => 'scriptBottom'));
	$this->Html->script('evento', array('block' => 'scriptBottom'));

	echo $this->fetch('scriptBottom');

	// CakePHP SQL dump
	echo $this->element('sql_dump');
?>
</body>
</html>