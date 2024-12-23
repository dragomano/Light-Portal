<?php declare(strict_types=1);

/**
 * @package UserInfo (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category plugin
 * @version 22.12.24
 */

namespace Bugo\LightPortal\Plugins\UserInfo;

use Bugo\Compat\User;
use Bugo\Compat\Utils;
use Bugo\LightPortal\Plugins\Block;
use Bugo\LightPortal\Plugins\Event;
use Exception;

use function show_user_info;
use function show_user_info_for_guests;

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
		User::loadMemberData([User::$info['id']]);

		return User::loadMemberContext(User::$info['id']);
	}

	public function prepareContent(Event $e): void
	{
		$this->setTemplate();

		if (! Utils::$context['user']['is_logged']) {
			show_user_info_for_guests();
			return;
		}

		$userData = $this->cache($this->name . '_addon_u' . Utils::$context['user']['id'])
			->setLifeTime($e->args->cacheTime)
			->setFallback(fn() => $this->getData());

		show_user_info($userData);
	}
}
