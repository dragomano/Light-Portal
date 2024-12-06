<?php declare(strict_types = 1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.8
 */

namespace Bugo\LightPortal\Utils;

use Bugo\Compat\Config;
use Bugo\Compat\User;
use Bugo\Compat\Utils;
use Nette\Utils\Html;

use function html_entity_decode;
use function preg_match;
use function preg_replace;
use function str_contains;
use function str_replace;
use function strip_tags;
use function strtolower;
use function ucwords;

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

	public static function getTranslatedTitle(array $titles): string
	{
		return $titles[User::$info['language']] ?? $titles[Config::$language] ?? '';
	}

	public static function getImageFromText(string $text): string
	{
		preg_match('/<img(.*)src(.*)=(.*)"(?<src>.*)"/U', $text, $value);

		$result = $value['src'] ??= '';

		if (empty($result) || str_contains($result, (string) Config::$modSettings['smileys_url']))
			return '';

		return $result;
	}

	public static function html(?string $name = null, array|string|null $params = null): Html
	{
		return Html::el($name, $params);
	}
}
