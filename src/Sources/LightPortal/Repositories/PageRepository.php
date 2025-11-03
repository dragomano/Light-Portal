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

use Bugo\Compat\Config;
use Bugo\Compat\ErrorHandler;
use Bugo\Compat\Lang;
use Bugo\Compat\Logging;
use Bugo\Compat\Msg;
use Bugo\Compat\Security;
use Bugo\Compat\User;
use Bugo\Compat\Utils;
use Exception;
use Laminas\Db\Sql\Predicate\Expression;
use Laminas\Db\Sql\Select;
use LightPortal\Database\PortalSqlInterface;
use LightPortal\Enums\AlertAction;
use LightPortal\Enums\ContentType;
use LightPortal\Enums\EntryType;
use LightPortal\Enums\NotifyType;
use LightPortal\Enums\Permission;
use LightPortal\Enums\PortalHook;
use LightPortal\Enums\PortalSubAction;
use LightPortal\Enums\Status;
use LightPortal\Events\EventDispatcherInterface;
use LightPortal\Utils\Content;
use LightPortal\Utils\DateTime;
use LightPortal\Utils\Icon;
use LightPortal\Utils\NotifierInterface;
use LightPortal\Utils\Setting;
use LightPortal\Utils\Str;

use function LightPortal\app;

use const LP_PAGE_URL;

if (! defined('SMF'))
	die('No direct access...');

final class PageRepository extends AbstractRepository implements PageRepositoryInterface
{
	protected string $entity = 'page';

	public function __construct(
		protected PortalSqlInterface $sql,
		protected EventDispatcherInterface $dispatcher,
		protected NotifierInterface $notifier
	)
	{
		parent::__construct($sql, $dispatcher);
	}

	public function getAll(
		int $start,
		int $limit,
		string $sort,
		string $filter = '',
		array $whereConditions = []
	): array
	{
		$params = $this->getLangQueryParams();

		$select = $this->sql->select()
			->from(['p' => 'lp_pages'])
			->columns([
				Select::SQL_STAR,
				'date' => new Expression('GREATEST(p.created_at, p.updated_at)'),
			])
			->join(
				['mem' => 'members'],
				'p.author_id = mem.id_member',
				['author_name' => new Expression('COALESCE(mem.real_name, ?)', [$params['guest']])],
				Select::JOIN_LEFT
			)
			->order($sort)
			->limit($limit)
			->offset($start);

		$this->addParamJoins($select, ['params' => ['allow_comments' => ['alias' => 'par']]]);

		$this->addTranslationJoins($select);

		if ($filter === 'list') {
			$select->where([
				'p.status = ?'      => Status::ACTIVE->value,
				'p.entry_type = ?'  => EntryType::DEFAULT->name(),
				'p.deleted_at = ?'  => 0,
				'p.created_at <= ?' => time(),
			]);

			$select->where(['p.permissions' => Permission::all()]);
			$select->where($this->getTranslationFilter());
		}

		if ($whereConditions) {
			$select->where($whereConditions);
		}

		$result = $this->sql->execute($select);

		$items = [];
		foreach ($result as $row) {
			Lang::censorText($row['title']);

			$items[$row['page_id']] = [
				'id'           => $row['page_id'],
				'category_id'  => $row['category_id'],
				'slug'         => $row['slug'],
				'type'         => $row['type'],
				'entry_type'   => $row['entry_type'],
				'status'       => $row['status'],
				'num_views'    => $row['num_views'],
				'num_comments' => $row['num_comments'],
				'author_id'    => $row['author_id'],
				'author_name'  => $row['author_name'],
				'date'         => DateTime::relative($row['date']),
				'created_at'   => $row['created_at'],
				'updated_at'   => $row['updated_at'],
				'is_front'     => Setting::isFrontpage($row['slug']),
				'title'        => Str::decodeHtmlEntities($row['title']),
			];
		}

		return $items;
	}

