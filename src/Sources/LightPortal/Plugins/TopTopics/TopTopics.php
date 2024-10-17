<?php

/**
 * @package TopTopics (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 19.02.24
 */

namespace Bugo\LightPortal\Plugins\TopTopics;

use Bugo\Compat\{Lang, User, Utils};
use Bugo\LightPortal\Plugins\Block;
use Bugo\LightPortal\Areas\Fields\{CheckboxField, NumberField, RadioField};

if (! defined('LP_NAME'))
	die('No direct access...');

class TopTopics extends Block
{
	public string $type = 'block ssi';

	public string $icon = 'fas fa-balance-scale-left';

	public function prepareBlockParams(array &$params): void
	{
		if (Utils::$context['current_block']['type'] !== 'top_topics')
			return;

		$params = [
			'popularity_type'   => 'replies',
			'num_topics'        => 10,
			'show_numbers_only' => false,
		];
	}

	public function validateBlockParams(array &$params): void
	{
		if (Utils::$context['current_block']['type'] !== 'top_topics')
			return;

		$params = [
			'popularity_type'   => FILTER_DEFAULT,
			'numе_topics'       => FILTER_VALIDATE_INT,
			'show_numbers_only' => FILTER_VALIDATE_BOOLEAN,
		];
	}

	public function prepareBlockFields(): void
	{
		if (Utils::$context['current_block']['type'] !== 'top_topics')
			return;

		RadioField::make('popularity_type', Lang::$txt['lp_top_topics']['type'])
			->setOptions(array_combine(['replies', 'views'], Lang::$txt['lp_top_topics']['type_set']))
			->setValue(Utils::$context['lp_block']['options']['popularity_type']);

		NumberField::make('num_topics', Lang::$txt['lp_top_topics']['num_topics'])
			->setAttribute('min', 1)
			->setValue(Utils::$context['lp_block']['options']['num_topics']);

		CheckboxField::make('show_numbers_only', Lang::$txt['lp_top_topics']['show_numbers_only'])
			->setValue(Utils::$context['lp_block']['options']['show_numbers_only']);
	}

	public function prepareContent(object $data, array $parameters): void
	{
		if ($data->type !== 'top_topics')
			return;

		$parameters['show_numbers_only'] ??= false;

		$topTopics = $this->cache('top_topics_addon_b' . $data->id . '_u' . User::$info['id'])
			->setLifeTime($data->cacheTime)
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

		echo '
		<dl class="stats">';

		$max = $topTopics[0]['num_' . $parameters['popularity_type']];

		foreach ($topTopics as $topic) {
			if ($topic['num_' . $parameters['popularity_type']] < 1)
				continue;

			$width = $topic['num_' . $parameters['popularity_type']] * 100 / $max;

			echo '
			<dt>', $topic['link'], '</dt>
			<dd class="statsbar generic_bar righttext">
				<div class="bar', (empty($topic['num_' . $parameters['popularity_type']]) ? ' empty"' : '" style="width: ' . $width . '%"'), '></div>
				<span>', ($parameters['show_numbers_only'] ? $topic['num_' . $parameters['popularity_type']] : Lang::getTxt('lp_' . $parameters['popularity_type'] . '_set', [$parameters['popularity_type'] => $topic['num_' . $parameters['popularity_type']]])), '</span>
			</dd>';
		}

		echo '
		</dl>';
	}
}
