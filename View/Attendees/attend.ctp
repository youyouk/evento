{<?php
	if($event['Event']['end_date'] < date('Y-m-d')) {
		$button_text = 'I went';
		$cancel_text = 'I went to this event, click to cancel.';
	} else {
		$button_text = 'I\'m going';
		$cancel_text = 'I\'m going to this event, click to cancel.';
	}

	function escapeJsonString($value) { # list from www.json.org: (\b backspace, \f formfeed)
	    $escapers = array("\\", "/", "\"", "\n", "\r", "\t", "\x08", "\x0c");
	    $replacements = array("\\\\", "\\/", "\\\"", "\\n", "\\r", "\\t", "\\f", "\\b");
	    $result = str_replace($escapers, $replacements, $value);
	    return $result;
	}

	if($isAttendee) {
?>
"attendee": 1,
"text": "<?php echo escapeJsonString(__($cancel_text)) ?>",
"link": "<?php echo escapeJsonString(Router::url(array('controller'=>'attendees', 'action'=>'attend', $event['Event']['id'], 0), true)); ?>",
"image": "<?php echo escapeJsonString($this->Html->link($this->Html->image('users/'.$attendee['User']['photo'],
	array('alt'=>h($attendee['User']['username']))), array('controller'=>'users', 'action'=>'view',
	$attendee['User']['slug']), array('escape'=>false, 'id'=>'attendee-me'))); ?>",
"template": "<?php
$template = '<div id="attendee-me" class="attendee-user">';
$template .= $this->Html->link($this->Html->image('users/'.$attendee['User']['photo'],
	array('alt'=>h($attendee['User']['username']))), array('controller'=>'users', 'action'=>'view',
	$attendee['User']['slug']), array('escape'=>false)).' ';
$template .= '<span>';
$template .= $this->Html->link($attendee['User']['username'], array('controller'=>'users', 'action'=>'view',
	$attendee['User']['slug'])).'<br> ';
if($attendee['City']['name']) {
  $template .= $attendee['City']['name'] . ', ' . $attendee['City']['Country']['name'] . '.';
}
$template .= '</span><div class="clear"></div></div>';
echo escapeJsonString($template);
?>",
<?php
}
else {
?>
"attendee": 0,
"text": "<?php echo escapeJsonString(__($button_text)); ?>",
"link": "<?php echo Router::url(array('controller'=>'attendees', 'action'=>'attend', $event['Event']['id'], 1), true); ?>",
<?php	
}
?>
"empty": "<?php echo escapeJsonString('<p class="empty-message">' . __('There are no attendees.') . '</p>') ?>",
"flash": "<?php echo escapeJsonString(__('Data saved')); ?>"}