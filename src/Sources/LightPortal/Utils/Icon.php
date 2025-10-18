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

namespace LightPortal\Utils;

use Bugo\FontAwesome\IconBuilder;
use LightPortal\Enums\PortalHook;
use LightPortal\Events\EventManagerFactory;
use LightPortal\Lists\IconList;
use InvalidArgumentException;

use function LightPortal\app;

if (! defined('SMF'))
	die('No direct access...');

class Icon
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

		try {
			$template = IconBuilder::make($icon)->ariaHidden()->html() . ' ';
		} catch (InvalidArgumentException) {
			$template = Str::html('i', ['aria-hidden' => 'true'])->class($icon)->toHtml();
		}

		app(EventManagerFactory::class)()->dispatch(
			PortalHook::prepareIconTemplate,
			[
				'template' => &$template,
				'icon'     => $icon,
			]
		);

		return $template;
	}

	public static function all(): array
	{
		return app(IconList::class)();
	}
}
