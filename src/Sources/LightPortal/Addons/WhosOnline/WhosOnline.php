<?php

/**
 * WhosOnline.php
 *
 * @package WhosOnline (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2023 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 19.09.23
 */

namespace Bugo\LightPortal\Addons\WhosOnline;

use Bugo\LightPortal\Addons\Block;

if (! defined('LP_NAME'))
	die('No direct access...');

class WhosOnline extends Block
{
	public string $type = 'block ssi';

	public string $icon = 'far fa-eye';

	public function blockOptions(array &$options): void
	{
		$options['whos_online']['parameters'] = [
			'show_group_key'  => false,
			'show_avatars'    => false,
			'update_interval' => 600,
		];
	}

	public function validateBlockData(array &$parameters, string $type): void
	{
		if ($type !== 'whos_online')
			return;

		$parameters['show_group_key']  = FILTER_VALIDATE_BOOLEAN;
		$parameters['show_avatars']    = FILTER_VALIDATE_BOOLEAN;
		$parameters['update_interval'] = FILTER_VALIDATE_INT;
	}

	public function prepareBlockFields(): void
	{
		if ($this->context['lp_block']['type'] !== 'whos_online')
			return;

		$this->context['posting_fields']['show_group_key']['label']['text'] = $this->txt['lp_whos_online']['show_group_key'];
		$this->context['posting_fields']['show_group_key']['input'] = [
			'type' => 'checkbox',
			'attributes' => [
				'id'      => 'show_group_key',
				'checked' => (bool) $this->context['lp_block']['options']['parameters']['show_group_key']
			]
		];

		$this->context['posting_fields']['show_avatars']['label']['text'] = $this->txt['lp_whos_online']['show_avatars'];
		$this->context['posting_fields']['show_avatars']['input'] = [
			'type' => 'checkbox',
			'attributes' => [
				'id'      => 'show_avatars',
				'checked' => (bool) $this->context['lp_block']['options']['parameters']['show_avatars']
			]
		];

		$this->context['posting_fields']['update_interval']['label']['text'] = $this->txt['lp_whos_online']['update_interval'];
		$this->context['posting_fields']['update_interval']['input'] = [
			'type' => 'number',
			'attributes' => [
				'id'    => 'update_interval',
				'min'   => 0,
				'value' => $this->context['lp_block']['options']['parameters']['update_interval']
			]
		];
	}

	public function prepareContent($data, array $parameters): void
	{
		if ($data->type !== 'whos_online')
			return;

		if ($this->request()->has('preview'))
			$parameters['update_interval'] = 0;

		$parameters['show_group_key'] ??= false;
		$parameters['show_avatars'] ??= false;

		$whos_online = $this->cache('whos_online_addon_b' . $data->block_id . '_u' . $this->user_info['id'])
			->setLifeTime($parameters['update_interval'] ?? $data->cache_time)
			->setFallback(self::class, 'getFromSsi', 'whosOnline', 'array');

		if (empty($whos_online))
			return;

		echo $this->translate('lp_guests_set', ['guests' => $whos_online['num_guests']]) . ', ' . $this->translate('lp_users_set', ['users' => $whos_online['num_users_online']]);

		$online_list = [];

		if ($this->user_info['buddies'] && $whos_online['num_buddies'])
			$online_list[] = $this->translate('lp_buddies_set', ['buddies' => $whos_online['num_buddies']]);

		if ($whos_online['num_spiders'])
			$online_list[] = $this->translate('lp_spiders_set', ['spiders' => $whos_online['num_spiders']]);

		if ($whos_online['num_users_hidden'])
			$online_list[] = $this->translate('lp_hidden_set', ['hidden' => $whos_online['num_users_hidden']]);

		if ($online_list)
			echo ' (' . $this->sentenceList($online_list) . ')';

		// With avatars
		if ($parameters['show_avatars']) {
			$users = array_map(fn($item) => $this->getUserAvatar($item['id']), $whos_online['users_online']);

			$whos_online['list_users_online'] = [];
			foreach ($whos_online['users_online'] as $key => $user) {
				$whos_online['list_users_online'][] = '<a href="' . $this->scripturl . '?action=profile;u=' . $user['id'] . '" title="' . $user['name'] . '">' . $users[$key] . '</a>';
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
					$groups[] = '<a href="' . $this->scripturl . '?action=groups;sa=members;group=' . $group['id'] . '"' . (empty($group['color']) ? '' : ' style="color: ' . $group['color'] . '"') . '>' . $group['name'] . '</a>';
				} else {
					$groups[] = '<span' . (empty($group['color']) ? '' : ' style="color: ' . $group['color'] . '"') . '>' . $group['name'] . '</span>';
				}
			}

			echo '
			<br>' . implode(', ', $groups);
		}
	}
}
