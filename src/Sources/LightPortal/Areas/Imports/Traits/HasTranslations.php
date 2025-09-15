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
			$orderedTranslation = [
				'item_id'     => $translation['item_id'],
				'type'        => $translation['type'],
				'lang'        => $translation['lang'],
				'title'       => $translation['title'] ?? '',
				'content'     => $translation['content'] ?? '',
				'description' => $translation['description'] ?? '',
			];

			$translations[$id] = $orderedTranslation;
		}

		$results = $this->insertData(
			'lp_translations',
			$method,
			$translations,
			$method === 'replace' ? [
				'item_id'     => 'int',
				'type'        => 'string-30',
				'lang'        => 'string-60',
				'title'       => 'string-255',
				'content'     => 'string',
				'description' => 'string-255',
			] : [
				'type'        => 'string-30',
				'lang'        => 'string-60',
				'title'       => 'string-255',
				'content'     => 'string',
				'description' => 'string-255',
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
				'empty_string' => '',
			]
		);
	}
}
