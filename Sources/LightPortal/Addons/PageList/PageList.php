<?php

/**
 * PageList.php
 *
 * @package PageList (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2022 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 21.02.22
 */

namespace Bugo\LightPortal\Addons\PageList;

use Bugo\LightPortal\Addons\Plugin;

if (! defined('LP_NAME'))
	die('No direct access...');

class PageList extends Plugin
{
	public string $icon = 'far fa-file-alt';

	public function blockOptions(array &$options)
	{
		$options['page_list']['parameters'] = [
			'categories' => [],
			'sort'       => 'page_id',
			'num_pages'  => 10,
		];
	}

	public function validateBlockData(array &$parameters, string $type)
	{
		if ($type !== 'page_list')
			return;

		$parameters['categories'] = [
			'name'   => 'categories',
			'filter' => FILTER_VALIDATE_INT,
			'flags'  => FILTER_REQUIRE_ARRAY
		];
		$parameters['sort']       = FILTER_SANITIZE_STRING;
		$parameters['num_pages']  = FILTER_VALIDATE_INT;
	}

	public function prepareBlockFields()
	{
		if ($this->context['lp_block']['type'] !== 'page_list')
			return;

		$this->context['posting_fields']['categories']['label']['text'] = $this->txt['lp_categories'];
		$this->context['posting_fields']['categories']['input'] = [
			'type' => 'select',
			'attributes' => [
				'id'       => 'categories',
				'name'     => 'categories[]',
				'multiple' => true
			],
			'options' => []
		];

		$this->context['posting_fields']['sort']['label']['text'] = $this->txt['lp_page_list']['sort'];
		$this->context['posting_fields']['sort']['input'] = [
			'type' => 'select',
			'attributes' => [
				'id' => 'sort'
			],
			'options' => []
		];

		$sort_set = array_combine(['page_id', 'author_name', 'title', 'alias', 'type', 'num_views', 'created_at', 'updated_at'], $this->txt['lp_page_list']['sort_set']);

		foreach ($sort_set as $key => $value) {
			$this->context['posting_fields']['sort']['input']['options'][$value] = [
				'value'    => $key,
				'selected' => $key == $this->context['lp_block']['options']['parameters']['sort']
			];
		}

		$this->context['posting_fields']['num_pages']['label']['text'] = $this->txt['lp_page_list']['num_pages'];
		$this->context['posting_fields']['num_pages']['input'] = [
			'type' => 'number',
			'after' => $this->txt['lp_page_list']['num_pages_subtext'],
			'attributes' => [
				'id'    => 'num_pages',
				'min'   => 0,
				'max'   => 999,
				'value' => $this->context['lp_block']['options']['parameters']['num_pages']
			]
		];

		$this->context['all_categories'] = $this->getAllCategories();

		$this->loadTemplate()->withLayer('page_list');
	}

	public function getData(array $parameters): array
	{
		$titles = $this->getAllTitles();
		$all_categories = $this->getAllCategories();

		if (empty($parameters['categories']))
			$parameters['categories'] = [];

		$categories = is_array($parameters['categories']) ? $parameters['categories'] : explode(',', $parameters['categories']);

		$request = $this->smcFunc['db_query']('', '
			SELECT
				p.page_id, p.category_id, p.alias, p.type, p.num_views, p.num_comments, p.created_at, p.updated_at,
				COALESCE(mem.real_name, {string:guest}) AS author_name, mem.id_member AS author_id
			FROM {db_prefix}lp_pages AS p
				LEFT JOIN {db_prefix}members AS mem ON (p.author_id = mem.id_member)
			WHERE p.status = {int:status}
				AND p.created_at <= {int:current_time}
				AND p.permissions IN ({array_int:permissions})' . (empty($categories) ? '' : '
				AND p.category_id IN ({array_int:categories})') . '
			ORDER BY {raw:sort} DESC' . (empty($parameters['num_pages']) ? '' : '
			LIMIT {int:limit}'),
			[
				'guest'        => $this->txt['guest_title'],
				'status'       => 1,
				'current_time' => time(),
				'permissions'  => $this->getPermissions(),
				'categories'   => $categories,
				'sort'         => $parameters['sort'],
				'limit'        => $parameters['num_pages']
			]
		);

		$pages = [];
		while ($row = $this->smcFunc['db_fetch_assoc']($request)) {
			if ($this->isFrontpage($row['alias']))
				continue;

			$pages[$row['page_id']] = [
				'id'            => $row['page_id'],
				'category_id'   => $row['category_id'],
				'category_name' => $all_categories[$row['category_id']]['name'],
				'category_link' => LP_BASE_URL . ';sa=categories;id=' . $row['category_id'],
				'title'         => $titles[$row['page_id']] ?? [],
				'author_id'     => $row['author_id'],
				'author_name'   => $row['author_name'],
				'alias'         => $row['alias'],
				'num_views'     => $row['num_views'],
				'num_comments'  => $row['num_comments'],
				'created_at'    => $row['created_at'],
				'updated_at'    => $row['updated_at']
			];
		}

		$this->smcFunc['db_free_result']($request);
		$this->context['lp_num_queries']++;

		return $pages;
	}

	public function prepareContent(string $type, int $block_id, int $cache_time, array $parameters)
	{
		if ($type !== 'page_list')
			return;

		$page_list = $this->cache('page_list_addon_b' . $block_id . '_u' . $this->user_info['id'])
			->setLifeTime($cache_time)
			->setFallback(__CLASS__, 'getData', $parameters);

		if ($page_list) {
			echo '
		<ul class="normallist page_list">';

			foreach ($page_list as $page) {
				if (empty($title = $this->getTranslatedTitle($page['title'])))
					continue;

				echo '
			<li>
				<a href="', $this->scripturl, '?', LP_PAGE_PARAM, '=', $page['alias'], '">', $title, '</a> ', $this->txt['by'], ' ', (empty($page['author_id']) ? $page['author_name'] : '<a href="' . $this->scripturl . '?action=profile;u=' . $page['author_id'] . '">' . $page['author_name'] . '</a>'), ', ', $this->getFriendlyTime($page['created_at']), ' (', __('lp_views_set', ['views' => $page['num_views']]);

				if ($page['num_comments'] && ! empty($this->modSettings['lp_show_comment_block']) && $this->modSettings['lp_show_comment_block'] === 'default')
					echo ', ' . __('lp_comments_set', ['comments' => $page['num_comments']]);

				echo ')
			</li>';
			}

			echo '
		</ul>';
		} else {
			echo '<div class="errorbox">', $this->txt['lp_page_list']['no_items'], '</div>';
		}
	}
}
