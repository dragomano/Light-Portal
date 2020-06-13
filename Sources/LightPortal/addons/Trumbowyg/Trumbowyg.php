<?php

namespace Bugo\LightPortal\Addons\Trumbowyg;

/**
 * Trumbowyg
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

class Trumbowyg
{
	/**
	 * Specifying the addon type (if 'block', you do not need to specify it)
	 *
	 * Указываем тип аддона (если 'block', то можно не указывать)
	 *
	 * @var string
	 */
	public static $addon_type = 'editor';

	/**
	 * The IDs list of dark themes
	 *
	 * Список идентификаторов тёмных тем оформления
	 *
	 * @var string
	 */
	private static $dark_themes = '';

	/**
	 * Automatically extend of an editor area (0|1|2)
	 *
	 * Автоматическое расширение окна редактора (0|1|2)
	 *
	 * @var bool
	 */
	private static $auto_grow = 0;

	/**
	 * Добавляем настройки
	 *
	 * @param array $settings
	 * @return void
	 */
	public static function addSettings(&$settings)
	{
		global $modSettings, $context, $txt;

		if (!isset($modSettings['lp_trumbowyg_addon_dark_themes']))
			$modSettings['lp_trumbowyg_addon_dark_themes'] = static::$dark_themes;
		if (!isset($modSettings['lp_trumbowyg_addon_auto_grow']))
			$modSettings['lp_trumbowyg_addon_auto_grow'] = static::$auto_grow;

		$context['lp_trumbowyg_addon_dark_themes_options'] = self::getForumThemes();

		$settings[] = array('multicheck', 'lp_trumbowyg_addon_dark_themes');
		$settings[] = array('select', 'lp_trumbowyg_addon_auto_grow', $txt['lp_trumbowyg_addon_auto_grow_set']);
	}

	/**
	 * Собираем названия существующих тем оформления
	 *
	 * @return void
	 */
	private static function getForumThemes()
	{
		global $smcFunc, $context;

		$result = $smcFunc['db_query']('', '
			SELECT id_theme, variable, value
			FROM {db_prefix}themes
			WHERE variable = {string:name}',
			array(
				'name' => 'name'
			)
		);

		$current_themes = [];
		while ($row = $smcFunc['db_fetch_assoc']($result))
			$current_themes[$row['id_theme']] = $row['value'];

		$smcFunc['db_free_result']($result);

		return $current_themes;
	}

	/**
	 * Adding your own editor for 'html' content
	 *
	 * Добавляем свой редактор для контента 'html'
	 *
	 * @param array $object
	 * @return void
	 */
	public static function prepareEditor($object)
	{
		global $modSettings, $txt, $editortxt, $settings;

		if ($object['type'] == 'html' || (!empty($object['options']['content']) && $object['options']['content'] === 'html')) {
			$dark_themes = !empty($modSettings['lp_trumbowyg_addon_dark_themes']) ? json_decode($modSettings['lp_trumbowyg_addon_dark_themes'], true) : [];

			loadLanguage('Editor');

			loadCssFile('https://cdn.jsdelivr.net/npm/trumbowyg@2/dist/ui/trumbowyg.min.css', array('external' => true));
			loadJavaScriptFile('https://cdn.jsdelivr.net/npm/trumbowyg@2/dist/trumbowyg.min.js', array('external' => true));

			if ($txt['lang_dictionary'] !== 'en')
				loadJavaScriptFile('https://cdn.jsdelivr.net/npm/trumbowyg@2/dist/langs/' . $txt['lang_dictionary'] . '.min.js', array('external' => true));

			loadJavaScriptFile('https://cdn.jsdelivr.net/npm/trumbowyg@2/plugins/history/trumbowyg.history.min.js', array('external' => true));
			loadJavaScriptFile('https://cdn.jsdelivr.net/npm/trumbowyg@2/plugins/pasteimage/trumbowyg.pasteimage.min.js', array('external' => true));
			loadJavaScriptFile('https://cdn.jsdelivr.net/npm/trumbowyg@2/plugins/preformatted/trumbowyg.preformatted.min.js', array('external' => true));
			loadCssFile('https://cdn.jsdelivr.net/npm/trumbowyg@2/dist/plugins/table/ui/trumbowyg.table.min.css', array('external' => true));
			loadJavaScriptFile('https://cdn.jsdelivr.net/npm/trumbowyg@2/plugins/table/trumbowyg.table.min.js', array('external' => true));
			addInlineJavaScript('
		$("#content").trumbowyg({
			lang: "' . $txt['lang_dictionary'] . '",
			btnsDef: {
				historyUndo: {
					title: "' . $editortxt['undo'] . '"
				},
				historyRedo: {
					title: "' . $editortxt['redo'] . '"
				}
			},
			btns: [
				["historyUndo", "historyRedo"],
				["strong", "em", "del"],
				["p", "h4"],
				["superscript", "subscript"],
				["justifyLeft", "justifyCenter", "justifyRight", "justifyFull"],
				["insertImage", "link"],
				["table", "preformatted", "blockquote"],
				["unorderedList", "orderedList"],
				["horizontalRule"],
				["viewHTML", "removeformat"],
				["fullscreen"]
			],
			semantic: {
				"div": "div"
			},
			urlProtocol: true,
			resetCss: true,
			urlProtocol: true,
			removeformatPasted: true,
			imageWidthModalEdit: true' . (!empty($modSettings['lp_trumbowyg_addon_auto_grow']) && $modSettings['lp_trumbowyg_addon_auto_grow'] == 1 ? ',
			autogrow: true' : '') . (!empty($modSettings['lp_trumbowyg_addon_auto_grow']) && $modSettings['lp_trumbowyg_addon_auto_grow'] == 2 ? ',
			autogrowOnEnter: true' : '') . '
		});' . (!empty($dark_themes) && !empty($dark_themes[$settings['theme_id']]) ? '
		$(".pf_content").addClass("trumbowyg-dark");' : ''), true);
		}
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
			'title' => 'Trumbowyg',
			'link' => 'https://github.com/Alex-D/Trumbowyg',
			'author' => '2012-2016 Alexandre Demode (Alex-D)',
			'license' => array(
				'name' => 'the MIT License (MIT)',
				'link' => 'https://github.com/Alex-D/Trumbowyg/blob/develop/LICENSE'
			)
		);
	}
}
