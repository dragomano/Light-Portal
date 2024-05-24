<?php

/**
 * PageList.php
 *
 * @package PageList (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 24.05.24
 */

namespace Bugo\LightPortal\Addons\PageList;

use Bugo\Compat\{Config, Lang, User, Utils};
use Bugo\LightPortal\Addons\Block;
use Bugo\LightPortal\Areas\BlockArea;
use Bugo\LightPortal\Areas\Fields\{CustomField, NumberField, VirtualSelectField};
use Bugo\LightPortal\Areas\Partials\CategorySelect;
use Bugo\LightPortal\Utils\DateTime;
use IntlException;

if (! defined('LP_NAME'))
	die('No direct access...');

class PageList extends Block
{
	public string $icon = 'far fa-file-alt';

	private const SORTING_SET = [
		'page_id', 'author_name', 'title', 'slug', 'type', 'num_views', 'created_at', 'updated_at'
	];

	public function prepareBlockParams(array &$params): void
	{
		if (Utils::$context['current_block']['type'] !== 'page_list')
			return;

		$params = [
			'categories' => '',
			'sort'       => 'page_id',
			'num_pages'  => 10,
		];
	}

	public function validateBlockParams(array &$params): void
	{
		if (Utils::$context['current_block']['type'] !== 'page_list')
			return;

		$params = [
			'categories' => FILTER_DEFAULT,
			'sort'       => FILTER_DEFAULT,
			'num_pages'  => FILTER_VALIDATE_INT,
		];
	}

	public function prepareBlockFields(): void
	{
		if (Utils::$context['current_block']['type'] !== 'page_list')
			return;

		CustomField::make('categories', Lang::$txt['lp_categories'])
			->setTab(BlockArea::TAB_CONTENT)
			->setValue(static fn() => new CategorySelect(), [
				'id'    => 'categories',
				'hint'  => Lang::$txt['lp_page_list']['categories_select'],
				'value' => Utils::$context['lp_block']['options']['categories'] ?? '',
			]);

		VirtualSelectField::make('sort', Lang::$txt['lp_page_list']['sort'])
			->setOptions(array_combine(self::SORTING_SET, Lang::$txt['lp_page_list']['sort_set']))
			->setValue(Utils::$context['lp_block']['options']['sort']);

		NumberField::make('num_pages', Lang::$txt['lp_page_list']['num_pages'])
			->setAfter(Lang::$txt['lp_page_list']['num_pages_subtext'])
			->setAttribute('min', 0)
			->setAttribute('max', 999)
			->setValue(Utils::$context['lp_block']['options']['num_pages']);
	}

	public function getData(array $parameters): array
	{
		$titles = $this->getEntityData('title');

		$allCategories = $this->getEntityData('category');

		$categories = empty($parameters['categories']) ? null : explode(',', (string) $parameters['categories']);

		$result = Utils::$smcFunc['db_query']('', '
			SELECT
				p.page_id, p.category_id, p.slug, p.type, p.num_views, p.num_comments, p.created_at, p.updated_at,
				COALESCE(mem.real_name, {string:guest}) AS author_name, mem.id_member AS author_id
			FROM {db_prefix}lp_pages AS p
				LEFT JOIN {db_prefix}members AS mem ON (p.author_id = mem.id_member)
			WHERE p.status = {int:status}
				AND p.created_at <= {int:current_time}
				AND p.permissions IN ({array_int:permissions})' . ($categories ? '
				AND p.category_id IN ({array_int:categories})' : '') . '
			ORDER BY {raw:sort} DESC' . (empty($parameters['num_pages']) ? '' : '
			LIMIT {int:limit}'),
			[
				'guest'        => Lang::$txt['guest_title'],
				'status'       => 1,
				'current_time' => time(),
				'permissions'  => $this->getPermissions(),
				'categories'   => $categories,
				'sort'         => $parameters['sort'],
				'limit'        => $parameters['num_pages'],
			]
		);

		$pages = [];
		while ($row = Utils::$smcFunc['db_fetch_assoc']($result)) {
			if ($this->isFrontpage($row['slug']))
				continue;

			$pages[$row['page_id']] = [
				'id'            => $row['page_id'],
				'category_id'   => $row['category_id'],
				'category_name' => $allCategories[$row['category_id']]['title'],
				'category_link' => LP_BASE_URL . ';sa=categories;id=' . $row['category_id'],
				'title'         => $titles[$row['page_id']] ?? [],
				'author_id'     => $row['author_id'],
				'author_name'   => $row['author_name'],
				'slug'          => $row['slug'],
				'num_views'     => $row['num_views'],
				'num_comments'  => $row['num_comments'],
				'created_at'    => $row['created_at'],
				'updated_at'    => $row['updated_at']
			];
		}

		Utils::$smcFunc['db_free_result']($result);

		return $pages;
	}

	/**
	 * @throws IntlException
	 */
	public function prepareContent(object $data, array $parameters): void
	{
		if ($data->type !== 'page_list')
			return;

		$pageList = $this->cache('page_list_addon_b' . $data->id . '_u' . User::$info['id'])
			->setLifeTime($data->cacheTime)
			->setFallback(self::class, 'getData', $parameters);

		if ($pageList) {
			echo '
		<ul class="normallist page_list">';

			foreach ($pageList as $page) {
				if (empty($title = $this->getTranslatedTitle($page['title'])))
					continue;

				echo '
			<li>
				<a href="', Config::$scripturl, '?', LP_PAGE_PARAM, '=', $page['slug'], '">', $title, '</a> ', Lang::$txt['by'], ' ', (empty($page['author_id']) ? $page['author_name'] : '<a href="' . Config::$scripturl . '?action=profile;u=' . $page['author_id'] . '">' . $page['author_name'] . '</a>'), ', ', DateTime::relative($page['created_at']), ' (', Lang::getTxt('lp_views_set', ['views' => $page['num_views']]);

				if ($page['num_comments'] && $this->getCommentBlockType() === 'default') {
					echo ', ' . Lang::getTxt('lp_comments_set', ['comments' => $page['num_comments']]);
				}

				echo ')
			</li>';
			}

			echo '
		</ul>';
		} else {
			echo '<div class="errorbox">', Lang::$txt['lp_page_list']['no_items'], '</div>';
		}
	}
}
