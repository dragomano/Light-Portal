<?php declare(strict_types=1);

/**
 * Helper.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.6
 */

namespace Bugo\LightPortal;

use Bugo\LightPortal\Utils\CacheTrait;
use Bugo\LightPortal\Utils\EntityManager;
use Bugo\LightPortal\Utils\RequestTrait;
use Bugo\LightPortal\Utils\SessionTrait;
use Bugo\LightPortal\Utils\SMFHookTrait;

use function dirname;
use function is_file;

if (! defined('SMF'))
	die('No direct access...');

trait Helper
{
	use CacheTrait;
	use RequestTrait;
	use SessionTrait;
	use SMFHookTrait;

	public function getEntityData(string $entity): array
	{
		return (new EntityManager())($entity);
	}

	public function require(string $filename, string $extension = '.php'): void
	{
		$path = dirname(__DIR__) . DIRECTORY_SEPARATOR . $filename . $extension;

		if (is_file($path)) {
			require_once $path;
		}
	}
}
