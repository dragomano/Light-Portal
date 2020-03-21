<?php

namespace Bugo\LightPortal;

/**
 * Subs.php
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

class Subs
{
	/**
	 * Load used styles and scripts
	 *
	 * Подключаем используемые таблицы стилей и скрипты
	 *
	 * @return void
	 */
	public static function loadCssFiles()
	{
		loadCssFile('https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@5/css/all.min.css', array('external' => true));
		loadCssFile('light_portal/flexboxgrid.min.css');
		loadCssFile('light_portal/light_portal.css');
	}

	/**
	 *
	 * Prepare information about current blocks of the portal
	 *
	 * Собираем информацию о текущих блоках портала
	 *
	 * @return void
	 */
	public static function loadBlocks()
	{
		global $context;

		$context['lp_all_title_classes']   = self::getTitleClasses();
		$context['lp_all_content_classes'] = self::getContentClasses();

		$context['lp_active_blocks']    = Helpers::useCache('active_blocks', 'getActiveBlocks', __CLASS__);
		$context['lp_active_pages_num'] = Helpers::useCache('active_pages_num_u' . $context['user']['id'], 'getActivePageQuantity', __CLASS__);
	}

	/**
	 * Get information about all active blocks of the portal
	 *
	 * Получаем информацию обо всех активных блоках портала
	 *
	 * @return array
	 */
	public static function getActiveBLocks()
	{
		global $smcFunc;

		$request = $smcFunc['db_query']('', '
			SELECT
				b.block_id, b.icon, b.icon_type, b.type, b.content, b.placement, b.priority, b.permissions, b.areas, b.title_class, b.title_style, b.content_class, b.content_style,
				bt.lang, bt.title, bp.name, bp.value
			FROM {db_prefix}lp_blocks AS b
				LEFT JOIN {db_prefix}lp_titles AS bt ON (bt.item_id = b.block_id AND bt.type = {string:type})
				LEFT JOIN {db_prefix}lp_params AS bp ON (bp.item_id = b.block_id AND bp.type = {string:type})
			WHERE b.status = {int:status}
			ORDER BY b.placement, b.priority',
			array(
				'type'   => 'block',
				'status' => Block::STATUS_ACTIVE
			)
		);

		$active_blocks = [];
		while ($row = $smcFunc['db_fetch_assoc']($request)) {
			censorText($row['content']);

			if (!isset($active_blocks[$row['block_id']]))
				$active_blocks[$row['block_id']] = array(
					'id'            => $row['block_id'],
					'icon'          => $row['icon'],
					'icon_type'     => $row['icon_type'],
					'type'          => $row['type'],
					'content'       => $row['content'],
					'placement'     => $row['placement'],
					'priority'      => $row['priority'],
					'permissions'   => $row['permissions'],
					'areas'         => explode(',', $row['areas']),
					'title_class'   => $row['title_class'],
					'title_style'   => $row['title_style'],
					'content_class' => $row['content_class'],
					'content_style' => $row['content_style'],
					'permissions'   => $row['permissions']
				);

			$active_blocks[$row['block_id']]['title'][$row['lang']] = $row['title'];

			if (!empty($row['name']))
				$active_blocks[$row['block_id']]['parameters'][$row['name']] = $row['value'];
		}

		$smcFunc['db_free_result']($request);

		return $active_blocks;
	}

	/**
	 * Get the total number of active pages
	 *
	 * Подсчитываем общее количество активных страниц
	 *
	 * @return int
	 */
	public static function getActivePageQuantity()
	{
		global $smcFunc, $user_info;

		$request = $smcFunc['db_query']('', '
			SELECT COUNT(page_id)
			FROM {db_prefix}lp_pages
			WHERE status = {int:status}' . (allowedTo('admin_forum') ? '' : '
				AND author_id = {int:user_id}'),
			array(
				'status'  => Page::STATUS_ACTIVE,
				'user_id' => $user_info['id']
			)
		);

		list ($num_pages) = $smcFunc['db_fetch_row']($request);
		$smcFunc['db_free_result']($request);

		return $num_pages;
	}

	/**
	 * Remove unnecessary areas for standalone mode
	 *
	 * Удаляем ненужные в автономном режиме области
	 *
	 * @param array $data
	 * @return void
	 */
	public static function unsetUnusedActions(array &$data)
	{
		global $modSettings, $context;

		$excluded_actions   = !empty($modSettings['lp_standalone_excluded_actions']) ? explode(',', $modSettings['lp_standalone_excluded_actions']) : [];
		$excluded_actions[] = 'portal';
		$excluded_actions   = array_flip($excluded_actions);

		foreach ($data as $action => $dump) {
			if (!array_key_exists($action, $excluded_actions))
				unset($data[$action]);
		}

		if (!array_key_exists('search', $excluded_actions))
			$context['allow_search'] = false;

		if (!array_key_exists('moderate', $excluded_actions))
			$context['allow_moderation_center'] = false;

		if (!array_key_exists('calendar', $excluded_actions))
			$context['allow_calendar'] = false;

		if (!array_key_exists('mlist', $excluded_actions))
			$context['allow_memberlist'] = false;
	}

	/**
	 * Prepare content to display
	 *
	 * Готовим контент к отображению в браузере
	 *
	 * @param string $content
	 * @param string $type
	 * @param int $block_id
	 * @param int $cache_time
	 * @return void
	 */
	public static function prepareContent(string &$content, string $type = 'bbc', int $block_id = 0, int $cache_time = 0)
	{
		global $context;

		if (!empty($block_id) && !empty($context['lp_active_blocks'][$block_id]))
			$parameters = $context['lp_active_blocks'][$block_id]['parameters'] ?? [];
		else
			$parameters = $context['lp_block']['options']['parameters'] ?? [];

		self::runAddons('prepareContent', array(&$content, $type, $block_id, $cache_time, $parameters));
	}

	/**
	 * Parse content depending on the type
	 *
	 * Парсим контент в зависимости от типа
	 *
	 * @param string $content
	 * @param string $type
	 * @return void
	 */
	public static function parseContent(string &$content, string $type = 'bbc')
	{
		global $context;

		switch ($type) {
			case 'bbc':
				$content = parse_bbc($content);
				break;
			case 'html':
				$content = un_htmlspecialchars($content);
				break;
			case 'php':
				$content = trim(un_htmlspecialchars($content));
				$content = trim($content, '<?php');
				$content = trim($content, '?>');
				ob_start();
				try {
					$content = html_entity_decode($content, ENT_COMPAT, $context['character_set'] ?? 'UTF-8');
					eval($content);
				} catch (\ParseError $p) {
					echo $p->getMessage();
				}
				$content = ob_get_clean();
				break;
			default:
				self::runAddons('parseContent', array(&$content, $type));
		}
	}

	/**
	 * Get nested directories
	 *
	 * Получаем вложенные директории
	 *
	 * @param string $path
	 * @return array
	 */
	private static function getNestedDirs(string $path)
	{
		$dirs = glob(rtrim($path, "/") . "/*", GLOB_ONLYDIR) or array();

		$nested_dirs = [];
		foreach ($dirs as $dir_path)
			$nested_dirs[] = $dir_path;

		return $nested_dirs;
	}

	/**
	 * Get names of the current addons
	 *
	 * Получаем имена имеющихся аддонов
	 *
	 * @return array
	 */
	public static function getAddons()
	{
		$addons = [];
		foreach (glob(LP_ADDONS . '/*.php') as $filename) {
			$filename = basename($filename);
			if ($filename !== 'index.php')
				$addons[] = str_replace('.php', '', $filename);
		}

		$dirs = self::getNestedDirs(LP_ADDONS);
		foreach ($dirs as $dir)
			$addons[] = basename($dir) . '\\' . basename($dir);

		return $addons;
	}

	/**
	 * Run addons
	 *
	 * Подключаем аддоны
	 *
	 * @param string $hook (https://github.com/dragomano/Light-Portal/wiki/Available-hooks)
	 * @param array $vars (extra variables)
	 * @return void
	 */
	public static function runAddons(string $hook = 'init', array $vars = [])
	{
		$light_portal_addons = Helpers::useCache('addons', 'getAddons', __CLASS__);

		if (empty($light_portal_addons))
			return;

		foreach ($light_portal_addons as $addon) {
			$class = __NAMESPACE__ . '\Addons\\' . $addon;

			self::loadAddonLanguage($addon);

			if (method_exists($class, $hook) && is_callable(array($class, $hook), false, $callable_name))
				call_user_func_array($callable_name, $vars);
		}
	}

	/**
	 * Require the language file of the addon
	 *
	 * Подключаем языковой файл аддона
	 *
	 * @param string $addon
	 * @return void
	 */
	public static function loadAddonLanguage(string $addon)
	{
		global $txt;

		$addon    = explode("\\", $addon)[0];
		$base_dir = LP_ADDONS . '/' . $addon . '/langs/';

		$languages = array(
			'english',
			Helpers::getUserLanguage()
		);

		foreach ($languages as $lang) {
			$lang_file = $base_dir . $lang . '.php';
			if (is_file($lang_file))
				require_once($lang_file);
		}
	}

	/**
	 * Load BBCode editor
	 *
	 * Подключаем редактор ББ-кода
	 *
	 * @param string $content
	 * @return void
	 */
	public static function createBbcEditor(string $content = '')
	{
		global $sourcedir, $context;

		$editorOptions = array(
			'id'           => 'content',
			'value'        => $content,
			'height'       => '300px',
			'width'        => '100%',
			'preview_type' => 2,
			'required'     => true
		);

		require_once($sourcedir . '/Subs-Editor.php');
		create_control_richedit($editorOptions);

		$context['post_box_name'] = $editorOptions['id'];

		addJavaScriptVar('oEditorID', $context['post_box_name'], true);
		addJavaScriptVar('oEditorObject', 'oEditorHandle_' . $context['post_box_name'], true);
	}

	/**
	 * Request a list of all localizations of the forum
	 *
	 * Запрашиваем список всех локализаций форума
	 *
	 * @return void
	 */
	public static function getForumLanguages()
	{
		global $modSettings, $context, $language;

		getLanguages();

		// Only one language by default!
		// Если на форуме отключен выбор языков, оставим только один (заданный по умолчанию)
		if (empty($modSettings['userLanguage'])) {
			$default_lang = $context['languages'][$language];
			$context['languages'] = [];
			$context['languages'][$language] = $default_lang;
		}
	}

	/**
	 * Get a list of all used classes for blocks with a header
	 *
	 * Получаем список всех используемых классов для блоков с заголовком
	 *
	 * @return array
	 */
	public static function getTitleClasses()
	{
		return [
			'div.cat_bar > h3.catbg'        => '<div class="cat_bar"><h3 class="catbg">%1$s</h3></div>',
			'div.title_bar > h3.titlebg'    => '<div class="title_bar"><h3 class="titlebg">%1$s</h3></div>',
			'div.title_bar > h4.titlebg'    => '<div class="title_bar"><h4 class="titlebg">%1$s</h4></div>',
			'div.sub_bar > h3.subbg'        => '<div class="sub_bar"><h3 class="subbg">%1$s</h3></div>',
			'div.sub_bar > h4.subbg'        => '<div class="sub_bar"><h4 class="subbg">%1$s</h4></div>',
			'div.errorbox > h3'             => '<div class="errorbox"><h3>%1$s</h3></div>',
			'div.noticebox > h3'            => '<div class="noticebox"><h3>%1$s</h3></div>',
			'div.infobox > h3'              => '<div class="infobox"><h3>%1$s</h3></div>',
			'div.descbox > h3'              => '<div class="descbox"><h3>%1$s</h3></div>',
			'div.generic_list_wrapper > h3' => '<div class="generic_list_wrapper"><h3>%1$s</h3></div>'
		];
	}

	/**
	 * Get a list of all used classes for blocks with content
	 *
	 * Получаем список всех используемых классов для блоков с контентом
	 *
	 * @return array
	 */
	public static function getContentClasses()
	{
		return [
			'div.roundframe'  => '<div class="roundframe noup"%2$s>%1$s</div>',
			'div.windowbg'    => '<div class="windowbg noup"%2$s>%1$s</div>',
			'div.information' => '<div class="information"%2$s>%1$s</div>',
			'div.errorbox'    => '<div class="errorbox"%2$s>%1$s</div>',
			'div.noticebox'   => '<div class="noticebox"%2$s>%1$s</div>',
			'div.infobox'     => '<div class="infobox"%2$s>%1$s</div>',
			'div.descbox'     => '<div class="descbox"%2$s>%1$s</div>',
			'_'               => '%1$s' // Empty class == w\o div
		];
	}

	/**
	 * Get array of titles for page/block object type
	 *
	 * Получаем массив всех заголовков для объекта типа page/block
	 *
	 * @param string $type
	 * @return array
	 */
	public static function getAllTitles(string $type = 'page')
	{
		global $smcFunc;

		$request = $smcFunc['db_query']('', '
			SELECT item_id, lang, title
			FROM {db_prefix}lp_titles
			WHERE type = {string:type}',
			array(
				'type' => $type
			)
		);

		$titles = [];
		while ($row = $smcFunc['db_fetch_assoc']($request)) {
			if (!empty($row['lang']))
				$titles[$row['item_id']][$row['lang']] = $row['title'];
		}

		$smcFunc['db_free_result']($request);

		return $titles;
	}
}
