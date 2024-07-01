<?php

global $user_info, $smcFunc, $db_prefix;

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
	'name' => 'slug',
]);

// Rename `title` to `value`
$smcFunc['db_change_column']('{db_prefix}lp_titles', 'title', [
	'name' => 'value',
]);

// Change sizes of fields
$smcFunc['db_change_column']('{db_prefix}lp_params', 'type', [
	'size' => 30,
]);

$smcFunc['db_change_column']('{db_prefix}lp_titles', 'type', [
	'size' => 30,
]);

// Rename `lp_page_tags` table to `lp_page_tag`
$smcFunc['db_query']('', '
	ALTER TABLE IF EXISTS {raw:old_table_name}
	RENAME TO {raw:new_table_name}',
	[
		'old_table_name' => str_replace('{db_prefix}', $db_prefix, '{db_prefix}lp_page_tags'),
		'new_table_name' => str_replace('{db_prefix}', $db_prefix, '{db_prefix}lp_page_tag'),
	]
);

// Replace indexes
$smcFunc['db_remove_index']('{db_prefix}lp_params', 'primary');

$smcFunc['db_add_column'](
	'{db_prefix}lp_params',
	[
		'name'     => 'id',
		'type'     => 'int',
		'size'     => 10,
		'unsigned' => true,
		'auto'     => true
	],
	[],
	'do_nothing'
);

$smcFunc['db_add_index']('{db_prefix}lp_params', [
	'type' => 'unique',
	'columns' => [
		'item_id', 'type', 'name',
	]
]);

$smcFunc['db_remove_index']('{db_prefix}lp_plugins', 'primary');

$smcFunc['db_add_column'](
	'{db_prefix}lp_plugins',
	[
		'name'     => 'id',
		'type'     => 'int',
		'size'     => 10,
		'unsigned' => true,
		'auto'     => true
	],
	[],
	'do_nothing'
);

$smcFunc['db_add_index']('{db_prefix}lp_plugins', [
	'type' => 'unique',
	'columns' => [
		'name', 'config',
	]
]);

$smcFunc['db_remove_index']('{db_prefix}lp_titles', 'primary');

$smcFunc['db_add_column'](
	'{db_prefix}lp_titles',
	[
		'name'     => 'id',
		'type'     => 'int',
		'size'     => 10,
		'unsigned' => true,
		'auto'     => true
	],
	[],
	'do_nothing'
);

$smcFunc['db_add_index']('{db_prefix}lp_titles', [
	'type' => 'unique',
	'columns' => [
		'item_id', 'type', 'lang',
	]
]);

clean_cache();

if (SMF === 'SSI') {
	echo 'Database changes are complete! Please wait...';
}
