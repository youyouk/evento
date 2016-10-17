<?php
/**
 * meta description for the events listings page (aka homepage)
 * generate the description content based on the app slogan and page number
 */
if($this->request->params['controller']=='events' && $this->request->params['action']=='index') {
?>
<meta name="description" content="<?php 
echo h(__(Configure::read('evento_settings.appSlogan'))); 
if($this->Paginator->current() > 1) {
	echo ' - ' . __('page') . ' ' . $this->Paginator->current();
}
?>" />
<?php
}
/**
 * meta description and keywords for the event view page
 * generate the description content based on the event description and the keywords meta
 * based on the event name, city, country and tags.
 */
else if($this->request->params['controller']=='events' && $this->request->params['action']=='view') {
?>
<meta name="description" content="<?php echo str_replace('\n',' ',h($this->Text->truncate($event['Event']['notes'], 150,array('ending'=>'...', 'exact'=>false))));
?>" />
<meta name="keywords" content="<?php 
		$meta_str = $event['Event']['name'].', '.h($event['City']['name']).', '.__($event['Country']['name']);
		$meta_str .= ', ' . $event['Category']['name'];
		if(!empty($event['Tag'])) $meta_str.= ",".implode(',',Set::extract('/Tag/name',$event));
		echo h($meta_str);
		?>" />
<?php
$currentUrl =	Router::url(array('controller'=>'events', 'action'=>'view', $event['Country']['slug']
		, $event['City']['slug']
		, $event['Venue']['slug'], $event['Event']['slug']), true);
?>
<link rel="canonical" href="<?php echo $currentUrl ?>" />

<?php // FACEBOOK METATAGS // ?>
<meta property="og:title" content="<?php echo h($event['Event']['name']) ?>" />
<meta property="og:site_name" content="<?php echo Configure::read('evento_settings.appName') ?>" />
<?php
if($event['Event']['logo']!='') {
	echo '<meta property="og:image" content="'.Router::url('/', true) . IMAGES_URL
	. 'logos/big/' . $event['Event']['logo'].'" />';
}


?>
<meta property="og:url" content="<?php echo $currentUrl ?>" />

<?php // TWITTER METATAGS // ?>
<?php
$twitterUser = Configure::read('evento_settings.twitterAccount');
if (!empty($twitterUser)) {
    if (strpos($twitterUser, '@') !== 0) {
        $twitterUser = '@'.$twitterUser;
    }
 ?>
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:site" content="<?php echo $twitterUser ?>">
<meta name="twitter:title" content="<?php echo h($event['Event']['name']) ?>">
<meta name="twitter:description" content="<?php echo h($this->Text->truncate($event['Event']['notes'], 120)); ?>">
<?php if($event['Event']['logo']!='') { ?>
<meta name="twitter:image" content="<?php echo Router::url('/', true) . IMAGES_URL
	. 'logos/big/' . $event['Event']['logo']; ?>">
<?php 
}
        }
	}
?>