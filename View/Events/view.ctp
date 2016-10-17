<?php
if($event['Event']['logo']!='') {
	echo '<div id="event-view-header" style="background-image: url(' . Router::url('/') . IMAGES_URL 
	. 'logos/big/' . $event['Event']['logo'] . ')"></div>';
}
?>
<div class="event-buttons">
	<div class="container">
<?php
	if($this->Session->read('Auth.User.id') == $event['Event']['user_id']) {
		echo $this->Html->link(__('Edit'), array('controller'=>'events', 'action'=>'edit'
			, $event['Event']['id']));

		if(Configure::read('evento_settings.disablePhotos') == false) {
			echo $this->Html->link(__('Manage gallery'), array('controller'=>'photos', 'action'=>'manage'
				, $event['Event']['slug']));
		}
	}

	if(Configure::read('evento_settings.disableAttendees') == false) {
		if($this->Session->read('Auth.User')) {
			$id = 'imgoing';
		} else {
			$id = 'imgoing-login';
		}

		if($event['Event']['end_date'] < date('Y-m-d')) {
			$button_text = 'I went';
			$cancel_text = 'I went to this event, click to cancel.';
		} else {
			$button_text = 'I\'m going';
			$cancel_text = 'I\'m going to this event, click to cancel.';
		}

		if($isAttendee) {
			echo $this->Html->link(__($cancel_text),
				array('controller'=>'attendees', 'action'=>'attend', 
					$event['Event']['id'], 0), array('id'=>$id, 'class'=>'imgoing-cancel')).' ';
		} else {
			echo $this->Html->link(__($button_text), array('controller'=>'attendees', 'action'=>'attend'
				, $event['Event']['id'], 1), array('id'=>$id, 'class'=>'imgoing-button'));
		}
	}
?>
	</div>
</div>
<div class="clear"></div>
<div class="container content">
	<div class="center_column">
		<div id="event-view-info" class="vevent">
			<h1 class="summary"><?php echo h(ucfirst($event['Event']['name'])); ?></h1>
			<div class="post-info">
            <?php echo $this->Element('event_full_date', array('event'=>$event)); ?>
				<p>
<?php
	echo $this->Html->link($event['Category']['name'], array('controller'=>'events', 'action'=>'index'
		, $event['Country']['slug'], $event['City']['slug'], $event['Venue']['slug']
		, $event['Category']['slug']), array('class'=>'category')) . '. ';
?>
					<span class="location vcard">
					<span class="adr">
<?php
	echo $this->Html->link(ucfirst($event['Venue']['name']), array('action'=>'index'
		, $event['Country']['slug'], $event['City']['slug']
		, $event['Venue']['slug']), array('class'=>'fn org'));
?></span>,
					<span class="street-address"><?php echo h($event['Venue']['address']); ?></span>, 
					<span class="locality">
<?php 
	echo $this->Html->link(ucfirst($event['City']['name']), 
		array('controller'=>'events', 'action'=>'index', $event['Country']['slug'], $event['City']['slug'])); 
?>.
					</span>
					<span class="country-name">
<?php
	echo $this->Html->link(__d('countries', $event['Country']['name'])
		, array('action'=>'index', $event['Country']['slug']), array('class'=>'country-name'));
?></span>.</span>
				</p>
<?php
	echo sprintf(__('Posted by %s'), $this->Html->link($event['User']['username']
		, array('controller'=>'users', 'action'=>'view', $event['User']['slug']))) . '.';

	if(!empty($event['Event']['web'])) {
		echo '<p>';
		$url = $event['Event']['web'];
		if (!preg_match('(^http)', $url)) {
			$url = 'http://' . $url;
		}
		echo $this->Html->link(__('Visit website'),$url, array('rel'=>'nofollow'));
		echo '</p>';
	}
?>
				</div>
			</div>
			<div class="clear"></div>
			<?php
				if($event['Event']['status'] == 'TENTATIVE') {
					echo '<p class="event-tentative">' . __('This event has not been confirmed yet') . '</p>';
				}
				else if($event['Event']['status'] == 'CANCELLED') {
					echo '<p class="event-cancelled">' . __('This event has been cancelled') . '</p>';
				}
			?>
			<div id="social-bookmarks" class="sbookmarks"> 
<?php 
	echo $this->Bookmark->getBookMarks(ucfirst(h($event['Event']['name'])));
	echo $this->Element('google_calendar');
	echo $this->Html->link(__('Export as iCal'), array('action'=>'ical', $event['Event']['id'])
		, array('class'=>'link'));
?>
		</div>
		<p class="description"><?php echo nl2br(h($event['Event']['notes'])); ?></p>
