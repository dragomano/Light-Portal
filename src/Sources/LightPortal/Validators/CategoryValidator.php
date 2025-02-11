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

namespace Bugo\LightPortal\Validators;

class CategoryValidator extends AbstractValidator
{
	protected array $filters = [
		'category_id' => FILTER_VALIDATE_INT,
		'icon'        => FILTER_DEFAULT,
		'description' => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
	];
}
