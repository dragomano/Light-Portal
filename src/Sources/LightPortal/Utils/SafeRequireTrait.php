<?php declare(strict_types=1);

/**
 * SafeRequireTrait.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.6
 */

namespace Bugo\LightPortal\Utils;

use function dirname;
use function is_file;

if (! defined('SMF'))
	die('No direct access...');

trait SafeRequireTrait
{
	public function require(string $filename, string $extension = '.php'): void
	{
		$path = dirname(__DIR__) . DIRECTORY_SEPARATOR . $filename . $extension;

		if (is_file($path)) {
			require_once $path;
		}
	}
}
