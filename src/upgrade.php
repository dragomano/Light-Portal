<?php

global $user_info, $smcFunc, $db_prefix, $language;

if (file_exists(__DIR__ . '/SSI.php') && ! defined('SMF')) {
	require_once __DIR__ . '/SSI.php';
} elseif (! defined('SMF')) {
	die('<b>Error:</b> Cannot install - please verify that you put this file in the same place as SMF\'s index.php and SSI.php files.');
}

if ((SMF === 'SSI') && ! $user_info['is_admin']) {
	die('Admin privileges required.');
}

db_extend('packages');

if (empty($smcFunc['db_list_tables'](false, $db_prefix . 'lp_titles')))
	return;

// Add columns
$smcFunc['db_add_column'](
	'{db_prefix}lp_titles',
	[
		'name' => 'content',
		'type' => 'mediumtext',
		'null' => true,
	],
	[],
	'do_nothing'
);

$smcFunc['db_add_column'](
	'{db_prefix}lp_titles',
	[
		'name' => 'description',
		'type' => 'varchar',
		'size' => 510,
		'null' => true,
	],
	[],
	'do_nothing'
);

$smcFunc['db_change_column'](
	'{db_prefix}lp_titles',
	'value',
	[
		'name' => 'title',
		'null' => true,
	]
);

$smcFunc['db_add_column'](
	'{db_prefix}lp_categories',
	[
		'name' => 'slug',
		'type' => 'varchar',
		'size' => 255,
		'null' => false,
	],
	[],
	'do_nothing'
);

$smcFunc['db_add_column'](
	'{db_prefix}lp_categories',
	[
		'name'     => 'parent_id',
		'type'     => 'int',
		'size'     => 10,
		'unsigned' => true,
		'default'  => 0,
	],
	[],
	'do_nothing'
);

// Copy page data
$colData = $smcFunc['db_list_columns']('{db_prefix}lp_pages', true);

if (isset($colData['content'])) {
	$result = $smcFunc['db_query']('', /** @lang text */ '
		SELECT page_id, COALESCE(content, "") AS content, COALESCE(description, "") AS description
		FROM {db_prefix}lp_pages',
		[]
	);

	$pages = [];
	while ($row = $smcFunc['db_fetch_assoc']($result)) {
		$pages[] = [
			'item_id'     => $row['page_id'],
			'type'        => 'page',
			'lang'        => $language,
			'content'     => $row['content'],
			'description' => $row['description'],
		];
	}

	$smcFunc['db_free_result']($result);

	if (! empty($pages)) {
		$smcFunc['db_insert']('replace',
			'{db_prefix}lp_titles',
			[
				'item_id'     => 'int',
				'type'        => 'string',
				'lang'        => 'string',
				'content'     => 'string',
				'description' => 'string',
			],
			$pages,
			['item_id', 'type', 'lang'],
		);
	}

	$smcFunc['db_remove_column']('{db_prefix}lp_pages', 'content');
	$smcFunc['db_remove_column']('{db_prefix}lp_pages', 'description');
}

// Copy block data
$colData = $smcFunc['db_list_columns']('{db_prefix}lp_blocks', true);

if (isset($colData['content'])) {
	$result = $smcFunc['db_query']('', /** @lang text */ '
		SELECT block_id, COALESCE(content, "") AS content, COALESCE(note, "") AS description
		FROM {db_prefix}lp_blocks',
		[]
	);

	$blocks = [];
	while ($row = $smcFunc['db_fetch_assoc']($result)) {
		$blocks[] = [
			'item_id'     => $row['block_id'],
			'type'        => 'block',
			'lang'        => $language,
			'content'     => $row['content'],
			'description' => $row['description'],
		];
	}

	$smcFunc['db_free_result']($result);

	if (! empty($blocks)) {
		$smcFunc['db_insert']('replace',
			'{db_prefix}lp_titles',
			[
				'item_id'     => 'int',
				'type'        => 'string',
				'lang'        => 'string',
				'content'     => 'string',
				'description' => 'string',
			],
			$blocks,
			['item_id', 'type', 'lang'],
		);
	}

	$smcFunc['db_remove_column']('{db_prefix}lp_blocks', 'content');
	$smcFunc['db_remove_column']('{db_prefix}lp_blocks', 'note');

}

// Copy category data
$colData = $smcFunc['db_list_columns']('{db_prefix}lp_categories', true);

if (isset($colData['description'])) {
	$result = $smcFunc['db_query']('', /** @lang text */ '
		SELECT category_id, COALESCE(description, "") AS description
		FROM {db_prefix}lp_categories',
		[]
	);

	$categories = [];
	while ($row = $smcFunc['db_fetch_assoc']($result)) {
		$categories[] = [
			'item_id'     => $row['category_id'],
			'type'        => 'category',
			'lang'        => $language,
			'description' => $row['description'],
		];
	}

	$smcFunc['db_free_result']($result);

	if (! empty($categories)) {
		$smcFunc['db_insert']('replace',
			'{db_prefix}lp_titles',
			[
				'item_id'     => 'int',
				'type'        => 'string',
				'lang'        => 'string',
				'description' => 'string',
			],
			$categories,
			['item_id', 'type', 'lang'],
		);
	}

	$smcFunc['db_remove_column']('{db_prefix}lp_categories', 'description');
}

// Rename lp_titles to lp_translations
$smcFunc['db_query']('', 'RENAME TABLE ' . $db_prefix . 'lp_titles TO ' . $db_prefix . 'lp_translations', []);

// Add new indexes
$smcFunc['db_add_index'](
	'{db_prefix}lp_translations',
	[
		'name'    => 'idx_translations_entity',
		'type'    => 'index',
		'columns' => ['type', 'item_id', 'lang'],
	]
);

$smcFunc['db_query']('', 'CREATE INDEX title_prefix ON {db_prefix}lp_translations (title(100))', []);

clean_cache();

if (SMF === 'SSI') {
	echo 'Database changes are complete! Please wait...';
}
