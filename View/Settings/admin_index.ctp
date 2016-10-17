<div id="admin-content">
    <div class="admin-content-wrap form-wrap">
<?php echo $this->Form->create('Settings', array('novalidate'=>true, 'url'=>array('action'=>'index'))); ?>
<h1 class="form-box-title"><?php echo __('Basic settings'); ?></h1>
<div class="form-box">
<?php
echo $this->Form->input('appName', array('label'=>__('Application name'), 'autocomplete'=>'off'));
echo $this->Form->input('appSlogan', array('label'=>__('Application slogan'), 'autocomplete'=>'off'));
echo $this->Form->input('adminEmail', array('label'=>__('Admin email'), 'autocomplete'=>'off'));
echo $this->Form->input('systemEmail', array('label'=>__('System email'), 'autocomplete'=>'off'));
echo $this->Form->input('twitterAccount', array('label'=>__('Twitter account'), 'autocomplete'=>'off'));
echo $this->Form->input('theme', array('type'=>'select', 'options'=>$this->request->data['themes'],
	'label'=>__('Theme'), 'div'=>false));
?>
</div>

<h1 class="form-box-title"><?php echo __('Time settings'); ?></h1>
<div class="form-box">
<?php
echo $this->Form->input('Settings.timeZone', array('type'=>'select', 'options'=>$tm,  
	'label'=> __('Time zone'), 'empty'=>true));
echo $this->Form->input('timeFormat', array('type'=>'select', 'options'=>array('12'=>'12', '24'=>'24'),
	'label'=>__('Time format')));
echo $this->Form->input('dateFormat', array('type'=>'select',  'options'=>array('d/m/Y'=>'dd/mm/yyyy',
	'm/d/Y'=>'mm/dd/yyyy'), 'label'=>__('Date format')));
echo $this->Form->input('weekStart', array('type'=>'select', 'options'=>
	array(
		'saturday'=>__('Saturday'),
		'sunday'=>__('Sunday'),
		'monday'=>__('Monday'),		
	), 'label'=>__('Week starts'), 'div'=>false));
?>	
</div>
<h1 class="form-box-title"><?php echo __('Country & language'); ?></h1>
<div class="form-box">
<?php
echo $this->Form->input('country_id', array('type'=>'select', 'options'=>$countries, 
	'label'=>__('Allow events for this country only'), 'empty'=>true));
echo $this->Form->input('city_name',array('label'=>__('Allow events for this city only'), 'autocomplete'=>'off'));
echo $this->Form->input('language', array('type'=>'select', 'options'=>$this->request->data['languages'], 
	'label'=>__('Language'), 'div'=>false));
?>
</div>
<h1 class="form-box-title"><?php echo __('Admin settings'); ?></h1>
<div class="form-box">
<?php
echo $this->Form->input('guestsCanAddEvents', array('class'=>'checkbox', 'type'=>'checkbox', 
	'label'=>__('Guests can add events'), 'div'=>false));
echo '<br/>';

echo $this->Form->input('adminVenues', array('class'=>'checkbox', 'type'=>'checkbox', 
	'label'=>__('Only admin can add new venues.'), 'div'=>false));
echo '<br/>';

echo $this->Form->input('moderateEvents', array('class'=>'checkbox', 'type'=>'checkbox', 
	'label'=>__('Admin moderates events before publishing.'), 'div'=>false));
echo '<br/>';

echo $this->Form->input('validateEmails', array('class'=>'checkbox', 'type'=>'checkbox', 
	'label'=>__('Users must confirm the email address.'), 'div'=>false));
echo '<br/>';

echo $this->Form->input('adminAddsUsers', array('class'=>'checkbox', 'type'=>'checkbox', 
	'label'=>__('Only admin can register new users.'), 'div'=>false));
echo '<br/>';

echo $this->Form->input('disableComments', array('class'=>'checkbox', 'type'=>'checkbox', 
	'label'=>__('Disable events comments.'), 'div'=>false));
echo '<br/>';

echo $this->Form->input('disablePhotos', array('class'=>'checkbox', 'type'=>'checkbox', 
	'label'=>__('Disable events photos.'), 'div'=>false));
echo '<br/>';

echo $this->Form->input('disableAttendees', array('class'=>'checkbox', 'type'=>'checkbox', 
	'label'=>__('Disable event attendees.'), 'div'=>false));
echo '<br/>';

?>
</div>
<h1 class="form-box-title"><?php echo __('Google Maps key'); ?></h1>
<div class="form-box">
<?php
echo $this->Form->input('googleMapKey', array('label'=>__('Google map key'). ' ('.$this->Html->link('code.google.com', 'http://code.google.com/apis/console', array('onclick'=>'window.open(this.href);return false;')).')', 'div'=>false));
?>
</div>
<h1 class="form-box-title"><?php echo __('Recaptcha'); ?></h1>
<div class="form-box">
<?php
echo $this->Form->input('recaptchaPublicKey', array('label'=>__('Public key') . ' ('.$this->Html->link('recaptcha.net',
	'https://www.google.com/recaptcha',  array('onclick'=>'window.open(this.href);return false;')).')', true));
echo $this->Form->input('recaptchaPrivateKey', array('label'=>__('Private key')));
?>
</div>
<h1 class="form-box-title"><?php echo __('Facebook App ID &amp; Secret') ?></h1>
<div class="form-box">
<?php
echo $this->Form->input('facebookAppId', array('label'=>__('Facebook App ID')));
echo $this->Form->input('facebookSecret', array('label'=>__('Facebook Secret'), 'div'=>false));
?>
</div>
<h1 class="form-box-title"><?php echo __('Paypal API settings') ?></h1>
<div class="form-box">
<?php
echo $this->Form->input('paypalAPIUsername', array('label'=>__('Paypal API username')));
echo $this->Form->input('paypalAPIPassword', array('label'=>__('Paypal API password')));
echo $this->Form->input('paypalAPISignature', array('label'=>__('Paypal API signature')));
echo $this->Form->input('paypalCurrency', array('type'=>'select', 'options'=>$currencies, 
	'label'=>__('Currency'), 'div'=>false));
echo $this->Form->input('paypalAddEventPrice', array('type'=>'number', 'min'=>0, 'label'=>__('Price for event publishing')));
echo $this->Form->input('paypalAPISandbox', array('class'=>'checkbox', 'type'=>'checkbox', 
	'label'=>__('Use Paypal API sandbox'), 'div'=>false));
echo '<br/>';
?>
</div>

<h1 class="form-box-title"><?php echo __('Header & footer'); ?></h1>
<div class="form-box">
<?php
echo $this->Form->input('htmlTop', array('type'=>'textarea', 'label'=>__('Page header')));
echo $this->Form->input('htmlBottom', array('type'=>'textarea', 'label'=>__('Page bottom'), 'div'=>false));
?>
</div>
<div class="content-box">
<?php 
echo $this->Form->end(); 
echo $this->Form->submit(__('Submit'), array('class'=>'button'));
?>
</div></div></div>