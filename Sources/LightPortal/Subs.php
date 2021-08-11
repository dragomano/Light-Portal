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
 * @version 1.8
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
		$context['lp_block_placements']    = self::getBlockPlacements();
		$context['lp_page_options']        = self::getPageOptions();
		$context['lp_plugin_types']        = self::getPluginTypes();
		$context['lp_page_types']          = self::getPageTypes();

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
	 * @return void
	 */
	public static function loadCssFiles()
	{
		loadCSSFile('https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@5/css/all.min.css', array('external' => true, 'seed' => false));
		loadCSSFile('light_portal/flexboxgrid.css');
		loadCSSFile('light_portal/light_portal.css');
	}

	/**
	 * @return array
	 */
	public static function getActiveBlocks(): array
	{
		global $smcFunc;

		if (($active_blocks = Helpers::cache()->get('active_blocks')) === null) {
			$request = $smcFunc['db_query']('', '
				SELECT
					b.block_id, b.icon, b.type, b.content, b.placement, b.priority, b.permissions, b.areas, b.title_class, b.title_style, b.content_class, b.content_style,
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

			Helpers::cache()->put('active_blocks', $active_blocks);
		}

		return $active_blocks;
	}

	/**
	 * Remove unnecessary areas for the standalone mode
	 *
	 * Удаляем ненужные в автономном режиме области
	 *
	 * @param array $data
	 * @return void
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

		$context['lp_disabled_actions'] = $disabled_actions;
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
			'cat_bar'              => '<div class="cat_bar"><h3 class="catbg">%1$s</h3></div>',
			'title_bar'            => '<div class="title_bar"><h3 class="titlebg">%1$s</h3></div>',
			'sub_bar'              => '<div class="sub_bar"><h3 class="subbg">%1$s</h3></div>',
			'noticebox'            => '<div class="noticebox"><h3>%1$s</h3></div>',
			'infobox'              => '<div class="infobox"><h3>%1$s</h3></div>',
			'descbox'              => '<div class="descbox"><h3>%1$s</h3></div>',
			'generic_list_wrapper' => '<div class="generic_list_wrapper"><h3>%1$s</h3></div>',
			'progress_bar'         => '<div class="progress_bar"><h3>%1$s</h3></div>',
			'popup_content'        => '<div class="popup_content"><h3>%1$s</h3></div>',
			''                     => '<div>%1$s</div>',
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
			'roundframe'   => '<div class="roundframe"%2$s>%1$s</div>',
			'roundframe2'  => '<div class="roundframe noup"%2$s>%1$s</div>',
			'windowbg'     => '<div class="windowbg"%2$s>%1$s</div>',
			'windowbg2'    => '<div class="windowbg noup"%2$s>%1$s</div>',
			'information'  => '<div class="information"%2$s>%1$s</div>',
			'information2' => '<div class="information noup"%2$s>%1$s</div>',
			'errorbox'     => '<div class="errorbox"%2$s>%1$s</div>',
			'noticebox'    => '<div class="noticebox"%2$s>%1$s</div>',
			'infobox'      => '<div class="infobox"%2$s>%1$s</div>',
			'descbox'      => '<div class="descbox"%2$s>%1$s</div>',
			'bbc_code'     => '<div class="bbc_code"%2$s>%1$s</div>',
			''             => '<div%2$s>%1$s</div>',
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
	 * @return array
	 */
	public static function getPagesInMenu(): array
	{
		global $smcFunc;

		if (($pages = Helpers::cache()->get('pages_in_menu', LP_CACHE_TIME * 4)) === null) {
			$request = $smcFunc['db_query']('', '
				SELECT ps.value, p.alias, p.permissions, ps2.value AS icon
				FROM {db_prefix}lp_params AS ps
					LEFT JOIN {db_prefix}lp_params AS ps2 ON (ps.item_id = ps2.item_id AND ps2.name = {literal:icon} AND ps2.type = {literal:page})
					INNER JOIN {db_prefix}lp_pages AS p ON (ps.item_id = p.page_id)
				WHERE ps.name = {literal:main_menu_item}
					AND ps.value != {string:blank_string}
					AND ps.type = {literal:page}
					AND p.status = {int:status}',
				array(
					'blank_string' => '',
					'status'       => Page::STATUS_ACTIVE
				)
			);

			$pages = [];
			while ($row = $smcFunc['db_fetch_assoc']($request)) {
				$pages[$row['alias']] = array(
					'title'       => json_decode($row['value'], true),
					'permissions' => $row['permissions'],
					'icon'        => $row['icon']
				);
			}

			$smcFunc['db_free_result']($request);
			$smcFunc['lp_num_queries']++;

			Helpers::cache()->put('pages_in_menu', $pages, LP_CACHE_TIME * 4);
		}

		return $pages;
	}

	/**
	 * @return array
	 */
	public static function getBlockPlacements(): array
	{
		global $txt;

		return array_combine(array('header', 'top', 'left', 'right', 'bottom', 'footer'), $txt['lp_block_placement_set']);
	}

	/**
	 * @return array
	 */
	public static function getPageOptions(): array
	{
		global $txt;

		return array_combine(array('show_author_and_date', 'show_related_pages', 'allow_comments', 'main_menu_item'), $txt['lp_page_options']);
	}

	/**
	 * @return array
	 */
	public static function getPluginTypes(): array
	{
		global $txt;

		return array_combine(array('block', 'editor', 'comment', 'parser', 'article', 'frontpage', 'impex', 'other'), $txt['lp_plugins_types']);
	}

	/**
	 * @return array
	 */
	public static function getPageTypes(): array
	{
		global $txt;

		return array_combine(array('bbc', 'html', 'php'), $txt['lp_page_types']);
	}
}
