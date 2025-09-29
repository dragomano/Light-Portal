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

use Bugo\Compat\Config;
use Bugo\Compat\Db;
use Bugo\Compat\Lang;
use Bugo\Compat\Logging;
use Bugo\Compat\Msg;
use Bugo\Compat\Security;
use Bugo\Compat\User;
use Bugo\Compat\Utils;
use Bugo\LightPortal\Enums\AlertAction;
use Bugo\LightPortal\Enums\EntryType;
use Bugo\LightPortal\Enums\NotifyType;
use Bugo\LightPortal\Enums\Permission;
use Bugo\LightPortal\Enums\PortalHook;
use Bugo\LightPortal\Enums\PortalSubAction;
use Bugo\LightPortal\Enums\Status;
use Bugo\LightPortal\Events\HasEvents;
use Bugo\LightPortal\Lists\CategoryList;
use Bugo\LightPortal\Migrations\PortalAdapterInterface;
use Bugo\LightPortal\Migrations\PortalSqlInterface;
use Bugo\LightPortal\Utils\Content;
use Bugo\LightPortal\Utils\DateTime;
use Bugo\LightPortal\Utils\Icon;
use Bugo\LightPortal\Utils\Notify;
use Bugo\LightPortal\Utils\Setting;
use Bugo\LightPortal\Utils\Str;
use Laminas\Db\Sql\Predicate\Expression;
use Laminas\Db\Sql\Select;

use function Bugo\LightPortal\app;

use const LP_PAGE_URL;

if (! defined('SMF'))
	die('No direct access...');

final class PageRepository extends AbstractRepository implements PageRepositoryInterface
{
	use HasEvents;

	protected string $entity = 'page';

	protected PortalSqlInterface $sql;

	public function __construct(protected PortalAdapterInterface $adapter)
	{
		$this->sql = $adapter->getSqlBuilder();
	}

	public function getAll(
		int $start,
		int $limit,
		string $sort,
		string $queryString = '',
		array $queryParams = []
	): array
	{
		$result = Db::$db->query('
			SELECT
				p.*, GREATEST(p.created_at, p.updated_at) AS date,
				COALESCE(mem.real_name, {string:guest}) AS author_name,
				COALESCE(t.title, tf.title, {string:empty_string}) AS title
			FROM {db_prefix}lp_pages AS p
				LEFT JOIN {db_prefix}members AS mem ON (p.author_id = mem.id_member)
				LEFT JOIN {db_prefix}lp_params AS par ON (p.page_id = par.item_id AND par.type = {literal:page} AND par.name = {literal:allow_comments})
				LEFT JOIN {db_prefix}lp_translations AS t ON (
					p.page_id = t.item_id AND t.type = {literal:page} AND t.lang = {string:lang}
				)
				LEFT JOIN {db_prefix}lp_translations AS tf ON (
					p.page_id = tf.item_id AND tf.type = {literal:page} AND tf.lang = {string:fallback_lang}
				)
			WHERE 1=1' . (empty($queryString) ? '' : '
				' . $queryString) . '
			ORDER BY {raw:sort}
			LIMIT {int:start}, {int:limit}',
			array_merge($queryParams, $this->getLangQueryParams(), [
				'user_id' => User::$me->id,
				'sort'    => $sort,
				'start'   => $start,
				'limit'   => $limit,
			])
		);

		$items = [];
		while ($row = Db::$db->fetch_assoc($result)) {
			Lang::censorText($row['title']);

			$items[$row['page_id']] = [
				'id'           => (int) $row['page_id'],
				'category_id'  => (int) $row['category_id'],
				'slug'         => $row['slug'],
				'type'         => $row['type'],
				'entry_type'   => $row['entry_type'],
				'status'       => (int) $row['status'],
				'num_views'    => (int) $row['num_views'],
				'num_comments' => (int) $row['num_comments'],
				'author_id'    => (int) $row['author_id'],
				'author_name'  => $row['author_name'],
				'date'         => DateTime::relative((int) $row['date']),
				'created_at'   => (int) $row['created_at'],
				'updated_at'   => (int) $row['updated_at'],
				'is_front'     => Setting::isFrontpage($row['slug']),
				'title'        => $row['title'],
			];
		}

		Db::$db->free_result($result);

		return $items;
	}

