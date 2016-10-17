<div id="admin-content">
	<div class="admin-content-wrap form-wrap">
		<h1 class="form-box-title"><?php echo __('Add a new category'); ?></h1>
		<div id="form-block" class="form-box">
<?php 
	echo $this->Form->create('Category', array('url'=>array('action'=>'add'),
   'novalidate'=>true));
	echo $this->Form->input('Category.name', array('label'=>__('Category'), 'autofocus'=>true));
?>
			<div class="submit">
<?php 
	echo $this->Form->submit(__('Save'), array('div'=>false));
	echo '<p class="backlink"> ' . $this->Html->link(__('Cancel'),
		array('controller'=>'categories', 'action'=>'index'), array('class'=>'back-button')) . ' </p>';
	echo $this->Form->end();
?>
				<div class="clear"></div>
			</div>
		</div>
	</div>
</div>