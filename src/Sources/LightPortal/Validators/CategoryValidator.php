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

namespace LightPortal\Validators;

use Laminas\Db\Sql\Expression;

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
		$select = $this->sql->select('lp_categories')
			->columns(['count' => new Expression('COUNT(category_id)')])
			->where([
				'slug = ?'         => $this->filteredData['slug'],
				'category_id != ?' => $this->filteredData['category_id'],
			]);

		$result = $this->sql->execute($select)->current();

		return $result['count'] == 0;
	}
}
