<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2026 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 3.0
 */

namespace LightPortal\Repositories;

use Laminas\Db\Sql\Predicate\Expression;
use Laminas\Db\Sql\Where;
use LightPortal\Enums\PortalSubAction;
use LightPortal\Enums\Status;
use LightPortal\Utils\Icon;
use LightPortal\Utils\Str;

if (! defined('SMF'))
	die('No direct access...');

class TagIndexRepository extends AbstractIndexRepository
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
			->from(['tag' => 'lp_tags'])
			->columns([
				'tag_id', 'slug', 'icon',
				'frequency' => new Expression('COUNT(pt.page_id)')
			])
			->join(['pt' => 'lp_page_tag'], 'tag.tag_id = pt.tag_id', [])
			->join(['p' => 'lp_pages'], 'pt.page_id = p.page_id', [])
			->where($this->getCommonTagWhere())
			->group(['tag.tag_id', 'tag.slug', 'tag.icon'])
			->order(new Expression($sort));

		$this->addTranslationJoins($select, ['primary' => 'tag.tag_id', 'entity' => 'tag']);

		$select->where($this->getTranslationFilter('tag', 'tag_id', ['title'], 'tag'));

		if ($limit) {
			$select->limit($limit)->offset($start);
		}

		$result = $this->sql->execute($select);

		$items = [];
		foreach ($result as $row) {
			$items[$row['tag_id']] = [
				'slug'      => $row['slug'],
				'icon'      => Icon::parse($row['icon']),
				'link'      => PortalSubAction::TAGS->url() . ';id=' . $row['tag_id'],
				'frequency' => $row['frequency'],
				'title'     => Str::decodeHtmlEntities($row['title']),
			];
		}

		return $items;
	}

	public function getTotalCount(string $filter = '', array $whereConditions = []): int
	{
		$select = $this->sql->select()
			->from(['tag' => 'lp_tags'])
			->columns(['count' => new Expression('COUNT(DISTINCT tag.tag_id)')])
			->join(['pt' => 'lp_page_tag'], 'tag.tag_id = pt.tag_id', [])
			->join(['p' => 'lp_pages'], 'pt.page_id = p.page_id', [])
			->where($this->getCommonTagWhere())
			->limit(1);

		$select->where($this->getTranslationFilter('tag', 'tag_id', ['title'], 'tag'));

		$result = $this->sql->execute($select)->current();

		return (int) $result['count'];
	}

	protected function getCommonTagWhere(): Where
	{
		$where = new Where();
		$where->equalTo('tag.status', Status::ACTIVE->value);

		foreach ($this->getCommonPageWhere()->getPredicates() as $predicate) {
			$where->addPredicate($predicate[1]);
		}

		return $where;
	}
}
