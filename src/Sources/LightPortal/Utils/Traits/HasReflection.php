<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 3.0
 */

namespace Bugo\LightPortal\Utils\Traits;

use ReflectionClass;

if (! defined('SMF'))
	die('No direct access...');

trait HasReflection
{
	public function getCalledClass(): ReflectionClass
	{
		return new ReflectionClass(static::class);
	}
}
