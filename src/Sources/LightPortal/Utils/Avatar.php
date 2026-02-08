<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2026 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 3.0
 */

namespace LightPortal\Utils;

use Bugo\Compat\Config;
use Bugo\Compat\User;

if (! defined('SMF'))
	die('No direct access...');

class Avatar
{
	public static function get(int $userId, array $userData = []): string
	{
		if ($userId === 0)
			return '';

		if ($userData === []) {
			$userData = self::getPreparedData([$userId]);
		}

		if (empty(User::$loaded[$userId]) && in_array($userId, $userData)) {
			User::load($userId);
		}

		if (empty(User::$loaded[$userId]))
			return '';

		$data = User::$loaded[$userId]->format(true);

		return $data['avatar']['image']
			?? Str::html('img', [
				'class'   => 'avatar',
				'width'   => 100,
				'height'  => 100,
				'src'     => Config::$modSettings['avatar_url'] . '/default.png',
				'loading' => 'lazy',
				'alt'     => $data['name'] ?? '',
			])->toHtml();
	}

	public static function getWithItems(array $items, string $entity = 'author'): array
	{
		$userData = self::getPreparedData(array_map(static fn($item) => $item[$entity]['id'], $items));

		return array_map(function ($item) use ($userData, $entity) {
			$item[$entity]['avatar'] = self::get((int) $item[$entity]['id'], $userData);
			return $item;
		}, $items);
	}

	protected static function getPreparedData(array $data): array
	{
		return array_map(fn($user) => $user->id, User::load($data));
	}
}
