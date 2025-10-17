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

namespace Bugo\LightPortal\DataHandlers\Traits;

use Laminas\Db\Sql\Predicate\Expression;

trait HasTranslations
{
	protected function replaceTranslations(array $translations, array $results, bool $replace = true): array
	{
		if ($translations === [] || $results === [])
			return [];

		foreach ($translations as $id => $translation) {
			foreach (['title', 'content', 'description'] as $field) {
				if (! isset($translation[$field])) {
					$translations[$id][$field] = '';
				}
			}
		}

		$results = $this->insertData(
			'lp_translations',
			$translations,
			['item_id', 'type', 'lang'],
			replace: $replace
		);

		if (! $results) {
			return [];
		}

		$update = $this->sql->update('lp_translations')
			->set([
				'title'       => new Expression('NULLIF(title, ?)', ['']),
				'content'     => new Expression('NULLIF(content, ?)', ['']),
				'description' => new Expression('NULLIF(description, ?)', ['']),
			]);

		$whereClause = $update->where;

		if ($whereClause === null) {
			return [];
		}

		$where = $update->where;
		$where->in('id', $results);
		$where->and
			->nest()
			->equalTo('title', '')
			->or->equalTo('content', '')
			->or->equalTo('description', '')
			->unnest();

		$this->sql->execute($update);

		return $results;
	}
}
