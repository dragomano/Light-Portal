<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 3.0
 */

namespace LightPortal\Enums;

use LightPortal\Enums\Traits\HasValues;

enum Action: string
{
	use HasValues;

	case ALL = 'all';
	case BOARDS = 'boards';
	case FORUM = 'forum';
	case HOME = 'home';
	case PAGES = 'pages';
	case PORTAL = 'portal';
	case TOPICS = 'topics';

	public static function select(): array
	{
		return array_combine(self::values(), self::values());
	}
}
