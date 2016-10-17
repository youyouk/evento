<?php
foreach($users as $user) {
	$u = $user['User']['username'] . ',' . $user['User']['email'];

	if($user['User']['web']) $u .= ',' . $user['User']['web'];
	else $u .= ',""';

	if($user['City']['name']) $u .= ',' . $user['City']['name'];
	else $u .= ',""';

	if($user['Country']['name']) $u .= ',' . __($user['Country']['name']);
	else $u .= ',""';

	echo $u . "\r\n";
}
?>