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

namespace LightPortal\Repositories;

use Bugo\Compat\Lang;
use Laminas\Db\Sql\Predicate\Expression;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Where;
use LightPortal\Enums\PortalSubAction;
use LightPortal\Enums\Status;
use LightPortal\Utils\Icon;
use LightPortal\Utils\Str;

if (! defined('SMF'))
	die('No direct access...');

class CategoryIndexRepository extends AbstractIndexRepository
{
	public function getAll(
		int $start,
		int $limit,
		string $sort,
		string $filter = '',
		array $whereConditions = []
	): array
	{
		$select = $this->sql->select()
			->from(['p' => 'lp_pages'])
			->columns([
				'category_id' => new Expression('COALESCE(c.category_id, 0)'),
				'frequency'   => new Expression('COUNT(DISTINCT p.page_id)'),
			])
			->join(
				['c' => 'lp_categories'],
				'p.category_id = c.category_id',
				[
					'slug'     => new Expression("COALESCE(c.slug, 'uncategorized')"),
					'icon'     => new Expression("COALESCE(c.icon, 'fas folder-open')"),
					'priority' => new Expression('COALESCE(c.priority, 0)'),
				],
				Select::JOIN_LEFT)
			->where($this->getCommonCategoriesWhere())
			->group(['c.category_id', 'c.slug', 'c.icon', 'c.priority', 'title', 'description'])
			->order(new Expression($sort));

		$this->addTranslationJoins($select, [
			'primary' => 'c.category_id',
			'entity'  => 'category',
			'fields'  => ['title', 'description'],
			'columns' => [
				'title' => new Expression("
					CASE
						WHEN p.category_id = 0 THEN ?
						ELSE COALESCE(NULLIF(t.title, ''), tf.title, '')
					END", [Lang::$txt['lp_no_category']]
				),
				'description' => new Expression("
					CASE
						WHEN p.category_id = 0 THEN ''
						ELSE COALESCE(NULLIF(t.description, ''), tf.description, '')
					END"
				)
			]
		]);

		$select->where(new Expression(
			"(p.category_id = 0 OR COALESCE(NULLIF(t.title, ''), tf.title, '') <> '')"
		));

		if ($limit) {
			$select->limit($limit)->offset($start);
		}

		$result = $this->sql->execute($select);

		$items = [];
		foreach ($result as $row) {
			$items[$row['category_id']] = [
				'slug'        => $row['slug'],
				'icon'        => Icon::parse($row['icon']),
				'link'        => PortalSubAction::CATEGORIES->url() . ';id=' . $row['category_id'],
				'priority'    => $row['priority'],
				'num_pages'   => $row['frequency'],
				'title'       => Str::decodeHtmlEntities($row['title']),
				'description' => $row['description'] ?? '',
			];
		}

		return $items;
	}

	public function getTotalCount(string $filter = '', array $whereConditions = []): int
	{
		$select = $this->sql->select()
			->from(['p' => 'lp_pages'])
			->columns(['count' => new Expression('COUNT(DISTINCT COALESCE(c.category_id, 0))')])
			->join(['c' => 'lp_categories'], 'p.category_id = c.category_id', [], Select::JOIN_LEFT)
			->where($this->getCommonCategoriesWhere())
			->limit(1);

		$this->addTranslationJoins($select, [
			'primary' => 'c.category_id',
			'entity'  => 'category',
			'columns' => [],
		]);

		$select->where(new Expression(
			"(p.category_id = 0 OR COALESCE(NULLIF(t.title, ''), tf.title, '') <> '')"
		));

		$result = $this->sql->execute($select)->current();

		return (int) $result['count'];
	}

	protected function getCommonCategoriesWhere(): Where
	{
		$where = new Where();
		$where
			->greaterThanOrEqualTo('p.category_id', 0)
			->nest()
			->equalTo('c.status', Status::ACTIVE->value)
			->or->equalTo('p.category_id', 0)
			->unnest();

		foreach ($this->getCommonPageWhere()->getPredicates() as $predicate) {
			$where->addPredicate($predicate[1]);
		}

		return $where;
	}
}