	public function getTotalCount(string $filter = '', array $whereConditions = []): int
	{
		$select = $this->sql->select()
			->from(['p' => 'lp_pages'])
			->columns(['count' => new Expression('COUNT(page_id)')]);

		$this->addTranslationJoins($select);

		if ($whereConditions) {
			$select->where($whereConditions);
		}

		$result = $this->sql->execute($select)->current();

		return $result['count'];
	}

	public function getData(int|string $item): array
	{
		if (empty($item)) {
			return [];
		}

		$params = $this->getLangQueryParams();

		$select = $this->sql->select()
			->from(['p' => 'lp_pages'])
			->join(
				['cat' => 'lp_categories'],
				'cat.category_id = p.category_id',
				['cat_icon' => 'icon'],
				Select::JOIN_LEFT
			)
			->join(
				['mem' => 'members'],
				'p.author_id = mem.id_member',
				['author_name' => new Expression('COALESCE(mem.real_name, ?)', [$params['guest']])],
				Select::JOIN_LEFT
			)
			->join(
				['com' => 'lp_comments'],
				'com.id = p.last_comment_id',
				['comment_date' => 'created_at'],
				Select::JOIN_LEFT
			)
			->where(['p.' . (is_int($item) ? 'page_id = ?' : 'slug = ?') => $item]);

		$this->addParamJoins($select);
		$this->addParamJoins($select, ['params' => ['allow_comments' => ['alias' => 'pac']]]);

		$this->addTranslationJoins($select, ['fields' => ['title', 'content', 'description']]);

		$this->addTranslationJoins($select, [
			'primary' => 'cat.category_id',
			'entity'  => 'category',
			'fields'  => ['cat_title' => 'title'],
			'alias'   => 'cat_t',
		]);

		$result = $this->sql->execute($select);

		foreach ($result as $row) {
			if ($row['type'] === ContentType::BBC->name()) {
				$row['content'] = Msg::un_preparsecode($row['content'] ?? '');
			}

			$data ??= [
				'id'              => $row['page_id'],
				'category_id'     => $row['category_id'],
				'author_id'       => $row['author_id'],
				'author'          => $row['author_name'],
				'slug'            => $row['slug'],
				'type'            => $row['type'],
				'entry_type'      => $row['entry_type'],
				'permissions'     => $row['permissions'],
				'status'          => $row['status'],
				'num_views'       => $row['num_views'],
				'num_comments'    => $row['num_comments'],
				'created_at'      => $row['created_at'],
				'updated_at'      => $row['updated_at'],
				'last_comment_id' => $row['last_comment_id'],
				'sort_value'      => $row['comment_date'],
				'image'           => $this->getImageFromContent($row['content'], $row['type']),
				'title'           => $row['title'],
				'content'         => $row['content'],
				'description'     => $row['description'],
				'cat_title'       => $row['cat_title'],
				'cat_icon'        => Icon::parse($row['cat_icon']),
				'tags'            => $this->getTags($row['page_id']),
			];

			$data['options'][$row['name']] = $row['value'];
		}

		return $data ?? [];
	}

	public function setData(int $item = 0): void
	{
		if (isset(Utils::$context['post_errors']) || $this->request()->hasNot(['save', 'save_exit'])) {
			return;
		}

		Security::checkSubmitOnce('check');

		$this->prepareBbcContent(Utils::$context['lp_page']);

		empty($item)
			? $item = $this->addData(Utils::$context['lp_page'])
			: $this->updateData($item, Utils::$context['lp_page']);

		$this->cache()->flush();

		$this->session()->free('lp');

		if ($this->request()->has('save_exit')) {
			$this->response()->redirect('action=admin;area=lp_pages;sa=main');
		}

		if ($this->request()->has('save')) {
			$this->response()->redirect('action=admin;area=lp_pages;sa=edit;id=' . $item);
		}
	}

	public function remove(mixed $items): void
	{
		$items = (array) $items;

		if ($items === [])
			return;

		$update = $this->sql->update('lp_pages');
		$update->set(['deleted_at' => time()]);
		$update->where->in('page_id', $items);
		$this->sql->execute($update);

		$this->session()->free('lp');
	}

