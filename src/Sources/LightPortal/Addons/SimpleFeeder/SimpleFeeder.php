<?php

/**
 * SimpleFeeder.php
 *
 * @package SimpleFeeder (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2023 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 19.09.23
 */

namespace Bugo\LightPortal\Addons\SimpleFeeder;

use Bugo\LightPortal\Addons\Block;
use IntlException;

if (! defined('LP_NAME'))
	die('No direct access...');

class SimpleFeeder extends Block
{
	public string $icon = 'fas fa-rss';

	public function blockOptions(array &$options): void
	{
		$options['rss_feed']['parameters'] = [
			'url'       => '',
			'show_text' => false,
		];
	}

	public function validateBlockData(array &$parameters, string $type): void
	{
		if ($type !== 'rss_feed')
			return;

		$parameters['url']       = FILTER_VALIDATE_URL;
		$parameters['show_text'] = FILTER_VALIDATE_BOOLEAN;
	}

	public function prepareBlockFields(): void
	{
		if ($this->context['lp_block']['type'] !== 'rss_feed')
			return;

		$this->context['posting_fields']['url']['label']['text'] = $this->txt['lp_rss_feed']['url'];
		$this->context['posting_fields']['url']['input'] = [
			'type' => 'url',
			'attributes' => [
				'maxlength'   => 255,
				'value'       => $this->context['lp_block']['options']['parameters']['url'],
				'placeholder' => $this->scripturl . '?action=.xml;type=rss2',
				'required'    => true,
				'style'       => 'width: 100%'
			],
			'tab' => 'content'
		];

		$this->context['posting_fields']['show_text']['label']['text'] = $this->txt['lp_rss_feed']['show_text'];
		$this->context['posting_fields']['show_text']['input'] = [
			'type' => 'checkbox',
			'attributes' => [
				'id'      => 'show_text',
				'checked' => (bool) $this->context['lp_block']['options']['parameters']['show_text']
			],
			'tab' => 'content'
		];
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
	public function prepareContent($data, array $parameters): void
	{
		if ($data->type !== 'rss_feed')
			return;

		$parameters['show_text'] ??= false;

		$feed = $this->cache('rss_feed_addon_b' . $data->block_id)
			->setLifeTime($data->cache_time)
			->setFallback(self::class, 'getData', $parameters['url']);

		if (empty($feed))
			return;

		if (isset($feed['data']))
			$feed = $feed['data'];

		foreach ($feed as $item) {
			echo '
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
