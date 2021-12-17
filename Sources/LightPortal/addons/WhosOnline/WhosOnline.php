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
 * @version 16.12.21
 */

namespace Bugo\LightPortal\Addons\WhosOnline;

use Bugo\LightPortal\Addons\Plugin;
use Bugo\LightPortal\Helper;

class WhosOnline extends Plugin
{
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
		global $context, $txt;

		if ($context['lp_block']['type'] !== 'whos_online')
			return;

		$context['posting_fields']['show_group_key']['label']['text'] = $txt['lp_whos_online']['show_group_key'];
		$context['posting_fields']['show_group_key']['input'] = array(
			'type' => 'checkbox',
			'attributes' => array(
				'id'      => 'show_group_key',
				'checked' => ! empty($context['lp_block']['options']['parameters']['show_group_key'])
			),
			'tab' => 'content'
		);

		$context['posting_fields']['update_interval']['label']['text'] = $txt['lp_whos_online']['update_interval'];
		$context['posting_fields']['update_interval']['input'] = array(
			'type' => 'number',
			'attributes' => array(
				'id'    => 'update_interval',
				'min'   => 0,
				'value' => $context['lp_block']['options']['parameters']['update_interval']
			)
		);
	}

	public function getData(): array
	{
		$this->loadSsi();

		return ssi_whosOnline('array');
	}

	public function prepareContent(string $type, int $block_id, int $cache_time, array $parameters)
	{
		global $user_info, $txt, $scripturl;

		if ($type !== 'whos_online')
			return;

		$whos_online = Helper::cache('whos_online_addon_b' . $block_id . '_u' . $user_info['id'])
			->setLifeTime($parameters['update_interval'] ?? $cache_time)
			->setFallback(__CLASS__, 'getData');

		if (empty($whos_online))
			return;

		echo Helper::getPluralText(comma_format($whos_online['num_guests']), $txt['lp_guests_set']) . ', ' . Helper::getPluralText(comma_format($whos_online['num_users_online']), $txt['lp_users_set']);

		$online_list = [];

		if (! empty($user_info['buddies']) && ! empty($whos_online['num_buddies']))
			$online_list[] = Helper::getPluralText(comma_format($whos_online['num_buddies']), $txt['lp_buddies_set']);

		if (! empty($whos_online['num_spiders']))
			$online_list[] = Helper::getPluralText(comma_format($whos_online['num_spiders']), $txt['lp_spiders_set']);

		if (! empty($whos_online['num_users_hidden']))
			$online_list[] = Helper::getPluralText(comma_format($whos_online['num_users_hidden']), $txt['lp_hidden_set']);

		if (! empty($online_list))
			echo ' (' . sentence_list($online_list) . ')';

		echo '
			<br>' . implode(', ', $whos_online['list_users_online']);

		if (! empty($parameters['show_group_key']) && ! empty($whos_online['online_groups'])) {
			$groups = [];

			foreach ($whos_online['online_groups'] as $group) {
				if ($group['hidden'] != 0 || $group['id'] == 3)
					continue;

				if (allowedTo('view_mlist')) {
					$groups[] = '<a href="' . $scripturl . '?action=groups;sa=members;group=' . $group['id'] . '"' . (empty($group['color']) ? '' : ' style="color: ' . $group['color'] . '"') . '>' . $group['name'] . '</a>';
				} else {
					$groups[] = '<span' . (empty($group['color']) ? '' : ' style="color: ' . $group['color'] . '"') . '>' . $group['name'] . '</span>';
				}
			}

			echo '
			<br>' . implode(', ', $groups);
		}
	}
}
