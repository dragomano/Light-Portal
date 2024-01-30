<?php declare(strict_types=1);

/**
 * Tag.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.5
 */

namespace Bugo\LightPortal\Actions;

use Bugo\LightPortal\Utils\{Config, ErrorHandler, Lang, User, Utils};
use IntlException;

if (! defined('SMF'))
	die('No direct access...');

final class Tag extends AbstractPageList
{
	public function show(PageInterface $page): void
	{
		if ($this->request()->hasNot('id'))
			$this->showAll();

		Utils::$context['lp_tag'] = $this->request('id', 0);

		if (array_key_exists(Utils::$context['lp_tag'], $this->getEntityList('tag')) === false) {
			Utils::$context['error_link'] = LP_BASE_URL . ';sa=tags';
			Lang::$txt['back'] = Lang::$txt['lp_all_page_tags'];
			ErrorHandler::fatalLang('lp_tag_not_found', status: 404);
		}

		Utils::$context['page_title']     = sprintf(Lang::$txt['lp_all_tags_by_key'], $this->getEntityList('tag')[Utils::$context['lp_tag']]);
		Utils::$context['canonical_url']  = LP_BASE_URL . ';sa=tags;id=' . Utils::$context['lp_tag'];
		Utils::$context['robot_no_index'] = true;

		Utils::$context['linktree'][] = [
			'name' => Lang::$txt['lp_all_page_tags'],
			'url'  => LP_BASE_URL . ';sa=tags'
		];

		Utils::$context['linktree'][] = [
			'name' => Utils::$context['page_title']
		];

		if (! empty(Config::$modSettings['lp_show_items_as_articles']))
			$page->showAsCards($this);

		$listOptions = $page->getList();
		$listOptions['id'] = 'lp_tags';
		$listOptions['get_items'] = [
			'function' => [$this, 'getPages']
		];
		$listOptions['get_count'] = [
			'function' => [$this, 'getTotalCount']
		];

		$this->createList($listOptions);

		Utils::obExit();
	}

