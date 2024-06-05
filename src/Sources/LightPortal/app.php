<?php declare(strict_types=1);

/**
 * app.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.6
 */

if (! defined('SMF'))
	die('We gotta get out of here!');

require_once __DIR__ . '/Libs/autoload.php';

use Bugo\LightPortal\Areas\ConfigArea;
use Bugo\LightPortal\Areas\CreditArea;
use Bugo\LightPortal\Integration;
use Laminas\Loader\StandardAutoloader;

// Register autoloader
$loader = new StandardAutoloader();
$loader->registerNamespace('Bugo\LightPortal', __DIR__);
$loader->register();

// This is the way
$app = new class {
	public function __construct()
	{
		if (SMF === 'BACKGROUND')
			return;

		(new Integration())();
		(new ConfigArea())();
		(new CreditArea())();
	}
};

new $app;
