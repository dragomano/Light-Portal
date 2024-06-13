<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.7
 */

namespace Bugo\LightPortal\Enums;

use function filter_var;

enum VarType
{
	case STRING;
	case INTEGER;
	case FLOAT;
	case BOOLEAN;
	case URL;
	case ARRAY;

	public function filter(mixed $var, array|int $options = 0): mixed
	{
		if ($this === self::ARRAY) {
			return filter_var($var, FILTER_VALIDATE_REGEXP, $options);
		}

		$filter = match ($this) {
			self::STRING  => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
			self::INTEGER => FILTER_VALIDATE_INT,
			self::FLOAT   => FILTER_VALIDATE_FLOAT,
			self::BOOLEAN => FILTER_VALIDATE_BOOLEAN,
			self::URL     => FILTER_VALIDATE_URL,
			default       => FILTER_DEFAULT,
		};

		return filter_var($var, $filter);
	}
}
