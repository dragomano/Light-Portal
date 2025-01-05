<?php declare(strict_types=1);

/**
 * @package News (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category plugin
 * @version 05.01.25
 */

namespace Bugo\LightPortal\Plugins\News;

use Bugo\Compat\Theme;
use Bugo\Compat\Utils;
use Bugo\LightPortal\Enums\Tab;
use Bugo\LightPortal\Plugins\Block;
use Bugo\LightPortal\Plugins\Event;
use Bugo\LightPortal\UI\Fields\SelectField;
use WPLake\Typed\Typed;

if (! defined('LP_NAME'))
	die('No direct access...');

class News extends Block
{
	public string $type = 'block ssi';

	public string $icon = 'far fa-newspaper';

	public function prepareBlockParams(Event $e): void
	{
		$e->args->params['selected_item'] = 0;
	}

	public function validateBlockParams(Event $e): void
	{
		$e->args->params['selected_item'] = FILTER_VALIDATE_INT;
	}

	public function prepareBlockFields(Event $e): void
	{
		$this->getData();

		$news = [$this->txt['random_news']];
		if (isset(Utils::$context['news_lines'])) {
			array_unshift(Utils::$context['news_lines'], $this->txt['random_news']);
			$news = Utils::$context['news_lines'];
		}

		SelectField::make('selected_item', $this->txt['selected_item'])
			->setTab(Tab::CONTENT)
			->setOptions($news)
			->setValue($e->args->options['selected_item']);
	}

	public function getData(int $item = 0): string
	{
		Theme::setupContext();

		return $item > 0 ? Utils::$context['news_lines'][$item - 1] : $this->getFromSSI($this->name, 'return');
	}

	public function prepareContent(Event $e): void
	{
		echo $this->getData(Typed::int($e->args->parameters['selected_item'])) ?: $this->txt['no_items'];
	}
}
