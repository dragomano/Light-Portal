<?php

declare(strict_types = 1);

/**
 * Sub.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2022 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.0
 */

namespace Bugo\LightPortal;

if (! defined('SMF'))
	die('Hacking attempt...');

final class Sub
{
	public static function isPortalShouldNotBeLoaded(): bool
	{
		global $context, $modSettings;

		if (! defined('LP_NAME') || ! empty($context['uninstalling']) || Helper::request()->is('printpage')) {
			$modSettings['minimize_files'] = 0;

			return true;
		}

		return false;
	}

	public static function defineVars()
	{
		global $context, $modSettings;

		[$context['lp_num_active_blocks'], $context['lp_num_active_pages']] = self::getNumActiveEntities();

		$context['lp_all_title_classes']   = self::getTitleClasses();
		$context['lp_all_content_classes'] = self::getContentClasses();
		$context['lp_block_placements']    = self::getBlockPlacements();
		$context['lp_page_options']        = self::getPageOptions();
		$context['lp_plugin_types']        = self::getPluginTypes();
		$context['lp_content_types']       = self::getContentTypes();

		$context['lp_enabled_plugins'] = empty($modSettings['lp_enabled_plugins']) ? [] : explode(',', $modSettings['lp_enabled_plugins']);

		// Width of some panels | Ширина некоторых панелей
		$context['lp_header_panel_width'] = empty($modSettings['lp_header_panel_width']) ? 12 : (int) $modSettings['lp_header_panel_width'];
		$context['lp_left_panel_width']   = empty($modSettings['lp_left_panel_width']) ? ['md' => 3, 'lg' => 3, 'xl' => 2] : json_decode($modSettings['lp_left_panel_width'], true);
		$context['lp_right_panel_width']  = empty($modSettings['lp_right_panel_width']) ? ['md' => 3, 'lg' => 3, 'xl' => 2] : json_decode($modSettings['lp_right_panel_width'], true);
		$context['lp_footer_panel_width'] = empty($modSettings['lp_footer_panel_width']) ? 12 : (int) $modSettings['lp_footer_panel_width'];

		// Block direction in panels | Направление блоков в панелях
		$context['lp_panel_direction'] = empty($modSettings['lp_panel_direction']) ? [] : json_decode($modSettings['lp_panel_direction'], true);

		$context['lp_active_blocks'] = Block::getActive();
	}

	public static function loadCssFiles()
	{
		global $modSettings;

		if (! isset($modSettings['lp_fa_source']) || $modSettings['lp_fa_source'] === 'css_cdn') {
			loadCSSFile(
				'https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@5/css/all.min.css',
				array('external' => true, 'seed' => false),
				'portal_fontawesome'
			);
		} elseif ($modSettings['lp_fa_source'] === 'css_local') {
			loadCSSFile('all.min.css', [], 'portal_fontawesome');
		} elseif ($modSettings['lp_fa_source'] === 'custom' && ! empty($modSettings['lp_fa_custom'])) {
			loadCSSFile(
				$modSettings['lp_fa_custom'],
				array('external' => true, 'seed' => false),
				'portal_fontawesome'
			);
		}

		loadCSSFile('light_portal/flexboxgrid.css');
		loadCSSFile('light_portal/portal.css');
		loadCSSFile('custom_frontpage.css');
	}

