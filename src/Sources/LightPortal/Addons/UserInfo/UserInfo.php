<?php

/**
 * UserInfo.php
 *
 * @package UserInfo (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2023 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 26.12.23
 */

namespace Bugo\LightPortal\Addons\UserInfo;

use Bugo\LightPortal\Addons\Block;
use Exception;

if (! defined('LP_NAME'))
	die('No direct access...');

class UserInfo extends Block
{
	public string $icon = 'fas fa-user';

	/**
	 * @throws Exception
	 */
	public function getData(): array
	{
		$this->loadMemberData([$this->user_info['id']]);

		return $this->loadMemberContext($this->user_info['id']);
	}

	public function prepareContent($data): void
	{
		if ($data->type !== 'user_info')
			return;

		$this->setTemplate();

		if (! $this->context['user']['is_logged']) {
			show_user_info_for_guests();
			return;
		}

		$userData = $this->cache('user_info_addon_u' . $this->context['user']['id'])
			->setLifeTime($data->cache_time)
			->setFallback(self::class, 'getData');

		show_user_info($userData);
	}
}
