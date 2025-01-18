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

use Bugo\Compat\{Config, Lang, Utils};
use Bugo\LightPortal\Actions\FrontPage;

require_once __DIR__ . '/SSI.php';

if (empty(Config::$sourcedir)) {
	die('<strong>' . Lang::$txt['error_occured'] . '</strong> ' . Lang::$txt['lp_standalone_mode_error']);
}

if (empty(Config::$modSettings['lp_standalone_mode']) || empty(Config::$modSettings['lp_standalone_url'])) {
	Utils::redirectexit();
}

try {
	app(FrontPage::class)->show();
} catch (Exception $e) {
	die($e->getMessage());
}

Utils::obExit();
