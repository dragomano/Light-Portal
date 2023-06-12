<?php

/**
 * RandomPages.php
 *
 * @package RandomPages (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2022-2023 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 30.04.23
 */

namespace Bugo\LightPortal\Addons\RandomPages;

use Bugo\LightPortal\Addons\Block;
use Bugo\LightPortal\Partials\CategorySelect;

if (! defined('LP_NAME'))
	die('No direct access...');

class RandomPages extends Block
{
	public string $icon = 'fas fa-random';

	public function blockOptions(array &$options)
	{
		$options['random_pages']['no_content_class'] = true;

		$options['random_pages']['parameters'] = [
			'categories' => '',
			'num_pages'  => 10,
		];
	}

	public function validateBlockData(array &$parameters, string $type)
	{
		if ($type !== 'random_pages')
			return;

		$parameters['categories'] = FILTER_DEFAULT;
		$parameters['num_pages']  = FILTER_VALIDATE_INT;
	}

	public function prepareBlockFields()
	{
		if ($this->context['lp_block']['type'] !== 'random_pages')
			return;

		$this->context['posting_fields']['categories']['label']['html'] = $this->txt['lp_categories'];
		$this->context['posting_fields']['categories']['input']['tab'] = 'content';
		$this->context['posting_fields']['categories']['input']['html'] = (new CategorySelect)([
			'id'    => 'categories',
			'hint'  => $this->txt['lp_random_pages']['categories_select'],
			'value' => $this->context['lp_block']['options']['parameters']['categories'] ?? '',
		]);

		$this->context['posting_fields']['num_pages']['label']['text'] = $this->txt['lp_random_pages']['num_pages'];
		$this->context['posting_fields']['num_pages']['input'] = [
			'type' => 'number',
			'attributes' => [
				'id'    => 'num_pages',
				'min'   => 1,
				'value' => $this->context['lp_block']['options']['parameters']['num_pages']
			]
		];
	}

