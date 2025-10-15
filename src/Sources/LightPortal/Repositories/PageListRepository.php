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

namespace Bugo\LightPortal\Repositories;

use Bugo\Compat\Lang;
use Bugo\LightPortal\Enums\EntryType;
use Bugo\LightPortal\Enums\Permission;
use Bugo\LightPortal\Enums\PortalSubAction;
use Bugo\LightPortal\Enums\Status;
use Bugo\LightPortal\Utils\Icon;
use Bugo\LightPortal\Utils\Str;
use Laminas\Db\Sql\Predicate\Expression;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Where;

if (! defined('SMF'))
	die('No direct access...');

final class PageListRepository extends AbstractRepository implements PageListRepositoryInterface
{
	protected string $entity = 'page';

	public function getPagesByCategory(int $categoryId, int $start, int $limit, string $sort): array
	{
		$select = $this->sql->select()
			->from(['p' => 'lp_pages'])
			->columns([
				Select::SQL_STAR,
				'date'         => new Expression('GREATEST(p.created_at, p.updated_at)'),
				'author_name'  => new Expression('COALESCE(mem.real_name, "")'),
				'comment_date' => new Expression('COALESCE(com.created_at, 0)'),
			])
			->join(['mem' => 'members'], 'p.author_id = mem.id_member', [], Select::JOIN_LEFT)
			->join(['com' => 'lp_comments'], 'p.last_comment_id = com.id', [], Select::JOIN_LEFT)
			->where($this->getCategoryWhere($categoryId))
			->order($sort)
			->limit($limit)
			->offset($start);

		$this->addTranslationJoins($select, ['fields' => ['title', 'content', 'description']]);

		$result = $this->sql->execute($select);

		$rows = [];
		foreach ($result as $row) {
			$rows[] = $row;
		}

		return $rows;
	}

	public function getTotalPagesByCategory(int $categoryId): int
	{
		$select = $this->sql->select()
			->from(['p' => 'lp_pages'])
			->columns(['count' => new Expression('COUNT(DISTINCT p.page_id)')])
			->where($this->getCategoryWhere($categoryId));

		$result = $this->sql->execute($select)->current();

		return $result['count'];
	}

