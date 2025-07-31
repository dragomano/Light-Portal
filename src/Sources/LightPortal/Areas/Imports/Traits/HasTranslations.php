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

namespace Bugo\LightPortal\Areas\Imports\Traits;

use Bugo\Compat\Db;

trait HasTranslations
{
	protected function replaceTranslations(array $translations, array &$results, string $method = 'replace'): void
	{
		if ($translations === [] || $results === [])
			return;

		foreach ($translations as $id => $translation) {
			foreach (['title', 'content', 'description'] as $field) {
				if (! isset($translation[$field])) {
					$translations[$id][$field] = '';
				}
			}
		}

		$results = $this->insertData(
			'lp_translations',
			$method,
			$translations,
			$method === 'replace' ? [
				'item_id'     => 'int',
				'type'        => 'string',
				'lang'        => 'string',
				'title'       => 'string',
				'content'     => 'string',
				'description' => 'string',
			] : [
				'type'        => 'string',
				'lang'        => 'string',
				'title'       => 'string',
				'content'     => 'string',
				'description' => 'string',
				'item_id'     => 'int',
			],
			$method === 'replace' ? ['item_id', 'type', 'lang'] : ['id'],
		);

		if (! $results)
			return;

		Db::$db->query('
			UPDATE {db_prefix}lp_translations
			SET title = NULLIF(title, {string:empty_string}),
				content = NULLIF(content, {string:empty_string}),
				description = NULLIF(description, {string:empty_string})
			WHERE id IN ({array_int:ids})
				AND title = {string:empty_string}
			    OR content = {string:empty_string}
			    OR description = {string:empty_string}',
			[
				'ids'          => $results,
				'empty_string' => ''
			]
		);
	}
}
