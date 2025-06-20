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

class TagValidator extends AbstractValidator
{
	protected array $filters = [
		'tag_id' => FILTER_VALIDATE_INT,
		'slug'   => [
			'filter'  => FILTER_VALIDATE_REGEXP,
			'options' => ['regexp' => '/' . LP_ALIAS_PATTERN . '/'],
		],
		'icon'   => FILTER_DEFAULT,
	];

	protected function extendErrors(): void
	{
		$this->checkSlug();
	}

	protected function isUnique(): bool
	{
		$result = Db::$db->query('', '
			SELECT COUNT(tag_id)
			FROM {db_prefix}lp_tags
			WHERE slug = {string:slug}
				AND tag_id != {int:item}',
			[
				'slug' => $this->filteredData['slug'],
				'item' => $this->filteredData['tag_id'],
			]
		);

		[$count] = Db::$db->fetch_row($result);

		Db::$db->free_result($result);

		return $count == 0;
	}
}
