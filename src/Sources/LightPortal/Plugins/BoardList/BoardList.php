<?php declare(strict_types=1);

/**
 * @package BoardList (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category plugin
 * @version 05.01.25
 */

namespace Bugo\LightPortal\Plugins\BoardList;

use Bugo\Compat\Config;
use Bugo\Compat\Utils;
use Bugo\LightPortal\Enums\ContentClass;
use Bugo\LightPortal\Enums\Tab;
use Bugo\LightPortal\Plugins\Block;
use Bugo\LightPortal\Plugins\Event;
use Bugo\LightPortal\Enums\TitleClass;
use Bugo\LightPortal\UI\Fields\CustomField;
use Bugo\LightPortal\UI\Partials\ContentClassSelect;
use Bugo\LightPortal\UI\Partials\TitleClassSelect;
use Bugo\LightPortal\Utils\Icon;
use Bugo\LightPortal\Utils\MessageIndex;
use Bugo\LightPortal\Utils\Str;
use WPLake\Typed\Typed;

if (! defined('LP_NAME'))
	die('No direct access...');

class BoardList extends Block
{
	public string $icon = 'far fa-list-alt';

	public function prepareBlockParams(Event $e): void
	{
		$e->args->params = [
			'no_content_class' => true,
			'category_class'   => TitleClass::TITLE_BAR->value,
			'board_class'      => ContentClass::ROUNDFRAME->value,
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

	public function prepareContent(Event $e): void
	{
		$boardList = $this->cache($this->name . '_addon_b' . $e->args->id . '_u' . Utils::$context['user']['id'])
			->setLifeTime($e->args->cacheTime)
			->setFallback(fn() => MessageIndex::getBoardList());

		if (empty($boardList))
			return;

		Utils::$context['current_board'] ??= 0;

		$parameters = $e->args->parameters;

		$categoryClass = Typed::string($parameters['category_class']);
		$boardClass = Typed::string($parameters['board_class']);

		foreach ($boardList as $category) {
			if ($categoryClass) {
				echo sprintf($this->getCategoryClasses()[$categoryClass], $category['name']);
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

			echo sprintf(Utils::$context['lp_all_content_classes'][$boardClass], $content);
		}
	}

	private function getCategoryClasses(): array
	{
		$createHtml = fn(TitleClass $class, string $headerClass): string => Str::html('div')
			->class($class->value)
			->addHtml(Str::html('h4', '%1$s')->class($headerClass))
			->toHtml();

		return [
			TitleClass::TITLE_BAR->value => $createHtml(TitleClass::TITLE_BAR, 'titlebg'),
			TitleClass::SUB_BAR->value   => $createHtml(TitleClass::SUB_BAR, 'subbg'),
		];
	}
}
