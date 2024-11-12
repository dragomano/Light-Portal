<?php

/**
 * @package TopTopics (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category plugin
 * @version 13.11.24
 */

namespace Bugo\LightPortal\Plugins\TopTopics;

use Bugo\Compat\{Lang, User};
use Bugo\LightPortal\Areas\Fields\{CheckboxField, NumberField, RadioField};
use Bugo\LightPortal\Plugins\{Block, Event};
use Bugo\LightPortal\Utils\Str;

if (! defined('LP_NAME'))
	die('No direct access...');

class TopTopics extends Block
{
	public string $type = 'block ssi';

	public string $icon = 'fas fa-balance-scale-left';

	public function prepareBlockParams(Event $e): void
	{
		if ($e->args->type !== $this->name)
			return;

		$e->args->params = [
			'popularity_type'   => 'replies',
			'num_topics'        => 10,
			'show_numbers_only' => false,
		];
	}

	public function validateBlockParams(Event $e): void
	{
		if ($e->args->type !== $this->name)
			return;

		$e->args->params = [
			'popularity_type'   => FILTER_DEFAULT,
			'numе_topics'       => FILTER_VALIDATE_INT,
			'show_numbers_only' => FILTER_VALIDATE_BOOLEAN,
		];
	}

	public function prepareBlockFields(Event $e): void
	{
		if ($e->args->type !== $this->name)
			return;

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

	public function prepareContent(Event $e): void
	{
		if ($e->args->type !== $this->name)
			return;

		$parameters = $e->args->parameters;
		$parameters['show_numbers_only'] ??= false;

		$topTopics = $this->cache($this->name . '_addon_b' . $e->args->id . '_u' . User::$info['id'])
			->setLifeTime($e->args->cacheTime)
			->setFallback(
				self::class,
				'getFromSsi',
				'topTopics',
				$parameters['popularity_type'],
				$parameters['num_topics'],
				'array'
			);

		if (empty($topTopics))
			return;

		$dl = Str::html('dl', ['class' => 'stats']);

		$max = $topTopics[0]['num_' . $parameters['popularity_type']];

		foreach ($topTopics as $topic) {
			if ($topic['num_' . $parameters['popularity_type']] < 1)
				continue;

			$width = $topic['num_' . $parameters['popularity_type']] * 100 / $max;

			$dt = Str::html('dt')->addHtml($topic['link']);

			$dd = Str::html('dd', ['class' => 'statsbar generic_bar righttext']);
			$barClass = empty($topic['num_' . $parameters['popularity_type']]) ? 'bar empty' : 'bar';
			$barStyle = empty($topic['num_' . $parameters['popularity_type']]) ? null : 'width: ' . $width . '%';

			$bar = Str::html('div', ['class' => $barClass, 'style' => $barStyle]);
			$dd->addHtml($bar);

			$countText = $parameters['show_numbers_only']
				? $topic['num_' . $parameters['popularity_type']]
				: Lang::getTxt('lp_' . $parameters['popularity_type'] . '_set', [$parameters['popularity_type'] => $topic['num_' . $parameters['popularity_type']]]);

			$dd->addHtml(Str::html('span', $countText));

			$dl->addHtml($dt);
			$dl->addHtml($dd);
		}

		echo $dl;
	}
}
