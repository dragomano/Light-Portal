<?php

namespace Bugo\LightPortal\Addons\Markdown;

/**
 * Markdown
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2021 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 1.6
 */

if (!defined('SMF'))
	die('Hacking attempt...');

class Markdown
{
	/**
	 * @var string
	 */
	public $addon_type = array('block', 'parser');

	/**
	 * Adding the new content type and block icon
	 *
	 * Добавляем новый тип контента и иконку блока
	 *
	 * @return void
	 */
	public function init()
	{
		global $txt, $context;

		$txt['lp_page_types']['md'] = 'Markdown';

		$context['lp_md_icon'] = 'fab fa-markdown';
	}

	/**
	 * @param array $options
	 * @return void
	 */
	public function blockOptions(&$options)
	{
		$options['md'] = array(
			'content' => true
		);
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
	public function parseContent(&$content, $type)
	{
		if ($type == 'md')
			$content = $this->getParsedContent($content);
	}

	/**
	 * Get Markdown content
	 *
	 * Получаем Markdown-контент
	 *
	 * @param string $text
	 * @return string
	 */
	private function getParsedContent($text)
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
