<div class="right_column" id="thumbnails-column">
	<div id="thumbnails">
<?php
	foreach($photos as $photo) {
		echo $this->Html->link($this->Html->image('events/small/'.$photo['Photo']['file']
			, array('alt'=>h($photo['Photo']['title']))), array('controller'=>'photos'
			, 'action'=>'view', $event['Event']['slug'], $photo['Photo']['id']), array('escape'=>false
			, 'data-photo'=> '/' . IMAGES_URL . 'events/' . $photo['Photo']['file'], 'class'=>'small-image'));
	}
	echo '</div>';
	echo '<div id="thumbnails-paginator"  data-page="0" data-event="' 
		. $event['Event']['id'] . '" data-more="true">';

	if($prev !== false) {
		echo $this->Html->link('<', array('controller'=>'photos', 'action'=>'page', $event['Event']['id']
			, $prev), array('id'=>'thumbs-prev'));
	}

	if($next !== false) {
		echo $this->Html->link('>', array('controller'=>'photos', 'action'=>'page', $event['Event']['id']
			, $next), array('id'=>'thumbs-next'));
	}
?>
	</div>
</div>
