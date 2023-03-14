<?php

use Bugo\LightPortal\Entities\FrontPage;

require_once __DIR__ . '/SSI.php';

if (empty($sourcedir))
	die('<b>Error:</b> Cannot run the portal - please verify that you put this file in the same place as SMF\'s index.php and SSI.php files.');

if (empty($modSettings['lp_standalone_mode']) || empty($modSettings['lp_standalone_url']))
	redirectexit();

require_once $sourcedir . '/LightPortal/Entities/FrontPage.php';

(new FrontPage)->show();

obExit();