<?php 
	if(isset($repeatEvents) && !empty($repeatEvents)) {
?>
		<div class="block-repeat-events">
			<h2><?php echo __('Other dates for this event'); ?></h2>
<?php
	foreach($repeatEvents as $repeatEvent) {
//		echo '<p>';
        $linkName = '<span class="repeat-date-day">' . __(date('D', strtotime($repeatEvent['Event']['start_date']))) . '</span>';
$linkName .= '<span class="repeat-date-day">' . date('d', strtotime($repeatEvent['Event']['start_date'])) . '</span>';
$linkName .= '<span class="repeat-date-month">' . __(date('M', strtotime($repeatEvent['Event']['start_date']))) .'</span>';
		//$linkName = $this->Timeformat->getFormattedDate($repeatEvent['Event']['end_date']);
		echo $this->Html->link($linkName
			, array('action'=>'view', $repeatEvent['Venue']['City']['Country']['slug']
			, $repeatEvent['Venue']['City']['slug'], $repeatEvent['Venue']['slug']
			, $repeatEvent['Event']['slug']), array('escape'=>false));
//		echo '</p>';
	}
?>
		</div>
<?php
	}
?>
		<span class="event-tags">
<?php
	if(!empty($event['Tag'])) {
		sort($event['Tag']);

		$event_category = $event['Category']['slug'];
		if(!$event_category) {
			$event_category = __d('url', 'all-categories');
		}

		$n = 1;
		foreach($event['Tag'] as $tag) {
			echo $this->Html->link($tag['Tag']['name'],
				array('controller'=>'events', 'action'=>'index',
					__d('url','all-countries'),
					__d('url', 'all-cities'), __d('url', 'all-venues'), __d('url', 'all-categories'),
					$tag['Tag']['slug']),array('rel'=>'tag'));
			$n++;
		}
	}
?>
		</span>
		</div>
		<div class="right_column"><?php echo $this->Element('google_maps'); ?></div>
		<div class="clear"></div>
	</div>
<?php
	if(Configure::read('evento_settings.disablePhotos') == false && !empty($event['Photo'])) {
		echo '<div id="small-photos">';
		echo '<div class="container"><div class="center_column" style="position: relative">';
		echo '<div id="photo-loader">';
		echo '<span>' . __('Loading...') . '</span>';
		echo '</div>';
		echo $this->Html->image('events/'.$event['Photo'][0]['Photo']['file'], array('id'=>'big-image'));
		echo '</div><div class="right_column" id="thumbnails-column"><div id="thumbnails">';
		foreach($event['Photo'] as $photo) {
			echo $this->Html->link($this->Html->image('events/small/'.$photo['Photo']['file']
				, array('alt'=>h($photo['Photo']['title']))), array('controller'=>'photos'
				, 'action'=>'view', $event['Event']['slug'], $photo['Photo']['id']), array('escape'=>false
				, 'data-photo'=> Router::url('/') . IMAGES_URL . 'events/' . $photo['Photo']['file']
				, 'class'=>'small-image'));
		}
		echo '</div>';
		if($photoPagination) {
			echo '<div id="thumbnails-paginator">';
			echo $this->Html->link('>', array('controller'=>'photos', 'action'=>'page', $event['Event']['id']
				, 1), array('id'=>'thumbs-next'));
			echo '</div>';
		}
		echo '</div><div class="clear"></div></div></div>';
	}
?>
<div class="container">
	<div class="center_column">
<?php
	if(Configure::read('evento_settings.disableComments') == false
	&& (isset($event['Comment']) && !empty($event['Comment']) || $this->Session->read('Auth.User.id'))) { 
?>
	<div class="block-comments">
		<h2><?php echo __('Comments'); ?></h2>
<?php
	foreach($event['Comment'] as $comment) {
		echo $this->Element('comment_post', array('comment' => $comment));
	}
	echo $this->Element('comment_form');
	echo '</div>';
}
		?>
	</div>
	<div class="right_column">
<?php
	if(Configure::read('evento_settings.disableAttendees') == false 
	&& ((isset($event['Attendees']) && !empty($event['Attendees'])) || $this->Session->read('Auth.User.id'))) {
?>
	<div class="block-attendees">
	<h2><?php echo __('Attendees'); ?></h2> 
	<div id="user-list" class="attendee-list">
<?php
		if(!empty($event['Attendees'])) {
			foreach($event['Attendees'] as $attendee) {
				if($this->Session->read('Auth.User.id') == $attendee['User']['id']) {
					$attendeesId = 'attendee-me';
				} else {
					$attendeesId = 'attendee';
				}
				echo '<div id="' . $attendeesId . '" class="attendee-user">';
				echo $this->Html->link($this->Html->image('users/' . $attendee['User']['photo'],
					array('alt'=>h($attendee['User']['username']))), array('controller'=>'users'
						, 'action'=>'view' , $attendee['User']['slug']), array('escape'=>false)) . ' ';
				echo '<span>';
				echo $this->Html->link($attendee['User']['username'], array('controller'=>'users'
					, 'action'=>'view', $attendee['User']['slug']));
				echo '<br> ';
				if($attendee['City']['name']) {
					echo $attendee['City']['name'] . ', ' . $attendee['Country']['name'] . '.';
				}
				echo '</span><div class="clear"></div>';
				echo '</div>';
			}
		} else {
			echo '<p class="empty-message">' . __('There are no attendees.') . '</p>';
		}
	}
?>
		</div>
		<div class="clear"></div>
	</div>
</div>
<div class="clear"></div>
</div>