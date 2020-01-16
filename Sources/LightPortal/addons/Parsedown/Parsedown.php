<?php

namespace Bugo\LightPortal\Addons\Parsedown;

/**
 * Parsedown
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2020 Bugo
 * @license https://opensource.org/licenses/BSD-3-Clause BSD
 *
 * @version 0.5
 */

if (!defined('SMF'))
	die('Hacking attempt...');

class Parsedown
{
	/**
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
	 * Парсим Markdown-контент
	 *
	 * @param string $text
	 * @return void
	 */
	private static function getParsedMarkdown($text)
	{
		require_once(__DIR__ . '/Parsedown/Parsedown.php');
		require_once(__DIR__ . '/Parsedown/ParsedownExtra.php');
		require_once(__DIR__ . '/Parsedown/ParsedownExtended.php');
		require_once(__DIR__ . '/Parsedown/ParsedownSMF.php');

		$parsedown = new \ParsedownSMF([
			'toc' => [
				'enable' => true,
				'inline' => true,
			],
			'mark' => true,
			'insert' => true,
			'smartTypography' => true,
			'scripts' => true,
			'kbd' => true,
			'task' => true,
			'math' => true,
			'diagrams' => true
		]);
		//$parsedown->setSafeMode(true);
		//$parsedown->setBreaksEnabled(true);
		//$parsedown->setUrlsLinked(false);

		loadJavaScriptFile('https://cdn.jsdelivr.net/npm/mathjax@3/es5/tex-mml-chtml.js', array('external' => true));

		// https://support.typora.io/Draw-Diagrams-With-Markdown/

		if (strpos($text, '```mermaid') !== false) {
			loadJavaScriptFile('https://unpkg.com/mermaid@8.4.4/dist/mermaid.min.js', array('external' => true));
			addInlineJavaScript('
	mermaid.initialize({startOnLoad:true});', true);
		}

		if (strpos($text, '```chart') !== false) {
			loadCSSFile('https://cdn.jsdelivr.net/npm/chart.js@2.9.3/dist/Chart.min.css', array('external' => true));
			loadJavaScriptFile('https://cdn.jsdelivr.net/npm/chart.js@2.9.3/dist/Chart.min.js', array('external' => true));
		}

		return $parsedown->text(un_htmlspecialchars($text));
	}

	/**
	 * Добавляем копирайты плагина
	 *
	 * @param array $links
	 * @return void
	 */
	public static function credits(&$links)
	{
		$links[] = array(
			'title' => 'Parsedown',
			'link' => 'https://parsedown.org/',
			'author' => '2013-2018 Emanuil Rusev, erusev.com',
			'license' => array(
				'name' => 'the MIT License (MIT)',
				'link' => 'https://github.com/erusev/parsedown/blob/master/LICENSE.txt'
			)
		);
		$links[] = array(
			'title' => 'Parsedown Extra',
			'link' => 'https://github.com/erusev/parsedown-extra',
			'author' => '2013-2018 Emanuil Rusev, erusev.com',
			'license' => array(
				'name' => 'the MIT License (MIT)',
				'link' => 'https://github.com/erusev/parsedown-extra/blob/master/LICENSE.txt'
			)
		);
		$links[] = array(
			'title' => 'Parsedown Extended',
			'link' => 'https://github.com/BenjaminHoegh/parsedownExtended',
			'author' => '2018 Benjamin Høegh',
			'license' => array(
				'name' => 'the MIT License (MIT)',
				'link' => 'https://github.com/BenjaminHoegh/ParsedownExtended/blob/master/LICENSE.md'
			)
		);
	}
}
