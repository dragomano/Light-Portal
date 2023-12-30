<?php

/**
 * News.php
 *
 * @package News (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2023 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 06.12.23
 */

namespace Bugo\LightPortal\Addons\News;

use Bugo\LightPortal\Addons\Block;
use Bugo\LightPortal\Areas\Fields\SelectField;

if (! defined('LP_NAME'))
	die('No direct access...');

class News extends Block
{
	public string $type = 'block ssi';

	public string $icon = 'far fa-newspaper';

	public function blockOptions(array &$options): void
	{
		$options['news']['parameters']['selected_item'] = 0;
	}

	public function validateBlockData(array &$parameters, string $type): void
	{
		if ($type !== 'news')
			return;

		$parameters['selected_item'] = FILTER_VALIDATE_INT;
	}

	public function prepareBlockFields(): void
	{
		if ($this->context['lp_block']['type'] !== 'news')
			return;

		$this->getData();

		$news = [$this->txt['lp_news']['random_news']];
		if (isset($this->context['news_lines'])) {
			array_unshift($this->context['news_lines'], $this->txt['lp_news']['random_news']);
			$news = $this->context['news_lines'];
		}

		SelectField::make('selected_item', $this->txt['lp_news']['selected_item'])
			->setTab('content')
			->setOptions($news)
			->setValue($this->context['lp_block']['options']['parameters']['selected_item']);
	}

	public function getData(int $item = 0): string
	{
		setupThemeContext();

		if ($item > 0)
			return $this->context['news_lines'][$item - 1];

		return $this->getFromSsi('news', 'return');
	}

	public function prepareContent(object $data, array $parameters): void
	{
		if ($data->type !== 'news')
			return;

		echo $this->getData($parameters['selected_item']) ?: $this->txt['lp_news']['no_items'];
	}
}
