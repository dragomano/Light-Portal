<?php

declare(strict_types=1);

/**
 * PageRepository.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.6
 */

namespace Bugo\LightPortal\Repositories;

use Bugo\Compat\{Config, Db, Logging, Lang};
use Bugo\Compat\{Msg, Security, User, Utils};
use Bugo\LightPortal\Actions\PageInterface;
use Bugo\LightPortal\Actions\PageListInterface;
use Bugo\LightPortal\Utils\{Content, DateTime, Notify};
use IntlException;

if (! defined('SMF'))
	die('No direct access...');

final class PageRepository extends AbstractRepository
{
	protected string $entity = 'page';

	/**
	 * @throws IntlException
	 */
	public function getAll(
		int $start,
		int $limit,
		string $sort,
		string $queryString = '',
		array $queryParams = []
	): array
	{
		$result = Db::$db->query('', '
			SELECT p.page_id, p.category_id, p.author_id, p.alias, p.type, p.permissions, p.status,
				p.num_views, p.num_comments, GREATEST(p.created_at, p.updated_at) AS date,
				mem.real_name AS author_name, COALESCE(t.title, tf.title, p.alias) AS page_title
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
				'alias'        => $row['alias'],
				'type'         => $row['type'],
				'status'       => (int) $row['status'],
				'num_views'    => (int) $row['num_views'],
				'num_comments' => (int) $row['num_comments'],
				'author_id'    => (int) $row['author_id'],
				'author_name'  => $row['author_name'],
				'created_at'   => DateTime::relative((int) $row['date']),
				'is_front'     => $this->isFrontpage($row['alias']),
				'title'        => $row['page_title'],
			];
		}

		Db::$db->free_result($result);
		Utils::$context['lp_num_queries']++;

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
		Utils::$context['lp_num_queries']++;

