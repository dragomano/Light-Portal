<?php

global $user_info, $language, $mbname, $modSettings, $settings, $smcFunc, $context;

if (version_compare(PHP_VERSION, '8.1', '<')) {
	die('This mod needs PHP 8.1 or greater. You will not be able to install/use this mod. Please, contact your host and ask for a php upgrade.');
}

if (! extension_loaded('intl')) {
	die('This mod needs intl extension to properly work with plurals, locale-aware numbers, and much more. Contact your host or install this extension by manual.');
}

if (file_exists(__DIR__ . '/SSI.php') && ! defined('SMF')) {
	require_once __DIR__ . '/SSI.php';
} elseif (! defined('SMF')) {
	die('<b>Error:</b> Cannot install - please verify that you put this file in the same place as SMF\'s index.php and SSI.php files.');
}

if ((SMF === 'SSI') && ! $user_info['is_admin']) {
	die('Admin privileges required.');
}

$content = '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nunc porttitor posuere accumsan. Aliquam erat volutpat. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Phasellus vel blandit dui. Aliquam nunc est, vehicula sit amet eleifend in, scelerisque quis sem. In aliquam nec lorem nec volutpat. Sed eu blandit erat. Suspendisse elementum lectus a ligula commodo, at lobortis justo accumsan. Aliquam mollis lectus ultricies, semper urna eu, fermentum eros. Sed a interdum odio. Quisque sit amet feugiat enim. Curabitur aliquam lectus at metus tristique tempus. Sed vitae nisi ultricies, tincidunt lacus non, ultrices ante.</p><p><br></p>
<p>Duis ac ex sed dolor suscipit vulputate at eu ligula. Aliquam efficitur ac ante convallis ultricies. Nullam pretium vitae purus dapibus tempor. Aenean vel fringilla eros. Proin lectus velit, tristique ut condimentum eu, semper sed ipsum. Duis venenatis dolor lectus, et ullamcorper tortor varius eu. Vestibulum quis nisi ut nunc mollis fringilla. Sed consectetur semper magna, eget blandit nulla commodo sed. Aenean sem ipsum, auctor eget enim id, scelerisque malesuada nibh. Nulla ornare pharetra laoreet. Phasellus dignissim nisl nec arcu cursus luctus.</p><p><br></p>
<p>Aliquam in quam ut diam consectetur semper. Aliquam commodo mi purus, bibendum laoreet massa tristique eget. Suspendisse ut purus nisi. Mauris euismod dolor nec scelerisque ullamcorper. Praesent imperdiet semper neque, ac luctus nunc ultricies eget. Praesent sodales ante sed dignissim vulputate. Ut vel ligula id sem feugiat sollicitudin non at metus. Aliquam vel est non sapien sodales semper. Suspendisse potenti. Sed convallis quis turpis eu pulvinar. Vivamus nulla elit, condimentum vitae commodo eu, pellentesque ullamcorper enim. Maecenas faucibus dolor nec enim interdum, quis iaculis lacus suscipit. Pellentesque aliquam, lectus id volutpat euismod, ante tellus mollis dui, sed placerat erat arcu sit amet purus.</p>';

$tables[] = [
	'name' => 'lp_blocks',
	'columns' => [
		[
			'name'     => 'block_id',
			'type'     => 'smallint',
			'size'     => 5,
			'unsigned' => true,
			'auto'     => true
		],
		[
			'name' => 'icon',
			'type' => 'varchar',
			'size' => 255,
			'null' => true
		],
		[
			'name' => 'type',
			'type' => 'varchar',
			'size' => 30,
			'null' => false
		],
		[
			'name' => 'placement',
			'type' => 'varchar',
			'size' => 10,
			'null' => false
		],
		[
			'name'     => 'priority',
			'type'     => 'tinyint',
			'size'     => 1,
			'unsigned' => true,
			'default'  => 0
		],
		[
			'name'     => 'permissions',
			'type'     => 'tinyint',
			'size'     => 1,
			'unsigned' => true,
			'default'  => 0
		],
		[
			'name'     => 'status',
			'type'     => 'tinyint',
			'size'     => 1,
			'unsigned' => true,
			'default'  => 1
		],
		[
			'name'    => 'areas',
			'type'    => 'varchar',
			'size'    => 255,
			'default' => 'all',
			'null'    => false
		],
		[
			'name' => 'title_class',
			'type' => 'varchar',
			'size' => 255,
			'null' => true
		],
		[
			'name' => 'content_class',
			'type' => 'varchar',
			'size' => 255,
			'null' => true
		]
	],
	'indexes' => [
		[
			'type'    => 'primary',
			'columns' => ['block_id']
		]
	]
];

