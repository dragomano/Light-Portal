<?php declare(strict_types=1);

/**
 * User.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.4
 */

namespace Bugo\LightPortal\Utils;

final class User
{
	public static array $info;

	public static array $profiles;

	public static array $settings;

	public static array $memberContext;

	private array $vars = [
		'info'          => 'user_info',
		'profiles'      => 'user_profile',
		'settings'      => 'user_settings',
		'memberContext' => 'memberContext',
	];

	public function __construct()
	{
		foreach ($this->vars as $key => $value) {
			if (! isset($GLOBALS[$value])) {
				$GLOBALS[$value] = [];
			}

			self::${$key} = &$GLOBALS[$value];
		}
	}
}
