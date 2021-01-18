<?php

namespace Bugo\LightPortal\Addons\BoardList;

use Bugo\LightPortal\Helpers;

/**
 * BoardList
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2021 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 1.5
 */

if (!defined('SMF'))
	die('Hacking attempt...');

class BoardList
{
	/**
	 * @var string
	 */
	public $addon_icon = 'far fa-list-alt';

	/**
	 * @var bool
	 */
	private $no_content_class = true;

	/**
	 * @var string
	 */
	private $category_class = 'div.title_bar > h4.titlebg';

	/**
	 * @var string
	 */
	private $board_class = 'div.roundframe';

	/**
	 * @param array $options
	 * @return void
	 */
	public function blockOptions(&$options)
	{
		$options['board_list']['no_content_class'] = $this->no_content_class;

		$options['board_list']['parameters']['category_class'] = $this->category_class;
		$options['board_list']['parameters']['board_class']    = $this->board_class;
	}

	/**
	 * @param array $parameters
	 * @param string $type
	 * @return void
	 */
	public function validateBlockData(&$parameters, $type)
	{
		if ($type !== 'board_list')
			return;

		$parameters['category_class'] = FILTER_SANITIZE_STRING;
		$parameters['board_class']    = FILTER_SANITIZE_STRING;
	}

	/**
	 * @return void
	 */
	public function prepareBlockFields()
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
			'options' => array(),
			'tab' => 'appearance'
		);

		foreach ($context['lp_all_title_classes'] as $key => $data) {
			$context['posting_fields']['category_class']['input']['options'][$key] = array(
				'value'    => $key,
				'selected' => $key == $context['lp_block']['options']['parameters']['category_class']
			);
		}

		$context['posting_fields']['board_class']['label']['text'] = $txt['lp_board_list_addon_board_class'];
		$context['posting_fields']['board_class']['input'] = array(
			'type' => 'select',
			'attributes' => array(
				'id' => 'board_class'
			),
			'options' => array(),
			'tab' => 'appearance'
		);

		foreach ($context['lp_all_content_classes'] as $key => $data) {
			$value = $key;
			$key   = $key == '_' ? $txt['no'] : $key;

			$context['posting_fields']['board_class']['input']['options'][$key] = array(
				'value'    => $value,
				'selected' => $value == $context['lp_block']['options']['parameters']['board_class']
			);
		}
	}

	/**
	 * Get the board list
	 *
	 * Получаем список разделов
	 *
	 * @return array
	 */
	public function getData()
	{
		Helpers::require('Subs-MessageIndex');

		$boardListOptions = array(
			'ignore_boards'   => true,
			'use_permissions' => true,
			'not_redirection' => true
		);

		return getBoardList($boardListOptions);
	}

	/**
	 * @param string $content
	 * @param string $type
	 * @param int $block_id
	 * @param int $cache_time
	 * @param array $parameters
	 * @return void
	 */
	public function prepareContent(&$content, $type, $block_id, $cache_time, $parameters)
	{
		global $context, $scripturl;

		if ($type !== 'board_list')
			return;

		$board_list = Helpers::cache('board_list_addon_b' . $block_id . '_u' . $context['user']['id'], 'getData', __CLASS__, $cache_time);

		if (!empty($board_list)) {
			$context['current_board'] = $context['current_board'] ?? 0;

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
