<?php if($this->Session->read('Auth.User.id')) { ?>
<div id="form-block" class="comments-form">
<?php
	echo $this->Form->create('Comment', array('id'=>'CommentWrite'
		, 'url'=>array('action'=>'write',$event['Event']['id']), 'novalidate'=>true));
	echo $this->Form->input('comment',array('label'=>''));
	if($useRecaptcha) {
		$this->Helpers->load('Recaptcha.Recaptcha');
		echo '<div id="recaptcha">';
		echo $this->Recaptcha->display();
		echo '</div>';
		echo $this->Form->error('recaptcha');
	}
	echo '<div class="submit-block">';
	echo $this->Form->submit(__('Submit'), array('div'=>false, 'id'=>'submit-button'));
	echo '</div>';
	echo $this->Form->end();
?>
	<div class="clear"></div>
</div>
<?php
}
?>
