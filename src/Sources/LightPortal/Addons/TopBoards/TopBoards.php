<?php

/**
 * @package TopBoards (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 19.02.24
 */

namespace Bugo\LightPortal\Addons\TopBoards;

use Bugo\Compat\{Lang, User, Utils};
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

	public function prepareBlockParams(array &$params): void
	{
		if (Utils::$context['current_block']['type'] !== 'top_boards')
			return;

		$params = [
			'num_boards'        => 10,
			'entity_type'       => 'num_topics',
			'show_numbers_only' => false,
		];
	}

	public function validateBlockParams(array &$params): void
	{
		if (Utils::$context['current_block']['type'] !== 'top_boards')
			return;

		$params = [
			'num_boards'        => FILTER_VALIDATE_INT,
			'entity_type'       => FILTER_DEFAULT,
			'show_numbers_only' => FILTER_VALIDATE_BOOLEAN,
		];
	}

	public function prepareBlockFields(): void
	{
		if (Utils::$context['current_block']['type'] !== 'top_boards')
			return;

		NumberField::make('num_boards', Lang::$txt['lp_top_boards']['num_boards'])
			->setAttribute('min', 1)
			->setValue(Utils::$context['lp_block']['options']['num_boards']);

		RadioField::make('entity_type', Lang::$txt['lp_top_boards']['entity_type'])
			->setOptions(array_combine(['num_topics', 'num_posts'], Lang::$txt['lp_top_boards']['entity_type_set']))
			->setValue(Utils::$context['lp_block']['options']['entity_type']);

		CheckboxField::make('show_numbers_only', Lang::$txt['lp_top_boards']['show_numbers_only'])
			->setValue(Utils::$context['lp_block']['options']['show_numbers_only']);
	}

	public function prepareContent(object $data, array $parameters): void
	{
		if ($data->type !== 'top_boards')
			return;

		$parameters['show_numbers_only'] ??= false;

		$topBoards = $this->cache('top_boards_addon_b' . $data->id . '_u' . User::$info['id'])
			->setLifeTime($data->cacheTime)
			->setFallback(self::class, 'getFromSsi', 'topBoards', (int) $parameters['num_boards'], 'array');

		if (empty($topBoards))
			return;

		echo '
		<dl class="stats">';

		$type = $parameters['entity_type'] === 'num_posts' ? 'posts' : 'topics';

		$max = $topBoards[0]['num_' . $type];

		foreach ($topBoards as $board) {
			if ($board['num_' . $type] < 1)
				continue;

			$width = $board['num_' . $type] * 100 / $max;

			echo '
			<dt>', $board['link'], '</dt>
			<dd class="statsbar generic_bar righttext">
				<div class="bar', (empty($board['num_' . $type]) ? ' empty"' : '" style="width: ' . $width . '%"'), '></div>
				<span>', ($parameters['show_numbers_only'] ? $board['num_' . $type] : Lang::getTxt(Lang::$txt['lp_top_boards'][$type], [$type => $board['num_' . $type]])), '</span>
			</dd>';
		}

		echo '
		</dl>';
	}
}
