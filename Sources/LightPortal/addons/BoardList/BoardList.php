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
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 1.1
 */

if (!defined('SMF'))
	die('Hacking attempt...');

class BoardList
{
	/**
	 * Specify an icon (from the FontAwesome Free collection)
	 *
	 * Указываем иконку (из коллекции FontAwesome Free)
	 *
	 * @var string
	 */
	public static $addon_icon = 'far fa-list-alt';

	/**
	 * You cannot select a class for the content of this block
	 *
	 * Нельзя выбрать класс для оформления контента этого блока
	 *
	 * @var bool
	 */
	private static $no_content_class = true;

	/**
	 * Default class for category headers
	 *
	 * Класс (по умолчанию) для оформления заголовков категорий
	 *
	 * @var string
	 */
	private static $category_class = 'div.title_bar > h4.titlebg';

	/**
	 * Default class for areas with board lists
	 *
	 * Класс (по умолчанию) для оформления блока со списком разделов
	 *
	 * @var string
	 */
	private static $board_class = 'div.roundframe';

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
		$options['board_list'] = array(
			'no_content_class' => static::$no_content_class,
			'parameters' => array(
				'category_class' => static::$category_class,
				'board_class'    => static::$board_class
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

		if ($context['current_block']['type'] !== 'board_list')
			return;

		$args['parameters'] = array(
			'category_class' => FILTER_SANITIZE_STRING,
			'board_class'    => FILTER_SANITIZE_STRING
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

		if ($context['lp_block']['type'] !== 'board_list')
			return;

		$context['posting_fields']['category_class']['label']['text'] = $txt['lp_board_list_addon_category_class'];
		$context['posting_fields']['category_class']['input'] = array(
			'type' => 'select',
			'attributes' => array(
				'id' => 'category_class'
			),
			'options' => array()
		);

		foreach ($context['lp_all_title_classes'] as $key => $data) {
			if (RC2_CLEAN) {
				$context['posting_fields']['category_class']['input']['options'][$key]['attributes'] = array(
					'value'    => $key,
					'selected' => $key == $context['lp_block']['options']['parameters']['category_class']
				);
			} else {
				$context['posting_fields']['category_class']['input']['options'][$key] = array(
					'value'    => $key,
					'selected' => $key == $context['lp_block']['options']['parameters']['category_class']
				);
			}
		}

		$context['posting_fields']['board_class']['label']['text'] = $txt['lp_board_list_addon_board_class'];
		$context['posting_fields']['board_class']['input'] = array(
			'type' => 'select',
			'attributes' => array(
				'id' => 'board_class'
			),
			'options' => array()
		);

		foreach ($context['lp_all_content_classes'] as $key => $data) {
			$value = $key;
			$key   = $key == '_' ? $txt['no'] : $key;

			if (RC2_CLEAN) {
				$context['posting_fields']['board_class']['input']['options'][$key]['attributes'] = array(
					'value'    => $value,
					'selected' => $value == $context['lp_block']['options']['parameters']['board_class']
				);
			} else {
				$context['posting_fields']['board_class']['input']['options'][$key] = array(
					'value'    => $value,
					'selected' => $value == $context['lp_block']['options']['parameters']['board_class']
				);
			}
		}
	}

	/**
	 * Get the board list
	 *
	 * Получаем список разделов
	 *
	 * @return array
	 */
	public static function getData()
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
		global $context, $scripturl;

		if ($type !== 'board_list')
			return;

		$board_list = Helpers::getFromCache('board_list_addon_b' . $block_id . '_u' . $context['user']['id'], 'getData', __CLASS__, $cache_time);

		if (!empty($board_list)) {
			$context['current_board']     = $context['current_board'] ?? 0;

			ob_start();

			foreach ($board_list as $category) {
				if (!empty($parameters['category_class']))
					echo sprintf($context['lp_all_title_classes'][$parameters['category_class']], $category['name']);

				$content = '
				<ul class="smalltext">';

				foreach ($category['boards'] as $board) {
					$content .= '
					<li>';

					if ($board['child_level']) {
						$content .= '
						<ul class="smalltext">
							<li>' . ($context['current_board'] == $board['id'] ? '<strong>' : '') . '&raquo; <a href="' . $scripturl . '?board=' . $board['id'] . '.0">' . $board['name'] . '</a>' . ($context['current_board'] == $board['id'] ? '</strong>' : '') . '</li>
						</ul>';
					} else {
						$content .= '
						' . ($context['current_board'] == $board['id'] ? '<strong>' : '') . '<a href="' . $scripturl . '?board=' . $board['id'] . '.0">' . $board['name'] . '</a>' . ($context['current_board'] == $board['id'] ? '</strong>' : '');
					}

					$content .= '
					</li>';
				}

				$content .= '
				</ul>';

				echo sprintf($context['lp_all_content_classes'][$parameters['board_class'] ?: '_'], $content, null);
			}

			$content = ob_get_clean();
		}
	}
}
