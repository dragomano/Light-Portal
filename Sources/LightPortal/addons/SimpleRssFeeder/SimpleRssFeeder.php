<?php

/**
 * SimpleRssFeeder.php
 *
 * @package SimpleRssFeeder (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2022 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 15.12.21
 */

namespace Bugo\LightPortal\Addons\SimpleRssFeeder;

use Bugo\LightPortal\Addons\Plugin;
use Bugo\LightPortal\Helper;

class SimpleRssFeeder extends Plugin
{
	public string $icon = 'fas fa-rss';

	public function blockOptions(array &$options)
	{
		$options['simple_rss_feeder']['parameters'] = [
			'url'       => '',
			'show_text' => false,
		];
	}

	public function validateBlockData(array &$parameters, string $type)
	{
		if ($type !== 'simple_rss_feeder')
			return;

		$parameters['url']       = FILTER_VALIDATE_URL;
		$parameters['show_text'] = FILTER_VALIDATE_BOOLEAN;
	}

	public function prepareBlockFields()
	{
		global $context, $txt, $scripturl;

		if ($context['lp_block']['type'] !== 'simple_rss_feeder')
			return;

		$context['posting_fields']['url']['label']['text'] = $txt['lp_simple_rss_feeder']['url'];
		$context['posting_fields']['url']['input'] = array(
			'type' => 'url',
			'attributes' => array(
				'maxlength'   => 255,
				'value'       => $context['lp_block']['options']['parameters']['url'],
				'placeholder' => $scripturl . '?action=.xml;type=rss2',
				'required'    => true,
				'style'       => 'width: 100%'
			),
			'tab' => 'content'
		);

		$context['posting_fields']['show_text']['label']['text'] = $txt['lp_simple_rss_feeder']['show_text'];
		$context['posting_fields']['show_text']['input'] = array(
			'type' => 'checkbox',
			'attributes' => array(
				'id'      => 'show_text',
				'checked' => ! empty($context['lp_block']['options']['parameters']['show_text'])
			),
			'tab' => 'content'
		);
	}

	public function getData(string $url): array
	{
		if (empty($url))
			return [];

		$file = file_get_contents($url);
		$rss  = simplexml_load_string($file);

		return $rss ? ['data' => $rss->channel->item] : [];
	}

	public function prepareContent(string $type, int $block_id, int $cache_time, array $parameters)
	{
		if ($type !== 'simple_rss_feeder')
			return;

		$feed = Helper::cache('simple_rss_feeder_addon_b' . $block_id)
			->setLifeTime($cache_time)
			->setFallback(__CLASS__, 'getData', $parameters['url']);

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
					<em>', Helper::getFriendlyTime(strtotime($item->pubDate)), '</em>
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