	public function restore(mixed $items): void
	{
		$items = (array) $items;

		if ($items === [])
			return;

		$update = $this->sql->update('lp_pages');
		$update->set(['deleted_at' => 0]);
		$update->where->in('page_id', $items);
		$this->sql->execute($update);

		$this->session()->free('lp');
	}

	public function removePermanently(mixed $items): void
	{
		$items = (array) $items;

		if ($items === [])
			return;

		$this->dispatcher->dispatch(PortalHook::onPageRemoving, ['items' => $items]);

		$deletePages = $this->sql->delete('lp_pages');
		$deletePages->where->in('page_id', $items);
		$this->sql->execute($deletePages);

		$deleteTranslations = $this->sql->delete('lp_translations');
		$deleteTranslations->where->in('item_id', $items);
		$deleteTranslations->where->equalTo('type', $this->entity);
		$this->sql->execute($deleteTranslations);

		$deleteParams = $this->sql->delete('lp_params');
		$deleteParams->where->in('item_id', $items);
		$deleteParams->where->equalTo('type', $this->entity);
		$this->sql->execute($deleteParams);

		$deletePageTag = $this->sql->delete('lp_page_tag');
		$deletePageTag->where->in('page_id', $items);
		$this->sql->execute($deletePageTag);

		$commentsToRemove = $this->sql->select('lp_comments')->columns(['id']);
		$commentsToRemove->where->in('page_id', $items);
		$result = $this->sql->execute($commentsToRemove);

		$commentIds = [];
		foreach ($result as $row) {
			$commentIds[] = $row['id'];
		}

		app(CommentRepositoryInterface::class)->remove($commentIds);

		$this->session()->free('lp');
	}

