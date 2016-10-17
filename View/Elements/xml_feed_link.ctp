<?php if(isset($feed)) { ?>
<link rel="alternate" type="application/atom+xml" title="Atom feed" href="<?php
echo Router::url(array('plugin'=>null, 'controller'=>'feeds', 'action'=>'index', $feed.'.xml'));
?>" />
<?php } ?>