		return (int) $count;
	}

	/**
	 * @throws IntlException
	 */
	public function getData(int|string $item): array
	{
		if ($item === 0 || $item === '')
			return [];

		$result = Db::$db->query('', '
			SELECT
				p.page_id, p.category_id, p.author_id, p.alias, p.description, p.content, p.type, p.permissions,
				p.status, p.num_views, p.created_at, p.updated_at,
				COALESCE(mem.real_name, {string:guest}) AS author_name, pt.lang, pt.title, pp.name, pp.value
			FROM {db_prefix}lp_pages AS p
				LEFT JOIN {db_prefix}members AS mem ON (p.author_id = mem.id_member)
				LEFT JOIN {db_prefix}lp_titles AS pt ON (p.page_id = pt.item_id AND pt.type = {literal:page})
				LEFT JOIN {db_prefix}lp_params AS pp ON (p.page_id = pp.item_id AND pp.type = {literal:page})
			WHERE p.' . (is_int($item) ? 'page_id = {int:item}' : 'alias = {string:item}'),
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
					$ogImage = Utils::$smcFunc['htmlspecialchars']($image);
				}
			}

			$data ??= [
				'id'          => (int) $row['page_id'],
				'category_id' => (int) $row['category_id'],
				'author_id'   => (int) $row['author_id'],
				'author'      => $row['author_name'],
				'alias'       => $row['alias'],
				'description' => $row['description'],
				'content'     => $row['content'],
				'type'        => $row['type'],
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
		Utils::$context['lp_num_queries']++;

		$this->prepareData($data);

		return $data ?? [];
	}

	public function setData(int $item = 0): void
	{
		if (isset(Utils::$context['post_errors']) || (
			$this->request()->hasNot('save') &&
			$this->request()->hasNot('save_exit'))
		) {
			return;
		}

		Security::checkSubmitOnce('check');

		$this->prepareBbcContent(Utils::$context['lp_page']);

		if (empty($item)) {
			Utils::$context['lp_page']['titles'] = array_filter(Utils::$context['lp_page']['titles']);
			$item = $this->addData();
		} else {
			$this->updateData($item);
		}

		$this->cache()->flush();

		$this->session('lp')->free('active_pages');

		if ($this->request()->has('save_exit'))
			Utils::redirectexit('action=admin;area=lp_pages;sa=main');

		if ($this->request()->has('save'))
			Utils::redirectexit('action=admin;area=lp_pages;sa=edit;id=' . $item);
	}

	public function remove(array $items): void
	{
		if ($items === [])
			return;

		$this->hook('onPageRemoving', [$items]);

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
			DELETE FROM {db_prefix}lp_page_tags
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

		$comments = [];
		while ($row = Db::$db->fetch_assoc($result)) {
			$comments[] = $row['id'];
		}

		Db::$db->free_result($result);
		Utils::$context['lp_num_queries'] += 4;

		if ($comments) {
			Db::$db->query('', '
				DELETE FROM {db_prefix}lp_comments
				WHERE id IN ({array_int:items})',
				[
					'items' => $comments,
				]
			);

			Db::$db->query('', '
				DELETE FROM {db_prefix}lp_params
				WHERE item_id IN ({array_int:items})
					AND type = {literal:comment}',
				[
					'items' => $comments,
				]
			);

			Utils::$context['lp_num_queries'] += 2;
		}

		$this->session('lp')->free('active_pages');
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
				SELECT p.page_id, p.alias, GREATEST(p.created_at, p.updated_at) AS date,
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
				SELECT p.page_id, p.alias, GREATEST(p.created_at, p.updated_at) AS date,
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
				'status'       => $page['status'],
				'permissions'  => $this->getPermissions(),
			]
		);

		[$prevId, $prevAlias] = Db::$db->fetch_row($result);
		[$nextId, $nextAlias] = Db::$db->fetch_row($result);

		Db::$db->free_result($result);
		Utils::$context['lp_num_queries']++;

		return [$prevId, $prevAlias, $nextId, $nextAlias];
	}

	public function getRelatedPages(array $page): array
	{
		$titleWords = explode(' ', $this->getTranslatedTitle($page['titles']));
		$aliasWords = explode('_', $page['alias']);

		$searchFormula = '';
		foreach ($titleWords as $key => $word) {
			$searchFormula .= ($searchFormula ? ' + ' : '') . 'CASE
			WHEN lower(t.title) LIKE lower(\'%' . $word . '%\')
		    THEN ' . (count($titleWords) - $key) * 2 . ' ELSE 0 END';
		}

		foreach ($aliasWords as $key => $word) {
			$searchFormula .= ' + CASE
			WHEN lower(p.alias) LIKE lower(\'%' . $word . '%\')
			THEN ' . (count($aliasWords) - $key) . ' ELSE 0 END';
		}

		$result = Db::$db->query('', '
			SELECT
				p.page_id, p.alias, p.content, p.type, (' . $searchFormula . ') AS related,
				COALESCE(t.title, tf.title) AS title
			FROM {db_prefix}lp_pages AS p
				LEFT JOIN {db_prefix}lp_titles AS t ON (p.page_id = t.item_id AND t.lang = {string:current_lang})
				LEFT JOIN {db_prefix}lp_titles AS tf ON (p.page_id = tf.item_id AND tf.lang = {string:fallback_lang})
			WHERE (' . $searchFormula . ') > 0
				AND p.status = {int:status}
				AND p.created_at <= {int:current_time}
				AND p.permissions IN ({array_int:permissions})
				AND p.page_id != {int:current_page}
			ORDER BY related DESC
			LIMIT 4',
			[
				'current_lang'  => Utils::$context['user']['language'],
				'fallback_lang' => Config::$language,
				'status'        => $page['status'],
				'current_time'  => time(),
				'permissions'   => $this->getPermissions(),
				'current_page'  => $page['id'],
			]
		);

		$items = [];
		while ($row = Db::$db->fetch_assoc($result)) {
			if ($this->isFrontpage($row['alias']))
				continue;

			$row['content'] = Content::parse($row['content'], $row['type']);

			$image = $this->getImageFromText($row['content']);

			$items[$row['page_id']] = [
				'id'    => $row['page_id'],
				'title' => $row['title'],
				'alias' => $row['alias'],
				'link'  => LP_PAGE_URL . $row['alias'],
				'image' => $image ?: (Config::$modSettings['lp_image_placeholder'] ?? ''),
			];
		}

		Db::$db->free_result($result);
		Utils::$context['lp_num_queries']++;

		return $items;
	}

	public function updateNumViews(int $item): void
	{
		Db::$db->query('', '
				UPDATE {db_prefix}lp_pages
				SET num_views = num_views + 1
				WHERE page_id = {int:item}
					AND status IN ({array_int:statuses})',
			[
				'item'     => $item,
				'statuses' => [PageInterface::STATUS_ACTIVE, PageInterface::STATUS_INTERNAL],
			]
		);

		Utils::$context['lp_num_queries']++;
	}

	public function getMenuItems(): array
	{
		if (($pages = $this->cache()->get('menu_pages')) === null) {
			$titles = $this->getEntityData('title');

			$result = Db::$db->query('', '
				SELECT p.page_id, p.alias, p.permissions, pp2.value AS icon
				FROM {db_prefix}lp_pages AS p
					LEFT JOIN {db_prefix}lp_params AS pp ON (p.page_id = pp.item_id AND pp.type = {literal:page})
					LEFT JOIN {db_prefix}lp_params AS pp2 ON (
						p.page_id = pp2.item_id AND pp2.type = {literal:page} AND pp2.name = {literal:page_icon}
					)
				WHERE p.status IN ({array_int:statuses})
					AND p.created_at <= {int:current_time}
					AND pp.name = {literal:show_in_menu}
					AND pp.value = {string:show_in_menu}',
				[
					'statuses'     => [PageInterface::STATUS_ACTIVE, PageInterface::STATUS_INTERNAL],
					'current_time' => time(),
					'show_in_menu' => '1',
				]
			);

			$pages = [];
			while ($row = Db::$db->fetch_assoc($result)) {
				$pages[$row['page_id']] = [
					'id'          => (int) $row['page_id'],
					'alias'       => $row['alias'],
					'permissions' => (int) $row['permissions'],
					'icon'        => $row['icon'],
				];

				$pages[$row['page_id']]['titles'] = $titles[$row['page_id']];
			}

			Db::$db->free_result($result);
			Utils::$context['lp_num_queries']++;

			$this->cache()->put('menu_pages', $pages);
		}

		return $pages;
	}

	private function addData(): int
	{
		Db::$db->transaction('begin');

		$item = (int) Db::$db->insert('',
			'{db_prefix}lp_pages',
			array_merge([
				'category_id' => 'int',
				'author_id'   => 'int',
				'alias'       => 'string-255',
				'description' => 'string-255',
				'content'     => 'string',
				'type'        => 'string',
				'permissions' => 'int',
				'status'      => 'int',
				'created_at'  => 'int',
			], Config::$db_type === 'postgresql' ? ['page_id' => 'int'] : []),
			array_merge([
				Utils::$context['lp_page']['category_id'],
				Utils::$context['lp_page']['author_id'],
				Utils::$context['lp_page']['alias'],
				Utils::$context['lp_page']['description'],
				Utils::$context['lp_page']['content'],
				Utils::$context['lp_page']['type'],
				Utils::$context['lp_page']['permissions'],
				Utils::$context['lp_page']['status'],
				$this->getPublishTime(),
			], Config::$db_type === 'postgresql' ? [$this->getAutoIncrementValue()] : []),
			['page_id'],
			1
		);

		Utils::$context['lp_num_queries']++;

		if (empty($item)) {
			Db::$db->transaction('rollback');
			return 0;
		}

		$this->hook('onPageSaving', [$item]);

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
			'url'       => LP_PAGE_URL . Utils::$context['lp_page']['alias']
		];

		if (empty(Utils::$context['allow_light_portal_manage_pages_any']))
			Notify::send('new_page', 'page_unapproved', $options);

		return $item;
	}

	private function updateData(int $item): void
	{
		Db::$db->transaction('begin');

		Db::$db->query('', '
			UPDATE {db_prefix}lp_pages
			SET category_id = {int:category_id}, author_id = {int:author_id}, alias = {string:alias},
				description = {string:description}, content = {string:content}, type = {string:type},
				permissions = {int:permissions}, status = {int:status}, updated_at = {int:updated_at}
			WHERE page_id = {int:page_id}',
			[
				'category_id' => Utils::$context['lp_page']['category_id'],
				'author_id'   => Utils::$context['lp_page']['author_id'],
				'alias'       => Utils::$context['lp_page']['alias'],
				'description' => Utils::$context['lp_page']['description'],
				'content'     => Utils::$context['lp_page']['content'],
				'type'        => Utils::$context['lp_page']['type'],
				'permissions' => Utils::$context['lp_page']['permissions'],
				'status'      => Utils::$context['lp_page']['status'],
				'updated_at'  => time(),
				'page_id'     => $item,
			]
		);

		Utils::$context['lp_num_queries']++;

		$this->hook('onPageSaving', [$item]);

		$this->saveTitles($item, 'replace');
		$this->saveTags($item, 'replace');
		$this->saveOptions($item, 'replace');

		if (Utils::$context['lp_page']['author_id'] !== User::$info['id']) {
			$title = Utils::$context['lp_page']['titles'][User::$info['language']];
			Logging::logAction('update_lp_page', [
				'page' => '<a href="' . LP_PAGE_URL . Utils::$context['lp_page']['alias'] . '">' . $title . '</a>'
			]);
		}

		Db::$db->transaction('commit');
	}

	private function saveTags(int $item, string $method = ''): void
	{
		Db::$db->query('', '
			DELETE FROM {db_prefix}lp_page_tags
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

		if (empty($relations))
			return;

		Db::$db->insert($method,
			'{db_prefix}lp_page_tags',
			[
				'page_id' => 'int',
				'tag_id'  => 'int',
			],
			$relations,
			['page_id', 'tag_id'],
		);

		Utils::$context['lp_num_queries']++;
	}

	private function getTags(int $item): array
	{
		$result = Db::$db->query('', '
			SELECT tag.tag_id, tag.icon, COALESCE(t.title, tf.title) AS title
			FROM {db_prefix}lp_tags AS tag
				INNER JOIN {db_prefix}lp_page_tags AS pt ON (tag.tag_id = pt.tag_id)
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
				'status'        => PageListInterface::STATUS_ACTIVE,
				'page_id'       => $item,
			]
		);

		$items = [];
		while ($row = Db::$db->fetch_assoc($result)) {
			$items[$row['tag_id']] = [
				'icon'  => $this->getIcon($row['icon'] ?: 'fas fa-tag'),
				'title' => $row['title'],
				'href'  => LP_BASE_URL . ';sa=tags;id=' . $row['tag_id'],
			];
		}

		Db::$db->free_result($result);
		Utils::$context['lp_num_queries']++;

		return $items;
	}

	/**
	 * @throws IntlException
	 */
	private function prepareData(?array &$data): void
	{
		if (empty($data))
			return;

		$isAuthor = $data['author_id'] && $data['author_id'] == User::$info['id'];

		$data['created']  = DateTime::relative((int) $data['created_at']);
		$data['updated']  = DateTime::relative((int) $data['updated_at']);
		$data['can_view'] = $this->canViewItem($data['permissions']) || User::$info['is_admin'] || $isAuthor;
		$data['can_edit'] = User::$info['is_admin']
			|| Utils::$context['allow_light_portal_manage_pages_any']
			|| (Utils::$context['allow_light_portal_manage_pages_own'] && $isAuthor);

		if ($data['type'] === 'bbc') {
			$data['content'] = Msg::unPreparseCode($data['content']);
		}

		if (! empty($data['category_id'])) {
			$data['category'] = $this->getEntityData('category')[$data['category_id']]['title'];
		}

		$data['tags'] = $this->getTags($data['id']);

		$this->hook('preparePageData', [&$data, $isAuthor]);
	}

	private function getPublishTime(): int
	{
		$publishTime = time();

		if (Utils::$context['lp_page']['date'])
			$publishTime = strtotime(Utils::$context['lp_page']['date']);

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
		Utils::$context['lp_num_queries']++;

		return (int) $value + 1;
	}
}