	public function getTotalCount(string $queryString = '', array $queryParams = []): int
	{
		$result = Db::$db->query('
			SELECT COUNT(p.page_id)
			FROM {db_prefix}lp_pages AS p
				LEFT JOIN {db_prefix}lp_translations AS t ON (
					p.page_id = t.item_id AND t.type = {literal:page} AND t.lang = {string:lang}
				)
			WHERE 1=1' . (empty($queryString) ? '' : '
				' . $queryString),
			array_merge($queryParams, $this->getLangQueryParams(), ['user_id' => User::$me->id])
		);

		[$count] = Db::$db->fetch_row($result);

		Db::$db->free_result($result);

		return (int) $count;
	}

	public function getData(int|string $item): array
	{
		if ($item === 0 || $item === '')
			return [];

		$result = Db::$db->query('
			SELECT
				p.*, pp.name, pp.value,
				COALESCE(mem.real_name, {string:guest}) AS author_name,
				COALESCE(NULLIF(t.title, {string:empty_string}), tf.title, {string:empty_string}) AS title,
				COALESCE(NULLIF(t.content, {string:empty_string}), tf.content, {string:empty_string}) AS content,
				COALESCE(NULLIF(t.description, {string:empty_string}), tf.description, {string:empty_string}) AS description,
				CASE WHEN pac.value != \'0\' THEN p.num_comments ELSE 0 END AS num_comments,
				COALESCE(com.created_at, 0) AS sort_value
			FROM {db_prefix}lp_pages AS p
				LEFT JOIN {db_prefix}members AS mem ON (p.author_id = mem.id_member)
				LEFT JOIN {db_prefix}lp_translations AS t ON (
					p.page_id = t.item_id AND t.type = {literal:page} AND t.lang = {string:lang}
				)
				LEFT JOIN {db_prefix}lp_translations AS tf ON (
					p.page_id = tf.item_id AND tf.type = {literal:page} AND tf.lang = {string:fallback_lang}
				)
				LEFT JOIN {db_prefix}lp_params AS pp ON (p.page_id = pp.item_id AND pp.type = {literal:page})
				LEFT JOIN {db_prefix}lp_params AS pac ON (p.page_id = pac.item_id AND pac.type = {literal:page} AND pac.name = {literal:allow_comments})
				LEFT JOIN {db_prefix}lp_comments AS com ON (com.id = p.last_comment_id)
			WHERE p.' . (is_int($item) ? 'page_id = {int:item}' : 'slug = {string:item}'),
			array_merge(
				$this->getLangQueryParams(),
				[
					'item' => $item,
				]
			)
		);

		while ($row = Db::$db->fetch_assoc($result)) {
			$data ??= [
				'id'              => (int) $row['page_id'],
				'category_id'     => (int) $row['category_id'],
				'author_id'       => (int) $row['author_id'],
				'author'          => $row['author_name'],
				'slug'            => $row['slug'],
				'type'            => $row['type'],
				'entry_type'      => $row['entry_type'],
				'permissions'     => (int) $row['permissions'],
				'status'          => (int) $row['status'],
				'num_views'       => (int) $row['num_views'],
				'num_comments'    => (int) $row['num_comments'],
				'created_at'      => (int) $row['created_at'],
				'updated_at'      => (int) $row['updated_at'],
				'last_comment_id' => (int) $row['last_comment_id'],
				'sort_value'      => (int) $row['sort_value'],
				'image'           => $this->getImageFromContent($row['content'], $row['type']),
				'title'           => $row['title'],
				'content'         => $row['content'],
				'description'     => $row['description'],
			];

			$data['options'][$row['name']] = $row['value'];
		}

		Db::$db->free_result($result);

		return $data ?? [];
	}

	public function setData(int $item = 0): void
	{
		if (isset(Utils::$context['post_errors']) || $this->request()->hasNot(['save', 'save_exit'])) {
			return;
		}

		Security::checkSubmitOnce('check');

		$this->prepareBbcContent(Utils::$context['lp_page']);

		if (empty($item)) {
			$item = $this->addData();
		} else {
			$this->updateData($item);
		}

		$this->cache()->flush();

		$this->session()->free('lp');

		if ($this->request()->has('save_exit')) {
			$this->response()->redirect('action=admin;area=lp_pages;sa=main');
		}

		if ($this->request()->has('save')) {
			$this->response()->redirect('action=admin;area=lp_pages;sa=edit;id=' . $item);
		}
	}

