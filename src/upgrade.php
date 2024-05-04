<?php

if (file_exists(__DIR__ . '/SSI.php') && ! defined('SMF')) {
	require_once __DIR__ . '/SSI.php';
} elseif (! defined('SMF')) {
	die('<b>Error:</b> Cannot install - please verify that you put this file in the same place as SMF\'s index.php and SSI.php files.');
}

if ((SMF === 'SSI') && ! $user_info['is_admin']) {
	die('Admin privileges required.');
}

db_extend('packages');

// Rename `alias` to `slug`
$smcFunc['db_change_column']('{db_prefix}lp_pages', 'alias', [
	'name' => 'slug'
]);

clean_cache();

if (SMF === 'SSI') {
	echo 'Database changes are complete! Please wait...';
}
