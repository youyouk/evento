<?php
/**
 * default settings for the install plugin
 */
$installSettings = array(
	// database default configuration
	'database_defaults' => array(
		'host' => 'localhost',
		'database' => 'evento'
	),
	// writable files and directories
	'is_writable' => array(
		IMAGES . 'users',
		IMAGES . 'users/small',
		IMAGES . 'events',
		IMAGES . 'events/small',
		IMAGES . 'logos',
		IMAGES . 'logos/small',
		IMAGES . 'logos/big'
	),
);
?>