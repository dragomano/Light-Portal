<?php

if (! defined('SMF'))
	die('No direct access...');

// Autoloading function
spl_autoload_register(function ($classname) {
	if (strpos($classname, 'Bugo\LightPortal') === false)
		return false;

	$classname = str_replace('\\', '/', str_replace('Bugo\LightPortal\\', '', $classname));
	$file_path = __DIR__ . '/' . $classname . '.php';

	if (! file_exists($file_path))
		return false;

	require_once $file_path;
});

// Portal initialization
$portal = new \Bugo\LightPortal\Integration();
$portal->hooks();
