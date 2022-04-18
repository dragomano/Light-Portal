<?php

if (file_exists(dirname(__FILE__) . '/SSI.php') && ! defined('SMF'))
	require_once(dirname(__FILE__) . '/SSI.php');
elseif(! defined('SMF'))
	die('<b>Error:</b> Cannot install - please verify that you put this file in the same place as SMF\'s index.php and SSI.php files.');

if (version_compare(PHP_VERSION, '7.4', '<'))
	die('This mod needs PHP 7.4 or greater. You will not be able to install/use this mod. Please, contact your host and ask for a php upgrade.');

if (! extension_loaded('intl'))
	die('This mod needs intl extension to properly work with plurals, locale-aware numbers, and much more. Contact your host or install this extension by manual.');

global $user_info, $mbname, $modSettings, $settings;

if ((SMF === 'SSI') && ! $user_info['is_admin'])
	die('Admin privileges required.');

$tables[] = array(
	'name' => 'lp_categories',
	'columns' => array(
		array(
			'name'     => 'category_id',
			'type'     => 'tinyint',
			'size'     => 3,
			'unsigned' => true,
			'auto'     => true
		),
		array(
			'name' => 'name',
			'type' => 'varchar',
			'size' => 255,
			'null' => false
		),
		array(
			'name' => 'description',
			'type' => 'varchar',
			'size' => 255,
			'null' => true
		),
		array(
			'name'     => 'priority',
			'type'     => 'tinyint',
			'size'     => 1,
			'unsigned' => true,
			'default'  => 0
		)
	),
	'indexes' => array(
		array(
			'type'    => 'primary',
			'columns' => array('category_id')
		)
	)
);

$tables[] = array(
	'name' => 'lp_blocks',
	'columns' => array(
		array(
			'name'     => 'block_id',
			'type'     => 'tinyint',
			'size'     => 3,
			'unsigned' => true,
			'auto'     => true
		),
		array(
			'name'     => 'user_id',
			'type'     => 'mediumint',
			'size'     => 8,
			'unsigned' => true,
			'default'  => 0
		),
		array(
			'name' => 'icon',
			'type' => 'varchar',
			'size' => 255,
			'null' => true
		),
		array(
			'name' => 'type',
			'type' => 'varchar',
			'size' => 255,
			'null' => false
		),
		array(
			'name' => 'note',
			'type' => 'varchar',
			'size' => 255,
			'null' => true
		),
		array(
			'name' => 'content',
			'type' => 'text',
			'null' => true
		),
		array(
			'name' => 'placement',
			'type' => 'varchar',
			'size' => 10,
			'null' => false
		),
		array(
			'name'     => 'priority',
			'type'     => 'tinyint',
			'size'     => 1,
			'unsigned' => true,
			'default'  => 0
		),
		array(
			'name'     => 'permissions',
			'type'     => 'tinyint',
			'size'     => 1,
			'unsigned' => true,
			'default'  => 0
		),
		array(
			'name'     => 'status',
			'type'     => 'tinyint',
			'size'     => 1,
			'unsigned' => true,
			'default'  => 1
		),
		array(
			'name'    => 'areas',
			'type'    => 'varchar',
			'size'    => 255,
			'default' => 'all',
			'null'    => false
		),
		array(
			'name' => 'title_class',
			'type' => 'varchar',
			'size' => 255,
			'null' => true
		),
		array(
			'name' => 'title_style',
			'type' => 'varchar',
			'size' => 255,
			'null' => true
		),
		array(
			'name' => 'content_class',
			'type' => 'varchar',
			'size' => 255,
			'null' => true
		),
		array(
			'name' => 'content_style',
			'type' => 'varchar',
			'size' => 255,
			'null' => true
		)
	),
	'indexes' => array(
		array(
			'type'    => 'primary',
			'columns' => array('block_id')
		)
	)
);

$tables[] = array(
	'name' => 'lp_comments',
	'columns' => array(
		array(
			'name'     => 'id',
			'type'     => 'int',
			'size'     => 10,
			'unsigned' => true,
			'auto'     => true
		),
		array(
			'name'     => 'parent_id',
			'type'     => 'int',
			'size'     => 10,
			'unsigned' => true,
			'default'  => 0
		),
		array(
			'name'     => 'page_id',
			'type'     => 'smallint',
			'size'     => 5,
			'unsigned' => true
		),
		array(
			'name'     => 'author_id',
			'type'     => 'mediumint',
			'size'     => 8,
			'unsigned' => true
		),
		array(
			'name' => 'message',
			'type' => 'text',
			'null' => false
		),
		array(
			'name'     => 'created_at',
			'type'     => 'int',
			'size'     => 10,
			'unsigned' => true,
			'default'  => 0
		)
	),
	'indexes' => array(
		array(
			'type'    => 'primary',
			'columns' => array('id')
		)
	)
);

