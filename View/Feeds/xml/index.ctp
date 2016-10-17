<?php echo '<?xml version="1.0" encoding="UTF-8"?>'; ?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
<channel>
<atom:link href="<?php echo Router::url('/feeds/' . $feed . '.xml',true); ?>" rel="self" type="application/rss+xml" />
<title><![CDATA[<?php echo Configure::read('evento_settings.appName'); ?>]]></title>
<link><?php echo Router::url('/',true) ?></link>
<description><![CDATA[<?php echo Configure::read('evento_settings.appSlogan'); ?>]]></description>
<language>en-us</language>
<?php foreach ($events as $event) { ?>
	<item>
	<title><![CDATA[<?php echo h($event['Event']['name']); ?>]]></title>
	<link><?php echo
		Router::url(array('admin'=>false, 'controller'=>'events', 'action'=>'view', $event['Country']['slug'],
			$event['City']['slug'], $event['Venue']['slug'], $event['Event']['slug'] ),true);
	?></link><guid><?php
	echo Router::url(array('admin'=>false, 'controller'=>'events', 'action'=>'view',
	 $event['Country']['slug'], $event['City']['slug'], $event['Venue']['slug'], $event['Event']['slug'] ),true);
	?></guid>
	<description><![CDATA[<?php 
        echo $this->Element('event_full_date', array('event'=>$event));
        echo nl2br(h($event['Event']['notes'])); ?>]]></description>
	<pubDate><?php echo date(DateTime::RFC2822  , strtotime($event['Event']['created'])); ?></pubDate>
	</item>
<?php } ?>
</channel></rss>