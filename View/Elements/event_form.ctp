<?php
if(!isset($page)) $page = null;
echo $this->Html->script( 
'https://maps.googleapis.com/maps/api/js?key='.Configure::read('evento_settings.googleMapKey') . '&amp;sensor=false', array('inline'=>false));

if($this->request->params['action']=='add' || $this->request->params['action']=='admin_add') {
	echo $this->Form->create('Event', array('type'=>'file', 'id'=>'EventAddForm', 'class'=>'event-form', 'novalidate'=>true));
}
else {
	echo $this->Form->create('Event', array('url'=>array('action'=>'edit', 'page'=>$page), 'type'=>'file', 
		'id'=>'EventAddForm', 'class'=>'event-form', 'novalidate'=>true));
}
echo $this->Form->hidden('Event.id');
echo $this->Form->hidden('Venue.lat');
echo $this->Form->hidden('Venue.lng');
if(!Configure::read('evento_settings.adminVenues')) echo $this->Form->hidden('Event.venue_id');

echo $this->Form->input('Event.name',array('label'=>__('Event Name'), 'autofocus'=>true, 'autocomplete'=>'off'));

echo '<div id="form-logo-box">';
if(isset($this->request->data['Event']['logo']) && !empty($this->request->data['Event']['logo'])) {
	$this->request->data['Event']['logo'] = 'logos/small/'.$this->request->data['Event']['logo'];
	echo $this->Html->image($this->request->data['Event']['logo'], array('alt'=>__('logo')));
}
else {
	echo $this->Html->image('default_logo.jpg');
};
echo '<div id="logo-input">';
echo $this->Form->input('filedata', array('type'=>'file', 'label'=>__('Event cover image')));
echo '</div><div class="clear"></div></div>';
if(isset($this->request->data['Event']['id']) && !empty($this->request->data['Event']['id']) &&
	!empty($this->request->data['Event']['logo'])) {
		echo '<div id="delete-logo-block">';
		echo $this->Form->input('delete_logo', array('type'=>'checkbox', 'div'=>false,
			'label'=>__('Delete cover image'), 'class'=>'checkbox-input'));
		echo '</div>';
}

echo $this->Form->input('Event.notes',array('label'=>__('Description')));

echo $this->Form->input('Event.category_id', array('type'=>'select', 'options'=>$categories, 'empty'=>false,
	'label'=>__('Category')));


echo '<div class="input text">';
if(!Configure::read('evento_settings.adminVenues')) {

echo '<div id="venue-name-div">';
echo $this->Form->input('Venue.name', array('label'=>__('Venue Name'), 'id'=>'VenueName', 'div'=>false, 'autocomplete'=>'off'));
?><div id="VenueName_autoComplete" data-url="<?php echo Router::url(array('admin'=>false, 'controller'=>'venues', 'action'=>'autoComplete')); ?>" class="auto_complete"></div>
<?php
echo '<div id="venue-name-block"><div id="venue-data"></div><a href="#" id="reset-venue">'.__('Change Venue').'</a></div>';

echo '</div>';
echo '</div>';

$venue_form_display = 'none';

if((!isset($this->request->data['Event']['venue_id']) || !$this->request->data['Event']['venue_id']) && (isset($this->request->data['Venue']['name']) ||
 	isset($this->request->data['Venue']['City']['name']) || isset($this->request->data['Venue']['City']['country_id']) ||
 	isset($this->request->data['Venue']['address']))) {
		$venue_form_display = 'block';
}
else if(isset($this->request->data['Event']['venue_id']) && $this->request->data['Event']['venue_id']) {
	echo '<input type="hidden" id="hiddenVenueName" value="'.h(str_replace('"','\"',h(str_replace('"','\"',
		$this->request->data['Venue']['name'])))).'"/>';
	echo '<input type="hidden" id="hiddenCityName" value="'
	.h(
		str_replace('"','\"',$this->request->data['Venue']['name'])
	).'"/>';
	echo '<input type="hidden" id="hiddenCountryName" value="'.h(str_replace('"','\"',
		__d('countries', $this->request->data['Venue']['City']['Country']['name']))).'"/>';
	echo '<input type="hidden" id="hiddenVenueAddress" value="'.h(str_replace('"','\"',$this->request->data['Venue']['address'])).'"/>';
}

echo '<div id="venue-form" style="display:'.$venue_form_display.'">';
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
	echo $this->Form->input('City.name', array('label'=>__('City'), 'div'=>false, 'id'=>'CityName'));
	?>
		<div id="CityName_autoComplete" data-url="<?php echo Router::url(array('admin'=>false, 'controller'=>'cities', 'action'=>'autoComplete')); ?>" data-rfield="country" class="auto_complete"></div>
	<?php
	echo '</div>';
}
else {
	echo $this->Form->hidden('City.name', array('value'=>$default_city, array('autocomplete'=>'off')));
}

