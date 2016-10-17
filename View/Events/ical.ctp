BEGIN:VCALENDAR<?php echo "\r\n"; ?>
VERSION:2.0<?php echo "\r\n"; ?>
X-WR-CALNAME:<?php echo $event['Event']['name']."\r\n"; ?>
PRODID:-//<?php echo Configure::read('evento_settings.appName'); ?> ICS//EN<?php echo "\r\n"; ?>
CALSCALE:GREGORIAN<?php echo "\r\n"; ?>
METHOD:PUBLISH<?php echo "\r\n"; ?>
BEGIN:VEVENT<?php echo "\r\n"; ?>
DTSTART;VALUE=DATE:<?php echo date("Ymd\THis", strtotime($event['Event']['start_date']))."\r\n"; ?>
DTEND;VALUE=DATE:<?php echo date("Ymd\THis", strtotime($event['Event']['end_date']))."\r\n"; ?>
TRANSP:TRANSPARENT<?php echo "\r\n"; ?>
SUMMARY:<?php echo str_replace(',','\,',$event['Event']['name'])."\r\n"; ?>
DESCRIPTION:<?php echo str_replace(',','\,',str_replace("\r\n", '\n', $event['Event']['notes']))."\r\n"; ?>
URL;VALUE=URI:<?php echo Router::url(array('controller'=>'events', 'action'=>'view', $event['Venue']['City']['Country']['slug'], $event['Venue']['City']['slug'], $event['Venue']['slug'], $event['Event']['slug']), true)."\r\n"; ?>
UID:<?php echo Router::url(array('controller'=>'events', 'action'=>'view', $event['Venue']['City']['Country']['slug'], $event['Venue']['City']['slug'], $event['Venue']['slug'], $event['Event']['slug']), true)."\r\n"; ?>
DTSTAMP:<?php echo date("Ymd\THis", strtotime($event['Event']['created']))."\r\n"; ?>
LAST-MODIFIED:<?php echo date("Ymd\THis", strtotime($event['Event']['modified']))."\r\n"; ?>
CATEGORIES:<?php echo $event['Category']['name']."\r\n"; ?>
STATUS:<?php echo $event['Event']['status']."\r\n"; ?>
ORGANIZER;CN=<?php echo $event['User']['username']; ?>:X-ADDR:<?php echo Router::url(array('controller'=>'users', 'action'=>'view', $event['User']['slug']), true)."\r\n"; ?>
LOCATION:<?php echo str_replace(',','\,',$event['Venue']['address']).'\, '.$event['Venue']['City']['name'].'. '.$event['Venue']['City']['Country']['name']."\r\n"; ?>
END:VEVENT<?php echo "\r\n"; ?>
END:VCALENDAR<?php echo "\r\n"; ?>
