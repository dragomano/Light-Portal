<?php declare(strict_types=1);

/**
 * @package UserInfo (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category plugin
 * @version 30.09.25
 */

namespace Bugo\LightPortal\Plugins\UserInfo;

use Bugo\Compat\User;
use Bugo\LightPortal\Enums\PortalHook;
use Bugo\LightPortal\Plugins\Block;
use Bugo\LightPortal\Plugins\Event;
use Bugo\LightPortal\Plugins\HookAttribute;
use Bugo\LightPortal\Plugins\PluginAttribute;
use Bugo\LightPortal\Utils\Traits\HasView;

if (! defined('LP_NAME'))
	die('No direct access...');

#[PluginAttribute(icon: 'fas fa-user')]
class UserInfo extends Block
{
	use HasView;

	public function getData(): array
	{
		User::load(User::$me->id);

		return User::$loaded[User::$me->id]->format();
	}

	#[HookAttribute(PortalHook::prepareContent)]
	public function prepareContent(Event $e): void
	{
		if (User::$me->is_guest) {
			echo $this->view('guest');

			return;
		}

		$userData = $this->userCache($this->name . '_addon')
			->setLifeTime($e->args->cacheTime)
			->setFallback(fn() => $this->getData());

		echo $this->view(params: [
			'user' => $userData,
		]);
	}
}
