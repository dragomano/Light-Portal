<?php

/**
 * @package SimpleFeeder (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category plugin
 * @version 05.11.24
 */

namespace Bugo\LightPortal\Plugins\SimpleFeeder;

use Bugo\Compat\{Config, Lang, Utils};
use Bugo\LightPortal\Areas\Fields\{CheckboxField, UrlField};
use Bugo\LightPortal\Enums\Tab;
use Bugo\LightPortal\Plugins\Block;
use Bugo\LightPortal\Plugins\Event;
use Bugo\LightPortal\Utils\DateTime;
use IntlException;

use function file_get_contents;
use function simplexml_load_string;
use function strtotime;

if (! defined('LP_NAME'))
	die('No direct access...');

class SimpleFeeder extends Block
{
	public string $icon = 'fas fa-rss';

	public function prepareBlockParams(Event $e): void
	{
		if (Utils::$context['current_block']['type'] !== 'simple_feeder')
			return;

		$e->args->params = [
			'url'       => '',
			'show_text' => false,
		];
	}

	public function validateBlockParams(Event $e): void
	{
		if (Utils::$context['current_block']['type'] !== 'simple_feeder')
			return;

		$e->args->params = [
			'url'       => FILTER_VALIDATE_URL,
			'show_text' => FILTER_VALIDATE_BOOLEAN,
		];
	}

	public function prepareBlockFields(): void
	{
		if (Utils::$context['current_block']['type'] !== 'simple_feeder')
			return;

		UrlField::make('url', Lang::$txt['lp_simple_feeder']['url'])
			->setTab(Tab::CONTENT)
			->required()
			->placeholder(Config::$scripturl . '?action=.xml;type=rss2')
			->setValue(Utils::$context['lp_block']['options']['url']);

		CheckboxField::make('show_text', Lang::$txt['lp_simple_feeder']['show_text'])
			->setTab(Tab::CONTENT)
			->setValue(Utils::$context['lp_block']['options']['show_text']);
	}

	public function getData(string $url): array
	{
		if ($url === '')
			return [];

		$file = file_get_contents($url);
		$rss  = simplexml_load_string($file);

		return $rss ? ['data' => $rss->channel->item] : [];
	}

	/**
	 * @throws IntlException
	 */
	public function prepareContent(Event $e): void
	{
		[$data, $parameters] = [$e->args->data, $e->args->parameters];

		if ($data->type !== 'simple_feeder')
			return;

		$parameters['show_text'] ??= false;

		$feed = $this->cache('simple_feeder_addon_b' . $data->id)
			->setLifeTime($data->cacheTime)
			->setFallback(self::class, 'getData', $parameters['url']);

		if (empty($feed))
			return;

		if (isset($feed['data']))
			$feed = $feed['data'];

		foreach ($feed as $item) {
			echo '
		<div class="windowbg">
			<div class="block">
				<span class="floatleft">
					<h5><a href="', $item->link, '">', $item->title, '</a></h5>
					<em>', DateTime::relative(strtotime((string) $item->pubDate)), '</em>
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
