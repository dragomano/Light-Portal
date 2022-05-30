<?php

/**
 * WhosOnline.php
 *
 * @package WhosOnline (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2022 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 11.05.22
 */

namespace Bugo\LightPortal\Addons\WhosOnline;

use Bugo\LightPortal\Addons\Plugin;

if (! defined('LP_NAME'))
	die('No direct access...');

class WhosOnline extends Plugin
{
	public string $type = 'block ssi';

	public string $icon = 'far fa-eye';

	public function blockOptions(array &$options)
	{
		$options['whos_online']['parameters'] = [
			'show_group_key'  => false,
			'update_interval' => 600,
		];
	}

	public function validateBlockData(array &$parameters, string $type)
	{
		if ($type !== 'whos_online')
			return;

		$parameters['show_group_key']  = FILTER_VALIDATE_BOOLEAN;
		$parameters['update_interval'] = FILTER_VALIDATE_INT;
	}

	public function prepareBlockFields()
	{
		if ($this->context['lp_block']['type'] !== 'whos_online')
			return;

		$this->context['posting_fields']['show_group_key']['label']['text'] = $this->txt['lp_whos_online']['show_group_key'];
		$this->context['posting_fields']['show_group_key']['input'] = [
			'type' => 'checkbox',
			'attributes' => [
				'id'      => 'show_group_key',
				'checked' => (bool) $this->context['lp_block']['options']['parameters']['show_group_key']
			],
			'tab' => 'content'
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

	public function prepareContent(string $type, int $block_id, int $cache_time, array $parameters)
	{
		if ($type !== 'whos_online')
			return;

		if ($this->request()->has('preview'))
			$parameters['update_interval'] = 0;

		$whos_online = $this->cache('whos_online_addon_b' . $block_id . '_u' . $this->user_info['id'])
			->setLifeTime($parameters['update_interval'] ?? $cache_time)
			->setFallback(__CLASS__, 'getFromSsi', 'whosOnline', 'array');

		if (empty($whos_online))
			return;

		echo __('lp_guests_set', ['guests' => $whos_online['num_guests']]) . ', ' . __('lp_users_set', ['users' => $whos_online['num_users_online']]);

		$online_list = [];

		if ($this->user_info['buddies'] && $whos_online['num_buddies'])
			$online_list[] = __('lp_buddies_set', ['buddies' => $whos_online['num_buddies']]);

		if ($whos_online['num_spiders'])
			$online_list[] = __('lp_spiders_set', ['spiders' => $whos_online['num_spiders']]);

		if ($whos_online['num_users_hidden'])
			$online_list[] = __('lp_hidden_set', ['hidden' => $whos_online['num_users_hidden']]);

		if ($online_list)
			echo ' (' . sentence_list($online_list) . ')';

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
