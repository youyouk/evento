<div class="event-buttons">
	<div class="container">
<?php
	echo $this->Html->link(__('Back'), array('controller'=>'events', 'action'=>'view'
		, $event['Venue']['City']['Country']['slug'], $event['Venue']['City']['slug'], $event['Venue']['slug']
		, $event['Event']['slug'] ));
	echo $this->Html->link(__('Upload a photo'), array('controller'=>'photos', 'action'=>'upload'
		, $event['Event']['slug']));
?>
	</div>
</div>
<div class="container content">
	<h1 class="summary"><?php echo $event['Event']['name'] ?></h1>

	<div class="post-info">
		<p class="event-when">
			<abbr class="dtstart" title="<?php echo $event['Event']['start_date']?>">
<?php 
	echo $this->Timeformat->getFormattedDate($event['Event']['start_date']);
?>
			</abbr>
			<abbr class="dtend" title="<?php echo $event['Event']['end_date']?>">
<?php 
	if($this->Time->format('dmY',$event['Event']['end_date']) 
	!= $this->Time->format('dmY',$event['Event']['start_date'])) {
		echo ' ' . __('until') . ' ';
		echo $this->Timeformat->getFormattedDate($event['Event']['start_date']);
	} else {
		if($this->Time->format($this->Timeformat->getTimeFormat(),$event['Event']['end_date'])
		!= $this->Time->format($this->Timeformat->getTimeFormat(),$event['Event']['start_date'])) {
			echo ' '.__('until').' '.$this->Time->format($this->Timeformat->getTimeFormat()
				, $event['Event']['end_date']);
		}
	}
?>
			</abbr>
		</p>
		<p class="adr"><?php echo h($event['Category']['name']) . '. '; ?>
			<span class="fn"><?php echo h(ucfirst($event['Venue']['name'])); ?></span>,
			<span class="location street-address"><?php echo h($event['Venue']['address']); ?></span>,
			<span class="locality"><?php echo h(ucfirst($event['Venue']['City']['name'])); ?>.</span>
			<span class="country-name">
<?php echo h(__d('countries', $event['Venue']['City']['Country']['name'])); ?></span>.
		</p>
	</div>
	<div id="gallery-thumbnails">
<?php
	if(!empty($photos)) {
		foreach($photos as $photo) {
			echo '<div class="manage-photo">';
			echo $this->Html->image('events/small/'.$photo['Photo']['file']);
			echo $this->Html->link(__('delete'), array('controller'=>'photos', 'action'=>'delete'
				, $photo['Photo']['id']));
			echo '</div>';
		}
	} else {
		echo '<p class="empty-message">' . __('There are no photos') . '</p>';
	}
?>
	</div>
</div>