	public function remove(array $items): void
	{
		if ($items === [])
			return;

		Db::$db->query('
			UPDATE {db_prefix}lp_pages
			SET deleted_at = {int:time}
			WHERE page_id IN ({array_int:items})',
			[
				'time'  => time(),
				'items' => $items,
			]
		);

		$this->session()->free('lp');
	}

	public function restore(array $items): void
	{
		if ($items === [])
			return;

		Db::$db->query('
			UPDATE {db_prefix}lp_pages
			SET deleted_at = 0
			WHERE page_id IN ({array_int:items})',
			[
				'items' => $items,
			]
		);

		$this->session()->free('lp');
	}

	public function removePermanently(array $items): void
	{
		if ($items === [])
			return;

		$this->events()->dispatch(PortalHook::onPageRemoving, ['items' => $items]);

		Db::$db->query('
			DELETE FROM {db_prefix}lp_pages
			WHERE page_id IN ({array_int:items})',
			[
				'items' => $items,
			]
		);

		Db::$db->query('
			DELETE FROM {db_prefix}lp_translations
			WHERE item_id IN ({array_int:items})
				AND type = {literal:page}',
			[
				'items' => $items,
			]
		);

		Db::$db->query('
			DELETE FROM {db_prefix}lp_params
			WHERE item_id IN ({array_int:items})
				AND type = {literal:page}',
			[
				'items' => $items,
			]
		);

		Db::$db->query('
			DELETE FROM {db_prefix}lp_page_tag
			WHERE page_id IN ({array_int:items})',
			[
				'items' => $items,
			]
		);

		$result = Db::$db->query('
			SELECT id FROM {db_prefix}lp_comments
			WHERE page_id IN ({array_int:items})',
			[
				'items' => $items,
			]
		);

		app(CommentRepository::class)->removeFromResult($result);

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
			'author_name;desc'  => ['field' => 'COALESCE(mem.real_name, ?)', 'direction' => 'desc', 'bind' => $params['guest']],
			'author_name'       => ['field' => 'COALESCE(mem.real_name, ?)', 'direction' => 'asc', 'bind' => $params['guest']],
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

		$nextWhereSql = '(' . $sortField . ' ' . $nextPrimaryOp . ' ? OR (' . $sortField . ' = ? AND ' . $secondaryField . ' ' . $nextSecondaryOp . ' ?))';
		$prevWhereSql = '(' . $sortField . ' ' . $prevPrimaryOp . ' ? OR (' . $sortField . ' = ? AND ' . $secondaryField . ' ' . $prevSecondaryOp . ' ?))';

		$nextWhere = new Expression($nextWhereSql, $nextWhereParams);
		$prevWhere = new Expression($prevWhereSql, $prevWhereParams);

		$languages = array_unique([$params['lang'], $params['fallback_lang']]);

		$base = $this->sql->select()
			->from(['p' => 'lp_pages'])
			->columns([
				'page_id',
				'slug',
				'title' => new Expression('COALESCE(t.title, tf.title)')
			])
			->join(
				['t' => 'lp_translations'],
				new Expression(
					't.item_id = p.page_id AND t.type = ? AND t.lang = ?',
					['page', $params['lang']]
				),
				[],
				Select::JOIN_LEFT
			)
			->join(
				['tf' => 'lp_translations'],
				new Expression(
					'tf.item_id = p.page_id AND tf.type = ? AND tf.lang = ?',
					['page', $params['fallback_lang']]
				),
				[],
				Select::JOIN_LEFT
			)
			->where(['(t.lang IN (?) OR tf.lang IN (?))' => [$languages, $languages]])
			->having(['title IS NOT NULL']);

		if (str_contains($sorting, 'last_comment')) {
			$base->join(
				['com' => 'lp_comments'],
				'com.id = p.last_comment_id',
				['created_at'],
				Select::JOIN_LEFT
			);
		}

		if (str_contains($sorting, 'author_name')) {
			$base->join(
				['mem' => 'members'],
				'mem.id_member = p.author_id',
				['real_name'],
				Select::JOIN_LEFT
			);
		}

		$listOrder = [
			new Expression($sortField . ' ' . $sortDirection, $sortBind !== null ? [$sortBind] : []),
			new Expression($secondaryField . ' ' . $sortDirection),
			new Expression('CASE WHEN t.title IS NOT NULL THEN 1 WHEN tf.title IS NOT NULL THEN 2 END')
		];

		$reverseOrder = [
			new Expression($sortField . ' ' . ($listAsc ? 'DESC' : 'ASC'), $sortBind !== null ? [$sortBind] : []),
			new Expression($secondaryField . ' ' . ($listAsc ? 'DESC' : 'ASC')),
			new Expression('CASE WHEN t.title IS NOT NULL THEN 1 WHEN tf.title IS NOT NULL THEN 2 END')
		];

		$prev = clone $base;
		$next = clone $base;

		$prev->where($baseWhere)->where($prevWhere)->order($reverseOrder)->limit(1);
		$next->where($baseWhere)->where($nextWhere)->order($listOrder)->limit(1);

		$result = [
			'prev' => $this->sql->prepareStatementForSqlObject($prev)->execute()->current() ?: [],
			'next' => $this->sql->prepareStatementForSqlObject($next)->execute()->current() ?: [],
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
		$slugWords  = explode('_', (string) $page['slug']);

		$searchFormula = '';
		foreach ($titleWords as $key => $word) {
			$searchFormula .= ($searchFormula ? ' + ' : '') . 'CASE
			WHEN lower(t.title) LIKE lower(\'%' . htmlentities($word) . '%\')
			THEN ' . (count($titleWords) - $key) * 2 . ' ELSE 0 END';
		}

		foreach ($slugWords as $key => $word) {
			$searchFormula .= ' + CASE
			WHEN lower(p.slug) LIKE lower(\'%' . $word . '%\')
			THEN ' . (count($slugWords) - $key) . ' ELSE 0 END';
		}

		$result = Db::$db->query('
			SELECT
				p.page_id, p.slug, p.type, (' . $searchFormula . ') AS related,
				COALESCE(t.title, tf.title, {string:empty_string}) AS title,
				COALESCE(t.content, tf.content, {string:empty_string}) AS content
			FROM {db_prefix}lp_pages AS p
				LEFT JOIN {db_prefix}lp_translations AS t ON (
					p.page_id = t.item_id AND t.type = {literal:page} AND t.lang = {string:lang}
				)
				LEFT JOIN {db_prefix}lp_translations AS tf ON (
					p.page_id = tf.item_id AND tf.type = {literal:page} AND tf.lang = {string:fallback_lang}
				)
			WHERE (' . $searchFormula . ') > 0
				AND p.status = {int:status}
				AND entry_type = {string:type}
				AND p.created_at <= {int:current_time}
				AND p.permissions IN ({array_int:permissions})
				AND p.page_id != {int:current_page}
			ORDER BY related DESC
			LIMIT 4',
			array_merge($this->getLangQueryParams(), [
				'status'       => $page['status'],
				'type'         => $page['entry_type'],
				'current_time' => time(),
				'permissions'  => Permission::all(),
				'current_page' => $page['id'],
			])
		);

		$items = [];
		while ($row = Db::$db->fetch_assoc($result)) {
			if (Setting::isFrontpage($row['slug']))
				continue;

			$row['content'] = Content::parse($row['content'], $row['type']);

			$image = Str::getImageFromText($row['content']);

			$items[$row['page_id']] = [
				'id'    => $row['page_id'],
				'slug'  => $row['slug'],
				'link'  => LP_PAGE_URL . $row['slug'],
				'image' => $image ?: (Config::$modSettings['lp_image_placeholder'] ?? ''),
				'title' => $row['title'],
			];
		}

		Db::$db->free_result($result);

		return $items;
	}

	public function updateNumViews(int $item): void
	{
		Db::$db->query('
			UPDATE {db_prefix}lp_pages
			SET num_views = num_views + 1
			WHERE page_id = {int:item}
				AND status NOT IN ({array_int:statuses})',
			[
				'item'     => $item,
				'statuses' => [Status::INACTIVE->value, Status::UNAPPROVED->value],
			]
		);
	}

	public function getMenuItems(): array
	{
		return $this->langCache('menu_pages')
			->setFallback(function () {
				$result = Db::$db->query('
				SELECT
					p.page_id, p.slug, p.permissions, pp2.value AS icon,
					(
						SELECT title
						FROM {db_prefix}lp_translations
						WHERE item_id = p.page_id
							AND type = {literal:page}
							AND lang IN ({string:lang}, {string:fallback_lang})
						ORDER BY lang = {string:lang} DESC
						LIMIT 1
					) AS page_title
				FROM {db_prefix}lp_pages AS p
					LEFT JOIN {db_prefix}lp_params AS pp ON (p.page_id = pp.item_id AND pp.type = {literal:page})
					LEFT JOIN {db_prefix}lp_params AS pp2 ON (
						p.page_id = pp2.item_id AND pp2.type = {literal:page} AND pp2.name = {literal:page_icon}
					)
				WHERE p.status = {int:status}
					AND p.entry_type IN ({array_string:types})
					AND p.created_at <= {int:current_time}
					AND pp.name = {literal:show_in_menu}
					AND pp.value = {string:show_in_menu}',
					array_merge($this->getLangQueryParams(), [
						'types'        => EntryType::withoutDrafts(),
						'status'       => Status::ACTIVE->value,
						'current_time' => time(),
						'show_in_menu' => '1',
					])
				);

				$pages = [];
				while ($row = Db::$db->fetch_assoc($result)) {
					Lang::censorText($row['page_title']);

					$pages[$row['page_id']] = [
						'id'          => (int) $row['page_id'],
						'slug'        => $row['slug'],
						'permissions' => (int) $row['permissions'],
						'icon'        => $row['icon'],
						'title'       => $row['page_title'],
					];
				}

				Db::$db->free_result($result);

				return $pages;
			});
	}

	public function prepareData(?array &$data): void
	{
		if (empty($data))
			return;

		$isAuthor = $data['author_id'] && $data['author_id'] == User::$me->id;

		$data['created']  = DateTime::relative((int) $data['created_at']);
		$data['updated']  = DateTime::relative((int) $data['updated_at']);
		$data['can_view'] = Permission::canViewItem($data['permissions']) || User::$me->is_admin || $isAuthor;
		$data['can_edit'] = User::$me->is_admin
			|| User::$me->allowedTo('light_portal_manage_pages_any')
			|| (User::$me->allowedTo('light_portal_manage_pages_own') && $isAuthor);

		if ($data['type'] === 'bbc') {
			$data['content'] = Msg::un_preparsecode($data['content']);
		}

		if (! empty($data['category_id'])) {
			$categories = app(CategoryList::class)();
			$data['category'] = $categories[$data['category_id']]['title'];
		}

		$data['tags'] = $this->getTags($data['id']);

		$this->events()->dispatch(PortalHook::preparePageData, ['data' => &$data, 'isAuthor' => $isAuthor]);
	}

	private function addData(): int
	{
		Db::$db->transaction('begin');

		$item = (int) Db::$db->insert('',
			'{db_prefix}lp_pages',
			array_merge([
				'category_id' => 'int',
				'author_id'   => 'int',
				'slug'        => 'string-255',
				'type'        => 'string',
				'entry_type'  => 'string',
				'permissions' => 'int',
				'status'      => 'int',
				'created_at'  => 'int',
			], Config::$db_type === 'postgresql' ? ['page_id' => 'int'] : []),
			array_merge([
				Utils::$context['lp_page']['category_id'],
				Utils::$context['lp_page']['author_id'],
				Utils::$context['lp_page']['slug'],
				Utils::$context['lp_page']['type'],
				Utils::$context['lp_page']['entry_type'],
				Utils::$context['lp_page']['permissions'],
				Utils::$context['lp_page']['status'],
				$this->getPublishTime(),
			], Config::$db_type === 'postgresql' ? [$this->getAutoIncrementValue()] : []),
			['page_id'],
			1
		);

		if (empty($item)) {
			Db::$db->transaction('rollback');
			return 0;
		}

		$this->events()->dispatch(PortalHook::onPageSaving, ['item' => $item]);

		$this->saveTranslations($item);
		$this->saveTags($item);
		$this->saveOptions($item);

		Db::$db->transaction();

		// Notify page moderators about new page
		$options = [
			'item'      => $item,
			'time'      => $this->getPublishTime(),
			'author_id' => Utils::$context['lp_page']['author_id'],
			'title'     => Utils::$context['lp_page']['title'],
			'url'       => LP_PAGE_URL . Utils::$context['lp_page']['slug']
		];

		if (! User::$me->allowedTo('light_portal_manage_pages_any')) {
			Notify::send(NotifyType::NEW_PAGE->name(), AlertAction::PAGE_UNAPPROVED->name(), $options);
		}

		return $item;
	}

	private function updateData(int $item): void
	{
		Db::$db->transaction('begin');

		Db::$db->query('
			UPDATE {db_prefix}lp_pages
			SET category_id = {int:category_id}, author_id = {int:author_id}, slug = {string:slug},
				type = {string:type}, entry_type = {string:entry_type}, permissions = {int:permissions},
				status = {int:status}, updated_at = {int:updated_at}
			WHERE page_id = {int:page_id}',
			[
				'category_id' => Utils::$context['lp_page']['category_id'],
				'author_id'   => Utils::$context['lp_page']['author_id'],
				'slug'        => Utils::$context['lp_page']['slug'],
				'type'        => Utils::$context['lp_page']['type'],
				'entry_type'  => Utils::$context['lp_page']['entry_type'],
				'permissions' => Utils::$context['lp_page']['permissions'],
				'status'      => Utils::$context['lp_page']['status'],
				'updated_at'  => time(),
				'page_id'     => $item,
			]
		);

		$this->events()->dispatch(PortalHook::onPageSaving, ['item' => $item]);

		$this->saveTranslations($item, 'replace');
		$this->saveTags($item, 'replace');
		$this->saveOptions($item, 'replace');

		if (Utils::$context['lp_page']['author_id'] !== User::$me->id) {
			$title = Utils::$context['lp_page']['title'];

			Logging::logAction('update_lp_page', [
				'page' => Str::html('a', $title)->href(LP_PAGE_URL . Utils::$context['lp_page']['slug'])
			]);
		}

		Db::$db->transaction();
	}

	private function getTags(int $item): array
	{
		$result = Db::$db->query('
			SELECT tag.tag_id, tag.icon, COALESCE(t.title, tf.title, {string:empty_string}) AS title
			FROM {db_prefix}lp_tags AS tag
				INNER JOIN {db_prefix}lp_page_tag AS pt ON (tag.tag_id = pt.tag_id)
				LEFT JOIN {db_prefix}lp_translations AS t ON (
					tag.tag_id = t.item_id AND t.type = {literal:tag} AND t.lang = {string:lang}
				)
				LEFT JOIN {db_prefix}lp_translations AS tf ON (
					tag.tag_id = tf.item_id AND tf.type = {literal:tag} AND tf.lang = {string:fallback_lang}
				)
			WHERE tag.status = {int:status}
				AND pt.page_id = {int:page_id}
			ORDER BY title',
			array_merge($this->getLangQueryParams(), [
				'status'  => Status::ACTIVE->value,
				'page_id' => $item,
			])
		);

		$items = [];
		while ($row = Db::$db->fetch_assoc($result)) {
			Lang::censorText($row['title']);

			$items[$row['tag_id']] = [
				'icon'  => Icon::parse($row['icon'] ?: 'fas fa-tag'),
				'href'  => PortalSubAction::TAGS->url() . ';id=' . $row['tag_id'],
				'title' => $row['title'],
			];
		}

		Db::$db->free_result($result);

		return $items;
	}

	private function saveTags(int $item, string $method = ''): void
	{
		Db::$db->query('
			DELETE FROM {db_prefix}lp_page_tag
			WHERE page_id = {int:item}',
			[
				'item' => $item,
			]
		);

		$relations = [];
		foreach (Utils::$context['lp_' . $this->entity]['tags'] as $tag) {
			$relations[] = [
				'page_id' => $item,
				'tag_id'  => $tag,
			];
		}

		if ($relations === [])
			return;

		Db::$db->insert($method,
			'{db_prefix}lp_page_tag',
			[
				'page_id' => 'int',
				'tag_id'  => 'int',
			],
			$relations,
			['page_id', 'tag_id'],
		);
	}

	private function getPublishTime(): int
	{
		$publishTime = time();

		if (Utils::$context['lp_page']['date']) {
			$publishTime = strtotime((string) Utils::$context['lp_page']['date']);
		}

		if (Utils::$context['lp_page']['time']) {
			$publishTime = strtotime(
				date('Y-m-d', $publishTime) . ' ' . Utils::$context['lp_page']['time']
			);
		}

		return $publishTime;
	}

	private function getAutoIncrementValue(): int
	{
		$result = Db::$db->query(/** @lang text */ "
			SELECT setval('{db_prefix}lp_pages_seq', (SELECT MAX(page_id) FROM {db_prefix}lp_pages))"
		);

		[$value] = Db::$db->fetch_row($result);

		Db::$db->free_result($result);

		return (int) $value + 1;
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
