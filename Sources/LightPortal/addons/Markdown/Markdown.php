<?php

namespace Bugo\LightPortal\Addons\Markdown;

/**
 * Markdown
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2020 Bugo
 * @license https://opensource.org/licenses/BSD-3-Clause BSD
 *
 * @version 1.0
 */

if (!defined('SMF'))
	die('Hacking attempt...');

class Markdown
{
	/**
	 * Adding some text variables
	 *
	 * Добавляем некоторые текстовые переменные
	 *
	 * @return void
	 */
	public static function init()
	{
		global $txt;

		$txt['lp_markdown_type'] = 'parser';
	}

	/**
	 * Parse 'md' content
	 *
	 * Парсим контент типа 'md'
	 *
	 * @param string $content
	 * @param string $type
	 * @return void
	 */
	public static function parseContent(&$content, $type)
	{
		if ($type == 'md')
			$content = self::getParsedMarkdown($content);
	}

	/**
	 * Parse Markdown content
	 *
	 * Парсим Markdown-контент
	 *
	 * @param string $text
	 * @return void
	 */
	private static function getParsedMarkdown($text)
	{
		require_once(__DIR__ . '/Michelf/MarkdownExtra.inc.php');
		require_once(__DIR__ . '/Michelf/MarkdownSMF.php');

		return Michelf\MarkdownSMF::defaultTransform(un_htmlspecialchars($text));
	}

	/**
	 * Adding the addon copyright
	 *
	 * Добавляем копирайты плагина
	 *
	 * @param array $links
	 * @return void
	 */
	public static function credits(&$links)
	{
		$links[] = array(
			'title' => 'PHP Markdown',
			'link' => 'https://github.com/michelf/php-markdown',
			'author' => '2004-2019 Michel Fortin',
			'license' => array(
				'name' => 'the BSD-style License',
				'link' => 'https://github.com/michelf/php-markdown/blob/lib/License.md'
			)
		);
	}
}
