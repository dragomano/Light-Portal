<?php declare(strict_types=1);

/**
 * ContentType.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.6
 */

namespace Bugo\LightPortal\Enums;

use Bugo\Compat\{Lang, User};
use Bugo\LightPortal\Enums\Traits\HasNames;

enum ContentType
{
	use HasNames;

	case BBC;
	case HTML;
	case PHP;

	public static function all(): array
	{
		$types = [
			self::BBC->name()  => Lang::$txt['lp_bbc']['title'],
			self::HTML->name() => Lang::$txt['lp_html']['title'],
			self::PHP->name()  => Lang::$txt['lp_php']['title'],
		];

		return User::$info['is_admin'] ? $types : array_slice($types, 0, 2);
	}
}
