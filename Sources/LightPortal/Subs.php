<?php

namespace Bugo\LightPortal;

/**
 * Subs.php
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

class Subs
{
	/**
	 * Check if the portal must not be loaded
	 *
	 * Проверяем, должен портал загружаться или нет
	 *
	 * @return bool
	 */
	public static function isPortalShouldNotBeLoaded(): bool
	{
		global $context, $modSettings;

		if (!defined('LP_NAME') || !empty($context['uninstalling']) || Helpers::request()->is('printpage')) {
			$modSettings['minimize_files'] = 0;

			return true;
		}

		return false;
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

		// Width of some panels | Ширина некоторых панелей
		$context['lp_header_panel_width'] = !empty($modSettings['lp_header_panel_width']) ? (int) $modSettings['lp_header_panel_width'] : 12;
		$context['lp_left_panel_width']   = !empty($modSettings['lp_left_panel_width']) ? json_decode($modSettings['lp_left_panel_width'], true) : ['md' => 3, 'lg' => 3, 'xl' => 2];
		$context['lp_right_panel_width']  = !empty($modSettings['lp_right_panel_width']) ? json_decode($modSettings['lp_right_panel_width'], true) : ['md' => 3, 'lg' => 3, 'xl' => 2];
		$context['lp_footer_panel_width'] = !empty($modSettings['lp_footer_panel_width']) ? (int) $modSettings['lp_footer_panel_width'] : 12;

		// Block direction in panels | Направление блоков в панелях
		$context['lp_panel_direction'] = !empty($modSettings['lp_panel_direction']) ? json_decode($modSettings['lp_panel_direction'], true) : [];

		$context['lp_active_blocks'] = self::getActiveBlocks();
	}

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
	 * Get information about all active blocks of the portal
	 *
	 * Получаем информацию обо всех активных блоках портала
	 *
	 * @return array
	 */
	public static function getActiveBlocks(): array
	{
		global $smcFunc;

		if (($active_blocks = Helpers::cache()->get('active_blocks', LP_CACHE_TIME)) === null) {
			$request = $smcFunc['db_query']('', '
				SELECT
					b.block_id, b.icon, b.icon_type, b.type, b.content, b.placement, b.priority, b.permissions, b.areas, b.title_class, b.title_style, b.content_class, b.content_style,
					bt.lang, bt.title, bp.name, bp.value
				FROM {db_prefix}lp_blocks AS b
					LEFT JOIN {db_prefix}lp_titles AS bt ON (b.block_id = bt.item_id AND bt.type = {literal:block})
					LEFT JOIN {db_prefix}lp_params AS bp ON (b.block_id = bp.item_id AND bp.type = {literal:block})
				WHERE b.status = {int:status}
				ORDER BY b.placement, b.priority',
				array(
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
						'content_style' => $row['content_style']
					);

				$active_blocks[$row['block_id']]['title'][$row['lang']] = $row['title'];

				if (!empty($row['name']))
					$active_blocks[$row['block_id']]['parameters'][$row['name']] = $row['value'];
			}

			$smcFunc['db_free_result']($request);
			$smcFunc['lp_num_queries']++;

			Helpers::cache()->put('active_blocks', $active_blocks, LP_CACHE_TIME);
		}

		return $active_blocks;
	}

	/**
	 * Remove unnecessary areas for the standalone mode and return the list of these areas
	 *
	 * Удаляем ненужные в автономном режиме области и возвращаем список этих областей
	 *
	 * @param array $data
	 * @return array
	 */
	public static function unsetDisabledActions(array &$data): array
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
	 * Get names of the current addons
	 *
	 * Получаем имена имеющихся аддонов
	 *
	 * @return array
	 */
	public static function getAddons(): array
	{
		$dirs = glob(LP_ADDON_DIR . '/*', GLOB_ONLYDIR) or array();

		$addons = [];
		foreach ($dirs as $dir) {
			$addons[] = basename($dir);
		}

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

		$addon_dir = LP_ADDON_DIR . '/' . $addon . '/langs/';
		$languages = array_unique(['english', $user_info['language']]);

		foreach ($languages as $lang) {
			$lang_file = $addon_dir . $lang . '.php';

			if (is_file($lang_file)) {
				require_once($lang_file);
			}
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
	 * @return void
	 */
	public static function runAddons(string $hook = '', array $vars = [], array $plugins = [])
	{
		global $context;

		$context['lp_bbc_icon']  = 'fas fa-square';
		$context['lp_html_icon'] = 'fab fa-html5';
		$context['lp_php_icon']  = 'fab fa-php';

		$addons = !empty($plugins) ? $plugins : $context['lp_enabled_plugins'];

		if (empty($addons))
			return;

		foreach ($addons as $id => $addon) {
			self::loadAddonLanguage($addon);

			$className = __NAMESPACE__ . '\Addons\\' . $addon . '\\' . $addon;

			if (!class_exists($className)) {
				continue;
			}

			$class = new $className;

			if (!isset($snake_name[$id])) {
				$snake_name[$id] = Helpers::getSnakeName($addon);

				$context['lp_' . $snake_name[$id] . '_type'] = property_exists($class, 'addon_type') ? $class->addon_type : 'block';
				$context['lp_' . $snake_name[$id] . '_icon'] = property_exists($class, 'addon_icon') ? $class->addon_icon : 'fas fa-puzzle-piece';
			}

			if (method_exists($class, 'init') && in_array($addon, $context['lp_enabled_plugins'])) {
				$class->init();
			}

			if (method_exists($class, $hook)) {
				$class->$hook(...$vars);
			}
		}
	}

	/**
	 * Get a list of all used classes for blocks with a header
	 *
	 * Получаем список всех используемых классов для блоков с заголовком
	 *
	 * @return array
	 */
	public static function getTitleClasses(): array
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
	public static function getContentClasses(): array
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
	 * Show script execution time and num queries
	 *
	 * Отображаем время выполнения скрипта и количество запросов к базе
	 *
	 * @return void
	 */
	public static function showDebugInfo()
	{
		global $context, $txt, $smcFunc;

		$context['lp_load_page_stats'] = LP_DEBUG ? sprintf($txt['lp_load_page_stats'], round(microtime(true) - $context['lp_load_time'], 3), $smcFunc['lp_num_queries']) : false;

		if (!empty($context['lp_load_page_stats']) && !empty($context['template_layers'])) {
			loadTemplate('LightPortal/ViewDebug');

			$key = array_search('portal', $context['template_layers']);
			if (empty($key)) {
				$context['template_layers'][] = 'debug';
			} else {
				$context['template_layers'] = array_merge(
					array_slice($context['template_layers'], 0, $key, true),
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

		if (Helpers::request()->is('forum'))
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

	/**
	 * Getting a list of pages to display in the main menu
	 *
	 * Получаем список страниц для отображения в главном меню
	 *
	 * @return array
	 */
	public static function getPagesInMenu(): array
	{
		global $smcFunc;

		if (($pages = Helpers::cache()->get('pages_in_menu', LP_CACHE_TIME * 4)) === null) {
			$request = $smcFunc['db_query']('', '
				SELECT ps.value, p.alias, p.permissions, ps2.value AS icon, ps3.value AS icon_type
				FROM {db_prefix}lp_params AS ps
					LEFT JOIN {db_prefix}lp_params AS ps2 ON (ps.item_id = ps2.item_id AND ps2.name = {literal:icon} AND ps2.type = {literal:page})
					LEFT JOIN {db_prefix}lp_params AS ps3 ON (ps.item_id = ps3.item_id AND ps3.name = {literal:icon_type} AND ps3.type = {literal:page})
					INNER JOIN {db_prefix}lp_pages AS p ON (ps.item_id = p.page_id)
				WHERE ps.name = {literal:main_menu_item}
					AND ps.value != {string:blank_string}
					AND ps.type = {literal:page}',
				array(
					'blank_string' => ''
				)
			);

			$pages = [];
			while ($row = $smcFunc['db_fetch_assoc']($request)) {
				$pages[$row['alias']] = array(
					'title'       => json_decode($row['value'], true),
					'permissions' => $row['permissions'],
					'icon'        => $row['icon'],
					'icon_type'   => $row['icon_type']
				);
			}

			$smcFunc['db_free_result']($request);
			$smcFunc['lp_num_queries']++;

			Helpers::cache()->put('pages_in_menu', $pages, LP_CACHE_TIME * 4);
		}

		return $pages;
	}
}
