<?php
if(!empty($countries)) {
	$n = 0;
	$one_country = Configure::read('evento_settings.country_id');
	if(!empty($one_country)) {
		$limit = count($countries[$one_country]['City']) / 2;
		echo '<ul id="cities-left">';
		foreach($countries[$one_country]['City'] as $city_c) {
			if(isset($limit) && $n > $limit) {
				echo '</ul><ul id="cities-right">';
				unset($limit);
			}
			$n++;
			echo '<li>';
			echo $this->Html->link(ucfirst($city_c['name']),
				array('controller'=>'events', 'action'=>'index', 
					$countries[$one_country]['Country']['slug'], $city_c['slug']));
			echo '</li>';
		}
		echo '</ul>';
	}
	else {
		$limit = count($countries) / 2;
		echo '<ul id="cities-left">';
		foreach($countries as $country) {
			if(isset($limit) && $n>=$limit ) {
				echo '</ul><ul id="cities-right">';
				unset($limit);
			}
			$n++;
			echo '<li class="country">'.$this->Html->link(__d('countries', $country['Country']['name'])
				, array('controller'=>'events', 'action'=>'index', $country['Country']['slug'])).'</li>';
			foreach($country['City'] as $city_c) {
				echo '<li>';
				echo $this->Html->link(ucfirst($city_c['name'])
					, array('controller'=>'events', 'action'=>'index',  $country['Country']['slug']
					, $city_c['slug']));
				echo '</li>';
			}
		}
		echo '</ul>';
	}
}
else {
	echo __('There are no cities');
}
?>