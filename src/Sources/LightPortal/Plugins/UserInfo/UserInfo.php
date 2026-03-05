<?php declare(strict_types=1);

/**
 * @package UserInfo (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2026 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category plugin
 * @version 11.02.26
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

	private const ZODIACS = [
		'capricorn', 'aquarius', 'pisces', 'aries', 'taurus', 'gemini',
		'cancer', 'leo', 'virgo', 'libra', 'scorpio', 'sagittarius',
	];

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

		echo $this->view(params: [
			'user'   => $userData,
			'zodiac' => $this->getZodiac($userData['birth_date']),
		]);
	}

	private function getZodiac(string $birth_date = ''): string
	{
		if (empty($birth_date) || $birth_date === '1004-01-01') {
			return '';
		}

		$month = (int) date('n', strtotime($birth_date));
		$day   = (int) date('j', strtotime($birth_date));

		$cutoffs = [19, 18, 20, 19, 20, 20, 22, 22, 22, 22, 21, 21];

		$index = $month - 1;
		if ($day > $cutoffs[$index]) {
			$index = ($index + 1) % 12;
		}

		return self::ZODIACS[$index];
	}
}
