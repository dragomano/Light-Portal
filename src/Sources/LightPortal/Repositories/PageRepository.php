<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.8
 */

namespace Bugo\LightPortal\Repositories;

use Bugo\Compat\{Config, Db, Lang, Logging};
use Bugo\Compat\{Msg, Security, User, Utils};
use Bugo\LightPortal\Args\ItemArgs;
use Bugo\LightPortal\Enums\{EntryType, Permission, PortalHook, Status};
use Bugo\LightPortal\EventManager;
use Bugo\LightPortal\Plugins\Event;
use Bugo\LightPortal\Utils\{CacheTrait, Content, DateTime};
use Bugo\LightPortal\Utils\{EntityDataTrait, Icon, Notify};
use Bugo\LightPortal\Utils\{RequestTrait, Setting, Str};

use function array_filter;
use function array_merge;
use function array_pop;
use function array_shift;
use function count;
use function date;
use function explode;
use function filter_input;
use function is_array;
use function is_int;
use function preg_match_all;
use function str_contains;
use function strtotime;
use function time;

use const LP_BASE_URL;
use const LP_PAGE_URL;

if (! defined('SMF'))
	die('No direct access...');

final class PageRepository extends AbstractRepository
{
	use CacheTrait;
	use EntityDataTrait;
	use RequestTrait;

	protected string $entity = 'page';

	private CommentRepository $commentRepository;

	public function __construct()
	{
		$this->commentRepository = new CommentRepository();
	}

	public function getAll(
		int $start,
		int $limit,
		string $sort,
		string $queryString = '',
		array $queryParams = []
	): array
	{
		$result = Db::$db->query('', '
			SELECT p.page_id, p.category_id, p.author_id, p.slug, p.type, p.entry_type, p.permissions, p.status,
				p.num_views, p.num_comments, GREATEST(p.created_at, p.updated_at) AS date,
				mem.real_name AS author_name, COALESCE(t.value, tf.value, p.slug) AS page_title
			FROM {db_prefix}lp_pages AS p
				LEFT JOIN {db_prefix}members AS mem ON (p.author_id = mem.id_member)
				LEFT JOIN {db_prefix}lp_titles AS t ON (
					p.page_id = t.item_id AND t.type = {literal:page} AND t.lang = {string:lang}
				)
				LEFT JOIN {db_prefix}lp_titles AS tf ON (
					p.page_id = tf.item_id AND tf.type = {literal:page} AND tf.lang = {string:fallback_lang}
				)
			WHERE 1=1' . (empty($queryString) ? '' : '
				' . $queryString) . '
			ORDER BY {raw:sort}
			LIMIT {int:start}, {int:limit}',
			array_merge($queryParams, [
				'lang'          => User::$info['language'],
				'fallback_lang' => Config::$language,
				'user_id'       => User::$info['id'],
				'sort'          => $sort,
				'start'         => $start,
				'limit'         => $limit,
			])
		);

		$items = [];
		while ($row = Db::$db->fetch_assoc($result)) {
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
				'created_at'   => DateTime::relative((int) $row['date']),
				'is_front'     => Setting::isFrontpage($row['slug']),
				'title'        => $row['page_title'],
			];
		}

		Db::$db->free_result($result);

