<?php

/**
 * News.php
 *
 * @package News (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2022 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 24.03.22
 */

namespace Bugo\LightPortal\Addons\News;

use Bugo\LightPortal\Addons\Plugin;

if (! defined('LP_NAME'))
	die('No direct access...');

class News extends Plugin
{
	public string $type = 'block ssi';

	public string $icon = 'far fa-newspaper';

	public function blockOptions(array &$options)
	{
		$options['news']['parameters']['selected_item'] = 0;
	}

	public function validateBlockData(array &$parameters, string $type)
	{
		if ($type !== 'news')
			return;

		$parameters['selected_item'] = FILTER_VALIDATE_INT;
	}

	public function prepareBlockFields()
	{
		if ($this->context['lp_block']['type'] !== 'news')
			return;

		$this->context['posting_fields']['selected_item']['label']['text'] = $this->txt['lp_news']['selected_item'];
		$this->context['posting_fields']['selected_item']['input'] = [
			'type' => 'select',
			'attributes' => [
				'id' => 'selected_item'
			],
			'options' => [],
			'tab' => 'content'
		];

		$this->getData();

		$news = [$this->txt['lp_news']['random_news']];
		if (isset($this->context['news_lines'])) {
			array_unshift($this->context['news_lines'], $this->txt['lp_news']['random_news']);
			$news = $this->context['news_lines'];
		}

		foreach ($news as $key => $value) {
			$this->context['posting_fields']['selected_item']['input']['options'][$value] = [
				'value'    => $key,
				'selected' => $key == $this->context['lp_block']['options']['parameters']['selected_item']
			];
		}
	}

	public function getData(int $item = 0): string
	{
		setupThemeContext();

		if ($item > 0)
			return $this->context['news_lines'][$item - 1];

		return $this->getFromSsi('news', 'return');
	}

	public function prepareContent(string $type, int $block_id, int $cache_time, array $parameters)
	{
		if ($type !== 'news')
			return;

		echo $this->getData($parameters['selected_item']) ?: $this->txt['lp_news']['no_items'];
	}
}
