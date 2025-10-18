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

namespace LightPortal\Utils\Traits;

use Bugo\Compat\Config;
use Bugo\Compat\User;
use Laminas\Db\Sql\Predicate\Expression;
use Laminas\Db\Sql\Select;

trait HasTranslationJoins
{
	public function addTranslationJoins(Select $select, array $config = []): void
	{
		$lang     = $config['lang']     ?? User::$me->language;
		$fallback = $config['fallback'] ?? Config::$language;
		$primary  = $config['primary']  ?? 'p.page_id';
		$entity   = $config['entity']   ?? 'page';
		$fields   = $config['fields']   ?? ['title'];
		$alias    = $config['alias']    ?? 't';

		$aliasFallback = $this->getFallbackAlias($alias);

		$select->join(
			[$alias => 'lp_translations'],
			new Expression(
				"$alias.item_id = $primary AND $alias.type = ? AND $alias.lang = ?",
				[$entity, $lang]
			),
			$this->getTranslationColumns($fields, $alias, $aliasFallback),
			Select::JOIN_LEFT
		);

		$select->join(
			[$aliasFallback => 'lp_translations'],
			new Expression(
				"$aliasFallback.item_id = $primary AND $aliasFallback.type = ? AND $aliasFallback.lang = ?",
				[$entity, $fallback]
			),
			[],
			Select::JOIN_LEFT
		);
	}

	protected function getFallbackAlias(string $alias): string
	{
		$aliasFallback = preg_replace('/t$/', 'tf', $alias, 1);

		if ($aliasFallback === $alias) {
			$aliasFallback = $alias . 'f';
		}

		return $aliasFallback;
	}

	protected function getTranslationColumns(array $fields, string $alias, string $aliasFallback): array
	{
		$columns = [];

		foreach ($fields as $aliasName => $field) {
			if (is_int($aliasName)) {
				$aliasName = $field;
			}

			$columns[$aliasName] = new Expression(
				sprintf('COALESCE(NULLIF(%1$s.%2$s, ""), %3$s.%2$s, "")', $alias, $field, $aliasFallback)
			);
		}

		return $columns;
	}
}
