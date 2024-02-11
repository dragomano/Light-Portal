<?php

use Bugo\Compat\{Config, Lang, Utils};
use Bugo\LightPortal\Actions\FrontPage;

require_once __DIR__ . '/SSI.php';

if (empty(Config::$sourcedir))
	die('<strong>' . Lang::$txt['error_occured'] . '</strong> ' . Lang::$txt['lp_standalone_mode_error']);

if (empty(Config::$modSettings['lp_standalone_mode']) || empty(Config::$modSettings['lp_standalone_url']))
	Utils::redirectexit();

require_once Config::$sourcedir . '/LightPortal/Actions/FrontPage.php';

try {
	(new FrontPage)->show();
} catch (Exception) {}

Utils::obExit();
