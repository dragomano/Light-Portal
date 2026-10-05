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

namespace LightPortal\Enums;

if (! defined('LP_NAME'))
	die('No direct access...');

enum License: string
{
	case Apache2           = 'Apache 2.0';
	case Bsd2              = 'BSD 2-Clause "Simplified"';
	case Bsd3              = 'BSD-3-Clause';
	case Cc0               = 'CC0 1.0';
	case CcBy4             = 'CC BY 4.0';
	case CcBySa4           = 'CC BY-SA 4.0';
	case FontAwesomeFree   = 'Font Awesome Free';
	case GoodBoy           = 'Good Boy';
	case Gpl3              = 'GPL-3.0';
	case Agpl3             = 'GNU AGPLv3';
	case Isc               = 'ISC';
	case Mit               = 'MIT';
	case NewBsd            = 'New BSD';
	case PictogrammersFree = 'Pictogrammers Free';

	public function url(): string
	{
		return match ($this) {
			self::Apache2            => 'https://www.apache.org/licenses/LICENSE-2.0',
			self::Bsd2               => 'https://opensource.org/licenses/BSD-2-Clause',
			self::Bsd3, self::NewBsd => 'https://opensource.org/licenses/BSD-3-Clause',
			self::Cc0                => 'https://creativecommons.org/publicdomain/zero/1.0/',
			self::CcBy4              => 'https://creativecommons.org/licenses/by/4.0/',
			self::CcBySa4            => 'https://creativecommons.org/licenses/by-sa/4.0/',
			self::FontAwesomeFree    => 'https://fontawesome.com/license/free',
			self::GoodBoy            => 'https://icons8.com/good-boy-license',
			self::Gpl3               => 'https://www.gnu.org/licenses/gpl-3.0.html',
			self::Agpl3              => 'https://www.gnu.org/licenses/agpl-3.0.html',
			self::Isc                => 'https://opensource.org/licenses/ISC',
			self::Mit                => 'https://opensource.org/licenses/MIT',
			self::PictogrammersFree  => 'https://pictogrammers.com/docs/general/license/',
		};
	}

	public static function tryFromName(string $name): ?self
	{
		foreach (self::cases() as $case) {
			if ($case->name === $name) {
				return $case;
			}
		}

		return null;
	}
}