	public function getCategoriesWithPageCount(int $start, int $limit, string $sort): array
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
				['slug', 'icon', 'priority'],
				Select::JOIN_LEFT)
			->where($this->getCommonCategoriesWhere())
			->group(['c.category_id', 'c.slug', 'c.icon', 'c.priority', 'title', 'description'])
			->order($sort);

		$this->addTranslationJoins($select, [
			'primary' => 'c.category_id',
			'entity'  => 'category',
			'fields'  => ['title', 'description'],
		]);

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
				'title'       => Str::decodeHtmlEntities($row['title'] ?: Lang::$txt['lp_no_category']),
				'description' => $row['description'] ?? '',
			];
		}

		return $items;
	}

	public function getTotalCategoriesWithPages(): int
	{
		$select = $this->sql->select()
			->from(['p' => 'lp_pages'])
			->columns(['count' => new Expression('COUNT(DISTINCT COALESCE(c.category_id, 0))')])
			->join(['c' => 'lp_categories'], 'p.category_id = c.category_id', [], Select::JOIN_LEFT)
			->where($this->getCommonCategoriesWhere())
			->limit(1);

		$result = $this->sql->execute($select)->current();

		return $result['count'];
	}

	public function getPagesByTag(int $tagId, int $start, int $limit, string $sort): array
	{
		$select = $this->sql->select()
			->from(['p' => 'lp_pages'])
			->columns([
				Select::SQL_STAR,
				'date'         => new Expression('GREATEST(p.created_at, p.updated_at)'),
				'author_name'  => new Expression('COALESCE(mem.real_name, "")'),
				'comment_date' => new Expression('COALESCE(com.created_at, 0)'),
			])
			->join(['mem' => 'members'], 'p.author_id = mem.id_member', [], Select::JOIN_LEFT)
			->join(['pt' => 'lp_page_tag'], 'p.page_id = pt.page_id', [])
			->join(['tag' => 'lp_tags'], 'pt.tag_id = tag.tag_id', [])
			->join(['com' => 'lp_comments'], 'p.last_comment_id = com.id', [], Select::JOIN_LEFT)
			->where($this->getTagWhere($tagId))
			->order($sort)
			->limit($limit)
			->offset($start);

		$this->addTranslationJoins($select, ['fields' => ['title', 'content', 'description']]);

		$result = $this->sql->execute($select);

		$rows = [];
		foreach ($result as $row) {
			$rows[] = $row;
		}

		return $rows;
	}

	public function getTotalPagesByTag(int $tagId): int
	{
		$select = $this->sql->select()
			->from(['p' => 'lp_pages'])
			->columns(['count' => new Expression('COUNT(DISTINCT p.page_id)')])
			->join(['pt' => 'lp_page_tag'], 'p.page_id = pt.page_id', [])
			->join(['tag' => 'lp_tags'], 'pt.tag_id = tag.tag_id', [])
			->where($this->getTagWhere($tagId));

		$result = $this->sql->execute($select)->current();

		return $result['count'];
	}

	public function getTagsWithPageCount(int $start, int $limit, string $sort): array
	{
		$select = $this->sql->select()
			->from(['p' => 'lp_pages'])
			->columns(['frequency' => new Expression('COUNT(DISTINCT p.page_id)')])
			->join(['pt' => 'lp_page_tag'], 'p.page_id = pt.page_id', [])
			->join(['tag' => 'lp_tags'], 'pt.tag_id = tag.tag_id', ['tag_id', 'slug', 'icon'])
			->where($this->getCommonTagWhere())
			->group(['tag.tag_id', 'tag.slug', 'tag.icon', 't.title', 'tf.title'])
			->order($sort);

		$this->addTranslationJoins($select, ['primary' => 'tag.tag_id', 'entity' => 'tag']);

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

	public function getTotalTagsWithPages(): int
	{
		$select = $this->sql->select()
			->from(['p' => 'lp_pages'])
			->columns(['count' => new Expression('COUNT(DISTINCT tag.tag_id)')])
			->join(['pt' => 'lp_page_tag'], 'p.page_id = pt.page_id', [])
			->join(['tag' => 'lp_tags'], 'pt.tag_id = tag.tag_id', [])
			->where($this->getCommonTagWhere())
			->limit(1);

		$result = $this->sql->execute($select)->current();

		return $result['count'];
	}

	protected function getCommonPageWhere(): Where
	{
		$where = new Where();
		$where
			->equalTo('p.status', Status::ACTIVE->value)
			->equalTo('p.deleted_at', 0)
			->in('p.entry_type', EntryType::withoutDrafts())
			->lessThanOrEqualTo('p.created_at', time())
			->in('p.permissions', Permission::all());

		return $where;
	}

	protected function getCategoryWhere(int $categoryId): Where
	{
		$where = new Where();
		$where->equalTo('p.category_id', $categoryId);

		foreach ($this->getCommonPageWhere()->getPredicates() as $predicate) {
			$where->addPredicate($predicate[1]);
		}

		return $where;
	}

	protected function getCommonCategoriesWhere(): Where
	{
		$where = new Where();
		$where
			->nest()
			->equalTo('c.status', Status::ACTIVE->value)
			->or->equalTo('p.category_id', 0)
			->unnest();

		foreach ($this->getCommonPageWhere()->getPredicates() as $predicate) {
			$where->addPredicate($predicate[1]);
		}

		return $where;
	}

	protected function getTagWhere(int $tagId): Where
	{
		$where = new Where();
		$where->equalTo('pt.tag_id', $tagId);

		foreach ($this->getCommonPageWhere()->getPredicates() as $predicate) {
			$where->addPredicate($predicate[1]);
		}

		return $where;
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
