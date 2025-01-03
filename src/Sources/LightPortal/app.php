<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.9
 */

if (! defined('SMF'))
	die('We gotta get out of here!');

require_once __DIR__ . '/Libs/autoload.php';

use Bugo\LightPortal\Areas\ConfigArea;
use Bugo\LightPortal\Areas\CreditArea;
use Bugo\LightPortal\Container;
use Bugo\LightPortal\Integration;

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

// Helper to work with Container
function app(string $service, array $params = []): mixed
{
	$instance = Container::get($service);

	if ($service === 'events') {
		return $instance($params);
	}

	return $instance;
}
