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
 * @version 16.12.21
 */

namespace Bugo\LightPortal\Addons\PageList;

use Bugo\LightPortal\Addons\Plugin;
use Bugo\LightPortal\Helper;

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

		$parameters['categories'] = array(
			'name'   => 'categories',
			'filter' => FILTER_VALIDATE_INT,
			'flags'  => FILTER_REQUIRE_ARRAY
		);
		$parameters['sort']       = FILTER_SANITIZE_STRING;
		$parameters['num_pages']  = FILTER_VALIDATE_INT;
	}

	public function prepareBlockFields()
	{
		global $context, $txt;

		if ($context['lp_block']['type'] !== 'page_list')
			return;

		// Prepare the category list
		$all_categories     = Helper::getAllCategories();
		$current_categories = $context['lp_block']['options']['parameters']['categories'] ?? [];
		$current_categories = is_array($current_categories) ? $current_categories : explode(',', $current_categories);

		$data = [];
		foreach ($all_categories as $id => $category) {
			$data[] = "\t\t\t\t" . '{text: "' . $category['name'] . '", value: "' . $id . '", selected: ' . (in_array($id, $current_categories) ? 'true' : 'false') . '}';
		}

		addInlineJavaScript('
		new SlimSelect({
			select: "#categories",
			data: [' . "\n" . implode(",\n", $data) . '
			],
			hideSelectedOption: true,
			showSearch: false,
			placeholder: "' . $txt['lp_page_list']['categories_subtext'] . '",
			searchHighlight: true,
			closeOnSelect: false
		});', true);

		$context['posting_fields']['categories']['label']['text'] = $txt['lp_categories'];
		$context['posting_fields']['categories']['input'] = array(
			'type' => 'select',
			'attributes' => array(
				'id'       => 'categories',
				'name'     => 'categories[]',
				'multiple' => true
			),
			'options' => array()
		);

		$context['posting_fields']['sort']['label']['text'] = $txt['lp_page_list']['sort'];
		$context['posting_fields']['sort']['input'] = array(
			'type' => 'select',
			'attributes' => array(
				'id' => 'sort'
			),
			'options' => array()
		);

		$sort_set = array_combine(array('page_id', 'author_name', 'title', 'alias', 'type', 'num_views', 'created_at', 'updated_at'), $txt['lp_page_list']['sort_set']);

		foreach ($sort_set as $key => $value) {
			$context['posting_fields']['sort']['input']['options'][$value] = array(
				'value'    => $key,
				'selected' => $key == $context['lp_block']['options']['parameters']['sort']
			);
		}

		$context['posting_fields']['num_pages']['label']['text'] = $txt['lp_page_list']['num_pages'];
		$context['posting_fields']['num_pages']['input'] = array(
			'type' => 'number',
			'after' => $txt['lp_page_list']['num_pages_subtext'],
			'attributes' => array(
				'id'    => 'num_pages',
				'min'   => 0,
				'max'   => 999,
				'value' => $context['lp_block']['options']['parameters']['num_pages']
			)
		);
	}

	public function getData(array $parameters): array
	{
		global $smcFunc, $txt, $scripturl;

		$titles = Helper::getAllTitles();
		$all_categories = Helper::getAllCategories();

		if (empty($parameters['categories']))
			$parameters['categories'] = [];

		$categories = is_array($parameters['categories']) ? $parameters['categories'] : explode(',', $parameters['categories']);

		$request = $smcFunc['db_query']('', '
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
			array(
				'guest'        => $txt['guest_title'],
				'status'       => 1,
				'current_time' => time(),
				'permissions'  => Helper::getPermissions(),
				'categories'   => $categories,
				'sort'         => $parameters['sort'],
				'limit'        => $parameters['num_pages']
			)
		);

		$pages = [];
		while ($row = $smcFunc['db_fetch_assoc']($request)) {
			if (Helper::isFrontpage($row['alias']))
				continue;

			$pages[$row['page_id']] = array(
				'id'            => $row['page_id'],
				'category_id'   => $row['category_id'],
				'category_name' => $all_categories[$row['category_id']]['name'],
				'category_link' => $scripturl . '?action=' . LP_ACTION . ';sa=categories;id=' . $row['category_id'],
				'title'         => $titles[$row['page_id']] ?? [],
				'author_id'     => $row['author_id'],
				'author_name'   => $row['author_name'],
				'alias'         => $row['alias'],
				'num_views'     => $row['num_views'],
				'num_comments'  => $row['num_comments'],
				'created_at'    => $row['created_at'],
				'updated_at'    => $row['updated_at']
			);
		}

		$smcFunc['db_free_result']($request);
		$smcFunc['lp_num_queries']++;

		return $pages;
	}

	public function prepareContent(string $type, int $block_id, int $cache_time, array $parameters)
	{
		global $user_info, $scripturl, $txt, $modSettings;

		if ($type !== 'page_list')
			return;

		$page_list = Helper::cache('page_list_addon_b' . $block_id . '_u' . $user_info['id'])
			->setLifeTime($cache_time)
			->setFallback(__CLASS__, 'getData', $parameters);

		if (! empty($page_list)) {
			echo '
		<ul class="normallist page_list">';

			foreach ($page_list as $page) {
				if (empty($title = Helper::getTranslatedTitle($page['title'])))
					continue;

				echo '
			<li>
				<a href="', $scripturl, '?', LP_PAGE_PARAM, '=', $page['alias'], '">', $title, '</a> ', $txt['by'], ' ', (empty($page['author_id']) ? $page['author_name'] : '<a href="' . $scripturl . '?action=profile;u=' . $page['author_id'] . '">' . $page['author_name'] . '</a>'), ', ', Helper::getFriendlyTime($page['created_at']), ' (', Helper::getPluralText($page['num_views'], $txt['lp_views_set']);

				if (! empty($page['num_comments']) && ! empty($modSettings['lp_show_comment_block']) && $modSettings['lp_show_comment_block'] === 'default')
					echo ', ' . Helper::getPluralText($page['num_comments'], $txt['lp_comments_set']);

				echo ')
			</li>';
			}

			echo '
		</ul>';
		} else {
			echo '<div class="errorbox">', $txt['lp_page_list']['no_items'], '</div>';
		}
	}
}
