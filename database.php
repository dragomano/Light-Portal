<?php

if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
	require_once(dirname(__FILE__) . '/SSI.php');
elseif(!defined('SMF'))
	die('<b>Error:</b> Cannot install - please verify that you put this file in the same place as SMF\'s index.php and SSI.php files.');

if (version_compare(PHP_VERSION, '7.2', '<'))
	die('This mod needs PHP 7.2 or greater. You will not be able to install/use this mod, contact your host and ask for a php upgrade.');

global $user_info, $mbname, $modSettings;

if ((SMF == 'SSI') && !$user_info['is_admin'])
	die('Admin privileges required.');

$tables[] = array(
	'name' => 'lp_blocks',
	'columns' => array(
		array(
			'name'     => 'block_id',
			'type'     => 'int',
			'size'     => 11,
			'unsigned' => true,
			'auto'     => true
		),
		array(
			'name' => 'icon',
			'type' => 'varchar',
			'size' => 60,
			'null' => true
		),
		array(
			'name' => 'icon_type',
			'type' => 'varchar',
			'size' => 10,
			'null' => true
		),
		array(
			'name' => 'type',
			'type' => 'varchar',
			'size' => 30,
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
			'default'  => 0,
			'unsigned' => true
		),
		array(
			'name'     => 'permissions',
			'type'     => 'tinyint',
			'size'     => 1,
			'default'  => 0,
			'unsigned' => true
		),
		array(
			'name'     => 'status',
			'type'     => 'tinyint',
			'size'     => 1,
			'default'  => 1,
			'unsigned' => true
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
			'size'     => 11,
			'unsigned' => true,
			'auto'     => true
		),
		array(
			'name'     => 'parent_id',
			'type'     => 'int',
			'size'     => 11,
			'unsigned' => true,
			'default'  => 0
		),
		array(
			'name'     => 'page_id',
			'type'     => 'int',
			'size'     => 11,
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
			'default'  => 0,
			'unsigned' => true
		)
	),
	'indexes' => array(
		 array(
			'type'    => 'primary',
			'columns' => array('id', 'page_id')
		 )
	)
);

$tables[] = array(
	'name' => 'lp_pages',
	'columns' => array(
		array(
			'name'     => 'page_id',
			'type'     => 'int',
			'size'     => 11,
			'unsigned' => true,
			'auto'     => true
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
			'type' => 'text',
			'null' => false
		),
		array(
			'name'    => 'type',
			'type'    => 'varchar',
			'size'    => 6,
			'default' => 'bbc',
			'null'    => false
		),
		array(
			'name'     => 'permissions',
			'type'     => 'tinyint',
			'size'     => 1,
			'default'  => 0,
			'unsigned' => true
		),
		array(
			'name'     => 'status',
			'type'     => 'tinyint',
			'size'     => 1,
			'default'  => 1,
			'unsigned' => true
		),
		array(
			'name'     => 'num_views',
			'type'     => 'int',
			'size'     => 10,
			'default'  => 0,
			'unsigned' => true
		),
		array(
			'name'     => 'num_comments',
			'type'     => 'int',
			'size'     => 10,
			'default'  => 0,
			'unsigned' => true
		),
		array(
			'name'     => 'created_at',
			'type'     => 'int',
			'size'     => 10,
			'default'  => 0,
			'unsigned' => true
		),
		array(
			'name'     => 'updated_at',
			'type'     => 'int',
			'size'     => 10,
			'default'  => 0,
			'unsigned' => true
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
			'type'     => 'int',
			'size'     => 11,
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
			'name'     => 'page_id',
			'type'     => 'int',
			'size'     => 11,
			'unsigned' => true
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
			'columns' => array('page_id', 'value')
		 )
	)
);

$tables[] = array(
	'name' => 'lp_titles',
	'columns' => array(
		array(
			'name'     => 'item_id',
			'type'     => 'int',
			'size'     => 11,
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

	if ($table['name'] == 'lp_blocks') {
		foreach ($table['columns'] as $columns) {
			if ($columns['name'] == 'note') {
				$smcFunc['db_add_column']('{db_prefix}lp_blocks', $columns, [], 'ignore');
				break;
			}
		}
	}

	if (isset($table['default']))
		$smcFunc['db_insert']('ignore', '{db_prefix}' . $table['name'], $table['default']['columns'], $table['default']['values'], $table['default']['keys']);
}

if (!isset($modSettings['lp_enabled_plugins']))
	updateSettings(array('lp_enabled_plugins' => 'Trumbowyg'));

if (SMF == 'SSI')
	echo 'Database changes are complete! Please wait...';
