<ul class="auto-complete">
<?php 
foreach($cities as $city) {
	echo '<li>' . h(ucfirst($city['City']['name'])) . '</li>';
} 
?>
</ul>