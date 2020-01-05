<?php

namespace Bugo\LightPortal\Addons;

/**
 * SimpleMDE
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2020 Bugo
 * @license https://opensource.org/licenses/BSD-3-Clause BSD
 *
 * @version 0.1
 */

if (!defined('SMF'))
	die('Hacking attempt...');

class SimpleMDE
{
	/**
	 * Добавляем новый тип контента
	 *
	 * @return void
	 */
	public static function page()
	{
		global $txt;

		$txt['lp_page_types']['md'] = 'Markdown';
	}

	/**
	 * Подключаем редактор для контента 'md'
	 *
	 * @param array $object
	 * @return void
	 */
	public static function prepareEditor($object)
	{
		global $txt;

		if ($object['type'] == 'md') {
			loadCssFile('https://cdn.jsdelivr.net/simplemde/latest/simplemde.min.css', array('external' => true));
			loadJavaScriptFile('https://cdn.jsdelivr.net/simplemde/latest/simplemde.min.js', array('external' => true));
			addInlineJavaScript('
		let simplemde = new SimpleMDE({
			element: document.getElementById("content"),
			spellChecker: false,
			placeholder: "' . $txt['lp_post_error_no_content'] . '",
			forceSync: true
		});', true);
		}
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
			'title' => 'SimpleMDE',
			'link' => 'https://github.com/sparksuite/simplemde-markdown-editor',
			'author' => '2015 Next Step Webs, Inc.',
			'license' => array(
				'name' => 'the MIT License (MIT)',
				'link' => 'https://github.com/sparksuite/simplemde-markdown-editor/blob/master/LICENSE'
			)
		);
	}
}
