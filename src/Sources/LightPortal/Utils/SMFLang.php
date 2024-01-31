<?php declare(strict_types=1);

/**
 * SMFLang.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.5
 */

namespace Bugo\LightPortal\Utils;

use function censorText;
use function getLanguages;
use function loadLanguage;
use function sentence_list;

if (! defined('SMF'))
	die('No direct access...');

class SMFLang
{
	public static array $txt;

	public static array $editortxt;

	public function __construct()
	{
		if (! isset($GLOBALS['txt']))
			$GLOBALS['txt'] = [];

		self::$txt = &$GLOBALS['txt'];

		if (! isset($GLOBALS['editortxt']))
			$GLOBALS['editortxt'] = [];

		self::$editortxt = &$GLOBALS['editortxt'];
	}

	public static function censorText(string &$text): void
	{
		censorText($text);
	}

	public static function get(): array
	{
		return getLanguages();
	}

	public static function load(string $language, string $lang = ''): void
	{
		loadLanguage($language, $lang);
	}

	public static function sentenceList(array $list): string
	{
		return sentence_list($list);
	}
}