	/**
	 * @throws IntlException
	 */
	public function getPages(int $start, int $items_per_page, string $sort): array
	{
		$result = Utils::$smcFunc['db_query']('', '
			SELECT
				p.page_id, p.category_id, p.author_id, p.alias, p.description, p.content, p.type, p.num_views, p.num_comments, GREATEST(p.created_at, p.updated_at) AS date,
				COALESCE(mem.real_name, \'\') AS author_name, ps.value, t.title
			FROM {db_prefix}lp_pages AS p
				INNER JOIN {db_prefix}lp_params AS ps ON (p.page_id = ps.item_id AND ps.type = {literal:page} AND ps.name = {literal:keywords})
				LEFT JOIN {db_prefix}members AS mem ON (p.author_id = mem.id_member)
				LEFT JOIN {db_prefix}lp_titles AS t ON (p.page_id = t.item_id AND t.type = {literal:page} AND t.lang = {string:lang})
			WHERE FIND_IN_SET({int:id}, ps.value) > 0
				AND p.status IN ({array_int:statuses})
				AND p.created_at <= {int:current_time}
				AND p.permissions IN ({array_int:permissions})
			ORDER BY {raw:sort}
			LIMIT {int:start}, {int:limit}',
			[
				'lang'         => User::$info['language'],
				'id'           => Utils::$context['lp_tag'],
				'statuses'     => [PageInterface::STATUS_ACTIVE, PageInterface::STATUS_INTERNAL],
				'current_time' => time(),
				'permissions'  => $this->getPermissions(),
				'sort'         => $sort,
				'start'        => $start,
				'limit'        => $items_per_page
			]
		);

		$rows = Utils::$smcFunc['db_fetch_all']($result);

		Utils::$smcFunc['db_free_result']($result);
		Utils::$context['lp_num_queries']++;

		return $this->getPreparedResults($rows);
	}

	public function getTotalCount(): int
	{
		$result = Utils::$smcFunc['db_query']('', '
			SELECT COUNT(p.page_id)
			FROM {db_prefix}lp_pages AS p
				INNER JOIN {db_prefix}lp_params AS ps ON (p.page_id = ps.item_id AND ps.type = {literal:page} AND ps.name = {literal:keywords})
			WHERE FIND_IN_SET({int:id}, ps.value) > 0
				AND p.status IN ({array_int:statuses})
				AND p.created_at <= {int:current_time}
				AND p.permissions IN ({array_int:permissions})',
			[
				'id'           => Utils::$context['lp_tag'],
				'statuses'     => [PageInterface::STATUS_ACTIVE, PageInterface::STATUS_INTERNAL],
				'current_time' => time(),
				'permissions'  => $this->getPermissions()
			]
		);

		[$num_items] = Utils::$smcFunc['db_fetch_row']($result);

		Utils::$smcFunc['db_free_result']($result);
		Utils::$context['lp_num_queries']++;

		return (int) $num_items;
	}

	public function showAll(): void
	{
		Utils::$context['page_title']     = Lang::$txt['lp_all_page_tags'];
		Utils::$context['canonical_url']  = LP_BASE_URL . ';sa=tags';
		Utils::$context['robot_no_index'] = true;

		Utils::$context['linktree'][] = [
			'name' => Utils::$context['page_title']
		];

		$listOptions = [
			'id' => 'tags',
			'items_per_page' => Config::$modSettings['defaultMaxListItems'] ?: 50,
			'title' => Utils::$context['page_title'],
			'no_items_label' => Lang::$txt['lp_no_tags'],
			'base_href' => Utils::$context['canonical_url'],
			'default_sort_col' => 'value',
			'get_items' => [
				'function' => [$this, 'getAll']
			],
			'get_count' => [
				'function' => fn() => count($this->getAll())
			],
			'columns' => [
				'value' => [
					'header' => [
						'value' => Lang::$txt['lp_keyword_column']
					],
					'data' => [
						'function' => fn($entry) => '<a href="' . $entry['link'] . '">' . $entry['value'] . '</a>',
						'class' => 'centertext'
					],
					'sort' => [
						'default' => 't.value DESC',
						'reverse' => 't.value'
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
						'default' => 'num DESC',
						'reverse' => 'num'
					]
				]
			],
			'form' => [
				'href' => Utils::$context['canonical_url']
			]
		];

		$this->createList($listOptions);

		Utils::obExit();
	}

	public function getAll(int $start = 0, int $items_per_page = 0, string $sort = 't.value'): array
	{
		$result = Utils::$smcFunc['db_query']('', '
			SELECT t.tag_id, t.value, COUNT(t.tag_id) AS num
			FROM {db_prefix}lp_pages AS p
				INNER JOIN {db_prefix}lp_params AS ps ON (p.page_id = ps.item_id AND ps.type = {literal:page} AND ps.name = {literal:keywords})
				INNER JOIN {db_prefix}lp_tags AS t ON (FIND_IN_SET(t.tag_id, ps.value) > 0)
			WHERE p.status IN ({array_int:statuses})
				AND p.created_at <= {int:current_time}
				AND p.permissions IN ({array_int:permissions})
			GROUP BY t.tag_id, t.value
			ORDER BY {raw:sort}' . ($items_per_page ? '
			LIMIT {int:start}, {int:limit}' : ''),
			[
				'statuses'     => [PageInterface::STATUS_ACTIVE, PageInterface::STATUS_INTERNAL],
				'current_time' => time(),
				'permissions'  => $this->getPermissions(),
				'sort'         => $sort,
				'start'        => $start,
				'limit'        => $items_per_page
			]
		);

		$items = [];
		while ($row = Utils::$smcFunc['db_fetch_assoc']($result)) {
			$items[$row['tag_id']] = [
				'value'     => $row['value'],
				'link'      => LP_BASE_URL . ';sa=tags;id=' . $row['tag_id'],
				'frequency' => $row['num']
			];
		}

		Utils::$smcFunc['db_free_result']($result);
		Utils::$context['lp_num_queries']++;

		return $items;
	}
}