<?php declare(strict_types=1);

/**
 * @package TopTopics (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category plugin
 * @version 01.10.25
 */

namespace Bugo\LightPortal\Plugins\TopTopics;

use Bugo\Compat\Lang;
use Bugo\LightPortal\Enums\PortalHook;
use Bugo\LightPortal\Plugins\Event;
use Bugo\LightPortal\Plugins\HookAttribute;
use Bugo\LightPortal\Plugins\PluginAttribute;
use Bugo\LightPortal\Plugins\SsiBlock;
use Bugo\LightPortal\UI\Fields\CheckboxField;
use Bugo\LightPortal\UI\Fields\NumberField;
use Bugo\LightPortal\UI\Fields\RadioField;
use Bugo\LightPortal\Utils\Str;
use WPLake\Typed\Typed;

if (! defined('LP_NAME'))
	die('No direct access...');

#[PluginAttribute(icon: 'fas fa-balance-scale-left')]
class TopTopics extends SsiBlock
{
	#[HookAttribute(PortalHook::prepareBlockParams)]
	public function prepareBlockParams(Event $e): void
	{
		$e->args->params = [
			'popularity_type'   => 'replies',
			'num_topics'        => 10,
			'show_numbers_only' => false,
		];
	}

	#[HookAttribute(PortalHook::validateBlockParams)]
	public function validateBlockParams(Event $e): void
	{
		$e->args->params = [
			'popularity_type'   => FILTER_DEFAULT,
			'num_topics'        => FILTER_VALIDATE_INT,
			'show_numbers_only' => FILTER_VALIDATE_BOOLEAN,
		];
	}

	#[HookAttribute(PortalHook::prepareBlockFields)]
	public function prepareBlockFields(Event $e): void
	{
		$options = $e->args->options;

		RadioField::make('popularity_type', $this->txt['type'])
			->setOptions(array_combine(['replies', 'views'], $this->txt['type_set']))
			->setValue($options['popularity_type']);

		NumberField::make('num_topics', $this->txt['num_topics'])
			->setAttribute('min', 1)
			->setValue($options['num_topics']);

		CheckboxField::make('show_numbers_only', $this->txt['show_numbers_only'])
			->setValue($options['show_numbers_only']);
	}

	#[HookAttribute(PortalHook::prepareContent)]
	public function prepareContent(Event $e): void
	{
		$parameters = $e->args->parameters;

		$type = Typed::string($parameters['popularity_type'], default: 'replies');
		$numTopics = Typed::int($parameters['num_topics'], default: 10);

		$topTopics = $this->userCache($this->name . '_addon_b' . $e->args->id)
			->setLifeTime($e->args->cacheTime)
			->setFallback(
				fn() => $this->getFromSSI(
					'topTopics',
					$type,
					$numTopics,
					'array'
				)
			);

		if (empty($topTopics))
			return;

		$dl = Str::html('dl', ['class' => 'stats']);

		$max = $topTopics[0]['num_' . $type];

		foreach ($topTopics as $topic) {
			if ($topic['num_' . $type] < 1)
				continue;

			$width = $topic['num_' . $type] * 100 / $max;

			$dt = Str::html('dt')->addHtml($topic['link']);

			$dd = Str::html('dd', ['class' => 'statsbar generic_bar righttext']);
			$barClass = empty($topic['num_' . $type]) ? 'bar empty' : 'bar';
			$barStyle = empty($topic['num_' . $type]) ? null : 'width: ' . $width . '%';

			$bar = Str::html('div', ['class' => $barClass, 'style' => $barStyle]);
			$dd->addHtml($bar);

			$countText = $parameters['show_numbers_only']
				? $topic['num_' . $type]
				: Lang::getTxt('lp_' . $type . '_set', [$type => $topic['num_' . $type]]);

			$dd->addHtml(Str::html('span', $countText));

			$dl->addHtml($dt);
			$dl->addHtml($dd);
		}

		echo $dl;
	}
}
