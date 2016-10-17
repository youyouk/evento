<div id="admin-content">
	<div class="admin-content-wrap form-wrap">
		<h1 class="form-box-title"><?php echo __('Edit user'); ?></h1>
		<div id="form-block" class="form-box">
<?php 
	echo $this->Form->create('User',array('url'=>array('action'=>'edit', $this->request->data['User']['id']
		, 'page'=> isset($this->request->params['named']['page'])?
			 $this->request->params['named']['page'] : null)
		, 'novalidate' => true));

	if($this->request->data['User']['photo']!='user_photo.jpg') {
		echo '<div id="user-photo-box">';
		echo $this->Html->image('users/'.$this->request->data['User']['photo'], array('alt'=>__('photo')));
		echo '<div id="photo-input">';
		echo $this->Form->input('delete_photo', array('type'=>'checkbox', 'div'=>false
			, 'label'=>__('delete photo'), 'class'=>'checkbox-input'));
		echo '</div><div class="clear"></div></div>';
	}

	echo $this->Form->input('username', array('label'=>__('Username'), 'autocomplete'=>'off'));
	echo $this->Form->input('email', array('label'=>__('Email'), 'autocomplete'=>'off'));

	echo $this->Form->input('group_id', array('type'=>'select', 'options'=>$groups, 'empty'=>false,
		'value'=>$this->request->data['User']['group_id'], 'label'=>__('Users group')));


	echo $this->Form->input('password', array('type'=>'password', 'label'=>__('New password')
		, 'required'=>false));
	echo $this->Form->input('password_confirm', array('type'=>'password'
		, 'label'=>__('New password confirmation')));
	echo $this->Form->input('active', array('class'=>'checkbox-input', 'type'=>'checkbox'
		, 'label'=>__('User account is active'), 'div'=>false));
?>
			<div class="submit">
<?php 
	echo $this->Form->submit(__('Save'), array('div'=>false));
	echo '<p class="backlink"> ';
	echo $this->Html->link(__('Cancel'), array('controller'=>'users', 'action'=>'index'
		, 'page'=> isset($this->request->params['named']['page'])?
			$this->request->params['named']['page'] : null)
		, array('class'=>'back-button'));
	echo '</p>';
	echo $this->Form->end();
?>
				<div class="clear"></div>
			</div>
		</div>
	</div>
</div>