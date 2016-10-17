<div id="admin-content">
	<div class="admin-content-wrap form-wrap">
		<h1 class="form-box-title"><?php echo __('Edit comment'); ?></h1>
		<div id="form-block" class="form-box">
<?php
	echo $this->Form->create('Comment',array('url'=>array('action'=>'edit'
		, $this->request->data['Comment']['id'], 'page'=>$page), 'novalidate'=>true));
	echo $this->Form->input('comment', array('type'=>'textarea', 'label'=>__('Write a comment')));
?>
			<div class="submit">
<?php 
	echo $this->Form->submit(__('Save'), array('div'=>false));
	echo '<p class="backlink"> ';
	echo $this->Html->link(__('Cancel')
		, array('controller'=>'comments', 'page'=>$page
		, 'action'=>($this->Session->read('Search.term'))? 'search':'index')
		, array('class'=>'back-button'));
	echo '</p>';
	echo $this->Form->end();
?>
				<div class="clear"></div>
			</div>
		</div>
	</div>
</div>