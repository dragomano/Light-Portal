<?php

declare(strict_types=1);

use Tests\CustomTestCase;

$txt['custom_profile_icon'] = 'Icon';
$txt['guest_title'] = 'Guest';

require_once __DIR__ . '/../src/Themes/default/languages/LightPortal/LightPortal.english.php';

$GLOBALS['txt'] = $txt;

$GLOBALS['context'] = [
    'admin_menu_name' => 'admin',
    'right_to_left'   => false,
];

$GLOBALS['mbname']    = 'Test Forum';
$GLOBALS['smcFunc']   = [];
$GLOBALS['sourcedir'] = __DIR__ . '/files';
$GLOBALS['boardurl']  = 'https://example.com/';
$GLOBALS['scripturl'] = 'https://example.com/index.php';
$GLOBALS['language']  = 'english';

$GLOBALS['modSettings'] = [
    'avatar_url'  => '',
    'smileys_url' => 'https://example.com/Smileys',
];

$GLOBALS['settings'] = [
    'default_theme_dir' => '/themes/default',
];

$GLOBALS['_INITIAL_STATE'] = [
    'scripturl'   => $GLOBALS['scripturl'],
    'boardurl'    => $GLOBALS['boardurl'],
    'language'    => $GLOBALS['language'],
    'modSettings' => $GLOBALS['modSettings'],
    'settings'    => $GLOBALS['settings'],
    'context'     => $GLOBALS['context'],
];

require_once __DIR__ . '/../src/Sources/LightPortal/Libs/autoload.php';

pest()->extends(CustomTestCase::class);

require_once __DIR__ . '/constants.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/namespace_functions.php';
