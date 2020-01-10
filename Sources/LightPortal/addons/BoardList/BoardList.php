<?php

namespace Bugo\LightPortal\Addons\BoardList;

/**
 * BoardList
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2020 Bugo
 * @license https://opensource.org/licenses/BSD-3-Clause BSD
 *
 * @version 0.3
 */

if (!defined('SMF'))
	die('Hacking attempt...');

class BoardList
{
	/**
	 * Добавляем заголовок и описание блока
	 *
	 * @return void
	 */
	public static function lang()
	{
		global $user_info, $txt;

		require_once(__DIR__ . '/langs/' . $user_info['language'] . '.php');

		$txt['lp_block_types']['boardlist'] = $txt['lp_boardlist_addon_title'];
		$txt['lp_block_types_descriptions']['boardlist'] = $txt['lp_boardlist_addon_desc'];
	}

	/**
	 * Добавляем параметры блока
	 *
	 * @param array $options
	 * @return void
	 */
	public static function blockOptions(&$options)
	{
		$options['boardlist'] = array(
			'no_content_class' => true,
			'parameters' => array(
				'category_class' => 'div.title_bar > h4.titlebg',
				'board_class'    => 'div.roundframe.noup'
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

		$args['category_class'] = FILTER_SANITIZE_STRING;
		$args['board_class']    = FILTER_SANITIZE_STRING;
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
	 * Формируем контент блока
	 *
	 * @param string $content
	 * @param string $type
	 * @param int $block_id
	 * @return void
	 */
	public static function prepareContent(&$content, $type, $block_id)
	{
		global $context, $sourcedir;

		if ($type !== 'boardlist')
			return;

		$context['current_board'] = $context['current_board'] ?? 0;

		if (($context['lp_boardlist'] = cache_get_data('light_portal_boardlist_addon', 3600)) == null) {
			require_once($sourcedir . '/Subs-MessageIndex.php');

			$boardListOptions = array(
				'ignore_boards'   => true,
				'use_permissions' => true,
				'not_redirection' => true
			);

			$context['lp_boardlist'] = getBoardList($boardListOptions);

			cache_put_data('light_portal_boardlist_addon', $context['lp_boardlist'], 3600);
		}

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
