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
 * @version 0.3
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
		require_once(LP_ADDONS . '/Parsedown/Parsedown/Parsedown.php');

		$Parsedown = new \Parsedown();
		//$Parsedown->setSafeMode(true);

		return $Parsedown->line($text);
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
	}
}
