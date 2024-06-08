<?php

/**
 * @package BoardList (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 24.05.24
 */

namespace Bugo\LightPortal\Addons\BoardList;

use Bugo\Compat\{Config, Lang, Utils};
use Bugo\LightPortal\Addons\Block;
use Bugo\LightPortal\Areas\Fields\CustomField;
use Bugo\LightPortal\Areas\Partials\{ContentClassSelect, TitleClassSelect};
use Bugo\LightPortal\Enums\Tab;
use Bugo\LightPortal\Utils\{Icon, MessageIndex};

if (! defined('LP_NAME'))
	die('No direct access...');

class BoardList extends Block
{
	public string $icon = 'far fa-list-alt';

	public function prepareBlockParams(array &$params): void
	{
		if (Utils::$context['current_block']['type'] !== 'board_list')
			return;

		$params = [
			'no_content_class' => true,
			'category_class'   => 'title_bar',
			'board_class'      => 'roundframe',
		];
	}

	public function validateBlockParams(array &$params): void
	{
		if (Utils::$context['current_block']['type'] !== 'board_list')
			return;

		$params = [
			'category_class' => FILTER_DEFAULT,
			'board_class'    => FILTER_DEFAULT,
		];
	}

	public function prepareBlockFields(): void
	{
		if (Utils::$context['current_block']['type'] !== 'board_list')
			return;

		CustomField::make('category_class', Lang::$txt['lp_board_list']['category_class'])
			->setTab(Tab::APPEARANCE)
			->setValue(static fn() => new TitleClassSelect(), [
				'id'    => 'category_class',
				'data'  => $this->getCategoryClasses(),
				'value' => Utils::$context['lp_block']['options']['category_class']
			]);

		CustomField::make('board_class', Lang::$txt['lp_board_list']['board_class'])
			->setTab(Tab::APPEARANCE)
			->setValue(static fn() => new ContentClassSelect(), [
				'id'    => 'board_class',
				'value' => Utils::$context['lp_block']['options']['board_class'],
			]);
	}

	public function getData(): array
	{
		return MessageIndex::getBoardList();
	}

	public function prepareContent(object $data, array $parameters): void
	{
		if ($data->type !== 'board_list')
			return;

		$boardList = $this->cache('board_list_addon_b' . $data->id . '_u' . Utils::$context['user']['id'])
			->setLifeTime($data->cacheTime)
			->setFallback(self::class, 'getData');

		if (empty($boardList))
			return;

		Utils::$context['current_board'] ??= 0;

		foreach ($boardList as $category) {
			if ($parameters['category_class'])
				echo sprintf($this->getCategoryClasses()[$parameters['category_class']], $category['name']);

			$content = '
				<ul class="smalltext">';

			foreach ($category['boards'] as $board) {
				$board['selected'] = $board['id'] == Utils::$context['current_board'];

				$content .= '
						<li>';

				if ($board['child_level']) {
					$content .= '
							<ul>
								<li style="margin-left: 1em">
									' . Icon::get($board['selected'] ? 'circle_dot' : 'chevron_right') . ' <a href="' . Config::$scripturl . '?board=' . $board['id'] . '.0">' . $board['name'] . '</a>
								</li>
							</ul>';
				} else {
					$content .= '
							' . Icon::get('circle' . ($board['selected'] ? '_dot' : '')) . ' <a href="' . Config::$scripturl . '?board=' . $board['id'] . '.0">' . $board['name'] . '</a>';
				}

				$content .= '
						</li>';
			}

			$content .= '
				</ul>';

			echo sprintf(Utils::$context['lp_all_content_classes'][$parameters['board_class']], $content);
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
