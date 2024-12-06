<?php

/**
 * @package BoardList (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category plugin
 * @version 03.12.24
 */

namespace Bugo\LightPortal\Plugins\BoardList;

use Bugo\Compat\Config;
use Bugo\Compat\Utils;
use Bugo\LightPortal\Enums\Tab;
use Bugo\LightPortal\Plugins\{Block, Event};
use Bugo\LightPortal\UI\Fields\CustomField;
use Bugo\LightPortal\UI\Partials\ContentClassSelect;
use Bugo\LightPortal\UI\Partials\TitleClassSelect;
use Bugo\LightPortal\Utils\Icon;
use Bugo\LightPortal\Utils\MessageIndex;
use Bugo\LightPortal\Utils\Str;

if (! defined('LP_NAME'))
	die('No direct access...');

class BoardList extends Block
{
	public string $icon = 'far fa-list-alt';

	public function prepareBlockParams(Event $e): void
	{
		$e->args->params = [
			'no_content_class' => true,
			'category_class'   => 'title_bar',
			'board_class'      => 'roundframe',
		];
	}

	public function validateBlockParams(Event $e): void
	{
		$e->args->params = [
			'category_class' => FILTER_DEFAULT,
			'board_class'    => FILTER_DEFAULT,
		];
	}

	public function prepareBlockFields(Event $e): void
	{
		$options = $e->args->options;

		CustomField::make('category_class', $this->txt['category_class'])
			->setTab(Tab::APPEARANCE)
			->setValue(static fn() => new TitleClassSelect(), [
				'id'    => 'category_class',
				'data'  => $this->getCategoryClasses(),
				'value' => $options['category_class']
			]);

		CustomField::make('board_class', $this->txt['board_class'])
			->setTab(Tab::APPEARANCE)
			->setValue(static fn() => new ContentClassSelect(), [
				'id'    => 'board_class',
				'value' => $options['board_class'],
			]);
	}

	public function getData(): array
	{
		return MessageIndex::getBoardList();
	}

	public function prepareContent(Event $e): void
	{
		$parameters = $e->args->parameters;

		$boardList = $this->cache($this->name . '_addon_b' . $e->args->id . '_u' . Utils::$context['user']['id'])
			->setLifeTime($e->args->cacheTime)
			->setFallback(self::class, 'getData');

		if (empty($boardList))
			return;

		Utils::$context['current_board'] ??= 0;

		foreach ($boardList as $category) {
			if ($parameters['category_class']) {
				echo sprintf($this->getCategoryClasses()[$parameters['category_class']], $category['name']);
			}

			$content = Str::html('ul')->class('smalltext');

			foreach ($category['boards'] as $board) {
				$board['selected'] = $board['id'] == Utils::$context['current_board'];

				$li = Str::html('li');

				if ($board['child_level']) {
					$childUl = Str::html('ul');
					$childLi = Str::html('li')->style('margin-left', '1em');

					$childLi->setHtml(
						Icon::get($board['selected'] ? 'circle_dot' : 'chevron_right') . ' ' .
						Str::html('a', $board['name'])
							->href(Config::$scripturl . '?board=' . $board['id'] . '.0')
					);
					$childUl->addHtml($childLi);
					$li->addHtml($childUl);
				} else {
					$li->setHtml(
						Icon::get('circle' . ($board['selected'] ? '_dot' : '')) . ' ' .
						Str::html('a', $board['name'])
							->href(Config::$scripturl . '?board=' . $board['id'] . '.0')
					);
				}

				$content->addHtml($li);
			}

			echo sprintf(Utils::$context['lp_all_content_classes'][$parameters['board_class']], $content);
		}
	}

	private function getCategoryClasses(): array
	{
		return [
			'title_bar' => Str::html('div')->class('title_bar')
				->addHtml(Str::html('h4', '%1$s')->class('titlebg'))
				->toHtml(),
			'sub_bar'   => Str::html('div')->class('sub_bar')
				->addHtml(Str::html('h4', '%1$s')->class('subbg'))
				->toHtml(),
		];
	}
}
