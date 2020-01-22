<?php

namespace Bugo\LightPortal\Addons\BoardList;

use Bugo\LightPortal\Helpers;

/**
 * BoardList
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2020 Bugo
 * @license https://opensource.org/licenses/BSD-3-Clause BSD
 *
 * @version 0.8
 */

if (!defined('SMF'))
	die('Hacking attempt...');

class BoardList
{
	/**
	 * Нельзя выбрать класс для оформления контента этого блока
	 *
	 * @var bool
	 */
	private static $no_content_class = true;

	/**
	 * Класс (по умолчанию) для оформления заголовков категорий
	 *
	 * @var string
	 */
	private static $category_class = 'div.title_bar > h4.titlebg';

	/**
	 * Класс (по умолчанию) для оформления блока со списком разделов
	 *
	 * @var string
	 */
	private static $board_class = 'div.roundframe.noup';

	/**
	 * Добавляем параметры блока
	 *
	 * @param array $options
	 * @return void
	 */
	public static function blockOptions(&$options)
	{
		$options['boardlist'] = array(
			'no_content_class' => static::$no_content_class,
			'parameters' => array(
				'category_class' => static::$category_class,
				'board_class'    => static::$board_class
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

		if ($context['current_block']['type'] !== 'boardlist')
			return;

		$args['parameters'] = array(
			'category_class' => FILTER_SANITIZE_STRING,
			'board_class'    => FILTER_SANITIZE_STRING
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

		if ($context['lp_block']['type'] !== 'boardlist')
			return;

		$context['posting_fields']['category_class']['label']['text'] = $txt['lp_boardlist_addon_category_class'];
		$context['posting_fields']['category_class']['input'] = array(
			'type' => 'select',
			'attributes' => array(
				'id' => 'category_class'
			),
			'options' => array()
		);

		foreach ($context['lp_all_title_classes'] as $key => $data) {
			$context['posting_fields']['category_class']['input']['options'][$key] = array(
				'value'    => $key,
				'selected' => $key == $context['lp_block']['options']['parameters']['category_class']
			);
		}

		$context['posting_fields']['board_class']['label']['text'] = $txt['lp_boardlist_addon_board_class'];
		$context['posting_fields']['board_class']['input'] = array(
			'type' => 'select',
			'attributes' => array(
				'id' => 'board_class'
			),
			'options' => array()
		);

		foreach ($context['lp_all_content_classes'] as $key => $data) {
			$context['posting_fields']['board_class']['input']['options'][$key] = array(
				'value'    => $key,
				'selected' => $key == $context['lp_block']['options']['parameters']['board_class']
			);
		}
	}

	/**
	 * Получаем список разделов, с учётом прав доступа и списка игнора
	 *
	 * @return array
	 */
	public static function getBoardList()
	{
		global $sourcedir;

		require_once($sourcedir . '/Subs-MessageIndex.php');

		$boardListOptions = array(
			'ignore_boards'   => true,
			'use_permissions' => true,
			'not_redirection' => true
		);

		return getBoardList($boardListOptions);
	}

	/**
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
		global $context, $scripturl;

		if ($type !== 'boardlist')
			return;

		$context['lp_boardlist'] = Helpers::useCache('boardlist_addon_b' . $block_id . '_u' . $context['user']['id'], 'getBoardList', __CLASS__, $cache_time);

		if ($parameters['board_class'] == '_')
			$parameters['board_class'] = '';
		else
			$parameters['board_class'] = strtr($parameters['board_class'], array('div.' => '', '.' => ' '));

		$context['current_board'] = $context['current_board'] ?? 0;

		if (!empty($context['lp_boardlist'])) {
			ob_start();

			foreach ($context['lp_boardlist'] as $category) {
				echo sprintf($context['lp_all_title_classes'][$parameters['category_class']], $category['name'], null, null);

				echo '
			<div', !empty($parameters['board_class']) ? ' class="' . $parameters['board_class'] . '"' : '', '>
				<ul class="smalltext" style="padding-left: 10px">';

				foreach ($category['boards'] as $board) {
					echo '
					<li>';

					if ($board['child_level'])
						echo '
						<ul class="smalltext" style="padding-left: 10px">
							<li>', $context['current_board'] == $board['id'] ? '<strong>' : '', '&raquo; <a href="', $scripturl, '?board=', $board['id'], '.0">', $board['name'], '</a>', $context['current_board'] == $board['id'] ? '</strong>' : '', '</li>
						</ul>';
					else
						echo '
						', $context['current_board'] == $board['id'] ? '<strong>' : '', '<a href="', $scripturl, '?board=', $board['id'], '.0">', $board['name'], '</a>', $context['current_board'] == $board['id'] ? '</strong>' : '';

					echo '
					</li>';
				}

				echo '
				</ul>
			</div>';
			}

			$content = ob_get_clean();
		}
	}
}
