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

namespace Bugo\LightPortal\Areas\Validators;

class TagValidator extends AbstractValidator
{
	use BaseValidateTrait;

	protected array $args = [
		'tag_id' => FILTER_VALIDATE_INT,
		'icon'   => FILTER_DEFAULT,
		'status' => FILTER_VALIDATE_INT,
	];
}
