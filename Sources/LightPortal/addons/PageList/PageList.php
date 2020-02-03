<?php

namespace Bugo\LightPortal\Addons\PageList;

use Bugo\LightPortal\Helpers;

/**
 * PageList
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2020 Bugo
 * @license https://opensource.org/licenses/BSD-3-Clause BSD
 *
 * @version 0.9.3
 */

if (!defined('SMF'))
	die('Hacking attempt...');

class PageList
{
	/**
	 * Способ сортировки страниц (см. $txt['lp_page_list_addon_sort_set'])
	 *
	 * @var string
	 */
	private static $sort = 'page_id';

	/**
	 * Добавляем параметры блока
	 *
	 * @param array $options
	 * @return void
	 */
	public static function blockOptions(&$options)
	{
		$options['page_list'] = array(
			'parameters' => array(
				'sort' => static::$sort
			)
		);
	}

	/**
	 * Валидируем параметры
	 *
	 * @param array $args
	 * @return void
	 */
	public static function validateBlockData(&$args)
	{
		global $context;

		if ($context['current_block']['type'] !== 'page_list')
			return;

		$args['parameters'] = array(
			'sort' => FILTER_SANITIZE_STRING
		);
	}

	/**
	 * Добавляем поля конкретно для этого блока
	 *
	 * @return void
	 */
	public static function prepareBlockFields()
	{
		global $context, $txt;

		if ($context['lp_block']['type'] !== 'page_list')
			return;

		$context['posting_fields']['sort']['label']['text'] = $txt['lp_page_list_addon_sort'];
		$context['posting_fields']['sort']['input'] = array(
			'type' => 'select',
			'attributes' => array(
				'id' => 'sort'
			),
			'options' => array()
		);

		foreach ($txt['lp_page_list_addon_sort_set'] as $key => $value) {
			if (!defined('JQUERY_VERSION')) {
				$context['posting_fields']['sort']['input']['options'][$value]['attributes'] = array(
					'value'    => $key,
					'selected' => $key == $context['lp_block']['options']['parameters']['sort']
				);
			} else {
				$context['posting_fields']['sort']['input']['options'][$value] = array(
					'value'    => $key,
					'selected' => $key == $context['lp_block']['options']['parameters']['sort']
				);
			}
		}
	}

	/**
	 * Получаем список активных страниц
	 *
	 * @param string $sort_type
	 * @return array
	 */
	public static function getPageList($sort_type)
	{
		global $smcFunc;

		$request = $smcFunc['db_query']('', '
			SELECT
				p.page_id, p.author_id, p.title, p.alias, p.type, p.permissions, p.num_views, p.created_at, p.updated_at, COALESCE(mem.real_name, 0) AS author_name
			FROM {db_prefix}lp_pages AS p
				LEFT JOIN {db_prefix}members AS mem ON (mem.id_member = p.author_id)
			WHERE p.alias != {string:alias}
				AND p.status = {int:status}
			ORDER BY {raw:sort} DESC',
			array(
				'alias'  => '/',
				'status' => 1,
				'sort'   => $sort_type
			)
		);

		$pages = [];
		while ($row = $smcFunc['db_fetch_assoc']($request)) {
			$pages[] = array(
				'id'         => $row['page_id'],
				'author_id'  => $row['author_id'],
				'author'     => $row['author_name'],
				'title'      => $row['title'],
				'alias'      => $row['alias'],
				'num_views'  => $row['num_views'],
				'created_at' => Helpers::getFriendlyTime($row['created_at']),
				'updated_at' => Helpers::getFriendlyTime($row['updated_at']),
				'can_show'   => Helpers::canShowItem($row['permissions'])
			);
		}

		$smcFunc['db_free_result']($request);

		$pages = array_filter($pages, function ($item) {
			return $item['can_show'] == true;
		});

		return $pages;
	}

	/**
	 * Формируем контент блока
	 *
	 * @param string $content
	 * @param string $type
	 * @param int $block_id
	 * @return void
	 */
	public static function prepareContent(&$content, $type, $block_id, $cache_time, $parameters)
	{
		global $context, $txt, $scripturl;

		if ($type !== 'page_list')
			return;

		$page_list = Helpers::useCache('page_list_addon_b' . $block_id . '_u' . $context['user']['id'] . '_sort_' . $parameters['sort'], 'getPageList', __CLASS__, $cache_time, $parameters['sort']);

		ob_start();

		if (!empty($page_list)) {
			echo '
			<ul class="normallist page_list">';

			foreach ($page_list as $page) {
				echo '
				<li>
					<a href="', $scripturl, '?page=', $page['alias'], '">', $page['title'], '</a> ', $txt['by'], ' ', (empty($page['author_id']) ? $txt['guest'] : '<a href="' . $scripturl . '?action=profile;u=' . $page['author_id'] . '">' . $page['author'] . '</a>'), ', ', $page['created_at'], ' (', Helpers::getCorrectDeclension($page['num_views'], $txt['lp_views_set']), ')
				</li>';
			}

			echo '
			</ul>';
		} else
			echo $txt['lp_page_list_addon_no_items'];

		$content = ob_get_clean();
	}
}
