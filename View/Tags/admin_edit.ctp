<div id="admin-content">
	<div class="admin-content-wrap from-wrap">
		<h1 class="form-box-title"><?php echo __('Edit tag'); ?></h1>
		<div id="form-block" class="form-box">
<?php
	echo $this->Form->create('Tag', array('novalidate'=>true, 'url'=>array('action'=>'edit'
		, 'page'=>isset($this->request->params['named']['page'])?
			$this->request->params['named']['page'] : null)));
	echo $this->Form->hidden('Tag.id');
	echo $this->Form->input('Tag.name', array('label'=>__('Tag'), 'autocomplete'=>'off'));
	if(isset($this->Form->validationErrors['Tag'])
	&& !empty($this->Form->validationErrors['Tag'])
	&& $this->request->data['Tag']['name']!='') {
		echo '<p class="admin-button">';
		echo $this->Html->link('<i class="fa fa-magnet"></i> ' . __('Merge tags')
			, array('action'=>'merge', $this->request->data['Tag']['id']
			, Inflector::slug(strtolower(trim($this->request->data['Tag']['name'])),'-'))
			, array('escape'=>false));
		echo '</p>';
	}
?>
			<div class="submit">
<?php
	echo $this->Form->submit(__('Save'), array('div'=>false));
	echo '<p class="backlink"> ' . $this->Html->link(__('Cancel')
		, array('controller'=>'tags', 'page'=>$page
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