	public function getPrevNextLinks(array $page, bool $withinCategory = false): array
	{
		$params = $this->getLangQueryParams();

		$sortOptions = [
			'created;desc'      => ['field' => 'p.created_at', 'direction' => 'desc'],
			'created'           => ['field' => 'p.created_at', 'direction' => 'asc'],
			'updated;desc'      => ['field' => 'GREATEST(p.created_at, p.updated_at)', 'direction' => 'desc'],
			'updated'           => ['field' => 'GREATEST(p.created_at, p.updated_at)', 'direction' => 'asc'],
			'last_comment;desc' => ['field' => 'COALESCE(com.created_at, p.created_at)', 'direction' => 'desc'],
			'last_comment'      => ['field' => 'COALESCE(com.created_at, p.created_at)', 'direction' => 'asc'],
			'title;desc'        => ['field' => 'LOWER(COALESCE(t.title, tf.title))', 'direction' => 'desc'],
			'title'             => ['field' => 'LOWER(COALESCE(t.title, tf.title))', 'direction' => 'asc'],
			'author_name;desc'  => ['field' => 'LOWER(COALESCE(mem.real_name, ?))', 'direction' => 'desc', 'bind' => $params['guest']],
			'author_name'       => ['field' => 'LOWER(COALESCE(mem.real_name, ?))', 'direction' => 'asc', 'bind' => $params['guest']],
			'num_views;desc'    => ['field' => 'p.num_views', 'direction' => 'desc'],
			'num_views'         => ['field' => 'p.num_views', 'direction' => 'asc'],
			'num_replies;desc'  => ['field' => 'p.num_comments', 'direction' => 'desc'],
			'num_replies'       => ['field' => 'p.num_comments', 'direction' => 'asc'],
		];

		$sorting = Utils::$context['lp_current_sorting'] ?? 'created;desc';
		$sortOption = $sortOptions[$sorting] ?? $sortOptions['created;desc'];
		$sortField = $sortOption['field'];
		$sortDirection = strtoupper($sortOption['direction']);
		$sortBind = $sortOption['bind'] ?? null;

		$currentSortValue = match (true) {
			str_contains($sorting, 'updated')      => max($page['created_at'], $page['updated_at']),
			str_contains($sorting, 'last_comment') => $page['sort_value'] ?? $page['created_at'],
			str_contains($sorting, 'title')        => Utils::$smcFunc['strtolower']($page['title']),
			str_contains($sorting, 'author_name')  => Utils::$smcFunc['strtolower']($page['author']),
			str_contains($sorting, 'num_views')    => $page['num_views'],
			str_contains($sorting, 'num_replies')  => $page['num_comments'],
			default => $page['created_at'],
		};

		$categories = Setting::get('lp_frontpage_categories', 'array', []);

		$baseWhere = [
			'p.page_id != ?'    => $page['id'],
			'p.created_at <= ?' => time(),
			'p.entry_type = ?'  => EntryType::DEFAULT->name(),
			'p.status = ?'      => Status::ACTIVE->value,
			'p.deleted_at = ?'  => 0,
			'p.permissions'     => Permission::all(),
		];

		if (! empty($categories)) {
			$baseWhere['p.category_id'] = $categories;
		}

		if ($withinCategory) {
			$baseWhere['p.category_id = ?'] = $page['category_id'];
		}

		$secondaryField = 'p.created_at';
		$currentSecondaryValue = $page['created_at'];
		$listAsc = $sortDirection === 'ASC';

		$nextPrimaryOp = $listAsc ? '>' : '<';
		$nextSecondaryOp = $listAsc ? '>' : '<';

		$prevPrimaryOp = $listAsc ? '<' : '>';
		$prevSecondaryOp = $listAsc ? '<' : '>';

		if ($sortBind !== null) {
			$nextWhereParams = [$sortBind, $currentSortValue, $sortBind, $currentSortValue, $currentSecondaryValue];
			$prevWhereParams = [$sortBind, $currentSortValue, $sortBind, $currentSortValue, $currentSecondaryValue];
		} else {
			$nextWhereParams = [$currentSortValue, $currentSortValue, $currentSecondaryValue];
			$prevWhereParams = [$currentSortValue, $currentSortValue, $currentSecondaryValue];
		}

		$nextWhereSql = sprintf(
			'(%s %s ? OR (%s = ? AND %s %s ?))',
			$sortField, $nextPrimaryOp, $sortField, $secondaryField, $nextSecondaryOp
		);
		$prevWhereSql = sprintf(
			'(%s %s ? OR (%s = ? AND %s %s ?))',
			$sortField, $prevPrimaryOp, $sortField, $secondaryField, $prevSecondaryOp
		);

		$nextWhere = new Expression($nextWhereSql, $nextWhereParams);
		$prevWhere = new Expression($prevWhereSql, $prevWhereParams);

		$languages = array_unique([$params['lang'], $params['fallback_lang']]);

		$base = $this->sql->select()
			->from(['p' => 'lp_pages'])
			->columns(['page_id', 'slug'])
			->where(['(t.lang IN (?) OR tf.lang IN (?))' => [$languages, $languages]])
			->where($baseWhere)
			->where(['COALESCE(t.title, tf.title, "") != ?' => ['']]);

		$this->addTranslationJoins($base);

		$where = "item_id = p.page_id AND type = ? AND lang IN (?) AND (title != ? OR content != ?)";
		$sql = "EXISTS (SELECT 1 FROM {$this->sql->getPrefix()}lp_translations WHERE $where)";
		$base->where(new Expression(
			$sql, [$this->entity, array_unique([User::$me->language, Config::$language]), '', '']
		));

		if (str_contains($sorting, 'last_comment')) {
			$base->join(
				['com' => 'lp_comments'],
				new Expression('com.id = p.last_comment_id'),
				['created_at'],
				Select::JOIN_LEFT
			);
		}

		if (str_contains($sorting, 'author_name')) {
			$base->join(
				['mem' => 'members'],
				new Expression('mem.id_member = p.author_id'),
				['real_name'],
				Select::JOIN_LEFT
			);
		}

		$prevOrder = [
			new Expression(
				$sortField . ' ' . ($listAsc ? 'DESC' : 'ASC'),
				$sortBind !== null ? [$sortBind] : []
			),
			new Expression($secondaryField . ' ' . ($listAsc ? 'DESC' : 'ASC')),
			new Expression('CASE WHEN t.title IS NOT NULL THEN 1 WHEN tf.title IS NOT NULL THEN 2 END'),
			new Expression('p.page_id ' . ($listAsc ? 'DESC' : 'ASC'))
		];

		$nextOrder = [
			new Expression(
				$sortField . ' ' . $sortDirection,
				$sortBind !== null ? [$sortBind] : []
			),
			new Expression($secondaryField . ' ' . $sortDirection),
			new Expression('CASE WHEN t.title IS NOT NULL THEN 1 WHEN tf.title IS NOT NULL THEN 2 END'),
			new Expression('p.page_id ' . ($listAsc ? 'ASC' : 'DESC'))
		];

		$prev = clone $base;
		$next = clone $base;

		$prev->where($prevWhere)->order($prevOrder)->limit(1);
		$next->where($nextWhere)->order($nextOrder)->limit(1);

		$result = [
			'prev' => $this->sql->execute($prev)->current() ?: [],
			'next' => $this->sql->execute($next)->current() ?: [],
		];

		return [
			$result['prev']['title'] ?? '',
			$result['prev']['slug'] ?? '',
			$result['next']['title'] ?? '',
			$result['next']['slug'] ?? '',
		];
	}

