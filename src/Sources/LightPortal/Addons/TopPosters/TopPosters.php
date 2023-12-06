<?php

/**
 * TopPosters.php
 *
 * @package TopPosters (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2023 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 06.12.23
 */

namespace Bugo\LightPortal\Addons\TopPosters;

use Bugo\LightPortal\Addons\Block;
use Bugo\LightPortal\Areas\Fields\CheckboxField;
use Bugo\LightPortal\Areas\Fields\NumberField;
use Exception;

if (! defined('LP_NAME'))
	die('No direct access...');

class TopPosters extends Block
{
	public string $icon = 'fas fa-users';

	public function blockOptions(array &$options): void
	{
		$options['top_posters']['parameters'] = [
			'show_avatars'      => true,
			'num_posters'       => 10,
			'show_numbers_only' => false,
		];
	}

	public function validateBlockData(array &$parameters, string $type): void
	{
		if ($type !== 'top_posters')
			return;

		$parameters['show_avatars']      = FILTER_VALIDATE_BOOLEAN;
		$parameters['num_posters']       = FILTER_VALIDATE_INT;
		$parameters['show_numbers_only'] = FILTER_VALIDATE_BOOLEAN;
	}

	public function prepareBlockFields(): void
	{
		if ($this->context['lp_block']['type'] !== 'top_posters')
			return;

		CheckboxField::make('show_avatars', $this->txt['lp_top_posters']['show_avatars'])
			->setValue($this->context['lp_block']['options']['parameters']['show_avatars']);

		NumberField::make('num_posters', $this->txt['lp_top_posters']['num_posters'])
			->setAttribute('min', 1)
			->setValue($this->context['lp_block']['options']['parameters']['num_posters']);

		CheckboxField::make('show_numbers_only', $this->txt['lp_top_posters']['show_numbers_only'])
			->setValue($this->context['lp_block']['options']['parameters']['show_numbers_only']);
	}

	public function getData(array $parameters): array
	{
		$result = $this->smcFunc['db_query']('', '
			SELECT id_member, real_name, posts
			FROM {db_prefix}members
			WHERE posts > {int:num_posts}
			ORDER BY posts DESC
			LIMIT {int:num_posters}',
			[
				'num_posts'   => 0,
				'num_posters' => $parameters['num_posters']
			]
		);

		$members = $this->smcFunc['db_fetch_all']($result);

		if (empty($members))
			return [];

		$loadedUserIds = $this->loadMemberData(array_column($members, 'id_member'));

		$posters = [];
		foreach ($members as $row) {
			if (! isset($this->memberContext[$row['id_member']]) && in_array($row['id_member'], $loadedUserIds)) {
				try {
					$this->loadMemberContext($row['id_member']);
				} catch (Exception $e) {
					$this->logError('[LP] TopPosters addon: ' . $e->getMessage());
				}
			}

			$posters[] = [
				'name'   => $row['real_name'],
				'link'   => $this->allowedTo('profile_view')
					? '<a href="' . $this->scripturl . '?action=profile;u=' . $row['id_member'] . '">' . $row['real_name'] . '</a>'
					: $row['real_name'],
				'avatar' => empty($parameters['show_avatars']) ? '' : $this->memberContext[$row['id_member']]['avatar']['image'],
				'posts'  => $row['posts']
			];
		}

		$this->smcFunc['db_free_result']($result);
		$this->context['lp_num_queries']++;

		return $posters;
	}

	public function prepareContent($data, array $parameters): void
	{
		if ($data->type !== 'top_posters')
			return;

		$parameters['show_numbers_only'] ??= false;

		$top_posters = $this->cache('top_posters_addon_b' . $data->block_id . '_u' . $this->user_info['id'])
			->setLifeTime($data->cache_time)
			->setFallback(self::class, 'getData', $parameters);

		if (empty($top_posters))
			return;

		echo '
		<dl class="top_posters stats">';

		$max = $top_posters[0]['posts'];

		foreach ($top_posters as $poster) {
			$width = $poster['posts'] * 100 / $max;

			echo '
			<dt>', $poster['avatar'], ' ', $poster['link'], '</dt>
			<dd class="statsbar generic_bar righttext">
				<div class="bar', (empty($poster['posts']) ? ' empty"' : '" style="width: ' . $width . '%"'), '></div>
				<span>', ($parameters['show_numbers_only'] ? $poster['posts'] : $this->translate($this->txt['lp_top_posters']['posts'], ['posts' => $poster['posts']])), '</span>
			</dd>';
		}

		echo '
		</dl>';
	}
}
