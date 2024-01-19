<?php

/**
 * WhosOnline.php
 *
 * @package WhosOnline (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 18.01.24
 */

namespace Bugo\LightPortal\Addons\WhosOnline;

use Bugo\LightPortal\Addons\Block;
use Bugo\LightPortal\Areas\Fields\{CheckboxField, NumberField};
use Bugo\LightPortal\Utils\{Config, Lang, User, Utils};

if (! defined('LP_NAME'))
	die('No direct access...');

class WhosOnline extends Block
{
	public string $type = 'block ssi';

	public string $icon = 'far fa-eye';

	public function prepareBlockParams(array &$params): void
	{
		if (Utils::$context['current_block']['type'] !== 'whos_online')
			return;

		$params = [
			'show_group_key'  => false,
			'show_avatars'    => false,
			'update_interval' => 600,
		];
	}

	public function validateBlockParams(array &$params): void
	{
		if (Utils::$context['current_block']['type'] !== 'whos_online')
			return;

		$params = [
			'show_group_key'  => FILTER_VALIDATE_BOOLEAN,
			'show_avatars'    => FILTER_VALIDATE_BOOLEAN,
			'update_interval' => FILTER_VALIDATE_INT,
		];
	}

	public function prepareBlockFields(): void
	{
		if (Utils::$context['current_block']['type'] !== 'whos_online')
			return;

		CheckboxField::make('show_group_key', Lang::$txt['lp_whos_online']['show_group_key'])
			->setValue(Utils::$context['lp_block']['options']['show_group_key']);

		CheckboxField::make('show_avatars', Lang::$txt['lp_whos_online']['show_avatars'])
			->setValue(Utils::$context['lp_block']['options']['show_avatars']);

		NumberField::make('update_interval', Lang::$txt['lp_whos_online']['update_interval'])
			->setAttribute('min', 0)
			->setValue(Utils::$context['lp_block']['options']['update_interval']);
	}

	public function prepareContent(object $data, array $parameters): void
	{
		if ($data->type !== 'whos_online')
			return;

		if ($this->request()->has('preview'))
			$parameters['update_interval'] = 0;

		$parameters['show_group_key'] ??= false;
		$parameters['show_avatars'] ??= false;

		$whos_online = $this->cache('whos_online_addon_b' . $data->block_id . '_u' . User::$info['id'])
			->setLifeTime($parameters['update_interval'] ?? $data->cache_time)
			->setFallback(self::class, 'getFromSsi', 'whosOnline', 'array');

		if (empty($whos_online))
			return;

		echo $this->translate('lp_guests_set', ['guests' => $whos_online['num_guests']]) . ', ' . $this->translate('lp_users_set', ['users' => $whos_online['num_users_online']]);

		$online_list = [];

		if (User::$info['buddies'] && $whos_online['num_buddies'])
			$online_list[] = $this->translate('lp_buddies_set', ['buddies' => $whos_online['num_buddies']]);

		if ($whos_online['num_spiders'])
			$online_list[] = $this->translate('lp_spiders_set', ['spiders' => $whos_online['num_spiders']]);

		if ($whos_online['num_users_hidden'])
			$online_list[] = $this->translate('lp_hidden_set', ['hidden' => $whos_online['num_users_hidden']]);

		if ($online_list)
			echo ' (' . Lang::sentenceList($online_list) . ')';

		// With avatars
		if ($parameters['show_avatars']) {
			$users = array_map(fn($item) => $this->getUserAvatar($item['id']), $whos_online['users_online']);

			$whos_online['list_users_online'] = [];
			foreach ($whos_online['users_online'] as $key => $user) {
				$whos_online['list_users_online'][] = '<a href="' . Config::$scripturl . '?action=profile;u=' . $user['id'] . '" title="' . $user['name'] . '">' . $users[$key] . '</a>';
			}
		}

		echo '
			<br>' . implode(', ', $whos_online['list_users_online']);

		if ($parameters['show_group_key'] && $whos_online['online_groups']) {
			$groups = [];

			foreach ($whos_online['online_groups'] as $group) {
				if ($group['hidden'] != 0 || $group['id'] == 3)
					continue;

				if ($this->allowedTo('view_mlist')) {
					$groups[] = '<a href="' . Config::$scripturl . '?action=groups;sa=members;group=' . $group['id'] . '"' . (empty($group['color']) ? '' : ' style="color: ' . $group['color'] . '"') . '>' . $group['name'] . '</a>';
				} else {
					$groups[] = '<span' . (empty($group['color']) ? '' : ' style="color: ' . $group['color'] . '"') . '>' . $group['name'] . '</span>';
				}
			}

			echo '
			<br>' . implode(', ', $groups);
		}
	}
}
