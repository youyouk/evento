<div id="admin-menu">
	<ul>
<?php

	echo '<li><i class="fa fa-gear"></i> ';
	echo $this->Html->link(__('Settings'), array('controller'=>'settings'
		, 'action'=>'index'), array('id'=>'menu-settings'));
	echo '</li>';

	echo '<li><i class="fa fa-calendar"></i> ';
	echo $this->Html->link(__('Events'), array('controller'=>'events', 'action'=>'index')
		, array('id'=>'menu-events'));
	echo '</li>';

	if(Configure::read('evento_settings.disableComments') != 1) {
		echo '<li><i class="fa fa-comments-o"></i> ';
		echo $this->Html->link(__('Comments'), array('controller'=>'comments', 'action'=>'index')
			, array('id'=>'menu-comments'));
		echo '</li>';
	}

	echo '<li><i class="fa fa-users"></i> ';
	echo $this->Html->link(__('Users'), array('controller'=>'users', 'action'=>'index')
		, array('id'=>'menu-users'));
	echo '</li>';

	if(Configure::read('evento_settings.disablePhotos') != 1) {
		echo '<li><i class="fa fa-picture-o"></i> ';
		echo $this->Html->link(__('Photos'), array('controller'=>'photos', 'action'=>'index')
			, array('id'=>'menu-photos'));
		echo '</li>';
	}

	echo '<li><i class="fa fa-globe"></i> ';
	echo $this->Html->link(__('Cities'), array('controller'=>'cities', 'action'=>'index')
		, array('id'=>'menu-cities'));
	echo '</li>';

	echo '<li><i class="fa fa-map-marker"></i> ';
	echo $this->Html->link(__('Venues'), array('controller'=>'venues', 'action'=>'index')
		, array('id'=>'menu-venues'));
	echo '</li>';

	echo '<li><i class="fa fa-tasks"></i> ';
	echo $this->Html->link(__('Categories'), array('controller'=>'categories', 'action'=>'index')
		, array('id'=>'menu-categories'));
	echo '</li>';

	echo '<li><i class="fa fa-tags"></i> ';
	echo $this->Html->link(__('Tags'), array('controller'=>'tags', 'action'=>'index')
	, array('id'=>'menu-tags'));
	echo '</li>';
?>
	</ul>
</div>