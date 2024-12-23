<?php declare(strict_types=1);

/**
 * @package SimpleFeeder (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category plugin
 * @version 22.12.24
 */

namespace Bugo\LightPortal\Plugins\SimpleFeeder;

use Bugo\Compat\Config;
use Bugo\LightPortal\Enums\Tab;
use Bugo\LightPortal\Plugins\Block;
use Bugo\LightPortal\Plugins\Event;
use Bugo\LightPortal\UI\Fields\CheckboxField;
use Bugo\LightPortal\UI\Fields\UrlField;
use Bugo\LightPortal\Utils\DateTime;
use Bugo\LightPortal\Utils\Str;

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
		$e->args->params = [
			'url'       => '',
			'show_text' => false,
		];
	}

	public function validateBlockParams(Event $e): void
	{
		$e->args->params = [
			'url'       => FILTER_VALIDATE_URL,
			'show_text' => FILTER_VALIDATE_BOOLEAN,
		];
	}

	public function prepareBlockFields(Event $e): void
	{
		$options = $e->args->options;

		UrlField::make('url', $this->txt['url'])
			->setTab(Tab::CONTENT)
			->required()
			->placeholder(Config::$scripturl . '?action=.xml;type=rss2')
			->setValue($options['url']);

		CheckboxField::make('show_text', $this->txt['show_text'])
			->setTab(Tab::CONTENT)
			->setValue($options['show_text']);
	}

	public function getData(string $url): array
	{
		if ($url === '')
			return [];

		$file = file_get_contents($url);
		$rss  = simplexml_load_string($file);

		return $rss ? ['data' => $rss->channel->item] : [];
	}

	public function prepareContent(Event $e): void
	{
		$parameters = $e->args->parameters;
		$parameters['show_text'] ??= false;

		$feed = $this->cache($this->name . '_addon_b' . $e->args->id)
			->setLifeTime($e->args->cacheTime)
			->setFallback(fn() => $this->getData($parameters['url']));

		if (empty($feed))
			return;

		if (isset($feed['data']))
			$feed = $feed['data'];

		foreach ($feed as $item) {
			echo Str::html('div', ['class' => 'windowbg'])
				->addHtml(
					Str::html('div', ['class' => 'block'])
						->addHtml(
							Str::html('span', ['class' => 'floatleft'])
								->addHtml(
									Str::html('h5')
										->addHtml(Str::html('a')->href($item->link)->setText($item->title))
								)
								->addHtml(Str::html('em')->setText(DateTime::relative(strtotime((string) $item->pubDate))))
						)
				)
				->addHtml($parameters['show_text'] ? Str::html('div', ['class' => 'list_posts double_height'])->setText($item->description) : '');
		}
	}
}