	public function getRelatedPages(array $page): array
	{
		$titleWords = explode(' ', $page['title']);
		$slugWords  = explode('-', (string) $page['slug']);
		$titleCount = count($titleWords);
		$slugCount  = count($slugWords);

		$searchConditions = [];

		foreach ($titleWords as $key => $word) {
			$searchConditions[] = sprintf(
				'CASE WHEN LOWER(t.title) LIKE LOWER("%%%s%%") THEN %d ELSE 0 END',
				$word,
				($titleCount - $key) * 2
			);
		}

		foreach ($slugWords as $key => $word) {
			$searchConditions[] = sprintf(
				'CASE WHEN LOWER(p.slug) LIKE LOWER("%%%s%%") THEN %d ELSE 0 END',
				$word,
				$slugCount - $key
			);
		}

		$searchFormula = implode(' + ', $searchConditions);

		$select = $this->sql->select()
			->from(['p' => 'lp_pages'])
			->columns([
				'page_id',
				'slug',
				'type',
				'related' => new Expression($searchFormula),
			])
			->where([
				'p.status'          => $page['status'],
				'p.entry_type'      => $page['entry_type'],
				'p.created_at <= ?' => time(),
				'p.page_id != ?'    => $page['id'],
			]);

		$this->addTranslationJoins($select, ['fields' => ['title', 'content']]);

		$select->where->in('p.permissions', Permission::all());
		$select
			->where(new Expression($searchFormula . ' > 0'))
			->where($this->getTranslationFilter())
			->order('related DESC')
			->limit(4);

		$result = $this->sql->execute($select);

		$items = [];
		foreach ($result as $row) {
			if (Setting::isFrontpage($row['slug']))
				continue;

			$row['content'] = Content::parse($row['content'], $row['type']);

			$items[$row['page_id']] = [
				'id'    => $row['page_id'],
				'slug'  => $row['slug'],
				'link'  => LP_PAGE_URL . $row['slug'],
				'image' => Str::getImageFromText($row['content']),
				'title' => $row['title'],
			];
		}

		return $items;
	}

	public function updateNumViews(int $item): void
	{
		$update = $this->sql->update('lp_pages');
		$update->set(['num_views' => new Expression('num_views + 1')]);
		$update->where(['page_id = ?' => $item]);
		$update->where->notIn('status', [Status::INACTIVE->value, Status::UNAPPROVED->value]);

		$this->sql->execute($update);
	}

