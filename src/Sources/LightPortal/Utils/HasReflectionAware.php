<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.8
 */

namespace Bugo\LightPortal\Utils;

use ReflectionClass;

if (! defined('SMF'))
	die('No direct access...');

trait HasReflectionAware
{
	public function getCalledClass(): ReflectionClass
	{
		return new ReflectionClass(static::class);
	}
}
