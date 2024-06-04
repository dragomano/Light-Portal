<?php declare(strict_types=1);

/**
 * Icon.php
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

use Bugo\FontAwesome\IconBuilder;
use Bugo\LightPortal\AddonHandler;

use function str_replace;

if (! defined('SMF'))
	die('No direct access...');

final class Icon
{
	public static function get(string $name, string $title = '', string $prefix = ''): string
	{
		$icon = (string) self::all()[$name];

		if ($title === '') {
			return $icon;
		}

		return str_replace(' class="', ' title="' . $title . '" class="' . $prefix, $icon);
	}

	public static function parse(?string $icon = ''): string
	{
		if (empty($icon))
			return '';

		$template = (new IconBuilder($icon, ['aria-hidden' => true]))->html() . ' ';

		AddonHandler::getInstance()->run('prepareIconTemplate', [&$template, $icon]);

		return $template;
	}

	public static function all(): array
	{
		return (new EntityManager())('icon');
	}
}
