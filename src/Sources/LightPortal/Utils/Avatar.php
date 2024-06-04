<?php declare(strict_types=1);

/**
 * Avatar.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.6
 */

namespace Bugo\LightPortal\Utils;

use Bugo\Compat\Config;
use Bugo\Compat\ErrorHandler;
use Bugo\Compat\User;
use Exception;
use Nette\Utils\Html;

use function array_map;
use function in_array;

if (! defined('SMF'))
	die('No direct access...');

class Avatar
{
	public static function get(int $userId, array $userData = []): string
	{
		if ($userId === 0)
			return '';

		if ($userData === [])
			$userData = User::loadMemberData([$userId]);

		if (! isset(User::$memberContext[$userId]) && in_array($userId, $userData)) {
			try {
				User::loadMemberContext($userId, true);
			} catch (Exception $e) {
				ErrorHandler::log('[LP] getUserAvatar helper: ' . $e->getMessage());
			}
		}

		if (empty(User::$memberContext[$userId]))
			return '';

		return User::$memberContext[$userId]['avatar']['image']
			?? Html::el('img', [
				'class'   => 'avatar',
				'width'   => 100,
				'height'  => 100,
				'src'     => Config::$modSettings['avatar_url'] . '/default.png',
				'loading' => 'lazy',
				'alt'     => User::$memberContext[$userId]['name'],
			])->toHtml();
	}

	public static function getWithItems(array $items, string $entity = 'author'): array
	{
		$userData = User::loadMemberData(array_map(static fn($item) => $item[$entity]['id'], $items));

		return array_map(function ($item) use ($userData, $entity) {
			$item[$entity]['avatar'] = self::get((int) $item[$entity]['id'], $userData);
			return $item;
		}, $items);
	}
}
