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
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 1.4
 */

if (!defined('SMF'))
	die('Hacking attempt...');

class PageList
{
	/**
	 * Specify an icon (from the FontAwesome Free collection)
	 *
	 * Указываем иконку (из коллекции FontAwesome Free)
	 *
	 * @var string
	 */
	public $addon_icon = 'far fa-file-alt';

	/**
	 * The sort method of pages
	 *
	 * Способ сортировки страниц (см. $txt['lp_page_list_addon_sort_set'])
	 *
	 * @var string
	 */
	private $sort = 'page_id';

	/**
	 * The maximum number of pages to output
	 *
	 * Максимальное количество страниц для вывода
	 *
	 * @var int
	 */
	private $num_pages = 10;

	/**
	 * Adding the block options
	 *
	 * Добавляем параметры блока
	 *
	 * @param array $options
	 * @return void
	 */
	public function blockOptions(&$options)
	{
		$options['page_list']['parameters']['sort']      = $this->sort;
		$options['page_list']['parameters']['num_pages'] = $this->num_pages;
	}

	/**
	 * Validate options
	 *
	 * Валидируем параметры
	 *
	 * @param array $parameters
	 * @param string $type
	 * @return void
	 */
	public function validateBlockData(&$parameters, $type)
	{
		if ($type !== 'page_list')
			return;

		$parameters['sort']      = FILTER_SANITIZE_STRING;
		$parameters['num_pages'] = FILTER_VALIDATE_INT;
	}

	/**
	 * Adding fields specifically for this block
	 *
	 * Добавляем поля конкретно для этого блока
	 *
	 * @return void
	 */
	public function prepareBlockFields()
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
			if (RC2_CLEAN) {
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

		$context['posting_fields']['num_pages']['label']['text'] = $txt['lp_page_list_addon_num_pages'];
		$context['posting_fields']['num_pages']['input'] = array(
			'type' => 'number',
			'after' => $txt['lp_page_list_addon_num_pages_subtext'],
			'attributes' => array(
				'id'    => 'num_pages',
				'min'   => 0,
				'value' => $context['lp_block']['options']['parameters']['num_pages']
			)
		);
	}

	/**
	 * Get the list of active pages
	 *
	 * Получаем список активных страниц
	 *
	 * @param array $parameters
	 * @return array
	 */
	public function getData(array $parameters)
	{
		global $smcFunc, $txt, $context;

		$titles = Helpers::getAllTitles();

		$request = $smcFunc['db_query']('', '
			SELECT
				p.page_id, p.alias, p.type, p.num_views, p.num_comments, p.created_at, p.updated_at,
				COALESCE(mem.real_name, {string:guest}) AS author_name, mem.id_member AS author_id
			FROM {db_prefix}lp_pages AS p
				LEFT JOIN {db_prefix}members AS mem ON (p.author_id = mem.id_member)
				LEFT JOIN {db_prefix}lp_titles AS t ON (p.page_id = t.item_id AND t.type = {string:type} AND t.lang = {string:lang})
			WHERE p.status = {int:status}
				AND p.created_at <= {int:current_time}
				AND p.permissions IN ({array_int:permissions})
			ORDER BY {raw:sort} DESC' . (!empty($parameters['num_pages']) ? '
			LIMIT {int:limit}' : ''),
			array(
				'guest'        => $txt['guest_title'],
				'type'         => 'page',
				'lang'         => $context['user']['language'],
				'status'       => 1,
				'current_time' => time(),
				'permissions'  => Helpers::getPermissions(),
				'sort'         => $parameters['sort'],
				'limit'        => $parameters['num_pages']
			)
		);

		$pages = [];
		while ($row = $smcFunc['db_fetch_assoc']($request)) {
			if (Helpers::isFrontpage($row['alias']))
				continue;

			$pages[$row['page_id']] = array(
				'id'           => $row['page_id'],
				'title'        => $titles[$row['page_id']] ?? [],
				'author_id'    => $row['author_id'],
				'author_name'  => $row['author_name'],
				'alias'        => $row['alias'],
				'num_views'    => $row['num_views'],
				'num_comments' => $row['num_comments'],
				'created_at'   => $row['created_at'],
				'updated_at'   => $row['updated_at']
			);
		}

		$smcFunc['db_free_result']($request);
		$smcFunc['lp_num_queries']++;

		return $pages;
	}

	/**
	 * Form the block content
	 *
	 * Формируем контент блока
	 *
	 * @param string $content
	 * @param string $type
	 * @param int $block_id
	 * @param int $cache_time
	 * @param array $parameters
	 * @return void
	 */
	public function prepareContent(&$content, $type, $block_id, $cache_time, $parameters)
	{
		global $user_info, $scripturl, $txt;

		if ($type !== 'page_list')
			return;

		$page_list = Helpers::cache('page_list_addon_b' . $block_id . '_u' . $user_info['id'], 'getData', __CLASS__, $cache_time, $parameters);

		ob_start();

		if (!empty($page_list)) {
			echo '
		<ul class="normallist page_list">';

			foreach ($page_list as $page) {
				if (empty($title = Helpers::getTitle($page)))
					continue;

				echo '
			<li>
				<a href="', $scripturl, '?page=', $page['alias'], '">', $title, '</a> ', $txt['by'], ' ', (empty($page['author_id']) ? $page['author_name'] : '<a href="' . $scripturl . '?action=profile;u=' . $page['author_id'] . '">' . $page['author_name'] . '</a>'), ', ', Helpers::getFriendlyTime($page['created_at']), ' (', Helpers::getCorrectDeclension($page['num_views'], $txt['lp_views_set']);

				if (!empty($page['num_comments']))
					echo ', ' . Helpers::getCorrectDeclension($page['num_comments'], $txt['lp_comments_set']);

				echo ')
			</li>';
			}

			echo '
		</ul>';
		} else {
			echo '<div class="errorbox">', $txt['lp_page_list_addon_no_items'], '</div>';
		}

		$content = ob_get_clean();
	}
}
