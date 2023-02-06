<?php

/**
 * BoardList.php
 *
 * @package BoardList (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2023 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 11.05.22
 */

namespace Bugo\LightPortal\Addons\BoardList;

use Bugo\LightPortal\Addons\Plugin;

if (! defined('LP_NAME'))
	die('No direct access...');

class BoardList extends Plugin
{
	public string $icon = 'far fa-list-alt';

	public function blockOptions(array &$options)
	{
		$options['board_list']['no_content_class'] = true;

		$options['board_list']['parameters'] = [
			'category_class' => 'title_bar',
			'board_class'    => 'roundframe',
		];
	}

	public function validateBlockData(array &$parameters, string $type)
	{
		if ($type !== 'board_list')
			return;

		$parameters['category_class'] = FILTER_DEFAULT;
		$parameters['board_class']    = FILTER_DEFAULT;
	}

	public function prepareBlockFields()
	{
		if ($this->context['lp_block']['type'] !== 'board_list')
			return;

		$this->context['posting_fields']['category_class']['label']['html'] = '<label for="category_class">' . $this->txt['lp_board_list']['category_class'] . '</label>';
		$this->context['posting_fields']['category_class']['input']['html'] = '<div id="category_class" name="category_class"></div>';
		$this->context['posting_fields']['category_class']['input']['tab']  = 'appearance';

		$this->context['posting_fields']['board_class']['label']['html'] = '<label for="board_class">' . $this->txt['lp_board_list']['board_class'] . '</label>';
		$this->context['posting_fields']['board_class']['input']['html'] = '<div id="board_class" name="board_class"></div>';
		$this->context['posting_fields']['board_class']['input']['tab']  = 'appearance';

		$this->context['category_classes'] = $this->getCategoryClasses();

		$this->setTemplate()->withLayer('board_list');
	}

	public function getData(): array
	{
		return $this->getBoardList([
			'ignore_boards'   => true,
			'use_permissions' => true,
			'not_redirection' => true,
		]);
	}

	public function prepareContent(string $type, int $block_id, int $cache_time, array $parameters)
	{
		if ($type !== 'board_list')
			return;

		$board_list = $this->cache('board_list_addon_b' . $block_id . '_u' . $this->context['user']['id'])
			->setLifeTime($cache_time)
			->setFallback(__CLASS__, 'getData');

		if (empty($board_list))
			return;

		$this->context['current_board'] = $this->context['current_board'] ?? 0;

		foreach ($board_list as $category) {
			if ($parameters['category_class'])
				echo sprintf($this->getCategoryClasses()[$parameters['category_class']], $category['name']);

			$content = '
				<ul class="smalltext">';

			foreach ($category['boards'] as $board) {
				$content .= '
					<li>';

				if ($board['child_level']) {
					$content .= '
						<ul class="smalltext">
							<li>' . ($this->context['current_board'] == $board['id'] ? '<strong>' : '') . '&raquo; <a href="' . $this->scripturl . '?board=' . $board['id'] . '.0">' . $board['name'] . '</a>' . ($this->context['current_board'] == $board['id'] ? '</strong>' : '') . '</li>
						</ul>';
					} else {
					$content .= '
						' . ($this->context['current_board'] == $board['id'] ? '<strong>' : '') . '<a href="' . $this->scripturl . '?board=' . $board['id'] . '.0">' . $board['name'] . '</a>' . ($this->context['current_board'] == $board['id'] ? '</strong>' : '');
					}

				$content .= '
					</li>';
			}

			$content .= '
				</ul>';

			echo sprintf($this->context['lp_all_content_classes'][$parameters['board_class']], $content, null);
		}
	}

	private function getCategoryClasses(): array
	{
		return [
			'title_bar' => '<div class="title_bar"><h4 class="titlebg">%1$s</h4></div>',
			'sub_bar'   => '<div class="sub_bar"><h4 class="subbg">%1$s</h4></div>',
		];
	}
}
