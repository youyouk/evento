<div id="admin-content">
	<div class="admin-content-wrap form-wrap">
		<h1 class="form-box-title"><?php echo __('Edit category'); ?></h1>
		<div id="form-block" class="form-box">
<?php
	echo $this->Form->create('Category', array('url'=>array('action'=>'edit'), 'novalidate'=>true));
	echo $this->Form->hidden('Category.id');
	echo $this->Form->input('Category.name', array('label'=>__('Category')));

	// if category already exists show the category merge box
	if(isset($this->Form->validationErrors['Category'])
	&& !empty($this->Form->validationErrors['Category'])
	&& $this->request->data['Category']['name'] != '') {
		echo '<p class="admin-button">';
		echo $this->Html->link('<i class="fa fa-magnet"></i> ' . __('Merge categories')
			, array('action'=>'merge', $this->request->data['Category']['id']
			, Inflector::slug(strtolower(trim($this->request->data['Category']['name'])), '-'))
			, array('escape'=>false));
		echo '</p>';
	}
?>
			<div class="submit">
<?php
	echo $this->Form->submit(__('Save'), array('div'=>false));
	echo '<p class="backlink">'.' '.$this->Html->link(__('Cancel'), 
		array('controller'=>'categories', 'action'=>'index'), array('class'=>'back-button')).'</p>';
	echo $this->Form->end();
?>
				<div class="clear"></div>
			</div>
		</div>
	</div>
</div>