	public function getData(array $parameters): array
	{
		$categories = empty($parameters['categories']) ? null : explode(',', $parameters['categories']);
		$num_pages  = empty($parameters['num_pages']) ? 0 : (int) $parameters['num_pages'];

		if (empty($num_pages))
			return [];

		$titles = $this->getEntityList('title');

		if ($this->db_type === 'postgresql') {
			$result = $this->smcFunc['db_query']('', '
				WITH RECURSIVE r AS (
					WITH b AS (
						SELECT min(p.page_id), (
							SELECT p.page_id FROM {db_prefix}lp_pages AS p
							WHERE p.status = {int:status}
								AND p.created_at <= {int:current_time}
								AND p.permissions IN ({array_int:permissions})' . (empty($categories) ? '' : '
								AND p.category_id IN ({array_int:categories})') . '
							ORDER BY p.page_id DESC
							LIMIT 1 OFFSET {int:limit} - 1
						) max
						FROM {db_prefix}lp_pages AS p
						WHERE p.status = {int:status}
							AND p.created_at <= {int:current_time}
							AND p.permissions IN ({array_int:permissions})' . (empty($categories) ? '' : '
							AND p.category_id IN ({array_int:categories})') . '
					)
					(
						SELECT p.page_id, min, max, array[]::integer[] || p.page_id AS a, 0 AS n
						FROM {db_prefix}lp_pages AS p, b
						WHERE p.status = {int:status}
							AND p.created_at <= {int:current_time}
							AND p.permissions IN ({array_int:permissions})
							AND p.page_id >= min + ((max - min) * random())::int' . (empty($categories) ? '' : '
							AND p.category_id IN ({array_int:categories})') . '
						LIMIT 1
					) UNION ALL (
						SELECT p.page_id, min, max, a || p.page_id, r.n + 1 AS n
						FROM {db_prefix}lp_pages AS p, r
						WHERE p.status = {int:status}
							AND p.created_at <= {int:current_time}
							AND p.permissions IN ({array_int:permissions})
							AND p.page_id >= min + ((max - min) * random())::int
							AND p.page_id <> all(a)' . (empty($categories) ? '' : '
							AND p.category_id IN ({array_int:categories})') . '
							AND r.n + 1 < {int:limit}
						LIMIT 1
					)
				)
				SELECT p.page_id
				FROM {db_prefix}lp_pages AS p, r
				WHERE r.page_id = p.page_id',
				[
					'status'       => 1,
					'current_time' => time(),
					'permissions'  => $this->getPermissions(),
					'categories'   => $categories,
					'limit'        => $num_pages
				]
			);

			$page_ids = [];
			while ($row = $this->smcFunc['db_fetch_assoc']($result))
				$page_ids[] = $row['page_id'];

			$this->smcFunc['db_free_result']($result);
			$this->context['lp_num_queries']++;

			if (empty($page_ids))
				return $this->getData(array_merge($parameters, ['num_pages' => $num_pages - 1]));

			$result = $this->smcFunc['db_query']('', '
				SELECT p.page_id, p.alias, p.created_at, p.num_views, COALESCE(mem.real_name, {string:guest}) AS author_name, mem.id_member AS author_id
				FROM {db_prefix}lp_pages AS p
					LEFT JOIN {db_prefix}members AS mem ON (p.author_id = mem.id_member)
				WHERE p.page_id IN ({array_int:page_ids})',
				[
					'guest'    => $this->txt['guest_title'],
					'page_ids' => $page_ids
				]
			);
		} else {
			$result = $this->smcFunc['db_query']('', '
				SELECT p.page_id, p.alias, p.created_at, p.num_views, COALESCE(mem.real_name, {string:guest}) AS author_name, mem.id_member AS author_id
				FROM {db_prefix}lp_pages AS p
					LEFT JOIN {db_prefix}members AS mem ON (p.author_id = mem.id_member)
				WHERE p.status = {int:status}
					AND p.created_at <= {int:current_time}
					AND p.permissions IN ({array_int:permissions})' . (empty($categories) ? '' : '
					AND p.category_id IN ({array_int:categories})') . '
				ORDER BY RAND()
				LIMIT {int:limit}',
				[
					'guest'        => $this->txt['guest_title'],
					'status'       => 1,
					'current_time' => time(),
					'permissions'  => $this->getPermissions(),
					'categories'   => $categories,
					'limit'        => $num_pages
				]
			);
		}

		$pages = [];
		while ($row = $this->smcFunc['db_fetch_assoc']($result)) {
			$pages[] = [
				'page_id'     => $row['page_id'],
				'alias'       => $row['alias'],
				'created_at'  => $row['created_at'],
				'num_views'   => $row['num_views'],
				'title'       => $titles[$row['page_id']] ?? [],
				'author_id'   => $row['author_id'],
				'author_name' => $row['author_name'],
			];
		}

		$this->smcFunc['db_free_result']($result);
		$this->context['lp_num_queries']++;

		return $pages;
	}

	public function prepareContent(string $type, int $block_id, int $cache_time, array $parameters)
	{
		if ($type !== 'random_pages')
			return;

		$randomPages = $this->cache('random_pages_addon_b' . $block_id . '_u' . $this->user_info['id'])
			->setLifeTime($cache_time)
			->setFallback(self::class, 'getData', $parameters);

		if ($randomPages) {
			echo '
			<ul class="random_pages noup">';

			foreach ($randomPages as $page) {
				if (empty($title = $this->getTranslatedTitle($page['title'])))
					continue;

				echo '
				<li class="windowbg">
					<a href="', $this->scripturl, '?', LP_PAGE_PARAM, '=', $page['alias'], '">', $title, '</a> ', $this->txt['by'], ' ', (empty($page['author_id']) ? $page['author_name'] : '<a href="' . $this->scripturl . '?action=profile;u=' . $page['author_id'] . '">' . $page['author_name'] . '</a>'), ', ', $this->getFriendlyTime($page['created_at']), ' (', $this->translate('lp_views_set', ['views' => $page['num_views']]);

				echo ')
				</li>';
			}

			echo '
			</ul>';
		}
	}
}