		return $items;
	}

	public function getTotalCount(string $queryString = '', array $queryParams = []): int
	{
		$result = Db::$db->query('', '
			SELECT COUNT(p.page_id)
			FROM {db_prefix}lp_pages AS p
				LEFT JOIN {db_prefix}lp_titles AS t ON (
					p.page_id = t.item_id AND t.type = {literal:page} AND t.lang = {string:lang}
				)
			WHERE 1=1' . (empty($queryString) ? '' : '
				' . $queryString),
			array_merge($queryParams, [
				'lang'    => User::$info['language'],
				'user_id' => User::$info['id'],
			])
		);

		[$count] = Db::$db->fetch_row($result);

		Db::$db->free_result($result);

		return (int) $count;
	}

	public function getData(int|string $item): array
	{
		if ($item === 0 || $item === '')
			return [];

		$result = Db::$db->query('', '
			SELECT
				p.page_id, p.category_id, p.author_id, p.slug, p.description, p.content, p.type, p.entry_type,
				p.permissions, p.status, p.num_views, p.created_at, p.updated_at,
				COALESCE(mem.real_name, {string:guest}) AS author_name, pt.lang, pt.value AS title, pp.name, pp.value
			FROM {db_prefix}lp_pages AS p
				LEFT JOIN {db_prefix}members AS mem ON (p.author_id = mem.id_member)
				LEFT JOIN {db_prefix}lp_titles AS pt ON (p.page_id = pt.item_id AND pt.type = {literal:page})
				LEFT JOIN {db_prefix}lp_params AS pp ON (p.page_id = pp.item_id AND pp.type = {literal:page})
			WHERE p.' . (is_int($item) ? 'page_id = {int:item}' : 'slug = {string:item}'),
			[
				'guest' => Lang::$txt['guest_title'],
				'item'  => $item,
			]
		);

		while ($row = Db::$db->fetch_assoc($result)) {
			Lang::censorText($row['content']);

			$ogImage = null;
			if (! empty(Config::$modSettings['lp_page_og_image'])) {
				$content = $row['content'];
				$content = Content::parse($content, $row['type']);
				$imageIsFound = preg_match_all('/<img(.*)src(.*)=(.*)"(.*)"/U', $content, $values);

				if ($imageIsFound && is_array($values)) {
					$allImages = array_pop($values);
					$image = Config::$modSettings['lp_page_og_image'] == 1
						? array_shift($allImages)
						: array_pop($allImages);
					$ogImage = Utils::htmlspecialchars($image);
				}
			}

			$data ??= [
				'id'          => (int) $row['page_id'],
				'category_id' => (int) $row['category_id'],
				'author_id'   => (int) $row['author_id'],
				'author'      => $row['author_name'],
				'slug'        => $row['slug'],
				'description' => $row['description'],
				'content'     => $row['content'],
				'type'        => $row['type'],
				'entry_type'  => $row['entry_type'],
				'permissions' => (int) $row['permissions'],
				'status'      => (int) $row['status'],
				'num_views'   => (int) $row['num_views'],
				'created_at'  => (int) $row['created_at'],
				'updated_at'  => (int) $row['updated_at'],
				'image'       => $ogImage,
			];

			$data['titles'][$row['lang']] = $row['title'];

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
		$this->prepareTitles();

		if (empty($item)) {
			Utils::$context['lp_page']['titles'] = array_filter(Utils::$context['lp_page']['titles']);
			$item = $this->addData();
		} else {
			$this->updateData($item);
		}

		$this->cache()->flush();

		$this->session()->free('lp');

		if ($this->request()->has('save_exit')) {
			Utils::redirectexit('action=admin;area=lp_pages;sa=main');
		}

		if ($this->request()->has('save')) {
			Utils::redirectexit('action=admin;area=lp_pages;sa=edit;id=' . $item);
		}
	}

	public function remove(array $items): void
	{
		if ($items === [])
			return;

		Db::$db->query('', '
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

		Db::$db->query('', '
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

		EventManager::getInstance()->dispatch(PortalHook::onPageRemoving, new Event(new ItemsArgs($items)));

		Db::$db->query('', '
			DELETE FROM {db_prefix}lp_pages
			WHERE page_id IN ({array_int:items})',
			[
				'items' => $items,
			]
		);

		Db::$db->query('', '
			DELETE FROM {db_prefix}lp_titles
			WHERE item_id IN ({array_int:items})
				AND type = {literal:page}',
			[
				'items' => $items,
			]
		);

		Db::$db->query('', '
			DELETE FROM {db_prefix}lp_params
			WHERE item_id IN ({array_int:items})
				AND type = {literal:page}',
			[
				'items' => $items,
			]
		);

		Db::$db->query('', '
			DELETE FROM {db_prefix}lp_page_tag
			WHERE page_id IN ({array_int:items})',
			[
				'items' => $items,
			]
		);

		$result = Db::$db->query('', '
			SELECT id FROM {db_prefix}lp_comments
			WHERE page_id IN ({array_int:items})',
			[
				'items' => $items,
			]
		);

		$this->commentRepository->removeFromResult($result);

		$this->session()->free('lp');
	}

	public function getPrevNextLinks(array $page): array
	{
		$orders = [
			'CASE WHEN com.created_at > 0 THEN 0 ELSE 1 END, comment_date DESC',
			'p.created_at DESC',
			'p.created_at',
			'date DESC',
		];

		$withinCategory = str_contains(
			filter_input(INPUT_SERVER, 'HTTP_REFERER') ?? '', 'action=portal;sa=categories;id'
		);

		$result = Db::$db->query('', '
			(
				SELECT p.page_id, p.slug, GREATEST(p.created_at, p.updated_at) AS date,
					CASE WHEN COALESCE(par.value, \'0\') != \'0\' THEN p.num_comments ELSE 0 END AS num_comments,
					com.created_at AS comment_date
				FROM {db_prefix}lp_pages AS p
					LEFT JOIN {db_prefix}lp_comments AS com ON (p.last_comment_id = com.id)
					LEFT JOIN {db_prefix}lp_params AS par ON (
						par.item_id = com.page_id
						AND par.type = {literal:page}
						AND par.name = {literal:allow_comments}
					)
				WHERE p.page_id != {int:page_id}' . ($withinCategory ? '
					AND p.category_id = {int:category_id}' : '') . '
					AND p.created_at <= {int:created_at}
					AND p.created_at <= {int:current_time}
					AND p.status = {int:status}
					AND p.permissions IN ({array_int:permissions})
					ORDER BY ' . (empty(Config::$modSettings['lp_frontpage_order_by_replies'])
				? '' : 'num_comments DESC, ') . $orders[Config::$modSettings['lp_frontpage_article_sorting'] ?? 0] . '
				LIMIT 1
			)
			UNION ALL
			(
				SELECT p.page_id, p.slug, GREATEST(p.created_at, p.updated_at) AS date,
					CASE WHEN COALESCE(par.value, \'0\') != \'0\' THEN p.num_comments ELSE 0 END AS num_comments,
					com.created_at AS comment_date
				FROM {db_prefix}lp_pages AS p
					LEFT JOIN {db_prefix}lp_comments AS com ON (p.last_comment_id = com.id)
					LEFT JOIN {db_prefix}lp_params AS par ON (
						par.item_id = com.page_id
						AND par.type = {literal:page}
						AND par.name = {literal:allow_comments}
					)
				WHERE p.page_id != {int:page_id}' . ($withinCategory ? '
					AND p.category_id = {int:category_id}' : '') . '
					AND p.created_at >= {int:created_at}
					AND p.created_at <= {int:current_time}
					AND p.entry_type = {string:type}
					AND p.status = {int:status}
					AND p.permissions IN ({array_int:permissions})
				ORDER BY ' . (empty(Config::$modSettings['lp_frontpage_order_by_replies'])
				? '' : 'num_comments DESC, ') . $orders[Config::$modSettings['lp_frontpage_article_sorting'] ?? 0] . '
				LIMIT 1
			)',
			[
				'page_id'      => $page['id'],
				'category_id'  => $page['category_id'],
				'created_at'   => $page['created_at'],
				'current_time' => time(),
				'type'         => $page['entry_type'],
				'status'       => $page['status'],
				'permissions'  => Permission::all(),
			]
		);

		[$prevId, $prevSlug] = Db::$db->fetch_row($result);
		[$nextId, $nextSlug] = Db::$db->fetch_row($result);

		Db::$db->free_result($result);

		return [$prevId, $prevSlug, $nextId, $nextSlug];
	}

	public function getRelatedPages(array $page): array
	{
		$titleWords = explode(' ', Str::getTranslatedTitle($page['titles']));
		$slugWords  = explode('_', (string) $page['slug']);

		$searchFormula = '';
		foreach ($titleWords as $key => $word) {
			$searchFormula .= ($searchFormula ? ' + ' : '') . 'CASE
			WHEN lower(t.value) LIKE lower(\'%' . $word . '%\')
			THEN ' . (count($titleWords) - $key) * 2 . ' ELSE 0 END';
		}

		foreach ($slugWords as $key => $word) {
			$searchFormula .= ' + CASE
			WHEN lower(p.slug) LIKE lower(\'%' . $word . '%\')
			THEN ' . (count($slugWords) - $key) . ' ELSE 0 END';
		}

		$result = Db::$db->query('', '
			SELECT
				p.page_id, p.slug, p.content, p.type, (' . $searchFormula . ') AS related,
				COALESCE(t.value, tf.value) AS title
			FROM {db_prefix}lp_pages AS p
				LEFT JOIN {db_prefix}lp_titles AS t ON (p.page_id = t.item_id AND t.lang = {string:current_lang})
				LEFT JOIN {db_prefix}lp_titles AS tf ON (p.page_id = tf.item_id AND tf.lang = {string:fallback_lang})
			WHERE (' . $searchFormula . ') > 0
				AND p.status = {int:status}
				AND entry_type = {string:type}
				AND p.created_at <= {int:current_time}
				AND p.permissions IN ({array_int:permissions})
				AND p.page_id != {int:current_page}
			ORDER BY related DESC
			LIMIT 4',
			[
				'current_lang'  => Utils::$context['user']['language'],
				'fallback_lang' => Config::$language,
				'status'        => $page['status'],
				'type'          => $page['entry_type'],
				'current_time'  => time(),
				'permissions'   => Permission::all(),
				'current_page'  => $page['id'],
			]
		);

		$items = [];
		while ($row = Db::$db->fetch_assoc($result)) {
			if (Setting::isFrontpage($row['slug']))
				continue;

			$row['content'] = Content::parse($row['content'], $row['type']);

			$image = Str::getImageFromText($row['content']);

			$items[$row['page_id']] = [
				'id'    => $row['page_id'],
				'title' => $row['title'],
				'slug'  => $row['slug'],
				'link'  => LP_PAGE_URL . $row['slug'],
				'image' => $image ?: (Config::$modSettings['lp_image_placeholder'] ?? ''),
			];
		}

		Db::$db->free_result($result);

		return $items;
	}

	public function updateNumViews(int $item): void
	{
		Db::$db->query('', '
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
		if (($pages = $this->cache()->get('menu_pages')) === null) {
			$titles = $this->getEntityData('title');

			$result = Db::$db->query('', '
				SELECT p.page_id, p.slug, p.permissions, pp2.value AS icon
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
				[
					'types'        => EntryType::withoutDrafts(),
					'status'       => Status::ACTIVE->value,
					'current_time' => time(),
					'show_in_menu' => '1',
				]
			);

			$pages = [];
			while ($row = Db::$db->fetch_assoc($result)) {
				$pages[$row['page_id']] = [
					'id'          => (int) $row['page_id'],
					'slug'        => $row['slug'],
					'permissions' => (int) $row['permissions'],
					'icon'        => $row['icon'],
				];

				$pages[$row['page_id']]['titles'] = $titles[$row['page_id']];
			}

			Db::$db->free_result($result);

			$this->cache()->put('menu_pages', $pages);
		}

		return $pages;
	}

	public function prepareData(?array &$data): void
	{
		if (empty($data))
			return;

		$isAuthor = $data['author_id'] && $data['author_id'] == User::$info['id'];

		$data['created']  = DateTime::relative((int) $data['created_at']);
		$data['updated']  = DateTime::relative((int) $data['updated_at']);
		$data['can_view'] = Permission::canViewItem($data['permissions']) || User::$info['is_admin'] || $isAuthor;
		$data['can_edit'] = User::$info['is_admin']
			|| Utils::$context['allow_light_portal_manage_pages_any']
			|| (Utils::$context['allow_light_portal_manage_pages_own'] && $isAuthor);

		if ($data['type'] === 'bbc') {
			$data['content'] = Msg::un_preparsecode($data['content']);
		}

		if (! empty($data['category_id'])) {
			$data['category'] = $this->getEntityData('category')[$data['category_id']]['title'];
		}

		$data['tags'] = $this->getTags($data['id']);

		EventManager::getInstance()->dispatch(
			PortalHook::preparePageData,
			new Event(new class ($data, $isAuthor) {
				public function __construct(public array &$data, public readonly bool $isAuthor) {}
			})
		);
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
				'description' => 'string-255',
				'content'     => 'string',
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
				Utils::$context['lp_page']['description'],
				Utils::$context['lp_page']['content'],
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

		EventManager::getInstance()->dispatch(PortalHook::onPageSaving, new Event(new ItemArgs($item)));

		$this->saveTitles($item);
		$this->saveTags($item);
		$this->saveOptions($item);

		Db::$db->transaction('commit');

		// Notify page moderators about new page
		$title = Utils::$context['lp_page']['titles'][User::$info['language']]
			?? Utils::$context['lp_page']['titles'][Config::$language];

		$options = [
			'item'      => $item,
			'time'      => $this->getPublishTime(),
			'author_id' => Utils::$context['lp_page']['author_id'],
			'title'     => $title,
			'url'       => LP_PAGE_URL . Utils::$context['lp_page']['slug']
		];

		if (empty(Utils::$context['allow_light_portal_manage_pages_any'])) {
			Notify::send('new_page', 'page_unapproved', $options);
		}

		return $item;
	}

	private function updateData(int $item): void
	{
		Db::$db->transaction('begin');

		Db::$db->query('', '
			UPDATE {db_prefix}lp_pages
			SET category_id = {int:category_id}, author_id = {int:author_id}, slug = {string:slug},
				description = {string:description}, content = {string:content}, type = {string:type},
				entry_type = {string:entry_type}, permissions = {int:permissions}, status = {int:status},
				updated_at = {int:updated_at}
			WHERE page_id = {int:page_id}',
			[
				'category_id' => Utils::$context['lp_page']['category_id'],
				'author_id'   => Utils::$context['lp_page']['author_id'],
				'slug'        => Utils::$context['lp_page']['slug'],
				'description' => Utils::$context['lp_page']['description'],
				'content'     => Utils::$context['lp_page']['content'],
				'type'        => Utils::$context['lp_page']['type'],
				'entry_type'  => Utils::$context['lp_page']['entry_type'],
				'permissions' => Utils::$context['lp_page']['permissions'],
				'status'      => Utils::$context['lp_page']['status'],
				'updated_at'  => time(),
				'page_id'     => $item,
			]
		);

		EventManager::getInstance()->dispatch(PortalHook::onPageSaving, new Event(new ItemArgs($item)));

		$this->saveTitles($item, 'replace');
		$this->saveTags($item, 'replace');
		$this->saveOptions($item, 'replace');

		if (Utils::$context['lp_page']['author_id'] !== User::$info['id']) {
			$title = Utils::$context['lp_page']['titles'][User::$info['language']];
			Logging::logAction('update_lp_page', [
				'page' => Str::html('a', $title)->href(LP_PAGE_URL . Utils::$context['lp_page']['slug'])
			]);
		}

		Db::$db->transaction('commit');
	}

	private function getTags(int $item): array
	{
		$result = Db::$db->query('', '
			SELECT tag.tag_id, tag.icon, COALESCE(t.value, tf.value) AS title
			FROM {db_prefix}lp_tags AS tag
				INNER JOIN {db_prefix}lp_page_tag AS pt ON (tag.tag_id = pt.tag_id)
				LEFT JOIN {db_prefix}lp_titles AS t ON (
					tag.tag_id = t.item_id AND t.type = {literal:tag} AND t.lang = {string:lang}
				)
				LEFT JOIN {db_prefix}lp_titles AS tf ON (
					tag.tag_id = tf.item_id AND tf.type = {literal:tag} AND tf.lang = {string:fallback_lang}
				)
			WHERE tag.status = {int:status}
				AND pt.page_id = {int:page_id}
			ORDER BY title',
			[
				'lang'          => User::$info['language'],
				'fallback_lang' => Config::$language,
				'status'        => Status::ACTIVE->value,
				'page_id'       => $item,
			]
		);

		$items = [];
		while ($row = Db::$db->fetch_assoc($result)) {
			$items[$row['tag_id']] = [
				'icon'  => Icon::parse($row['icon'] ?: 'fas fa-tag'),
				'title' => $row['title'],
				'href'  => LP_BASE_URL . ';sa=tags;id=' . $row['tag_id'],
			];
		}

		Db::$db->free_result($result);

		return $items;
	}

	private function saveTags(int $item, string $method = ''): void
	{
		Db::$db->query('', '
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
		$result = Db::$db->query('', /** @lang text */ "
			SELECT setval('{db_prefix}lp_pages_seq', (SELECT MAX(page_id) FROM {db_prefix}lp_pages))"
		);

		[$value] = Db::$db->fetch_row($result);

		Db::$db->free_result($result);

		return (int) $value + 1;
	}
}
