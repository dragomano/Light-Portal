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
 * @version 0.7
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
	 * @return void
	 */
	public static function prepareContent(&$content, $type, $block_id)
	{
		global $context;

		if ($type !== 'boardlist')
			return;

		$context['current_board'] = $context['current_board'] ?? 0;
		$context['lp_boardlist']  = Helpers::useCache('boardlist_addon_u' . $context['user']['id'], 'getBoardList', __CLASS__);

		$parameters = $context['lp_active_blocks'][$block_id]['parameters'] ?? $context['lp_block']['options']['parameters'];

		if ($parameters['board_class'] == '_')
			$parameters['board_class'] = '';
		else
			$parameters['board_class'] = strtr($parameters['board_class'], array('div.' => '', '.' => ' '));

		ob_start();
		require(__DIR__ . '/template.php');
		$content = ob_get_clean();
	}
}