	public function getMenuItems(): array
	{
		return $this->langCache('menu_pages')
			->setFallback(function () {
				$params = $this->getLangQueryParams();

				$subSelect = $this->sql->select()
					->from('lp_translations')
					->columns(['title'])
					->where(new Expression('item_id = p.page_id'));
				$subSelect->where->equalTo('type', $this->entity);
				$subSelect->where->in('lang', [$params['lang'], $params['fallback_lang']]);
				$subSelect
					->order(new Expression('lang = ? DESC', [$params['lang']]))
					->limit(1);

				$select = $this->sql->select()
					->from(['p' => 'lp_pages'])
					->columns([
						'page_id',
						'slug',
						'permissions',
						'icon' => new Expression('pp2.value'),
						'page_title' => $subSelect,
					])
					->where([
						'p.status'          => Status::ACTIVE->value,
						'p.deleted_at'      => 0,
						'p.created_at <= ?' => time(),
						'pp.value = ?'      => '1',
					]);

				$this->addParamJoins($select, ['params' => ['show_in_menu', 'page_icon']]);

				$select->where->in('p.entry_type', EntryType::withoutDrafts());
				$select->where($this->getTranslationFilter('p', 'page_id', ['title']));

				$result = $this->sql->execute($select);

				$pages = [];
				foreach ($result as $row) {
					Lang::censorText($row['page_title']);

					$pages[$row['page_id']] = [
						'id'          => $row['page_id'],
						'slug'        => $row['slug'],
						'permissions' => $row['permissions'],
						'icon'        => $row['icon'],
						'title'       => $row['page_title'],
					];
				}

				return $pages;
			});
	}

	public function prepareData(?array &$data): void
	{
		if (empty($data))
			return;

		$isAuthor = $data['author_id'] && $data['author_id'] == User::$me->id;

		$data['created']  = DateTime::relative($data['created_at']);
		$data['updated']  = DateTime::relative($data['updated_at']);
		$data['can_view'] = Permission::canViewItem($data['permissions']) || User::$me->is_admin || $isAuthor;
		$data['can_edit'] = User::$me->is_admin
			|| User::$me->allowedTo('light_portal_manage_pages_any')
			|| (User::$me->allowedTo('light_portal_manage_pages_own') && $isAuthor);

		if ($data['type'] === ContentType::BBC->name()) {
			$data['content'] = Msg::un_preparsecode($data['content']);
		}

		$this->dispatcher->dispatch(PortalHook::preparePageData, ['data' => &$data, 'isAuthor' => $isAuthor]);
	}

	public function fetchTags(array $pageIds): iterable
	{
		if ($pageIds === []) {
			return;
		}

		$select = $this->sql->select()
			->from(['tag' => 'lp_tags'])
			->join(
				['pt' => 'lp_page_tag'],
				'tag.tag_id = pt.tag_id',
				['page_id']
			)
			->where([
				'tag.status' => Status::ACTIVE->value,
				'pt.page_id' => $pageIds,
			])
			->where($this->getTranslationFilter('tag', 'tag_id', ['title'], 'tag'))
			->order('title');

		$this->addTranslationJoins($select, ['primary' => 'tag.tag_id', 'entity' => 'tag']);

		$result = $this->sql->execute($select);

		foreach ($result as $row) {
			Lang::censorText($row['title']);

			yield $row['page_id'] => [
				'tag_id' => $row['tag_id'],
				'slug'   => $row['slug'],
				'icon'   => Icon::parse($row['icon'] ?: 'fas fa-tag'),
				'href'   => PortalSubAction::TAGS->url() . ';id=' . $row['tag_id'],
				'name'   => $row['title'],
			];
		}
	}

