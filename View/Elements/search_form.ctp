<div id="event-search-form">
<?php
	echo $this->Form->create('Search', array('url'=>array('plugin'=>null, 'controller'=>'events'
		, 'action'=>'search')));
	echo $this->Form->input('term', array('type'=>'text', 'label'=>false, 'autocomplete'=>'off'
		, 'div'=>false, 'placeholder'=>__('Search') . '...'));
	echo '<br>';
	echo $this->Form->submit(__('Search'), array('div'=>false, 'id'=>'SearchButton', 'alt'=>'search'));
	echo $this->Form->end();
?>
</div>
