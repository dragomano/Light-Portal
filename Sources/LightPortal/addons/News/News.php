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
 * @version 16.12.21
 */

namespace Bugo\LightPortal\Addons\News;

use Bugo\LightPortal\Addons\Plugin;

class News extends Plugin
{
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
		global $context, $txt;

		if ($context['lp_block']['type'] !== 'news')
			return;

		$context['posting_fields']['selected_item']['label']['text'] = $txt['lp_news']['selected_item'];
		$context['posting_fields']['selected_item']['input'] = array(
			'type' => 'select',
			'attributes' => array(
				'id' => 'selected_item'
			),
			'options' => array(),
			'tab' => 'content'
		);

		$this->getData();

		$news = [$txt['lp_news']['random_news']];
		if (! empty($context['news_lines'])) {
			array_unshift($context['news_lines'], $txt['lp_news']['random_news']);
			$news = $context['news_lines'];
		}

		foreach ($news as $key => $value) {
			$context['posting_fields']['selected_item']['input']['options'][$value] = array(
				'value'    => $key,
				'selected' => $key == $context['lp_block']['options']['parameters']['selected_item']
			);
		}
	}

	public function getData(int $item = 0): string
	{
		global $context;

		$this->loadSsi();

		setupThemeContext();

		if ($item > 0)
			return $context['news_lines'][$item - 1];

		return ssi_news('return');
	}

	public function prepareContent(string $type, int $block_id, int $cache_time, array $parameters)
	{
		global $txt;

		if ($type !== 'news')
			return;

		echo $this->getData($parameters['selected_item']) ?: $txt['lp_news']['no_items'];
	}
}