$tables[] = [
	'name' => 'lp_categories',
	'columns' => [
		[
			'name'     => 'category_id',
			'type'     => 'smallint',
			'size'     => 5,
			'unsigned' => true,
			'auto'     => true
		],
		[
			'name'     => 'parent_id',
			'type'     => 'int',
			'size'     => 10,
			'unsigned' => true,
			'default'  => 0
		],
		[
			'name' => 'slug',
			'type' => 'varchar',
			'size' => 255,
			'null' => false
		],
		[
			'name' => 'icon',
			'type' => 'varchar',
			'size' => 255,
			'null' => true
		],
		[
			'name'     => 'priority',
			'type'     => 'tinyint',
			'size'     => 1,
			'unsigned' => true,
			'default'  => 0
		],
		[
			'name'     => 'status',
			'type'     => 'tinyint',
			'size'     => 1,
			'unsigned' => true,
			'default'  => 1
		],
	],
	'indexes' => [
		[
			'type'    => 'primary',
			'columns' => ['category_id']
		],
		[
			'type'    => 'unique',
			'columns' => ['slug']
		]
	]
];

$tables[] = [
	'name' => 'lp_comments',
	'columns' => [
		[
			'name'     => 'id',
			'type'     => 'int',
			'size'     => 10,
			'unsigned' => true,
			'auto'     => true
		],
		[
			'name'     => 'parent_id',
			'type'     => 'int',
			'size'     => 10,
			'unsigned' => true,
			'default'  => 0
		],
		[
			'name'     => 'page_id',
			'type'     => 'smallint',
			'size'     => 5,
			'unsigned' => true
		],
		[
			'name'     => 'author_id',
			'type'     => 'mediumint',
			'size'     => 8,
			'unsigned' => true
		],
		[
			'name' => 'message',
			'type' => 'text',
			'null' => false
		],
		[
			'name'     => 'created_at',
			'type'     => 'int',
			'size'     => 10,
			'unsigned' => true,
			'default'  => 0
		]
	],
	'indexes' => [
		[
			'type'    => 'primary',
			'columns' => ['id']
		]
	]
];

