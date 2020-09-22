<?php

namespace Bugo\LightPortal;

/**
 * Subs.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2020 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 1.1
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
		loadCssFile('https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@5/css/all.min.css', array('external' => true, 'seed' => false));
		loadCssFile('light_portal/flexboxgrid.css');
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
		global $context, $modSettings;

		$context['lp_all_title_classes']   = self::getTitleClasses();
		$context['lp_all_content_classes'] = self::getContentClasses();
		$context['lp_fontawesome_enabled'] = Helpers::doesThisThemeUseFontAwesome();

		$context['lp_active_blocks']    = Helpers::getFromCache('active_blocks', 'getActiveBlocks', __CLASS__);
		$context['lp_num_active_pages'] = Helpers::getFromCache('num_active_pages_u' . $context['user']['id'], 'getNumActivePages', __CLASS__);

		// Width of some panels | Ширина некоторых панелей
		$context['lp_header_panel_width'] = !empty($modSettings['lp_header_panel_width']) ? (int) $modSettings['lp_header_panel_width'] : 12;
		$context['lp_left_panel_width']   = !empty($modSettings['lp_left_panel_width']) ? json_decode($modSettings['lp_left_panel_width'], true) : ['md' => 3, 'lg' => 3, 'xl' => 2];
		$context['lp_right_panel_width']  = !empty($modSettings['lp_right_panel_width']) ? json_decode($modSettings['lp_right_panel_width'], true) : ['md' => 3, 'lg' => 3, 'xl' => 2];
		$context['lp_footer_panel_width'] = !empty($modSettings['lp_footer_panel_width']) ? (int) $modSettings['lp_footer_panel_width'] : 12;

		// Block direction in panels | Направление блоков в панелях
		$context['lp_panel_direction'] = !empty($modSettings['lp_panel_direction']) ? json_decode($modSettings['lp_panel_direction'], true) : [];
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
		global $smcFunc, $context;

		$request = $smcFunc['db_query']('', '
			SELECT
				b.block_id, b.icon, b.icon_type, b.type, b.content, b.placement, b.priority, b.permissions, b.areas, b.title_class, b.title_style, b.content_class, b.content_style,
				bt.lang, bt.title, bp.name, bp.value
			FROM {db_prefix}lp_blocks AS b
				LEFT JOIN {db_prefix}lp_titles AS bt ON (b.block_id = bt.item_id AND bt.type = {string:type})
				LEFT JOIN {db_prefix}lp_params AS bp ON (b.block_id = bp.item_id AND bp.type = {string:type})
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
		$context['lp_num_queries']++;

		return $active_blocks;
	}

	/**
	 * Get the total number of active pages of the current user
	 *
	 * Подсчитываем общее количество активных страниц текущего пользователя
	 *
	 * @return int
	 */
	public static function getNumActivePages()
	{
		global $smcFunc, $context;

		$request = $smcFunc['db_query']('', '
			SELECT COUNT(page_id)
			FROM {db_prefix}lp_pages
			WHERE status = {int:status}' . ($context['user']['is_admin'] ? '' : '
				AND author_id = {int:user_id}'),
			array(
				'status'  => Page::STATUS_ACTIVE,
				'user_id' => $context['user']['id']
			)
		);

		[$num_pages] = $smcFunc['db_fetch_row']($request);
		$smcFunc['db_free_result']($request);
		$context['lp_num_queries']++;

		return (int) $num_pages;
	}

	/**
	 * Remove unnecessary areas for the standalone mode and return the list of these areas
	 *
	 * Удаляем ненужные в автономном режиме области и возвращаем список этих областей
	 *
	 * @param array $data
	 * @return array
	 */
	public static function unsetDisabledActions(array &$data)
	{
		global $modSettings, $context;

		$disabled_actions = !empty($modSettings['lp_standalone_mode_disabled_actions']) ? explode(',', $modSettings['lp_standalone_mode_disabled_actions']) : [];
		$disabled_actions[] = 'home';
		$disabled_actions = array_flip($disabled_actions);

		foreach ($data as $action => $dump) {
			if (array_key_exists($action, $disabled_actions))
				unset($data[$action]);
		}

		if (array_key_exists('search', $disabled_actions))
			$context['allow_search'] = false;

		if (array_key_exists('moderate', $disabled_actions))
			$context['allow_moderation_center'] = false;

		if (array_key_exists('calendar', $disabled_actions))
			$context['allow_calendar'] = false;

		if (array_key_exists('mlist', $disabled_actions))
			$context['allow_memberlist'] = false;

		return $disabled_actions;
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

				// Integrate with the Paragrapher mod
				call_integration_hook('integrate_paragrapher_string', array(&$content));

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
	 * Get names of the current addons
	 *
	 * Получаем имена имеющихся аддонов
	 *
	 * @return array
	 */
	public static function getAddons()
	{
		if (!defined('LP_ADDONS'))
			return [];

		$dirs = glob(rtrim(LP_ADDONS, "/") . "/*", GLOB_ONLYDIR) or array();

		$addons = [];
		foreach ($dirs as $dir)
			$addons[] = basename($dir);

		return $addons;
	}

	/**
	 * Require the language file of the addon
	 *
	 * Подключаем языковой файл аддона
	 *
	 * @param string $addon
	 * @return void
	 */
	public static function loadAddonLanguage(string $addon = '')
	{
		global $user_info, $txt;

		$base_dir  = LP_ADDONS . '/' . $addon . '/langs/';
		$languages = array_merge(['english'], [$user_info['language']]);

		foreach ($languages as $lang) {
			$lang_file = $base_dir . $lang . '.php';

			if (is_file($lang_file))
				require_once($lang_file);
		}
	}

	/**
	 * Run addons
	 *
	 * Подключаем аддоны
	 *
	 * @see https://github.com/dragomano/Light-Portal/wiki/Available-hooks
	 *
	 * @param string $hook
	 * @param array $vars (extra variables)
	 * @param array $plugins
	 * @return mixed
	 */
	public static function runAddons(string $hook = 'init', array $vars = [], array $plugins = [])
	{
		global $txt, $context;

		$txt['lp_bbc_icon']  = 'fas fa-square';
		$txt['lp_html_icon'] = 'fab fa-html5';
		$txt['lp_php_icon']  = 'fab fa-php';

		$addons = !empty($plugins) ? $plugins : $context['lp_enabled_plugins'];

		if (empty($addons))
			return false;

		$results = [];
		foreach ($addons as $id => $addon) {
			$class = __NAMESPACE__ . '\Addons\\' . $addon . '\\' . $addon;
			self::loadAddonLanguage($addon);

			if (!isset($addon_snake_name[$id])) {
				$addon_snake_name[$id] = Helpers::getSnakeName($addon);
				$txt['lp_' . $addon_snake_name[$id] . '_type'] = property_exists($class, 'addon_type') ? $class::$addon_type : 'block';
				$txt['lp_' . $addon_snake_name[$id] . '_icon'] = property_exists($class, 'addon_icon') ? $class::$addon_icon : 'fas fa-puzzle-piece';
			}

			if (method_exists($class, $hook) && is_callable(array($class, $hook), false, $callable_name))
				$results[$hook] = call_user_func_array($callable_name, $vars);
		}

		return $results[$hook] ?? null;
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
			'height'       => '1px',
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

		// Only one language by default! | Если на форуме отключен выбор языков, оставим только один
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
			'_'               => '<div%2$s>%1$s</div>' // Empty class
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
		global $smcFunc, $context;

		$request = $smcFunc['db_query']('', '
			SELECT item_id, lang, title
			FROM {db_prefix}lp_titles
			WHERE type = {string:type}
			ORDER BY lang, title',
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
		$context['lp_num_queries']++;

		return $titles;
	}

	/**
	 * Show script execution time and num queries
	 *
	 * Отображаем время выполнения скрипта и количество запросов к базе
	 *
	 * @return void
	 */
	public static function showDebugInfo()
	{
		global $context, $txt;

		$context['lp_load_page_stats'] = LP_DEBUG ? sprintf($txt['lp_load_page_stats'], round(microtime(true) - $context['lp_load_time'], 3), $context['lp_num_queries']) : false;

		if (!empty($context['lp_load_page_stats']) && !empty($context['template_layers'])) {
			loadTemplate('LightPortal/ViewDebug');

			$key = array_search('portal', $context['template_layers']);
			if (empty($key)) {
				$context['template_layers'][] = 'debug';
			} else {
				$context['template_layers'] = array_merge(
					array_slice($context['template_layers'], 0, (int) $key, true),
					array('debug'),
					array_slice($context['template_layers'], $key, null, true)
				);
			}
		}
	}

	/**
	 * Fix canonical url for forum action
	 *
	 * Исправляем канонический адрес для области forum
	 *
	 * @return void
	 */
	public static function fixCanonicalUrl()
	{
		global $context, $scripturl;

		if ($context['current_action'] == 'forum')
			$context['canonical_url'] = $scripturl . '?action=forum';
	}

	/**
	 * Change the link tree
	 *
	 * Меняем дерево ссылок
	 *
	 * @return void
	 */
	public static function fixLinktree()
	{
		global $context, $scripturl;

		if (empty($context['current_board']) || empty($context['linktree'][1]))
			return;

		$old_url = explode('#', $context['linktree'][1]['url']);

		if (!empty($old_url[1]))
			$context['linktree'][1]['url'] = $scripturl . '?action=forum#' . $old_url[1];
	}
}
