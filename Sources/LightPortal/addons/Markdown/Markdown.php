<?php

/**
 * Markdown
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2020-2021 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 1.9
 */

namespace Bugo\LightPortal\Addons\Markdown;

use Bugo\LightPortal\Addons\Plugin;

class Markdown extends Plugin
{
	/**
	 * @var string
	 */
	public $icon = 'fab fa-markdown';

	/**
	 * @var array
	 */
	public $type = array('block', 'parser');

	/**
	 * Adding the new content type
	 *
	 * Добавляем новый тип контента
	 *
	 * @return void
	 */
	public function init()
	{
		global $context;

		$context['lp_page_types']['markdown'] = 'Markdown';
	}

	/**
	 * @param array $options
	 * @return void
	 */
	public function blockOptions(array &$options)
	{
		$options['markdown'] = array(
			'content' => true
		);
	}

	/**
	 * Parse 'markdown' content
	 *
	 * Парсим контент типа 'markdown'
	 *
	 * @param string $content
	 * @param string $type
	 * @return void
	 */
	public function parseContent(string &$content, string $type)
	{
		if ($type == 'markdown')
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
	private function getParsedContent(string $text): string
	{
		require_once __DIR__ . '/Michelf/MarkdownInterface.php';
		require_once __DIR__ . '/Michelf/Markdown.php';
		require_once __DIR__ . '/Michelf/MarkdownExtra.php';
		require_once __DIR__ . '/Michelf/MarkdownSMF.php';

		return Michelf\MarkdownSMF::defaultTransform(un_htmlspecialchars($text));
	}

	/**
	 * @param array $links
	 * @return void
	 */
	public function credits(array &$links)
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
