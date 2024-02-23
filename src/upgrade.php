<?php

if (file_exists(__DIR__ . '/SSI.php') && ! defined('SMF')) {
	require_once __DIR__ . '/SSI.php';
} elseif (! defined('SMF')) {
	die('<b>Error:</b> Cannot install - please verify that you put this file in the same place as SMF\'s index.php and SSI.php files.');
}

if ((SMF === 'SSI') && ! $user_info['is_admin']) {
	die('Admin privileges required.');
}

// Fetch category names
$result = $smcFunc['db_query']('', '
	SELECT category_id, name
	FROM {db_prefix}lp_categories',
	[]
);

$categories = [];
while ($row = $smcFunc['db_fetch_assoc']($result)) {
	$categories[$row['category_id']] = $row['name'];
}

$smcFunc['db_free_result']($result);

// Insert category names to lp_titles table
if ($categories !== []) {
	$titles = [];

	foreach ($categories as $id => $name) {
		$titles[] = [
			'item_id' => $id,
			'type'    => 'category',
			'lang'    => $user_info['language'],
			'title'   => $name,
		];
	}

	if ($titles !== []) {
		$smcFunc['db_insert']('',
			'{db_prefix}lp_titles',
			[
				'item_id' => 'int',
				'type'    => 'string',
				'lang'    => 'string',
				'title'   => 'string',
			],
			$titles,
			['item_id', 'type', 'lang']
		);
	}
}

// Add a status column
$column = [
	'name'     => 'status',
	'type'     => 'tinyint',
	'size'     => 1,
	'unsigned' => true,
	'default'  => 1
];

$smcFunc['db_add_column']('{db_prefix}lp_categories', $column, [], 'ignore');

// Drop a name column
$smcFunc['db_remove_column']('{db_prefix}lp_categories', 'name');

if (SMF === 'SSI') {
	echo 'Database changes are complete! Please wait...';
}
