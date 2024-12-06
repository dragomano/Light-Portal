<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.8
 */

namespace Bugo\LightPortal\Areas\Validators;

class CategoryValidator extends AbstractValidator
{
	use BaseValidateTrait;

	protected array $args = [
		'category_id' => FILTER_VALIDATE_INT,
		'icon'        => FILTER_DEFAULT,
		'description' => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
		'priority'    => FILTER_VALIDATE_INT,
		'status'      => FILTER_VALIDATE_INT,
	];
}
