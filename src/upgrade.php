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

// Add an icon column
$column = [
	'name' => 'icon',
	'type' => 'varchar',
	'size' => 255,
	'null' => true
];

$smcFunc['db_add_column']('{db_prefix}lp_categories', $column, [], 'ignore');

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

// Create lp_page_tags table
$table = [
	'name'    => 'lp_page_tags',
	'columns' => [
		[
			'name'     => 'page_id',
			'type'     => 'int',
			'size'     => 10,
			'unsigned' => true
		],
		[
			'name'     => 'tag_id',
			'type'     => 'int',
			'size'     => 10,
			'unsigned' => true
		]
	],
	'indexes' => [
		[
			'type'    => 'primary',
			'columns' => ['page_id', 'tag_id']
		]
	]
];

db_extend('packages');

$smcFunc['db_create_table']('{db_prefix}' . $table['name'], $table['columns'], $table['indexes'], [], 'update');

// Fetch tags
$result = $smcFunc['db_query']('', '
	SELECT tag_id, value
	FROM {db_prefix}lp_tags',
	[]
);

$tags = [];
while ($row = $smcFunc['db_fetch_assoc']($result)) {
	$tags[$row['tag_id']] = $row['value'];
}

$smcFunc['db_free_result']($result);

// Insert tag titles to lp_titles table
if ($tags !== []) {
	$titles = [];

	foreach ($tags as $id => $name) {
		$titles[] = [
			'item_id' => $id,
			'type'    => 'tag',
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

// Add an icon column
$column = [
	'name' => 'icon',
	'type' => 'varchar',
	'size' => 255,
	'null' => true
];

$smcFunc['db_add_column']('{db_prefix}lp_tags', $column, [], 'ignore');

// Add a status column
$column = [
	'name'     => 'status',
	'type'     => 'tinyint',
	'size'     => 1,
	'unsigned' => true,
	'default'  => 1
];

$smcFunc['db_add_column']('{db_prefix}lp_tags', $column, [], 'ignore');

// Drop a value column
$smcFunc['db_remove_column']('{db_prefix}lp_tags', 'value');

// Migrate keywords from lp_params to lp_page_tags table
$result = $smcFunc['db_query']('', '
	SELECT item_id, value
	FROM {db_prefix}lp_params
	WHERE type = {literal:page} AND name = {literal:keywords}',
	[]
);

$keywords = [];
while ($row = $smcFunc['db_fetch_assoc']($result)) {
	$keywords[$row['item_id']] = $row['value'];
}

$smcFunc['db_free_result']($result);

if ($keywords) {
	$values = [];

	foreach ($keywords as $pageId => $value) {
		$tags = explode(',', $value);

		foreach ($tags as $tagId) {
			$values[] = [
				'page_id' => $pageId,
				'tag_id'  => $tagId,
			];
		}
	}

	if ($values !== []) {
		$smcFunc['db_insert']('',
			'{db_prefix}lp_page_tags',
			[
				'page_id' => 'int',
				'tag_id'  => 'int',
			],
			$values,
			['page_id', 'tag_id']
		);
	}
}

// Delete deprecated values from lp_params table
$smcFunc['db_query']('', '
	DELETE FROM {db_prefix}lp_params
	WHERE type = {literal:page} AND name = {literal:keywords}',
	[]
);

if (SMF === 'SSI') {
	echo 'Database changes are complete! Please wait...';
}
