<?php declare(strict_types=1);

/**
 * @package BoardList (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2026 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category plugin
 * @version 26.10.25
 */

namespace LightPortal\Plugins\BoardList;

use Bugo\Compat\Config;
use Bugo\Compat\Utils;
use LightPortal\Enums\ContentClass;
use LightPortal\Enums\Tab;
use LightPortal\Enums\TitleClass;
use LightPortal\Plugins\Block;
use LightPortal\Plugins\Event;
use LightPortal\Plugins\PluginAttribute;
use LightPortal\UI\Fields\CustomField;
use LightPortal\UI\Partials\SelectFactory;
use LightPortal\Utils\Icon;
use LightPortal\Utils\MessageIndex;
use LightPortal\Utils\Str;

if (! defined('LP_NAME'))
	die('No direct access...');

#[PluginAttribute(icon: 'far fa-list-alt', showContentClass: false)]
class BoardList extends Block
{
	public function prepareBlockParams(Event $e): void
	{
		$e->args->params = [
			'category_class' => TitleClass::TITLE_BAR->value,
			'board_class'    => ContentClass::ROUNDFRAME->value,
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
			->setValue(fn() => SelectFactory::titleClass([
				'id'    => 'category_class',
				'data'  => $this->getCategoryClasses(),
				'value' => $options['category_class'],
			]));

		CustomField::make('board_class', $this->txt['board_class'])
			->setTab(Tab::APPEARANCE)
			->setValue(static fn() => SelectFactory::contentClass([
				'id'    => 'board_class',
				'value' => $options['board_class'],
			]));
	}

	public function prepareContent(Event $e): void
	{
		$boardList = $this->userCache($this->name . '_addon_b' . $e->args->id)
			->setLifeTime($e->args->cacheTime)
			->setFallback(MessageIndex::getBoardList(...));

		if (empty($boardList))
			return;

		Utils::$context['current_board'] ??= 0;

		$parameters = $e->args->parameters;

		$categoryClass = Str::typed('string', $parameters['category_class']);
		$boardClass = Str::typed('string', $parameters['board_class']);

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

			echo sprintf(ContentClass::values()[$boardClass], $content);
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
