<div id="admin-content">
	<div class="admin-content-wrap">
		<div class="notice">
			<p>
<?php
	echo __('Please delete the venues in this city before proceeding.');
 ?>
			</p>
<?php
	echo $this->Html->link(__('Back')
		, array('controller'=>'cities', 'action'=>'index', 'page'=>$page
		, 'action'=>($this->Session->read('Search.term'))? 'search':'index'), array('class'=>'back-button'));
?>
		</div>
	</div>
</div>