$tables[] = [
	'name'    => 'lp_page_tag',
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

$tables[] = [
	'name' => 'lp_pages',
	'columns' => [
		[
			'name'     => 'page_id',
			'type'     => 'int',
			'size'     => 10,
			'unsigned' => true,
			'auto'     => true
		],
		[
			'name'     => 'category_id',
			'type'     => 'int',
			'size'     => 10,
			'unsigned' => true,
			'default'  => 0
		],
		[
			'name'     => 'author_id',
			'type'     => 'mediumint',
			'size'     => 8,
			'unsigned' => true,
			'default'  => 0
		],
		[
			'name' => 'slug',
			'type' => 'varchar',
			'size' => 255,
			'null' => false
		],
		[
			'name'    => 'type',
			'type'    => 'varchar',
			'size'    => 10,
			'default' => 'bbc',
			'null'    => false
		],
		[
			'name'    => 'entry_type',
			'type'    => 'varchar',
			'size'    => 10,
			'default' => 'default',
			'null'    => false
		],
		[
			'name'     => 'permissions',
			'type'     => 'tinyint',
			'size'     => 1,
			'unsigned' => true,
			'default'  => 0
		],
		[
			'name'     => 'status',
			'type'     => 'tinyint',
			'size'     => 1,
			'unsigned' => true,
			'default'  => 1
		],
		[
			'name'     => 'num_views',
			'type'     => 'int',
			'size'     => 10,
			'unsigned' => true,
			'default'  => 0
		],
		[
			'name'     => 'num_comments',
			'type'     => 'int',
			'size'     => 10,
			'unsigned' => true,
			'default'  => 0
		],
		[
			'name'     => 'created_at',
			'type'     => 'int',
			'size'     => 10,
			'unsigned' => true,
			'default'  => 0
		],
		[
			'name'     => 'updated_at',
			'type'     => 'int',
			'size'     => 10,
			'unsigned' => true,
			'default'  => 0
		],
		[
			'name'     => 'deleted_at',
			'type'     => 'int',
			'size'     => 10,
			'unsigned' => true,
			'default'  => 0
		],
		[
			'name'     => 'last_comment_id',
			'type'     => 'int',
			'size'     => 10,
			'unsigned' => true,
			'default'  => 0
		]
	],
	'indexes' => [
		[
			'type'    => 'primary',
			'columns' => ['page_id']
		],
		[
			'type'    => 'unique',
			'columns' => ['slug']
		]
	],
	'default' => [
		'columns' => [
			'page_id'     => 'int',
			'author_id'   => 'int',
			'slug'        => 'string-255',
			'type'        => 'string',
			'permissions' => 'int',
			'created_at'  => 'int'
		],
		'values' => [
			[
				1, $user_info['id'], 'home', 'html', 3, time()
			]
		],
		'keys' => ['page_id']
	]
];

$tables[] = [
	'name' => 'lp_params',
	'columns' => [
		[
			'name'     => 'id',
			'type'     => 'int',
			'size'     => 10,
			'unsigned' => true,
			'auto'     => true
		],
		[
			'name'     => 'item_id',
			'type'     => 'int',
			'size'     => 10,
			'unsigned' => true
		],
		[
			'name'    => 'type',
			'type'    => 'varchar',
			'size'    => 30,
			'default' => 'block',
			'null'    => false
		],
		[
			'name' => 'name',
			'type' => 'varchar',
			'size' => 255,
			'null' => false
		],
		[
			'name' => 'value',
			'type' => 'text',
			'null' => false
		]
	],
	'indexes' => [
		[
			'type'    => 'primary',
			'columns' => ['id']
		],
		[
			'type'    => 'unique',
			'columns' => ['item_id', 'type', 'name']
		]
	],
	'default' => [
		'columns' => [
			'item_id' => 'int',
			'type'    => 'string-10',
			'name'    => 'string-255',
			'value'   => 'string'
		],
		'values' => [
			[1, 'page', 'show_author_and_date', 0]
		],
		'keys' => ['item_id', 'type', 'name']
	]
];

$tables[] = [
	'name' => 'lp_plugins',
	'columns' => [
		[
			'name'     => 'id',
			'type'     => 'int',
			'size'     => 10,
			'unsigned' => true,
			'auto'     => true
		],
		[
			'name' => 'name',
			'type' => 'varchar',
			'size' => 100,
			'null' => false
		],
		[
			'name' => 'config',
			'type' => 'varchar',
			'size' => 100,
			'null' => false
		],
		[
			'name' => 'value',
			'type' => 'text',
			'null' => true
		]
	],
	'indexes' => [
		[
			'type'    => 'primary',
			'columns' => ['id']
		],
		[
			'type'    => 'unique',
			'columns' => ['name', 'config']
		]
	],
	'default' => [
		'columns' => [
			'name'   => 'string',
			'config' => 'string',
			'value'  => 'string'
		],
		'values' => [
			['hello_portal', 'keyboard_navigation', '1'],
			['hello_portal', 'show_buttons', '1'],
			['hello_portal', 'show_progress', '1'],
			['hello_portal', 'theme', 'flattener'],
		],
		'keys' => ['name', 'config']
	]
];

$tables[] = [
	'name' => 'lp_tags',
	'columns' => [
		[
			'name'     => 'tag_id',
			'type'     => 'int',
			'size'     => 10,
			'unsigned' => true,
			'auto'     => true
		],
		[
			'name' => 'icon',
			'type' => 'varchar',
			'size' => 255,
			'null' => true
		],
		[
			'name'     => 'status',
			'type'     => 'tinyint',
			'size'     => 1,
			'unsigned' => true,
			'default'  => 1
		],
	],
	'indexes' => [
		[
			'type'    => 'primary',
			'columns' => ['tag_id']
		]
	]
];

$tables[] = [
	'name' => 'lp_translations',
	'columns' => [
		[
			'name'     => 'id',
			'type'     => 'int',
			'size'     => 10,
			'unsigned' => true,
			'auto'     => true
		],
		[
			'name'     => 'item_id',
			'type'     => 'int',
			'size'     => 10,
			'unsigned' => true
		],
		[
			'name'    => 'type',
			'type'    => 'varchar',
			'size'    => 30,
			'default' => 'block',
			'null'    => false
		],
		[
			'name' => 'lang',
			'type' => 'varchar',
			'size' => 20,
			'null' => false
		],
		[
			'name' => 'title',
			'type' => 'varchar',
			'size' => 255,
			'null' => true
		],
		[
			'name' => 'content',
			'type' => 'mediumtext',
			'null' => true,
		],
		[
			'name' => 'description',
			'type' => 'varchar',
			'size' => 510,
			'null' => true
		],
	],
	'indexes' => [
		[
			'type'    => 'primary',
			'columns' => ['id']
		],
		[
			'type'    => 'unique',
			'columns' => ['item_id', 'type', 'lang']
		],
		[
			'type'    => 'index',
			'columns' => ['type', 'item_id', 'lang']
		]
	],
	'default' => [
		'columns' => [
			'item_id' => 'int',
			'type'    => 'string-10',
			'lang'    => 'string-20',
			'title'   => 'string-255',
			'content' => 'string',
		],
		'values' => [1, 'page', $language, $mbname, $content],
		'keys' => ['item_id', 'type', 'lang']
	]
];

db_extend('packages');

foreach ($tables as $table) {
	$smcFunc['db_create_table']('{db_prefix}' . $table['name'], $table['columns'], $table['indexes']);

	if (isset($table['default'])) {
		$smcFunc['db_insert']('ignore', '{db_prefix}' . $table['name'], $table['default']['columns'], $table['default']['values'], $table['default']['keys']);
	}
}

$smcFunc['db_query']('', '
	DELETE FROM {db_prefix}background_tasks
	WHERE task_file LIKE {string:task_file}',
	[
		'task_file' => '%$sourcedir/LightPortal%'
	]
);

$addSettings = ['lp_weekly_cleaning' => '0'];
if (! isset($modSettings['lp_enabled_plugins']))
	$addSettings['lp_enabled_plugins'] = 'CodeMirror,HelloPortal,ThemeSwitcher,UserInfo';
if (! isset($modSettings['lp_frontpage_layout']))
	$addSettings['lp_frontpage_layout'] = 'default.blade.php';
if (! isset($modSettings['lp_comment_block']))
	$addSettings['lp_comment_block'] = 'none';
if (! isset($modSettings['lp_permissions_default']))
	$addSettings['lp_permissions_default'] = '0';
if (! isset($modSettings['lp_fa_source']))
	$addSettings['lp_fa_source'] = 'css_cdn';
updateSettings($addSettings);

if (! @is_writable($layouts = $settings['default_theme_dir'] . '/LightPortal'))
	smf_chmod($layouts);
if (! @is_writable($langs = $settings['default_theme_dir'] . '/languages/LightPortal'))
	smf_chmod($langs);
if (! @is_writable($css_dir = $settings['default_theme_dir'] . '/css/light_portal'))
	smf_chmod($css_dir);
if (! @is_writable($scripts = $settings['default_theme_dir'] . '/scripts/light_portal'))
	smf_chmod($scripts);

$context['lp_num_queries'] ??= 0;

if (SMF === 'SSI') {
	echo 'Database changes are complete! Please wait...';
}
