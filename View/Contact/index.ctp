<div class="container content">
	<div id="contact-form" class="generic-form">
<?php if(!isset($email_sent)) { ?>
		<h1><?php echo __('Contact us');?></h1>
		<div id="form-block">
<?php
	echo $this->Form->create('Contact'
		, array('url'=>array('controller'=>$this->request->params['controller'], 'action'=>'index')
		, 'novalidate'=>true
		, 'id'=>'ContactForm'));
	echo $this->Form->input('email', array('type'=>'text', 'autofocus'=>true));
	echo $this->Form->label('message', __('Message'));
	echo $this->Form->textarea('message');
	echo $this->Form->error('message');
	if($useRecaptcha) {
		echo $this->Recaptcha->display();
		echo $this->Form->error('recaptcha');
	}
	echo $this->Form->end(__('Submit'));
?>
			<div class="clear"></div>
		</div>
<?php
}
else {
	echo '<p>'.__('Your message has been sent.').'</p>';
}
?>
	</div>
</div>