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
 * @version 19.09.23
 */

namespace Bugo\LightPortal\Addons\BoardList;

use Bugo\LightPortal\Addons\Block;
use Bugo\LightPortal\Partials\{ContentClassSelect, TitleClassSelect};

if (! defined('LP_NAME'))
	die('No direct access...');

class BoardList extends Block
{
	public string $icon = 'far fa-list-alt';

	public function blockOptions(array &$options): void
	{
		$options['board_list']['no_content_class'] = true;

		$options['board_list']['parameters'] = [
			'category_class' => 'title_bar',
			'board_class'    => 'roundframe',
		];
	}

	public function validateBlockData(array &$parameters, string $type): void
	{
		if ($type !== 'board_list')
			return;

		$parameters['category_class'] = FILTER_DEFAULT;
		$parameters['board_class']    = FILTER_DEFAULT;
	}

	public function prepareBlockFields(): void
	{
		if ($this->context['lp_block']['type'] !== 'board_list')
			return;

		$this->context['posting_fields']['category_class']['label']['html'] = $this->txt['lp_board_list']['category_class'];
		$this->context['posting_fields']['category_class']['input']['tab']  = 'appearance';
		$this->context['posting_fields']['category_class']['input']['html'] = (new TitleClassSelect)([
			'id'    => 'category_class',
			'data'  => $this->getCategoryClasses(),
			'value' => $this->context['lp_block']['options']['parameters']['category_class']
		]);

		$this->context['posting_fields']['board_class']['label']['html'] = $this->txt['lp_board_list']['board_class'];
		$this->context['posting_fields']['board_class']['input']['tab']  = 'appearance';
		$this->context['posting_fields']['board_class']['input']['html'] = (new ContentClassSelect)([
			'id'    => 'board_class',
			'value' => $this->context['lp_block']['options']['parameters']['board_class'],
		]);
	}

	public function getData(): array
	{
		return $this->getBoardList();
	}

	public function prepareContent($data, array $parameters): void
	{
		if ($data->type !== 'board_list')
			return;

		$board_list = $this->cache('board_list_addon_b' . $data->block_id . '_u' . $this->context['user']['id'])
			->setLifeTime($data->cache_time)
			->setFallback(self::class, 'getData');

		if (empty($board_list))
			return;

		$this->context['current_board'] ??= 0;

		foreach ($board_list as $category) {
			if ($parameters['category_class'])
				echo sprintf($this->getCategoryClasses()[$parameters['category_class']], $category['name']);

			$content = '
				<ul class="smalltext">';

			foreach ($category['boards'] as $board) {
				$board['selected'] = $board['id'] == $this->context['current_board'];

				$content .= '
						<li>';

				if ($board['child_level']) {
					$content .= '
							<ul>
								<li style="margin-left: 1em">
									' . $this->context['lp_icon_set'][$board['selected'] ? 'circle_dot' : 'chevron_right'] . ' <a href="' . $this->scripturl . '?board=' . $board['id'] . '.0">' . $board['name'] . '</a>
								</li>
							</ul>';
				} else {
					$content .= '
							' . $this->context['lp_icon_set']['circle' . ($board['selected'] ? '_dot' : '')] . ' <a href="' . $this->scripturl . '?board=' . $board['id'] . '.0">' . $board['name'] . '</a>';
				}

				$content .= '
						</li>';
			}

			$content .= '
				</ul>';

			echo sprintf($this->context['lp_all_content_classes'][$parameters['board_class']], $content);
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
