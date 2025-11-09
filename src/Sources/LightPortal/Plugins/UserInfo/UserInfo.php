<?php declare(strict_types=1);

/**
 * @package UserInfo (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category plugin
 * @version 05.11.25
 */

namespace LightPortal\Plugins\UserInfo;

use Bugo\Compat\User;
use LightPortal\Plugins\Block;
use LightPortal\Plugins\Event;
use LightPortal\Plugins\PluginAttribute;
use LightPortal\Utils\Traits\HasView;

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

	public function prepareContent(Event $e): void
	{
		if (User::$me->is_guest) {
			echo $this->view('guest');

			return;
		}

		$userData = $this->userCache($this->name . '_addon')
			->setLifeTime($e->args->cacheTime)
			->setFallback($this->getData(...));

		echo $this->view(params: ['user' => $userData]);
	}
}