	/**
	 * Remove unnecessary areas for the standalone mode
	 *
	 * Удаляем ненужные в автономном режиме области
	 */
	public static function unsetDisabledActions(array &$data)
	{
		global $modSettings, $context;

		$disabled_actions = empty($modSettings['lp_standalone_mode_disabled_actions']) ? [] : explode(',', $modSettings['lp_standalone_mode_disabled_actions']);
		$disabled_actions[] = 'home';
		$disabled_actions = array_flip($disabled_actions);

		foreach (array_keys($data) as $action) {
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
	 */
	public static function getContentClasses(): array
	{
		return [
			'roundframe'           => '<div class="roundframe noup" %2$s>%1$s</div>',
			'roundframe2'          => '<div class="roundframe" %2$s>%1$s</div>',
			'windowbg'             => '<div class="windowbg noup" %2$s>%1$s</div>',
			'windowbg2'            => '<div class="windowbg" %2$s>%1$s</div>',
			'information'          => '<div class="information" %2$s>%1$s</div>',
			'errorbox'             => '<div class="errorbox" %2$s>%1$s</div>',
			'noticebox'            => '<div class="noticebox" %2$s>%1$s</div>',
			'infobox'              => '<div class="infobox" %2$s>%1$s</div>',
			'descbox'              => '<div class="descbox" %2$s>%1$s</div>',
			'bbc_code'             => '<div class="bbc_code" %2$s>%1$s</div>',
			'generic_list_wrapper' => '<div class="generic_list_wrapper" %2$s>%1$s</div>',
			''                     => '<div%2$s>%1$s</div>',
		];
	}

	/**
	 * Fix canonical url for forum action
	 *
	 * Исправляем канонический адрес для области forum
	 */
	public static function fixCanonicalUrl()
	{
		global $context, $scripturl;

		if (Helper::request()->is('forum'))
			$context['canonical_url'] = $scripturl . '?action=forum';
	}

	/**
	 * Change the link tree
	 *
	 * Меняем дерево ссылок
	 */
	public static function fixLinktree()
	{
		global $context, $scripturl;

		if (empty($context['current_board']) && Helper::request()->has('c') === false || empty($context['linktree'][1]))
			return;

		$old_url = explode('#', $context['linktree'][1]['url']);

		if (! empty($old_url[1]))
			$context['linktree'][1]['url'] = $scripturl . '?action=forum#' . $old_url[1];
	}

	public static function getBlockPlacements(): array
	{
		global $txt;

		return array_combine(array('header', 'top', 'left', 'right', 'bottom', 'footer'), $txt['lp_block_placement_set']);
	}

	public static function getPageOptions(): array
	{
		global $txt;

		return array_combine(array('show_author_and_date', 'show_related_pages', 'allow_comments'), $txt['lp_page_options']);
	}

	public static function getPluginTypes(): array
	{
		global $txt;

		return array_combine(array('block', 'editor', 'comment', 'parser', 'article', 'frontpage', 'impex', 'other', 'block_options', 'page_options'), $txt['lp_plugins_types']);
	}

	public static function getContentTypes(): array
	{
		global $txt, $user_info, $modSettings;

		$types = array_combine(array('bbc', 'html', 'php'), $txt['lp_page_types']);

		return $user_info['is_admin'] || empty($modSettings['lp_prohibit_php']) ? $types : array_slice($types, 0, 2);
	}

	private static function getNumActiveEntities(): array
	{
		global $user_info, $smcFunc;

		if (($num_entities = Helper::cache()->get('num_active_entities_u' . $user_info['id'])) === null) {
			$request = $smcFunc['db_query']('', '
				SELECT
					(
						SELECT COUNT(b.block_id)
						FROM {db_prefix}lp_blocks b
						WHERE b.status = {int:status}' . ($user_info['is_admin'] ? '' : '
							AND b.user_id = {int:user_id}') . '
					) AS num_blocks,
					(
						SELECT COUNT(p.page_id)
						FROM {db_prefix}lp_pages p
						WHERE p.status = {int:status}' . ($user_info['is_admin'] ? '' : '
							AND p.author_id = {int:user_id}') . '
					) AS num_pages',
				array(
					'status'  => 1,
					'user_id' => $user_info['id']
				)
			);

			$num_entities = $smcFunc['db_fetch_assoc']($request);

			$smcFunc['db_free_result']($request);
			$smcFunc['lp_num_queries']++;

			Helper::cache()->put('num_active_entities_u' . $user_info['id'], $num_entities);
		}

		return array_values($num_entities);
	}
}
