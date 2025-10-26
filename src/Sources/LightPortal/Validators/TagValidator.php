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
		$select = $this->sql->select('lp_tags')
			->columns(['count' => new Expression('COUNT(tag_id)')])
			->where([
				'slug = ?'    => $this->filteredData['slug'],
				'tag_id != ?' => $this->filteredData['tag_id'],
			]);

		$result = $this->sql->execute($select)->current();

		return $result['count'] == 0;
	}
}
