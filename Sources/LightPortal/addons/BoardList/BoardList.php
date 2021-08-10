<?php

/**
 * BoardList
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2021 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 1.8
 */

namespace Bugo\LightPortal\Addons\BoardList;

use Bugo\LightPortal\Addons\Plugin;
use Bugo\LightPortal\Helpers;

class BoardList extends Plugin
{
	/**
	 * @var string
	 */
	public $icon = 'far fa-list-alt';

	/**
	 * @param array $options
	 * @return void
	 */
	public function blockOptions(array &$options)
	{
		$options['board_list']['no_content_class'] = true;

		$options['board_list']['parameters'] = [
			'category_class' => 'title_bar > h4',
			'board_class'    => 'roundframe',
		];
	}

	/**
	 * @param array $parameters
	 * @param string $type
	 * @return void
	 */
	public function validateBlockData(array &$parameters, string $type)
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

		$context['posting_fields']['category_class']['label']['text'] = $txt['lp_board_list']['category_class'];
		$context['posting_fields']['category_class']['input'] = array(
			'type' => 'select',
			'attributes' => array(
				'id' => 'category_class'
			),
			'options' => array(),
			'tab' => 'appearance'
		);

		foreach ($this->getCategoryClasses() as $key => $data) {
			$context['posting_fields']['category_class']['input']['options'][$key] = array(
				'value'    => $key,
				'selected' => $key == $context['lp_block']['options']['parameters']['category_class']
			);
		}

		$context['posting_fields']['board_class']['label']['text'] = $txt['lp_board_list']['board_class'];
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
			$key   = empty($key) ? $txt['no'] : $key;

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
	public function getData(): array
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
	public function prepareContent(string &$content, string $type, int $block_id, int $cache_time, array $parameters)
	{
		global $context, $scripturl;

		if ($type !== 'board_list')
			return;

		$board_list = Helpers::cache('board_list_addon_b' . $block_id . '_u' . $context['user']['id'])
			->setLifeTime($cache_time)
			->setFallback(__CLASS__, 'getData');

		if (empty($board_list))
			return;

		$context['current_board'] = $context['current_board'] ?? 0;

		foreach ($board_list as $category) {
			if (!empty($parameters['category_class']))
				echo sprintf($this->getCategoryClasses()[$parameters['category_class']], $category['name']);

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

			echo sprintf($context['lp_all_content_classes'][$parameters['board_class']], $content, null);
		}
	}

	private function getCategoryClasses(): array
    {
		return [
			'title_bar > h4' => '<div class="title_bar"><h4 class="titlebg">%1$s</h4></div>',
			'sub_bar > h4'   => '<div class="sub_bar"><h4 class="subbg">%1$s</h4></div>',
		];
	}
}
