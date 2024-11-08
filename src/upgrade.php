<?php

global $user_info, $smcFunc;

if (file_exists(__DIR__ . '/SSI.php') && ! defined('SMF')) {
	require_once __DIR__ . '/SSI.php';
} elseif (! defined('SMF')) {
	die('<b>Error:</b> Cannot install - please verify that you put this file in the same place as SMF\'s index.php and SSI.php files.');
}

if ((SMF === 'SSI') && ! $user_info['is_admin']) {
	die('Admin privileges required.');
}

db_extend('packages');

// Add `entry_type` to `lp_pages`
$smcFunc['db_add_column'](
	'{db_prefix}lp_pages',
	[
		'name'    => 'entry_type',
		'type'    => 'varchar',
		'size'    => 10,
		'default' => 'default',
		'null'    => false
	],
	[],
	'do_nothing'
);

// Update entries
$result = $smcFunc['db_query']('', /** @lang text */ '
	SELECT page_id AS id, status FROM {db_prefix}lp_pages
	WHERE status >= 3',
	[]
);

$blog_pages = $internal_pages = [];
while ($row = $smcFunc['db_fetch_assoc']($result)) {
	if ($row['status'] === '3') {
		$internal_pages[] = $row['id'];
	} elseif ($row['status'] === '4') {
		$blog_pages[] = $row['id'];
	}
}

$smcFunc['db_free_result']($result);

if ($internal_pages) {
	$smcFunc['db_query']('', /** @lang text */ '
		UPDATE {db_prefix}lp_pages
		SET entry_type = {literal:internal}, status = 1
		WHERE page_id IN ({array_int:pages})',
		[
			'pages' => $internal_pages,
		]
	);
}

if ($blog_pages) {
	$smcFunc['db_query']('', /** @lang text */ '
		UPDATE {db_prefix}lp_pages
		SET entry_type = {literal:blog}, status = 1
		WHERE page_id IN ({array_int:pages})',
		[
			'pages' => $blog_pages,
		]
	);
}

clean_cache();

if (SMF === 'SSI') {
	echo 'Database changes are complete! Please wait...';
}
