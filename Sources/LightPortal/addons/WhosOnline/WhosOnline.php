<?php

namespace Bugo\LightPortal\Addons\WhosOnline;

use Bugo\LightPortal\Helpers;

/**
 * WhosOnline
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2020 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 1.4
 */

if (!defined('SMF'))
	die('Hacking attempt...');

class WhosOnline
{
	/**
	 * @var string
	 */
	public $addon_icon = 'far fa-eye';

	/**
	 * @var bool
	 */
	private $show_group_key = false;

	/**
	 * @var int
	 */
	private $update_interval = 600;

	/**
	 * @param array $options
	 * @return void
	 */
	public function blockOptions(&$options)
	{
		$options['whos_online']['parameters']['show_group_key']  = $this->show_group_key;
		$options['whos_online']['parameters']['update_interval'] = $this->update_interval;
	}

	/**
	 * @param array $parameters
	 * @param string $type
	 * @return void
	 */
	public function validateBlockData(&$parameters, $type)
	{
		if ($type !== 'whos_online')
			return;

		$parameters['show_group_key']  = FILTER_VALIDATE_BOOLEAN;
		$parameters['update_interval'] = FILTER_VALIDATE_INT;
	}

	/**
	 * @return void
	 */
	public function prepareBlockFields()
	{
		global $context, $txt;

		if ($context['lp_block']['type'] !== 'whos_online')
			return;

		$context['posting_fields']['show_group_key']['label']['text'] = $txt['lp_whos_online_addon_show_group_key'];
		$context['posting_fields']['show_group_key']['input'] = array(
			'type' => 'checkbox',
			'attributes' => array(
				'id'      => 'show_group_key',
				'checked' => !empty($context['lp_block']['options']['parameters']['show_group_key'])
			),
			'tab' => 'content'
		);

		$context['posting_fields']['update_interval']['label']['text'] = $txt['lp_whos_online_addon_update_interval'];
		$context['posting_fields']['update_interval']['input'] = array(
			'type' => 'number',
			'attributes' => array(
				'id'    => 'update_interval',
				'min'   => 0,
				'value' => $context['lp_block']['options']['parameters']['update_interval']
			)
		);
	}

	/**
	 * Get the list of online members
	 *
	 * Получаем список пользователей онлайн
	 *
	 * @return array
	 */
	public function getData()
	{
		global $boarddir;

		require_once($boarddir . '/SSI.php');

		return ssi_whosOnline('array');
	}

	/**
	 * @param string $content
	 * @param string $type
	 * @param int $block_id
	 * @param int $cache_time
	 * @param array $parameters
	 * @return void
	 */
	public function prepareContent(&$content, $type, $block_id, $cache_time, $parameters)
	{
		global $user_info, $txt, $scripturl;

		if ($type !== 'whos_online')
			return;

		$whos_online = Helpers::cache(
			'whos_online_addon_b' . $block_id . '_u' . $user_info['id'],
			'getData',
			__CLASS__,
			$parameters['update_interval'] ?? $cache_time
		);

		if (!empty($whos_online)) {
			ob_start();

			echo Helpers::getCorrectDeclension(comma_format($whos_online['num_guests']), $txt['lp_guests_set']) . ', ' . Helpers::getCorrectDeclension(comma_format($whos_online['num_users_online']), $txt['lp_users_set']);

			$online_list = [];

			if (!empty($user_info['buddies']) && !empty($whos_online['num_buddies']))
				$online_list[] = Helpers::getCorrectDeclension(comma_format($whos_online['num_buddies']), $txt['lp_buddies_set']);

			if (!empty($whos_online['num_spiders']))
				$online_list[] = Helpers::getCorrectDeclension(comma_format($whos_online['num_spiders']), $txt['lp_spiders_set']);

			if (!empty($whos_online['num_users_hidden']))
				$online_list[] = Helpers::getCorrectDeclension(comma_format($whos_online['num_users_hidden']), $txt['lp_hidden_set']);

			if (!empty($online_list))
				echo ' (' . implode(', ', $online_list) . ')';

			echo '
			<br>' . implode(', ', $whos_online['list_users_online']);

			if (!empty($parameters['show_group_key']) && !empty($whos_online['online_groups'])) {
				$groups = [];

				foreach ($whos_online['online_groups'] as $group) {
					if ($group['hidden'] != 0 || $group['id'] == 3)
						continue;

					if (allowedTo('view_mlist')) {
						$groups[] = '<a href="' . $scripturl . '?action=groups;sa=members;group=' . $group['id'] . '"' . (!empty($group['color']) ? ' style="color: ' . $group['color'] . '"' : '') . '>' . $group['name'] . '</a>';
					} else {
						$groups[] = '<span' . (!empty($group['color']) ? ' style="color: ' . $group['color'] . '"' : '') . '>' . $group['name'] . '</span>';
					}
				}

				echo '
			<br>' . implode(', ', $groups);
			}

			$content = ob_get_clean();
		}
	}
}
