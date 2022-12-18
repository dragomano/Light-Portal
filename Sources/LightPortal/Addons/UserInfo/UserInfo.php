<?php

/**
 * UserInfo.php
 *
 * @package UserInfo (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2022 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 18.12.22
 */

namespace Bugo\LightPortal\Addons\UserInfo;

use Bugo\LightPortal\Addons\Plugin;

if (! defined('LP_NAME'))
	die('No direct access...');

class UserInfo extends Plugin
{
	public string $icon = 'fas fa-user';

	public function getData(): array
	{
		if (! isset($this->memberContext[$this->user_info['id']]) && in_array($this->user_info['id'], $this->loadMemberData($this->user_info['id']))) {
			try {
				$this->loadMemberContext($this->user_info['id']);
			} catch (\Exception $e) {
				$this->logError('[LP] UserInfo addon: ' . $e->getMessage());
			}
		}

		return $this->memberContext[$this->user_info['id']];
	}

	public function prepareContent(string $type, int $block_id, int $cache_time)
	{
		if ($type !== 'user_info')
			return;

		$this->setTemplate();

		if (! $this->context['user']['is_logged']) {
			show_user_info_for_guests();
			return;
		}

		$userData = $this->cache('user_info_addon_u' . $this->context['user']['id'])
			->setLifeTime($cache_time)
			->setFallback(__CLASS__, 'getData');

		show_user_info($userData);
	}
}
