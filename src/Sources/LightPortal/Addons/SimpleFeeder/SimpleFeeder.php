<?php

/**
 * SimpleFeeder.php
 *
 * @package SimpleFeeder (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 17.01.24
 */

namespace Bugo\LightPortal\Addons\SimpleFeeder;

use Bugo\LightPortal\Addons\Block;
use Bugo\LightPortal\Areas\Fields\{CheckboxField, TextField};
use Bugo\LightPortal\Utils\{Config, Lang, Utils};
use IntlException;

if (! defined('LP_NAME'))
	die('No direct access...');

class SimpleFeeder extends Block
{
	public string $icon = 'fas fa-rss';

	public function prepareBlockParams(array &$params): void
	{
		if (Utils::$context['current_block']['type'] !== 'simple_feeder')
			return;

		$params = [
			'url'       => '',
			'show_text' => false,
		];
	}

	public function validateBlockParams(array &$params): void
	{
		if (Utils::$context['current_block']['type'] !== 'simple_feeder')
			return;

		$params = [
			'url'       => FILTER_VALIDATE_URL,
			'show_text' => FILTER_VALIDATE_BOOLEAN,
		];
	}

	public function prepareBlockFields(): void
	{
		if (Utils::$context['current_block']['type'] !== 'simple_feeder')
			return;

		TextField::make('url', Lang::$txt['lp_simple_feeder']['url'])
			->setType('url')
			->setTab('content')
			->required()
			->setAttribute('maxlength', 255)
			->placeholder(Config::$scripturl . '?action=.xml;type=rss2')
			->setAttribute('style', 'width: 100%')
			->setValue(Utils::$context['lp_block']['options']['url']);

		CheckboxField::make('show_text', Lang::$txt['lp_simple_feeder']['show_text'])
			->setTab('content')
			->setValue(Utils::$context['lp_block']['options']['show_text']);
	}

	public function getData(string $url): array
	{
		if (empty($url))
			return [];

		$file = file_get_contents($url);
		$rss  = simplexml_load_string($file);

		return $rss ? ['data' => $rss->channel->item] : [];
	}

	/**
	 * @throws IntlException
	 */
	public function prepareContent(object $data, array $parameters): void
	{
		if ($data->type !== 'simple_feeder')
			return;

		$parameters['show_text'] ??= false;

		$feed = $this->cache('simple_feeder_addon_b' . $data->block_id)
			->setLifeTime($data->cache_time)
			->setFallback(self::class, 'getData', $parameters['url']);

		if (empty($feed))
			return;

		if (isset($feed['data']))
			$feed = $feed['data'];

		foreach ($feed as $item) {
			echo /** @lang text */ '
		<div class="windowbg">
			<div class="block">
				<span class="floatleft half_content">
					<h5><a href="', $item->link, '">', $item->title, '</a></h5>
					<em>', $this->getFriendlyTime(strtotime($item->pubDate)), '</em>
				</span>
			</div>';

			if ($parameters['show_text']) {
				echo '
			<div class="list_posts double_height">', $item->description, '</div>';
			}

			echo '
		</div>';
		}
	}
}
