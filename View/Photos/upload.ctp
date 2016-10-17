<div class="container content">
	<div class="form generic-form">
		<h1><?php echo sprintf(__('Upload photo to %s'), $event['Event']['name']); ?></h1>
		<div id="form-block">
<?php
	echo '<p class="muted"><i>('.__("Max size").' '.ini_get('upload_max_filesize').')</i></p>';
?>
		<form method="post" enctype="multipart/form-data" action="<?php 
	echo $this->Html->url(array('controller'=>'photos', 'action'=>'upload', $event['Event']['slug']));
		?>" id="PhotoUpload" novalidate="novalidate">
<?php
	echo $this->Form->hidden('Photo.title',array('value'=>$event['Event']['name']));
	echo $this->Form->file('Photo.filedata');
	echo $this->Form->error('Photo.filedata');
?>
			<div class="submit">
<?php 
	echo $this->Form->submit(__('Submit'), array('div'=>false, 'id'=>'submit-button'));
	echo $this->Html->link(__('Cancel')
		, array('controller'=>'photos', 'action'=>'manage', $event['Event']['slug'])
		, array('class'=>'back-button')).'</p>';
?>
				<div class="clear"></div>
			</div>
<?php echo $this->Form->end(); ?>
		</div>
		<div class="clear"></div>
	</div>
</div>