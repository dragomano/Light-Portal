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

namespace Bugo\LightPortal\UI\Partials;

use Stringable;

if (! defined('SMF'))
	die('No direct access...');

abstract class AbstractPartial implements Stringable
{
	abstract public function __invoke(): string;

	public function __toString(): string
	{
		return static::__invoke();
	}
}
