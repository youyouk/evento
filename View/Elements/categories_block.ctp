<?php
if(!empty($categories)) {
	if(!isset($country)) $country = __d('url', 'all-countries');
	if(!isset($city)) $city = __d('url', 'all-cities');
	if(!isset($venue)) $venue = __d('url', 'all-venues');
	if(!isset($current_tag)) $current_tag = null;
	if(!isset($year)) $year = null;
	if(!isset($month)) $month = null;
	if(!isset($day)) $day = null;
?>
<div id="categories_column">
	<h2><?php echo __('Categories'); ?></h2>
<?php
	$n = 1;
	$category_break = count($categories) / 2;
	echo '<ul id="categories-left">';
	foreach($categories as $cat) {
		if(isset($category_break) && $n > $category_break) { 
			echo '</ul><ul id="categories-right">';
			unset($category_break);
		}
		echo '<li>';
		echo $this->Html->link($cat['Category']['name'], array('action'=>'index', $country, $city, $venue
			, $cat['Category']['slug'], $current_tag, $year, $month, $day));
		echo '</li>';
		$n++;
	}
	echo '</ul>';
	unset($n);
?>
</div>
<?php } ?>