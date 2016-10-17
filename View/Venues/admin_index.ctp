<div id="admin-content">
	<div class="admin-content-wrap">
		<div class="header">
<?php
	echo $this->Html->link(__('Add a Venue'), array('action'=>'add'), array('class'=>'button'));
	echo $this->Form->create('Search', array('url'=>array('controller'=>'venues', 'action'=>'search')));
	echo $this->Form->input('Search.term', array('class'=>'user-search', 'type'=>'text'
		, 'label'=>false, 'div'=>false, 'placeholder'=>__('Venue')));
	echo $this->Form->submit(__('Search'), array('class'=>'user-search-button', 'div'=>false));
	echo $this->Form->end();
?>
			<div class="clear"></div>
		</div>
<?php
if(!empty($venues)) {
	echo '<table id="admin">';
	echo '<thead>';
	echo '<tr>';
	echo '<th class="name">' . __('Venue') . '</th>';
	echo "<th class=\"icon\"><i class=\"fa fa-trash-o\"></i></th>";
	echo '<th class="icon"><i class="fa fa-pencil"></i></th>';
	echo '</tr>';
	echo '</thead>';
	echo '<tbody>';
  	foreach($venues as $venue) {
	  	echo '<tr>';
	  	echo '<td class="name">';
		echo ($venue['Venue']['name']);
		echo '<span class="venue-address">';
		echo '('.$venue['Venue']['address'] . ', ' . $venue['City']['name'] . ', ' .
		    __d('countries', $venue['Country']['name']) . ')';
		echo '</span>';
		echo '</td>';
	  	echo '<td class="icon">';
		echo $this->Html->link('<i class="fa fa-trash-o"></i>'
			, array('controller'=>'venues', 'action'=>'delete', $venue['Venue']['id']
			, 'page'=> isset($this->request->params['named']['page'])?
				 $this->request->params['named']['page'] : null)
			, array('escape'=>false));
		echo '</td>';
	  	echo '<td class="icon">';
		echo $this->Html->link('<i class="fa fa-pencil"></i>'
			, array('controller'=>'venues', 'action'=>'edit', $venue['Venue']['id']
			, 'page'=> isset($this->request->params['named']['page'])?
				$this->request->params['named']['page'] : null)
			, array('escape'=>false));
		echo '</td>';
	  	echo '</tr>';
	}
	echo '</tbody>';
	echo '</table>';
	echo $this->Element('paginator');  
}
else {
	echo '<div class="content-box"><p class="center empty">' . __('There are no venues') . '.</p></div>';
}
?>
	</div>
</div>