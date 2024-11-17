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

namespace Bugo\LightPortal\Actions;

use Bugo\Compat\{Config, Db, ErrorHandler};
use Bugo\Compat\{Lang, User, Utils};
use Bugo\LightPortal\Enums\{EntryType, Permission, Status};
use Bugo\LightPortal\Utils\{Icon, ItemList, RequestTrait, Str};

use function array_key_exists;
use function count;
use function sprintf;
use function time;

use const LP_BASE_URL;

if (! defined('SMF'))
	die('No direct access...');

final class Tag extends AbstractPageList
{
	use RequestTrait;

	public function show(PageInterface $page): void
	{
		if ($this->request()->hasNot('id')) {
			$this->showAll();
		}

		$tag = [
			'id' => (int) $this->request('id', 0)
		];

		$tags = $this->getEntityData('tag');
		if (array_key_exists($tag['id'], $tags) === false) {
			Utils::$context['error_link'] = LP_BASE_URL . ';sa=tags';
			Lang::$txt['back'] = Lang::$txt['lp_all_page_tags'];
			ErrorHandler::fatalLang('lp_tag_not_found', status: 404);
		}

		$tag = $tags[$tag['id']];
		Utils::$context['page_title'] = sprintf(Lang::$txt['lp_all_tags_by_key'], $tag['title']);

		Utils::$context['current_tag'] = $tag['id'];

		Utils::$context['canonical_url']  = LP_BASE_URL . ';sa=tags;id=' . $tag['id'];
		Utils::$context['robot_no_index'] = true;

		Utils::$context['linktree'][] = [
			'name' => Lang::$txt['lp_all_page_tags'],
			'url'  => LP_BASE_URL . ';sa=tags',
		];

		Utils::$context['linktree'][] = [
			'name' => $tag['title'],
		];

		$page->showAsCards($this);

		$listOptions = $page->getList();
		$listOptions['id'] = 'lp_tags';
		$listOptions['get_items'] = [
			'function' => $this->getPages(...)
		];

		new ItemList($listOptions);

		Utils::obExit();
	}

