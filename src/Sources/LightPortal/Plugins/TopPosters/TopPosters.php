<?php

/**
 * @package TopPosters (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category plugin
 * @version 05.11.24
 */

namespace Bugo\LightPortal\Plugins\TopPosters;

use Bugo\Compat\{Config, Lang, User, Utils};
use Bugo\LightPortal\Areas\Fields\{CheckboxField, NumberField};
use Bugo\LightPortal\Plugins\{Block, Event};
use Bugo\LightPortal\Utils\Avatar;

if (! defined('LP_NAME'))
	die('No direct access...');

class TopPosters extends Block
{
	public string $icon = 'fas fa-users';

	public function prepareBlockParams(Event $e): void
	{
		if (Utils::$context['current_block']['type'] !== 'top_posters')
			return;

		$e->args->params = [
			'show_avatars'      => true,
			'num_posters'       => 10,
			'show_numbers_only' => false,
		];
	}

	public function validateBlockParams(Event $e): void
	{
		if (Utils::$context['current_block']['type'] !== 'top_posters')
			return;

		$e->args->params = [
			'show_avatars'      => FILTER_VALIDATE_BOOLEAN,
			'num_posters'       => FILTER_VALIDATE_INT,
			'show_numbers_only' => FILTER_VALIDATE_BOOLEAN,
		];
	}

	public function prepareBlockFields(): void
	{
		if (Utils::$context['current_block']['type'] !== 'top_posters')
			return;

		CheckboxField::make('show_avatars', Lang::$txt['lp_top_posters']['show_avatars'])
			->setValue(Utils::$context['lp_block']['options']['show_avatars']);

		NumberField::make('num_posters', Lang::$txt['lp_top_posters']['num_posters'])
			->setAttribute('min', 1)
			->setValue(Utils::$context['lp_block']['options']['num_posters']);

		CheckboxField::make('show_numbers_only', Lang::$txt['lp_top_posters']['show_numbers_only'])
			->setValue(Utils::$context['lp_block']['options']['show_numbers_only']);
	}

	public function getData(array $parameters): array
	{
		$result = Utils::$smcFunc['db_query']('', '
			SELECT id_member, real_name, posts
			FROM {db_prefix}members
			WHERE posts > {int:num_posts}
			ORDER BY posts DESC
			LIMIT {int:num_posters}',
			[
				'num_posts'   => 0,
				'num_posters' => $parameters['num_posters'],
			]
		);

		$members = Utils::$smcFunc['db_fetch_all']($result);

		if (empty($members))
			return [];

		$posters = [];
		foreach ($members as $row) {
			$posters[] = [
				'poster' => [
					'id'     => $row['id_member'],
					'name'   => $row['real_name'],
					'posts'  => $row['posts'],
					'link'   => User::hasPermission('profile_view')
						? '<a href="' . Config::$scripturl . '?action=profile;u=' . $row['id_member'] . '">' . $row['real_name'] . '</a>'
						: $row['real_name'],
				]
			];
		}

		Utils::$smcFunc['db_free_result']($result);

		if ($parameters['show_avatars'] && empty($parameters['use_simple_style'])) {
			$posters = Avatar::getWithItems($posters, 'poster');
		}

		return array_column($posters, 'poster');
	}

	public function prepareContent(Event $e): void
	{
		[$data, $parameters] = [$e->args->data, $e->args->parameters];

		if ($data->type !== 'top_posters')
			return;

		$parameters['show_numbers_only'] ??= false;
		$parameters['num_posters'] ??= 10;

		$topPosters = $this->cache('top_posters_addon_b' . $data->id . '_u' . User::$info['id'])
			->setLifeTime($data->cacheTime)
			->setFallback(self::class, 'getData', $parameters);

		if (empty($topPosters)) {
			echo Lang::$txt['lp_top_posters']['none'];
			return;
		}

		echo '
		<dl class="top_posters stats">';

		$max = $topPosters[0]['posts'];

		foreach ($topPosters as $poster) {
			$width = $poster['posts'] * 100 / $max;

			echo '
			<dt>', empty($parameters['show_avatars']) ? '' : $poster['avatar'], ' ', $poster['link'], '</dt>
			<dd class="statsbar generic_bar righttext">
				<div class="bar', (empty($poster['posts']) ? ' empty"' : '" style="width: ' . $width . '%"'), '></div>
				<span>', ($parameters['show_numbers_only'] ? $poster['posts'] : Lang::getTxt(Lang::$txt['lp_top_posters']['posts'], ['posts' => $poster['posts']])), '</span>
			</dd>';
		}

		echo '
		</dl>';
	}
}
