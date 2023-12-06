<?php

/**
 * TopBoards.php
 *
 * @package TopBoards (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2023 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 06.12.23
 */

namespace Bugo\LightPortal\Addons\TopBoards;

use Bugo\LightPortal\Addons\Block;
use Bugo\LightPortal\Areas\Fields\CheckboxField;
use Bugo\LightPortal\Areas\Fields\NumberField;
use Bugo\LightPortal\Areas\Fields\RadioField;

if (! defined('LP_NAME'))
	die('No direct access...');

class TopBoards extends Block
{
	public string $type = 'block ssi';

	public string $icon = 'fas fa-balance-scale-left';

	public function blockOptions(array &$options): void
	{
		$options['top_boards']['parameters'] = [
			'num_boards'        => 10,
			'entity_type'       => 'num_topics',
			'show_numbers_only' => false,
		];
	}

	public function validateBlockData(array &$parameters, string $type): void
	{
		if ($type !== 'top_boards')
			return;

		$parameters['num_boards']        = FILTER_VALIDATE_INT;
		$parameters['entity_type']       = FILTER_DEFAULT;
		$parameters['show_numbers_only'] = FILTER_VALIDATE_BOOLEAN;
	}

	public function prepareBlockFields(): void
	{
		if ($this->context['lp_block']['type'] !== 'top_boards')
			return;

		NumberField::make('num_boards', $this->txt['lp_top_boards']['num_boards'])
			->setAttribute('min', 1)
			->setValue($this->context['lp_block']['options']['parameters']['num_boards']);

		RadioField::make('entity_type', $this->txt['lp_top_boards']['entity_type'])
			->setOptions(array_combine(['num_topics', 'num_posts'], $this->txt['lp_top_boards']['entity_type_set']))
			->setValue($this->context['lp_block']['options']['parameters']['entity_type']);

		CheckboxField::make('show_numbers_only', $this->txt['lp_top_boards']['show_numbers_only'])
			->setValue($this->context['lp_block']['options']['parameters']['show_numbers_only']);
	}

	public function prepareContent($data, array $parameters): void
	{
		if ($data->type !== 'top_boards')
			return;

		$parameters['show_numbers_only'] ??= false;

		$top_boards = $this->cache('top_boards_addon_b' . $data->block_id . '_u' . $this->user_info['id'])
			->setLifeTime($data->cache_time)
			->setFallback(self::class, 'getFromSsi', 'topBoards', (int) $parameters['num_boards'], 'array');

		if (empty($top_boards))
			return;

		echo '
		<dl class="stats">';

		$type = $parameters['entity_type'] === 'num_posts' ? 'posts' : 'topics';

		$max = $top_boards[0]['num_' . $type];

		foreach ($top_boards as $board) {
			if ($board['num_' . $type] < 1)
				continue;

			$width = $board['num_' . $type] * 100 / $max;

			echo '
			<dt>', $board['link'], '</dt>
			<dd class="statsbar generic_bar righttext">
				<div class="bar', (empty($board['num_' . $type]) ? ' empty"' : '" style="width: ' . $width . '%"'), '></div>
				<span>', ($parameters['show_numbers_only'] ? $board['num_' . $type] : $this->translate($this->txt['lp_top_boards'][$type], [$type => $board['num_' . $type]])), '</span>
			</dd>';
		}

		echo '
		</dl>';
	}
}
