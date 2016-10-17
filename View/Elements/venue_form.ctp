<?php
echo $this->Html->script( 
'https://maps.googleapis.com/maps/api/js?key='.Configure::read('evento_settings.googleMapKey') . '&amp;sensor=false', array('inline'=>false));

if($this->request->params['action']=='add' || $this->request->params['action']=='admin_add') echo $this->Form->create('Venue', array('class'=>'venue-form', 'novalidate'=>true));
else echo $this->Form->create('Event', array('class'=>'venue-form', 'novalidate'=>true, 'url'=>$this->Html->url(array('action'=>'edit', $this->request->data['Venue']['id'],
'page'=> isset($this->request->params['named']['page'])? $this->request->params['named']['page']:null),
	true)));
echo $this->Form->hidden('Venue.id');
echo $this->Form->hidden('Venue.lat');
echo $this->Form->hidden('Venue.lng');
echo '<div class="input text">';
echo $this->Form->input('Venue.name', array('label'=>__('Venue Name'), 'autocomplete'=>'off'));
echo '</div>';

echo '<div id="venue-form">';
if(!Configure::read('evento_settings.country_id')) {	
	echo $this->Form->input('City.country_id', array('options'=>$countries, 'empty'=>true, 
		'label'=>__('Country')));		
}
else {
	echo $this->Form->hidden('City.country_name', array('value'=>$country_name));
	echo $this->Form->hidden('City.country_id', array('value'=>Configure::read('evento_settings.country_id')));
}

if(!Configure::read('evento_settings.city_name')) {
	echo '<div class="input">';
	echo $this->Form->input('City.name', array('label'=>__('City'), 'div'=>false, 'id'=>'CityName', 'autocomplete'=>'off'));
?>
	<div id="CityName_autoComplete"  data-url="<?php echo Router::url(array('admin'=>false, 'controller'=>'cities', 'action'=>'autoComplete')); ?>" class="auto_complete"></div>
<?php
	echo '</div>';
}
else {
	echo $this->Form->hidden('City.name', array('value'=>$default_city));
}

echo $this->Form->input('Venue.address', array('autocomplete'=>'off'));

$latlng = '';
if(isset($this->request->data['Venue']['lat']) && isset($this->request->data['Venue']['lng'])) { 
	$latlng = ' data-lat="'.$this->request->data['Venue']['lat'].'" data-lng="'.$this->request->data['Venue']['lng'].'"';
}

//google map		
echo '<div class="input"><div id="map" class="map-edit"'.$latlng.'></div></div>';
echo '</div>';
?>
<div class="submit">
<?php 
	echo $this->Form->submit(__('Save'), array('div'=>false));
	echo '<p class="backlink">'.' ';
	echo $this->Html->link(__('Cancel'), array('action'=>'index',
	'page'=> isset($this->request->params['named']['page'])? $this->request->params['named']['page']:null), array('class'=>'back-button')).'</p>';
	echo $this->Form->end();
?>
<div class="clear"></div>
</div>
