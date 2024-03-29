<?php declare(strict_types=1);

/**
 * AbstractValidator.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.6
 */

namespace Bugo\LightPortal\Areas\Validators;

use Bugo\LightPortal\Helper;

abstract class AbstractValidator
{
	use Helper;

	abstract public function validate(): array;
}