	private function addData(array $data): int
	{
		try {
			$this->transaction->begin();

			$insert = $this->sql->insert('lp_pages', 'page_id')
				->values([
					'category_id' => $data['category_id'],
					'author_id'   => $data['author_id'],
					'slug'        => $data['slug'],
					'type'        => $data['type'],
					'entry_type'  => $data['entry_type'],
					'permissions' => $data['permissions'],
					'status'      => $data['status'],
					'created_at'  => $this->getPublishTime($data),
				]);

			$result = $this->sql->execute($insert);

			$item = (int) $result->getGeneratedValue('page_id');

			if (empty($item)) {
				$this->transaction->rollback();

				return 0;
			}

			$this->dispatcher->dispatch(PortalHook::onPageSaving, ['item' => $item]);

			$data['id'] = $item;

			$this->saveTranslations($data);
			$this->saveOptions($data);
			$this->saveTags($data);

			$this->transaction->commit();

			// Notify page moderators about new page
			$options = [
				'item'      => $item,
				'time'      => $this->getPublishTime($data),
				'author_id' => $data['author_id'],
				'title'     => $data['title'],
				'url'       => LP_PAGE_URL . $data['slug']
			];

			if (! User::$me->allowedTo('light_portal_manage_pages_any')) {
				$this->notifier->notify(NotifyType::NEW_PAGE->name(), AlertAction::PAGE_UNAPPROVED->name(), $options);
			}

			return $item;
		} catch (Exception $e) {
			$this->transaction->rollback();

			ErrorHandler::fatal($e->getMessage(), false);

			return 0;
		}
	}

	private function updateData(int $item, array $data): void
	{
		try {
			$this->transaction->begin();

			$update = $this->sql->update('lp_pages')
				->set([
					'category_id' => $data['category_id'],
					'author_id'   => $data['author_id'],
					'slug'        => $data['slug'],
					'type'        => $data['type'],
					'entry_type'  => $data['entry_type'],
					'permissions' => $data['permissions'],
					'status'      => $data['status'],
					'updated_at'  => time(),
				])
				->where(['page_id = ?' => $item]);

			$this->sql->execute($update);

			$this->dispatcher->dispatch(PortalHook::onPageSaving, ['item' => $item]);

			$this->saveTranslations($data, true);
			$this->saveTags($data, true);
			$this->saveOptions($data, true);

			if ($data['author_id'] !== User::$me->id) {
				$title = $data['title'];

				Logging::logAction('update_lp_page', [
					$this->entity => Str::html('a', $title)->href(LP_PAGE_URL . $data['slug'])
				]);
			}

			$this->transaction->commit();
		} catch (Exception $e) {
			$this->transaction->rollback();

			ErrorHandler::fatal($e->getMessage(), false);
		}
	}

	private function getTags(int $item): array
	{
		$tags = [];

		foreach ($this->fetchTags([$item]) as $tag) {
			$tags[$tag['tag_id']] = $tag;
		}

		return $tags;
	}

	private function saveTags(array $data, bool $replace = false): void
	{
		$rows = [];
		foreach ($data['tags'] as $tag) {
			$rows[] = [
				'page_id' => $data['id'],
				'tag_id'  => $tag,
			];
		}

		if ($rows === [])
			return;

		$sqlObject = $replace
			? $this->sql->replace('lp_page_tag')->setConflictKeys(['page_id', 'tag_id'])->batch($rows)
			: $this->sql->insert('lp_page_tag')->batch($rows);

		$this->sql->execute($sqlObject);
	}

	private function getPublishTime(array $data): int
	{
		$publishTime = time();

		if ($data['date']) {
			$publishTime = strtotime((string) $data['date']);
		}

		if ($data['time']) {
			$publishTime = strtotime(
				date('Y-m-d', $publishTime) . ' ' . $data['time']
			);
		}

		return $publishTime;
	}

	private function getImageFromContent(string $content, string $type): string
	{
		if (empty(Config::$modSettings['lp_page_og_image']))
			return '';

		$content = Content::parse($content, $type);
		$imageIsFound = preg_match_all('/<img(.*)src(.*)=(.*)"(.*)"/U', $content, $values);

		if ($imageIsFound && is_array($values)) {
			$allImages = array_pop($values);
			$image = Config::$modSettings['lp_page_og_image'] == 1
				? array_shift($allImages)
				: array_pop($allImages);

			return Utils::htmlspecialchars($image);
		}

		return '';
	}
}
