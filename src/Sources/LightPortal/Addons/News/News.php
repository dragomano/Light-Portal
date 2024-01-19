<?php

/**
 * News.php
 *
 * @package News (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 17.01.24
 */

namespace Bugo\LightPortal\Addons\News;

use Bugo\LightPortal\Addons\Block;
use Bugo\LightPortal\Areas\Fields\SelectField;
use Bugo\LightPortal\Utils\{Lang, Utils};

if (! defined('LP_NAME'))
	die('No direct access...');

class News extends Block
{
	public string $type = 'block ssi';

	public string $icon = 'far fa-newspaper';

	public function prepareBlockParams(array &$params): void
	{
		if (Utils::$context['current_block']['type'] !== 'news')
			return;

		$params['selected_item'] = 0;
	}

	public function validateBlockParams(array &$params): void
	{
		if (Utils::$context['current_block']['type'] !== 'news')
			return;

		$params['selected_item'] = FILTER_VALIDATE_INT;
	}

	public function prepareBlockFields(): void
	{
		if (Utils::$context['current_block']['type'] !== 'news')
			return;

		$this->getData();

		$news = [Lang::$txt['lp_news']['random_news']];
		if (isset(Utils::$context['news_lines'])) {
			array_unshift(Utils::$context['news_lines'], Lang::$txt['lp_news']['random_news']);
			$news = Utils::$context['news_lines'];
		}

		SelectField::make('selected_item', Lang::$txt['lp_news']['selected_item'])
			->setTab('content')
			->setOptions($news)
			->setValue(Utils::$context['lp_block']['options']['selected_item']);
	}

	public function getData(int $item = 0): string
	{
		setupThemeContext();

		if ($item > 0)
			return Utils::$context['news_lines'][$item - 1];

		return $this->getFromSsi('news', 'return');
	}

	public function prepareContent(object $data, array $parameters): void
	{
		if ($data->type !== 'news')
			return;

		echo $this->getData($parameters['selected_item']) ?: Lang::$txt['lp_news']['no_items'];
	}
}
