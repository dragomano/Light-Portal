<?php declare(strict_types=1);

/**
 * AbstractPartial.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.6
 */

namespace Bugo\LightPortal\Areas\Partials;

use Bugo\LightPortal\Helper;
use Stringable;

if (! defined('SMF'))
	die('No direct access...');

abstract class AbstractPartial implements Stringable
{
	use Helper;

	abstract public function __invoke(): string;

	public function __toString(): string
	{
		return static::__invoke();
	}
}
