<?php declare(strict_types=1);

/**
 * BBCodeParser.php
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

use function parse_bbc;

if (! defined('SMF'))
	die('No direct access...');

final class BBCodeParser
{
	private static object $parser;

	public static function load(): object
	{
		if (! isset(self::$parser)) {
			self::$parser = new self();
		}

		return self::$parser;
	}

	public function parse(string|bool $message, bool $smileys = true, string|int $cache_id = '', array $parse_tags = []): array|string
	{
		return parse_bbc($message, $smileys, $cache_id, $parse_tags);
	}
}
