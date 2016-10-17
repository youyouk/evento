<?php
	echo $this->Form->create('User', array('url'=>array('action' => 'register'), 'novalidate' => true));
	echo $this->Form->input('username', array('div'=>false, 'autofocus'=>true, 'autocomplete'=>'off'));
	echo $this->Form->input('email', array('div'=>false, 'autocomplete'=>'off'));
	echo $this->Form->input('password', array('div'=>false));

	if(!isset($this->request->params['admin']) || !$this->request->params['admin']) {
		if($useRecaptcha) { 
			echo $this->Recaptcha->display();
			echo $this->Form->error('recaptcha');
		}
		echo $this->Form->end(__('Register'));
	}
	else {
		echo $this->Form->input('group_id', array('type'=>'select', 'options'=>$groups, 'empty'=>false,
			'label'=>__('Users group')));
?>
<div class="submit">
<?php 
	echo $this->Form->submit(__('Save'), array('div'=>false));
	echo '<p class="backlink"> ';
	echo $this->Html->link(__('Cancel'), array('controller'=>'users', 'action'=>'index')
		, array('class'=>'back-button'));
	echo '</p>';
	echo $this->Form->end();
?>
	<div class="clear"></div>
</div>
<?php } ?>
