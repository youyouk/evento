<?php
	echo $this->Html->script('https://maps.googleapis.com/maps/api/js?key='
		. Configure::read('evento_settings.googleMapKey') . '&amp;sensor=false', array('inline'=>false));
?>
<div id="view_map_full">
<?php
	$latlng = '';
	if($event['Venue']['lat'] && $event['Venue']['lng']) { 
		$latlng = ' data-lat="' . $event['Venue']['lat'] . '" data-lng="' . $event['Venue']['lng'] . '"';
	}
?>
	<div id="map" style="display:block;"<?php echo $latlng ?>></div>
</div>