<?php
	$params = array();
	$params['action'] = 'TEMPLATE';
	$params['text'] = $event['Event']['name'];
	$params['dates'] =  date('Ymd\THis',strtotime($event['Event']['start_date']))
		. '/' . date('Ymd\THis',strtotime($event['Event']['end_date']));
	$params['details'] =  $this->Text->truncate($event['Event']['notes'], 500);
	$params['location'] = $event['Venue']['address'] . ', ' . $event['City']['name']
		. ', ' . $event['Country']['name'] . '.';
	$params['trp'] = 'false';
	$params['sprop'] = $event['Event']['web'];
	$params['sprop=name:'] = $event['Event']['name'];

	$url = "https://www.google.com/calendar/event?" . http_build_query($params);
?>
<a href="<?php echo htmlentities($url); ?>" onclick="window.open(this.href); return false;" class="link">
<?php echo __('Google Calendar');?>
</a> 