echo $this->Form->input('Venue.address', array('autocomplete'=>'off'));

$latlng = '';
if(isset($this->request->data['Event']['lat']) && isset($this->request->data['Event']['lng'])) { 
	$latlng = ' data-lat="'.$this->request->data['Event']['lat'].'" data-lng="'.$this->request->data['Event']['lng'].'"';
}

//google map
echo '<div class="input"><div id="map" class="map-edit" style="display:block;"'. $latlng .'></div></div>';


}
else {
	echo $this->Form->input('Event.venue_id', array('options'=>$venues));
}

echo '</div>';
echo '<div id="start_date_input">';
echo $this->Form->input('start_date',array('dateFormat'=>'DMY',
							'timeFormat'=>Configure::read('evento_settings.timeFormat'),
							'minYear' => date('Y'),
							'maxYear' => date('Y')+5,
							'separator' => ""
							));
echo '</div>';
echo $this->Form->input('end_date_check', array('type'=>'checkbox', 'id'=>'end_date_check', 'label'=>__('Add end date'), 'checked'=>isset($end_date_checked)));
echo '<div id="end_date_input">';
echo $this->Form->input('end_date',array(  'dateFormat'=>'DMY',
							'timeFormat'=>Configure::read('evento_settings.timeFormat'),
							'minYear' => date('Y'),
							'maxYear' => date('Y')+5,
							'separator' => "",
							'class'=>'EndDate'
							));
echo '</div>';

echo $this->Element('event_repeat_form');


echo $this->Form->input('web', array('label'=>__('Website'), 'autocomplete'=>'off'));

echo '<div class="input text">';

echo $this->Form->input('tags', array('id'=>'Tags', 'div'=>false, 'autocomplete'=>'off', 'label'=>__('Tags') 
. ' <span class="muted">' . __('comma separated') . '</span>'));
?>
	<div id="Tags_autoComplete" data-url="<?php echo Router::url(array('admin'=>false, 'controller'=>'tags', 'action'=>'autoComplete')); ?>" class="auto_complete"></div>

<?php

echo $this->Form->label('Event.status', __('Status',true));
echo $this->Form->select('Event.status', array(
	'CONFIRMED'=>__('Confirmed', true),	// default option
	'TENTATIVE'=>__('Tentative', true),
	'CANCELLED'=>__('Cancelled', true)
), array('empty'=>false));

echo '</div>';

if(isset($this->request->params['admin'])) {
  echo $this->Form->input('published', array('type'=>'checkbox', 'label'=>__('Event is published')));
  echo $this->Form->input('promoted', array('type'=>'checkbox', 'label'=>__('Event is promoted')));
  echo $this->Form->input('promoted_in_category', array('type'=>'checkbox', 'label'=>__('Event is promoted in its category')));
}

if(isset($useRecaptcha) && $useRecaptcha) {
	echo $this->Recaptcha->display();
	echo $this->Form->error('recaptcha');
}
?>
<?php
$acceptButtonText = __('Save');
if (isset($paypal)) {
  echo '<div class="paypal-notice">';
  echo __('Publish your event for %s. Payments with Paypal.', $paypalPublishPrice.' '.$paypalCurrency);
  echo '</div>';
  $acceptButtonText = __('Pay with Paypal');
}
?>
<div id="submit" class="submit">
<?php
echo $this->Form->submit($acceptButtonText, array('div'=>false, 'id'=>'submit-button'));
if($this->request->params['action']=='edit') {
	$link = array('controller'=>'events', 'action'=>'view',
		$event['Venue']['City']['Country']['slug'], $event['Venue']['City']['slug'],
		$event['Venue']['slug'], $event['Event']['slug']);
}
else {
	$link = array('controller'=>'events', 'page'=>$page,
  	'action'=>($this->Session->read('Search.term'))? 'search':'index');
}
echo $this->Html->link(__('Cancel'), $link, array('class'=>'back-button'));
echo "</p>";
?>
<div class="clear"></div>
</div>
<?php
echo $this->Form->end();
?>
