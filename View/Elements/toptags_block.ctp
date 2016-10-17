<?php if(!empty($toptags)) { ?>
<div id="tags_column">
	<h2><?php echo __('Tags'); ?></h2>
	<div class="tags">
		<div id="tags-1">
<?php
	$n=1;
	$limit = count($toptags) / 2;
	foreach($toptags as $toptag) {
		echo $this->Html->link($toptag['Tag']['name'], array('controller'=>'events', 'action'=>'index'
			, $country, $city, $venue, $category, $toptag['Tag']['slug'], $year, $month, $day)
			, array('rel'=>'tag'));
		echo '<br/>';
		if(isset($limit) && $n >= $limit) {
			echo '</div><div id="tags-2">';
			unset($limit);
		}
		$n++;
	}
?>
		</div>
	</div>
</div>
<?php  } ?>