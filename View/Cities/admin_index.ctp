<div id="admin-content">
	<div class="admin-content-wrap">
		<div class="header">
<?php
	echo $this->Form->create('Search', array('url'=>array('controller'=>'cities', 'action'=>'search')));
	echo $this->Form->input('Search.city', array('class'=>'user-search', 'type'=>'text', 'label'=>false
		, 'div'=>false, 'placeholder'=>__('City')));
	echo $this->Form->submit(__('Search'), array('class'=>'user-search-button', 'div'=>false
		, 'autocomplete'=>'off'));
	echo $this->Form->end();
?>
		</div>
<?php
if(!empty($cities)) {
	echo '<table id="admin">';
	echo '<thead>';
	echo '<tr>';
	echo '<th class="name">' . __('City') . '</th>';
	echo '<th class="icon"><i class="fa fa-trash-o"></i></th>';
	echo '<th class="icon"><i class="fa fa-pencil"></i></th>';
	echo '</tr>';
	echo '</thead>';
	echo '<tbody>';
	foreach($cities as $city) {
		echo '<tr>';
		echo '<td class="name">';
		echo h($city['City']['name'] . ', ' . __d('countries', $city['Country']['name']) . '.');
		echo '</td>';
		echo '<td class="icon">';
		echo $this->Html->link('<i class="fa fa-trash-o"></i>'
			, array('controller'=>'cities', 'action'=>'delete', $city['City']['id']
			, 'page'=> isset($this->request->params['named']['page'])?
				 $this->request->params['named']['page'] : null), array('escape'=>false));
		echo '</td>';
		echo '<td class="icon">';
		echo $this->Html->link('<i class="fa fa-pencil"></i>'
			, array('controller'=>'cities', 'action'=>'edit', $city['City']['id']
			, 'page'=> isset($this->request->params['named']['page'])?
				 $this->request->params['named']['page']:null), array('escape'=>false));
		echo '</td>';
		echo '</tr>';
	}
	echo '</tbody>';
	echo '</table>';
	echo $this->Element('paginator');
}
else {
	echo '<div class="content-box"><p class="center empty">' . __('There are no cities') . '.</p></div>';
}
?>
	</div>
</div>