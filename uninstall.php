<?php

if (file_exists(__DIR__ . '/SSI.php') && ! defined('SMF'))
	require_once __DIR__ . '/SSI.php';
elseif(! defined('SMF'))
	die('<b>Error:</b> Cannot install - please verify that you put this file in the same place as SMF\'s index.php and SSI.php files.');

if ((SMF === 'SSI') && ! $user_info['is_admin'])
	die('Admin privileges required.');

$smcFunc['db_query']('', '
	DELETE FROM {db_prefix}background_tasks
	WHERE task_file LIKE {string:task_file}',
	[
		'task_file' => '%$sourcedir/LightPortal%'
	]
);

if (SMF === 'SSI')
	echo 'Database changes are complete! Please wait...';
