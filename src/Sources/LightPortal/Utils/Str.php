<?php declare(strict_types = 1);

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

use Bugo\Compat\Config;
use Bugo\Compat\Utils;
use Nette\Utils\Html;
use WPLake\Typed\Typed;

if (! defined('SMF'))
	die('No direct access...');

class Str
{
	public static function cleanBbcode(array|string &$data): void
	{
		$data = preg_replace('~\[[^]]+]~', '', $data);
	}

	public static function getSnakeName(string $name): string
	{
		return strtolower(preg_replace('/(?<=[a-z])(?=[A-Z])|(?<=[A-Z])(?=[A-Z][a-z])/', '_', $name));
	}

	public static function getCamelName(string $name): string
	{
		return str_replace(' ', '', ucwords(str_replace('_', ' ', $name)));
	}

	public static function getTeaser(string $text, int $length = 150): string
	{
		$text = html_entity_decode($text);
		$text = preg_replace('#(<cite.*?>).*?(</cite>)#', '$1$2', $text);

		return Utils::shorten(strip_tags((string) $text), $length) ?: '...';
	}

	public static function getImageFromText(string $text): string
	{
		preg_match('/<img[^>]+src="([^"]+)"/i', $text, $m);
		$src = $m[1] ?? '';

		return (! $src || str_contains($src, (string) Config::$modSettings['smileys_url']))
			? (Config::$modSettings['lp_image_placeholder'] ?? '')
			: $src;
	}

	public static function decodeHtmlEntities(string $string): string
	{
		return html_entity_decode($string, ENT_QUOTES | ENT_HTML5, 'UTF-8');
	}

	public static function html(?string $name = null, array|string|null $params = null): Html
	{
		return Html::el($name, $params);
	}

	public static function typed(string $type, ...$args): mixed
	{
		return Typed::$type(...$args);
	}
}
