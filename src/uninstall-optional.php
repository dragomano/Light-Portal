<?php

global $smcFunc, $db_type, $modSettings;

if (file_exists(__DIR__ . '/SSI.php') && ! defined('SMF'))
	require_once __DIR__ . '/SSI.php';
elseif(! defined('SMF'))
	die('<b>Error:</b> Cannot install - please verify that you put this file in the same place as SMF\'s index.php and SSI.php files.');

if ((SMF === 'SSI') && ! $user_info['is_admin'])
	die('Admin privileges required.');

$request = $smcFunc['db_query']('', '
	SELECT variable FROM {db_prefix}settings
	WHERE variable ' . ($db_type === 'postgresql' ? "~ '^lp_'" : 'REGEXP "^lp_"'),
	[]
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
		[
			'settings' => $settingsToRemove,
		]
	);
}

updateSettings(['settings_updated' => time()]);

$smcFunc['db_query']('', '
	DELETE FROM {db_prefix}permissions
	WHERE permission LIKE {string:permissions}',
	[
		'permissions' => '%light_portal%',
	]
);

if (SMF === 'SSI')
	echo 'Database changes are complete! Please wait...';
