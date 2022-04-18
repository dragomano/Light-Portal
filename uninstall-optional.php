<?php

if (file_exists(dirname(__FILE__) . '/SSI.php') && ! defined('SMF'))
	require_once(dirname(__FILE__) . '/SSI.php');
elseif(! defined('SMF'))
	die('<b>Error:</b> Cannot install - please verify that you put this file in the same place as SMF\'s index.php and SSI.php files.');

if ((SMF === 'SSI') && ! $user_info['is_admin'])
	die('Admin privileges required.');

global $smcFunc, $db_type, $modSettings;

$request = $smcFunc['db_query']('', '
	SELECT variable FROM {db_prefix}settings
	WHERE variable ' . ($db_type === 'postgresql' ? "~ '^lp_'" : 'REGEXP "^lp_"'),
	array()
);

$settingsToRemove = [];
while ($row = $smcFunc['db_fetch_assoc']($request)) {
	$settingsToRemove[] = $row['variable'];
}

$smcFunc['db_free_result']($request);

if (! empty($settingsToRemove)) {
	foreach ($settingsToRemove as $setting) {
		if (isset($modSettings[$setting]))
			unset($modSettings[$setting]);
	}

	$smcFunc['db_query']('', '
		DELETE FROM {db_prefix}settings
		WHERE variable IN ({array_string:settings})',
		array(
			'settings' => $settingsToRemove,
		)
	);
}

updateSettings(array(
	'settings_updated' => time(),
));

$smcFunc['db_query']('', '
	DELETE FROM {db_prefix}permissions
	WHERE permission LIKE {string:permissions}',
	array(
		'permissions' => '%light_portal%',
	)
);

if (SMF === 'SSI')
	echo 'Database changes are complete! Please wait...';