	public function getPages(int $start, int $limit, string $sort): array
	{
		$result = Db::$db->query('', '
			SELECT
				p.page_id, p.category_id, p.author_id, p.slug, p.description, p.content,
				p.type, p.num_views, p.num_comments, GREATEST(p.created_at, p.updated_at) AS date,
				COALESCE(mem.real_name, \'\') AS author_name, COALESCE(t.value, tf.value) AS title,
				COALESCE(tt.value, ttf.value) AS tag_title
			FROM {db_prefix}lp_pages AS p
				INNER JOIN {db_prefix}lp_page_tag AS pt ON (p.page_id = pt.page_id)
				INNER JOIN {db_prefix}lp_tags AS tag ON (pt.tag_id = tag.tag_id)
				LEFT JOIN {db_prefix}members AS mem ON (p.author_id = mem.id_member)
				LEFT JOIN {db_prefix}lp_titles AS t ON (
					p.page_id = t.item_id AND t.type = {literal:page} AND t.lang = {string:lang}
				)
				LEFT JOIN {db_prefix}lp_titles AS tf ON (
					p.page_id = tf.item_id AND tf.type = {literal:page} AND tf.lang = {string:fallback_lang}
				)
				LEFT JOIN {db_prefix}lp_titles AS tt ON (
					pt.tag_id = tt.item_id AND tt.type = {literal:tag} AND tt.lang = {string:lang}
				)
				LEFT JOIN {db_prefix}lp_titles AS ttf ON (
					pt.tag_id = ttf.item_id AND ttf.type = {literal:tag} AND ttf.lang = {string:fallback_lang}
				)
			WHERE pt.tag_id = {int:id}
				AND tag.status = {int:status}
				AND p.status = {int:status}
				AND p.entry_type IN ({array_string:types})
				AND p.created_at <= {int:current_time}
				AND p.permissions IN ({array_int:permissions})
			ORDER BY {raw:sort}
			LIMIT {int:start}, {int:limit}',
			[
				'lang'          => User::$info['language'],
				'fallback_lang' => Config::$language,
				'id'            => Utils::$context['current_tag'],
				'status'        => Status::ACTIVE->value,
				'types'         => EntryType::withoutDrafts(),
				'current_time'  => time(),
				'permissions'   => Permission::all(),
				'sort'          => $sort,
				'start'         => $start,
				'limit'         => $limit,
			]
		);

		$rows = Db::$db->fetch_all($result);

		Db::$db->free_result($result);

		return $this->getPreparedResults($rows);
	}

	public function getTotalCount(): int
	{
		$result = Db::$db->query('', '
			SELECT COUNT(p.page_id)
			FROM {db_prefix}lp_pages AS p
				INNER JOIN {db_prefix}lp_page_tag AS pt ON (p.page_id = pt.page_id)
				INNER JOIN {db_prefix}lp_tags AS tag ON (pt.tag_id = tag.tag_id)
			WHERE pt.tag_id = {int:id}
				AND tag.status = {int:status}
				AND p.status  = {int:status}
				AND p.entry_type IN ({array_string:types})
				AND p.created_at <= {int:current_time}
				AND p.permissions IN ({array_int:permissions})',
			[
				'id'           => Utils::$context['current_tag'],
				'status'       => Status::ACTIVE->value,
				'types'        => EntryType::withoutDrafts(),
				'current_time' => time(),
				'permissions'  => Permission::all(),
			]
		);

		[$count] = Db::$db->fetch_row($result);

		Db::$db->free_result($result);

		return (int) $count;
	}

	public function showAll(): void
	{
		Utils::$context['page_title']     = Lang::$txt['lp_all_page_tags'];
		Utils::$context['canonical_url']  = LP_BASE_URL . ';sa=tags';
		Utils::$context['robot_no_index'] = true;

		Utils::$context['linktree'][] = [
			'name' => Utils::$context['page_title'],
		];

		$listOptions = [
			'id' => 'tags',
			'items_per_page' => Config::$modSettings['defaultMaxListItems'] ?: 50,
			'title' => Utils::$context['page_title'],
			'no_items_label' => Lang::$txt['lp_no_tags'],
			'base_href' => Utils::$context['canonical_url'],
			'default_sort_col' => 'value',
			'get_items' => [
				'function' => $this->getAll(...)
			],
			'get_count' => [
				'function' => fn() => count($this->getAll())
			],
			'columns' => [
				'value' => [
					'header' => [
						'value' => Lang::$txt['lp_tag_column']
					],
					'data' => [
						'function' => static fn($entry) => $entry['icon'] . ' ' . Str::html('a', $entry['title'])
							->href($entry['link']),
					],
					'sort' => [
						'default' => 'title DESC',
						'reverse' => 'title'
					]
				],
				'frequency' => [
					'header' => [
						'value' => Lang::$txt['lp_frequency_column']
					],
					'data' => [
						'db'    => 'frequency',
						'class' => 'centertext'
					],
					'sort' => [
						'default' => 'frequency DESC',
						'reverse' => 'frequency'
					]
				]
			],
			'form' => [
				'href' => Utils::$context['canonical_url']
			]
		];

		new ItemList($listOptions);

		Utils::obExit();
	}

	public function getAll(int $start = 0, int $limit = 0, string $sort = 'title'): array
	{
		$result = Db::$db->query('', '
			SELECT tag.tag_id, tag.icon, COALESCE(tt.value, tf.value) AS title, COUNT(tag.tag_id) AS frequency
			FROM {db_prefix}lp_pages AS p
				INNER JOIN {db_prefix}lp_page_tag AS pt ON (p.page_id = pt.page_id)
				INNER JOIN {db_prefix}lp_tags AS tag ON (pt.tag_id = tag.tag_id)
				LEFT JOIN {db_prefix}lp_titles AS tt ON (
					pt.tag_id = tt.item_id AND tt.type = {literal:tag} AND tt.lang = {string:lang}
				)
				LEFT JOIN {db_prefix}lp_titles AS tf ON (
					pt.tag_id = tf.item_id AND tf.type = {literal:tag} AND tf.lang = {string:fallback_lang}
				)
			WHERE p.status = {int:status}
				AND p.entry_type IN ({array_string:types})
				AND p.created_at <= {int:current_time}
				AND p.permissions IN ({array_int:permissions})
				AND tag.status = {int:status}
			GROUP BY tag.tag_id, tag.icon, tt.value, tf.value
			ORDER BY {raw:sort}' . ($limit ? '
			LIMIT {int:start}, {int:limit}' : ''),
			[
				'lang'          => User::$info['language'],
				'fallback_lang' => Config::$language,
				'types'         => EntryType::names(),
				'current_time'  => time(),
				'permissions'   => Permission::all(),
				'status'        => Status::ACTIVE->value,
				'sort'          => $sort,
				'start'         => $start,
				'limit'         => $limit,
			]
		);

		$items = [];
		while ($row = Db::$db->fetch_assoc($result)) {
			$items[$row['tag_id']] = [
				'icon'      => Icon::parse($row['icon']),
				'title'     => $row['title'],
				'link'      => LP_BASE_URL . ';sa=tags;id=' . $row['tag_id'],
				'frequency' => (int) $row['frequency'],
			];
		}

		Db::$db->free_result($result);

		return $items;
	}
}
