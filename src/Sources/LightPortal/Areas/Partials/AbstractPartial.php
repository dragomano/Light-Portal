<?php declare(strict_types=1);

/**
 * AbstractPartial.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2023 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.3
 */

namespace Bugo\LightPortal\Areas\Partials;

use Stringable;
use Bugo\LightPortal\Helper;

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
