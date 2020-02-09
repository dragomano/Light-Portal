<?php

namespace Bugo\LightPortal\Addons\TopPages;

use Bugo\LightPortal\Helpers;

/**
 * TopPages
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2020 Bugo
 * @license https://opensource.org/licenses/BSD-3-Clause BSD
 *
 * @version 1.0
 */

if (!defined('SMF'))
	die('Hacking attempt...');

class TopPages
{
	/**
	 * Type of popularity calculation (comments|views)
	 *
	 * Тип расчёта популярности (comments|views)
	 *
	 * @var string
	 */
	private static $type = 'comments';

	/**
	 * The maximum number of pages to output
	 *
	 * Максимальное количество страниц для вывода
	 *
	 * @var int
	 */
	private static $num_pages = 10;

	/**
	 * Display only numbers (true|false)
	 *
	 * Отображать только цифры (true|false)
	 *
	 * @var bool
	 */
	private static $show_numbers_only = false;

	/**
	 * Adding the block options
	 *
	 * Добавляем параметры блока
	 *
	 * @param array $options
	 * @return void
	 */
	public static function blockOptions(&$options)
	{
		$options['top_pages'] = array(
			'parameters' => array(
				'popularity_type'   => static::$type,
				'num_pages'         => static::$num_pages,
				'show_numbers_only' => static::$show_numbers_only
			)
		);
	}

	/**
	 * Validate options
	 *
	 * Валидируем параметры
	 *
	 * @param array $args
	 * @return void
	 */
	public static function validateBlockData(&$args)
	{
		global $context;

		if ($context['current_block']['type'] !== 'top_pages')
			return;

		$args['parameters'] = array(
			'popularity_type'   => FILTER_SANITIZE_STRING,
			'num_pages'         => FILTER_VALIDATE_INT,
			'show_numbers_only' => FILTER_VALIDATE_BOOLEAN
		);
	}

	/**
	 * Adding fields specifically for this block
	 *
	 * Добавляем поля конкретно для этого блока
	 *
	 * @return void
	 */
	public static function prepareBlockFields()
	{
		global $context, $txt;

		if ($context['lp_block']['type'] !== 'top_pages')
			return;

		$context['posting_fields']['popularity_type']['label']['text'] = $txt['lp_top_pages_addon_type'];
		$context['posting_fields']['popularity_type']['input'] = array(
			'type' => 'select',
			'attributes' => array(
				'id' => 'popularity_type'
			),
			'options' => array()
		);

		foreach ($txt['lp_top_pages_addon_type_set'] as $key => $value) {
			if (!defined('JQUERY_VERSION')) {
				$context['posting_fields']['popularity_type']['input']['options'][$value]['attributes'] = array(
					'value'    => $key,
					'selected' => $key == $context['lp_block']['options']['parameters']['popularity_type']
				);
			} else {
				$context['posting_fields']['popularity_type']['input']['options'][$value] = array(
					'value'    => $key,
					'selected' => $key == $context['lp_block']['options']['parameters']['popularity_type']
				);
			}
		}

		$context['posting_fields']['num_pages']['label']['text'] = $txt['lp_top_pages_addon_num_pages'];
		$context['posting_fields']['num_pages']['input'] = array(
			'type' => 'number',
			'attributes' => array(
				'id' => 'num_pages',
				'min' => 1,
				'value' => $context['lp_block']['options']['parameters']['num_pages']
			)
		);

		$context['posting_fields']['show_numbers_only']['label']['text'] = $txt['lp_top_posters_addon_show_numbers_only'];
		$context['posting_fields']['show_numbers_only']['input'] = array(
			'type' => 'checkbox',
			'attributes' => array(
				'id' => 'show_numbers_only',
				'checked' => !empty($context['lp_block']['options']['parameters']['show_numbers_only'])
			)
		);
	}

	/**
	 * Get the list of popular pages
	 *
	 * Получаем список популярных страниц
	 *
	 * @param array $params
	 * @return void
	 */
	public static function getTopPages($params)
	{
		global $smcFunc, $modSettings, $scripturl;

		[$popularity_type, $num_pages] = $params;

		$request = $smcFunc['db_query']('', '
			SELECT
				p.page_id, p.author_id, p.title, p.alias, p.type, p.permissions, p.num_views, p.created_at, p.updated_at,
				COALESCE(mem.real_name, 0) AS author_name, COUNT(com.id) AS num_comments
			FROM {db_prefix}lp_pages AS p
				LEFT JOIN {db_prefix}lp_comments AS com ON (com.page_id = p.page_id)
				LEFT JOIN {db_prefix}members AS mem ON (mem.id_member = com.author_id)
			WHERE p.status = {int:status}' . (!empty($modSettings['lp_frontpage_mode']) || !empty($modSettings['lp_frontpage_disable']) ? '
				AND p.alias != {string:alias}' : '') . '
			GROUP BY p.page_id, p.author_id, p.title, p.alias, p.type, p.permissions, p.num_views, p.created_at, p.updated_at, mem.real_name
			ORDER BY ' . ($popularity_type == 'comments' ? 'num_comments' : 'p.num_views') . ' DESC
			LIMIT {int:limit}',
			array(
				'status' => 1,
				'alias'  => '/',
				'limit'  => $num_pages
			)
		);

		$pages = array();
		while ($row = $smcFunc['db_fetch_assoc']($request)) {
			$pages[] = array(
				'num_comments' => $row['num_comments'],
				'num_views'    => $row['num_views'],
				'link'         => '<a href="' . $scripturl . '?page=' . $row['alias'] . '">' . $row['title'] . '</a>'
			);
		}

		$smcFunc['db_free_result']($request);

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
	public static function prepareContent(&$content, $type, $block_id, $cache_time, $parameters)
	{
		global $context, $txt;

		if ($type !== 'top_pages')
			return;

		$top_pages = Helpers::useCache(
			'top_pages_addon_b' . $block_id . '_u' . $context['user']['id'] . '_' . $parameters['popularity_type'],
			'getTopPages',
			__CLASS__,
			$cache_time,
			array($parameters['popularity_type'], $parameters['num_pages'])
		);

		if (!empty($top_pages)) {
			ob_start();

			echo '
			<dl class="stats">';

			$max = $top_pages[0]['num_' . $parameters['popularity_type']];

			foreach ($top_pages as $page) {
				if ($page['num_' . $parameters['popularity_type']] < 1)
					continue;

				$width = $page['num_' . $parameters['popularity_type']] * 100 / $max;

				echo '
				<dt>', $page['link'], '</dt>
				<dd class="statsbar generic_bar righttext">
					<div class="bar', (empty($page['num_' . $parameters['popularity_type']]) ? ' empty"' : '" style="width: ' . $width . '%"'), '></div>
					<span>', $parameters['show_numbers_only'] ? $page['num_' . $parameters['popularity_type']] : Helpers::getCorrectDeclension($page['num_' . $parameters['popularity_type']], $txt['lp_' . $parameters['popularity_type'] . '_set']), '</span>
				</dd>';
			}

			echo '
			</dl>';

			$content = ob_get_clean();
		}
	}
}
