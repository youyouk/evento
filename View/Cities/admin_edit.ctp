<div id="admin-content">
	<div class="admin-content-wrap form-wrap">
		<h1 class="form-box-title"><?php echo __('Edit City'); ?></h1>
		<div id="form-block" class="form-box">
<?php
	echo $this->Form->create('City', array('novalidate'=>true, 'url'=>array('action'=>'edit'
		, 'page'=>isset($this->request->params['named']['page'])?
			$this->request->params['named']['page'] : null)));
	echo $this->Form->label('City.country_id', __('Country'));
	echo $this->Form->select('City.country_id', $countries);
	echo $this->Form->error('City.country_id');
	echo $this->Form->hidden('City.id');
	echo $this->Form->input('City.name', array('label'=>__('City')));

	// if city exists display city merge box
	if(isset($this->Form->validationErrors['City'])
	&& !empty($this->Form->validationErrors['City'])
	&& $this->request->data['City']['name']!='') {
		echo '<p class="admin-button">';
		echo $this->Html->link('<i class="fa fa-magnet"></i> ' . __('Merge cities')
			, array('action'=>'merge', $this->request->data['City']['id']
			, Inflector::slug(strtolower($this->request->data['City']['name']), '-')), array('escape'=>false));
		echo '</p>';
	}
?>
			<div class="submit">
<?php
	echo $this->Form->submit(__('Save'), array('div'=>false));
	echo '<p class="backlink"> ';
	echo $this->Html->link(__('Cancel')
		, array('controller'=>'cities', 'action'=>'index', 'page'=>$page
		, 'action'=>($this->Session->read('Search.term'))? 'search' : 'index')
		, array('class'=>'back-button'));
	echo '</p>';
	echo $this->Form->end();
?>
			<div class="clear"></div>
			</div>
		</div>
	</div>
</div>