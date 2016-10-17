<ul class="auto-complete">
<?php
foreach($tags as $tag){
	echo '<li>' . h($tag['Tag']['name']) . '</li>';
} 
?>
</ul> 