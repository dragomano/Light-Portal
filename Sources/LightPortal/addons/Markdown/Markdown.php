<?php

namespace Bugo\LightPortal\Addons\Markdown;

/**
 * Markdown
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2020 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 1.4
 */

if (!defined('SMF'))
	die('Hacking attempt...');

class Markdown
{
	/**
	 * Specifying the addon type (if 'block', you do not need to specify it)
	 *
	 * Указываем тип аддона (если 'block', то можно не указывать)
	 *
	 * @var string
	 */
	public $addon_type = 'parser';

	/**
	 * Parse 'md' content
	 *
	 * Парсим контент типа 'md'
	 *
	 * @param string $content
	 * @param string $type
	 * @return void
	 */
	public function parseContent(&$content, $type)
	{
		if ($type == 'md')
			$content = $this->getParsedMarkdown($content);
	}

	/**
	 * Parse Markdown content
	 *
	 * Парсим Markdown-контент
	 *
	 * @param string $text
	 * @return string
	 */
	private function getParsedMarkdown($text)
	{
		require_once(__DIR__ . '/Michelf/MarkdownInterface.php');
		require_once(__DIR__ . '/Michelf/Markdown.php');
		require_once(__DIR__ . '/Michelf/MarkdownExtra.php');
		require_once(__DIR__ . '/Michelf/MarkdownSMF.php');

		return Michelf\MarkdownSMF::defaultTransform(un_htmlspecialchars($text));
	}

	/**
	 * @param array $links
	 * @return void
	 */
	public function credits(&$links)
	{
		$links[] = array(
			'title' => 'PHP Markdown',
			'link' => 'https://github.com/michelf/php-markdown',
			'author' => 'Michel Fortin',
			'license' => array(
				'name' => 'the BSD-style License',
				'link' => 'https://github.com/michelf/php-markdown/blob/lib/License.md'
			)
		);
	}
}