$tables[] = array(
	'name' => 'lp_pages',
	'columns' => array(
		array(
			'name'     => 'page_id',
			'type'     => 'smallint',
			'size'     => 5,
			'unsigned' => true,
			'auto'     => true
		),
		array(
			'name'     => 'category_id',
			'type'     => 'int',
			'size'     => 10,
			'unsigned' => true,
			'default'  => 0
		),
		array(
			'name'     => 'author_id',
			'type'     => 'mediumint',
			'size'     => 8,
			'unsigned' => true,
			'default'  => 0
		),
		array(
			'name' => 'alias',
			'type' => 'varchar',
			'size' => 255,
			'null' => false
		),
		array(
			'name' => 'description',
			'type' => 'varchar',
			'size' => 255,
			'null' => true
		),
		array(
			'name' => 'content',
			'type' => 'mediumtext',
			'null' => false
		),
		array(
			'name'    => 'type',
			'type'    => 'varchar',
			'size'    => 10,
			'default' => 'bbc',
			'null'    => false
		),
		array(
			'name'     => 'permissions',
			'type'     => 'tinyint',
			'size'     => 1,
			'unsigned' => true,
			'default'  => 0
		),
		array(
			'name'     => 'status',
			'type'     => 'tinyint',
			'size'     => 1,
			'unsigned' => true,
			'default'  => 1
		),
		array(
			'name'     => 'num_views',
			'type'     => 'int',
			'size'     => 10,
			'unsigned' => true,
			'default'  => 0
		),
		array(
			'name'     => 'num_comments',
			'type'     => 'int',
			'size'     => 10,
			'unsigned' => true,
			'default'  => 0
		),
		array(
			'name'     => 'created_at',
			'type'     => 'int',
			'size'     => 10,
			'unsigned' => true,
			'default'  => 0
		),
		array(
			'name'     => 'updated_at',
			'type'     => 'int',
			'size'     => 10,
			'unsigned' => true,
			'default'  => 0
		)
	),
	'indexes' => array(
		array(
			'type'    => 'primary',
			'columns' => array('page_id')
		),
		array(
			'type'    => 'unique',
			'columns' => array('alias')
		)
	),
	'default' => array(
		'columns' => array(
			'page_id'     => 'int',
			'author_id'   => 'int',
			'alias'       => 'string-255',
			'content'     => 'string',
			'type'        => 'string',
			'permissions' => 'int',
			'created_at'  => 'int'
		),
		'values' => array(
			array(1, $user_info['id'], 'home', '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nunc porttitor posuere accumsan. Aliquam erat volutpat. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Phasellus vel blandit dui. Aliquam nunc est, vehicula sit amet eleifend in, scelerisque quis sem. In aliquam nec lorem nec volutpat. Sed eu blandit erat. Suspendisse elementum lectus a ligula commodo, at lobortis justo accumsan. Aliquam mollis lectus ultricies, semper urna eu, fermentum eros. Sed a interdum odio. Quisque sit amet feugiat enim. Curabitur aliquam lectus at metus tristique tempus. Sed vitae nisi ultricies, tincidunt lacus non, ultrices ante.</p><p><br></p>
			<p>Duis ac ex sed dolor suscipit vulputate at eu ligula. Aliquam efficitur ac ante convallis ultricies. Nullam pretium vitae purus dapibus tempor. Aenean vel fringilla eros. Proin lectus velit, tristique ut condimentum eu, semper sed ipsum. Duis venenatis dolor lectus, et ullamcorper tortor varius eu. Vestibulum quis nisi ut nunc mollis fringilla. Sed consectetur semper magna, eget blandit nulla commodo sed. Aenean sem ipsum, auctor eget enim id, scelerisque malesuada nibh. Nulla ornare pharetra laoreet. Phasellus dignissim nisl nec arcu cursus luctus.</p><p><br></p>
			<p>Aliquam in quam ut diam consectetur semper. Aliquam commodo mi purus, bibendum laoreet massa tristique eget. Suspendisse ut purus nisi. Mauris euismod dolor nec scelerisque ullamcorper. Praesent imperdiet semper neque, ac luctus nunc ultricies eget. Praesent sodales ante sed dignissim vulputate. Ut vel ligula id sem feugiat sollicitudin non at metus. Aliquam vel est non sapien sodales semper. Suspendisse potenti. Sed convallis quis turpis eu pulvinar. Vivamus nulla elit, condimentum vitae commodo eu, pellentesque ullamcorper enim. Maecenas faucibus dolor nec enim interdum, quis iaculis lacus suscipit. Pellentesque aliquam, lectus id volutpat euismod, ante tellus mollis dui, sed placerat erat arcu sit amet purus.</p>', 'html', 3, time())
		),
		'keys' => array('page_id')
	)
);

$tables[] = array(
	'name' => 'lp_params',
	'columns' => array(
		array(
			'name'     => 'item_id',
			'type'     => 'smallint',
			'size'     => 5,
			'unsigned' => true
		),
		array(
			'name'    => 'type',
			'type'    => 'varchar',
			'size'    => 10,
			'default' => 'block',
			'null'    => false
		),
		array(
			'name' => 'name',
			'type' => 'varchar',
			'size' => 255,
			'null' => false
		),
		array(
			'name' => 'value',
			'type' => 'text',
			'null' => false
		)
	),
	'indexes' => array(
		array(
			'type'    => 'primary',
			'columns' => array('item_id', 'type', 'name')
		)
	),
	'default' => array(
		'columns' => array(
			'item_id' => 'int',
			'type'    => 'string-10',
			'name'    => 'string-255',
			'value'   => 'string'
		),
		'values' => array(
			array(1, 'page', 'show_author_and_date', 0)
		),
		'keys' => array('item_id', 'type', 'name')
	)
);

$tables[] = array(
	'name' => 'lp_tags',
	'columns' => array(
		array(
			'name'     => 'tag_id',
			'type'     => 'smallint',
			'size'     => 5,
			'unsigned' => true,
			'auto'     => true
		),
		array(
			'name' => 'value',
			'type' => 'varchar',
			'size' => 255,
			'null' => false
		)
	),
	'indexes' => array(
		array(
			'type'    => 'primary',
			'columns' => array('tag_id')
		)
	)
);

$tables[] = array(
	'name' => 'lp_titles',
	'columns' => array(
		array(
			'name'     => 'item_id',
			'type'     => 'smallint',
			'size'     => 5,
			'unsigned' => true
		),
		array(
			'name'    => 'type',
			'type'    => 'varchar',
			'size'    => 10,
			'default' => 'block',
			'null'    => false
		),
		array(
			'name' => 'lang',
			'type' => 'varchar',
			'size' => 60,
			'null' => false
		),
		array(
			'name' => 'title',
			'type' => 'varchar',
			'size' => 255,
			'null' => false
		)
	),
	'indexes' => array(
		array(
			'type'    => 'primary',
			'columns' => array('item_id', 'type', 'lang')
		)
	),
	'default' => array(
		'columns' => array(
			'item_id' => 'int',
			'type'    => 'string-10',
			'lang'    => 'string-60',
			'title'   => 'string-255'
		),
		'values' => array(
			array(1, 'page', 'english', $mbname)
		),
		'keys' => array('item_id', 'type', 'lang')
	)
);

db_extend('packages');

foreach ($tables as $table) {
	$smcFunc['db_create_table']('{db_prefix}' . $table['name'], $table['columns'], $table['indexes']);

	if ($table['name'] === 'lp_blocks') {
		foreach ($table['columns'] as $column) {
			if ($column['name'] === 'user_id' || $column['name'] === 'note') {
				$smcFunc['db_add_column']('{db_prefix}lp_blocks', $column, [], 'ignore');
			}
		}
	}

	if ($table['name'] === 'lp_pages') {
		foreach ($table['columns'] as $column) {
			if ($column['name'] === 'category_id') {
				$smcFunc['db_add_column']('{db_prefix}lp_pages', $column, [], 'ignore');
				break;
			}
		}
	}

	if (isset($table['default']))
		$smcFunc['db_insert']('ignore', '{db_prefix}' . $table['name'], $table['default']['columns'], $table['default']['values'], $table['default']['keys']);
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
	$addSettings['lp_enabled_plugins'] = 'HelloPortal,ThemeSwitcher,Trumbowyg,UserInfo';
if (! isset($modSettings['lp_show_comment_block']))
	$addSettings['lp_show_comment_block'] = 'default';
if (! isset($modSettings['lp_fa_source']))
	$addSettings['lp_fa_source'] = 'css_cdn';
if (! empty($addSettings))
	updateSettings($addSettings);

if (! @is_writable($layouts = $settings['default_theme_dir'] . '/LightPortal'))
	smf_chmod($layouts);
if (! @is_writable($langs = $settings['default_theme_dir'] . '/languages/LightPortal'))
	smf_chmod($langs);
if (! @is_writable($css_dir = $settings['default_theme_dir'] . '/css/light_portal'))
	smf_chmod($css_dir);
if (! @is_writable($scripts = $settings['default_theme_dir'] . '/scripts/light_portal'))
	smf_chmod($scripts);

if (SMF === 'SSI')
	echo 'Database changes are complete! Please wait...';
