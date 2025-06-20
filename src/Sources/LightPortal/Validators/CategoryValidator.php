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

namespace Bugo\LightPortal\Validators;

use Bugo\Compat\Db;

class CategoryValidator extends AbstractValidator
{
	protected array $filters = [
		'category_id' => FILTER_VALIDATE_INT,
		'slug'        => [
			'filter'  => FILTER_VALIDATE_REGEXP,
			'options' => ['regexp' => '/' . LP_ALIAS_PATTERN . '/'],
		],
		'icon'        => FILTER_DEFAULT,
		'description' => FILTER_UNSAFE_RAW,
	];

	protected function extendErrors(): void
	{
		$this->checkSlug();
	}

	protected function isUnique(): bool
	{
		$result = Db::$db->query('', '
			SELECT COUNT(category_id)
			FROM {db_prefix}lp_categories
			WHERE slug = {string:slug}
				AND category_id != {int:item}',
			[
				'slug' => $this->filteredData['slug'],
				'item' => $this->filteredData['category_id'],
			]
		);

		[$count] = Db::$db->fetch_row($result);

		Db::$db->free_result($result);

		return $count == 0;
	}
}
