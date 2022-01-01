<?php

declare(strict_types = 1);

/**
 * Main.php
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
	die('No direct access...');

abstract class Main
{
	use Helper;

	protected function isPortalCanBeLoaded(): bool
	{
		if (! defined('LP_NAME') || ! empty($this->context['uninstalling']) || $this->request()->is('printpage')) {
			$this->modSettings['minimize_files'] = 0;
			return false;
		}

		return true;
	}

	protected function defineVars()
	{
		[$this->context['lp_num_active_blocks'], $this->context['lp_num_active_pages']] = $this->getNumActiveEntities();

		$this->context['lp_all_title_classes']   = $this->getTitleClasses();
		$this->context['lp_all_content_classes'] = $this->getContentClasses();
		$this->context['lp_block_placements']    = $this->getBlockPlacements();
		$this->context['lp_page_options']        = $this->getPageOptions();
		$this->context['lp_plugin_types']        = $this->getPluginTypes();
		$this->context['lp_content_types']       = $this->getContentTypes();

		$this->context['lp_enabled_plugins'] = empty($this->modSettings['lp_enabled_plugins']) ? [] : explode(',', $this->modSettings['lp_enabled_plugins']);

		// Width of some panels | Ширина некоторых панелей
		$this->context['lp_header_panel_width'] = empty($this->modSettings['lp_header_panel_width']) ? 12 : (int) $this->modSettings['lp_header_panel_width'];
		$this->context['lp_left_panel_width'] = empty($this->modSettings['lp_left_panel_width']) ? ['md' => 3, 'lg' => 3, 'xl' => 2] : json_decode($this->modSettings['lp_left_panel_width'], true);
		$this->context['lp_right_panel_width'] = empty($this->modSettings['lp_right_panel_width']) ? ['md' => 3, 'lg' => 3, 'xl' => 2] : json_decode($this->modSettings['lp_right_panel_width'], true);
		$this->context['lp_footer_panel_width'] = empty($this->modSettings['lp_footer_panel_width']) ? 12 : (int) $this->modSettings['lp_footer_panel_width'];

		// Block direction in panels | Направление блоков в панелях
		$this->context['lp_panel_direction'] = empty($this->modSettings['lp_panel_direction']) ? [] : json_decode($this->modSettings['lp_panel_direction'], true);

		$this->context['lp_active_blocks'] = (new Entities\Block)->getActive();
	}

	protected function loadCssFiles()
	{
		if (! isset($this->modSettings['lp_fa_source']) || $this->modSettings['lp_fa_source'] === 'css_cdn') {
			\loadCSSFile(
				'https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@5/css/all.min.css',
				['external' => true, 'seed' => false],
				'portal_fontawesome'
			);
		} elseif ($this->modSettings['lp_fa_source'] === 'css_local') {
			\loadCSSFile('all.min.css', [], 'portal_fontawesome');
		} elseif ($this->modSettings['lp_fa_source'] === 'custom' && ! empty($this->modSettings['lp_fa_custom'])) {
			\loadCSSFile(
				$this->modSettings['lp_fa_custom'],
				['external' => true, 'seed' => false],
				'portal_fontawesome'
			);
		}

		\loadCSSFile('light_portal/flexboxgrid.css');
		\loadCSSFile('light_portal/portal.css');
		\loadCSSFile('custom_frontpage.css');
	}

	/**
	 * Remove unnecessary areas for the standalone mode
	 *
	 * Удаляем ненужные в автономном режиме области
	 */
	protected function unsetDisabledActions(array &$data)
	{
		$disabled_actions = empty($this->modSettings['lp_standalone_mode_disabled_actions']) ? [] : explode(',', $this->modSettings['lp_standalone_mode_disabled_actions']);
		$disabled_actions[] = 'home';
		$disabled_actions = array_flip($disabled_actions);

		foreach (array_keys($data) as $action) {
			if (array_key_exists($action, $disabled_actions))
				unset($data[$action]);
		}

		if (array_key_exists('search', $disabled_actions))
			$this->context['allow_search'] = false;

		if (array_key_exists('moderate', $disabled_actions))
			$this->context['allow_moderation_center'] = false;

		if (array_key_exists('calendar', $disabled_actions))
			$this->context['allow_calendar'] = false;

		if (array_key_exists('mlist', $disabled_actions))
			$this->context['allow_memberlist'] = false;

		$this->context['lp_disabled_actions'] = $disabled_actions;
	}

	/**
	 * Fix canonical url for forum action
	 *
	 * Исправляем канонический адрес для области forum
	 */
	protected function fixCanonicalUrl()
	{
		if ($this->request()->is('forum'))
			$this->context['canonical_url'] = $this->scripturl . '?action=forum';
	}

	/**
	 * Change the link tree
	 *
	 * Меняем дерево ссылок
	 */
	protected function fixLinktree()
	{
		if (empty($this->context['current_board']) && $this->request()->has('c') === false || empty($this->context['linktree'][1]))
			return;

		$old_url = explode('#', $this->context['linktree'][1]['url']);

		if (! empty($old_url[1]))
			$this->context['linktree'][1]['url'] = $this->scripturl . '?action=forum#' . $old_url[1];
	}

	/**
	 * Show the script execution time and the number of the portal queries
	 *
	 * Отображаем время выполнения скрипта и количество запросов к базе
	 */
	protected function showDebugInfo()
	{
		if (empty($this->modSettings['lp_show_debug_info']) || empty($this->context['user']['is_admin']) || empty($this->context['template_layers']))
			return;

		$this->context['lp_load_page_stats'] = sprintf($this->txt['lp_load_page_stats'], round(microtime(true) - $this->context['lp_load_time'], 3), $this->context['lp_num_queries']);

		loadTemplate('LightPortal/ViewDebug');

		if (empty($key = array_search('lp_portal', $this->context['template_layers']))) {
			$this->context['template_layers'][] = 'debug';
			return;
		}

		$this->context['template_layers'] = array_merge(
			array_slice($this->context['template_layers'], 0, $key, true),
			['debug'],
			array_slice($this->context['template_layers'], $key, null, true)
		);
	}

	private function getNumActiveEntities(): array
	{
		if (($num_entities = $this->cache()->get('num_active_entities_u' . $this->user_info['id'])) === null) {
			$request = $this->smcFunc['db_query']('', '
				SELECT
					(
						SELECT COUNT(b.block_id)
						FROM {db_prefix}lp_blocks b
						WHERE b.status = {int:status}' . ($this->user_info['is_admin'] ? '' : '
							AND b.user_id = {int:user_id}') . '
					) AS num_blocks,
					(
						SELECT COUNT(p.page_id)
						FROM {db_prefix}lp_pages p
						WHERE p.status = {int:status}' . ($this->user_info['is_admin'] ? '' : '
							AND p.author_id = {int:user_id}') . '
					) AS num_pages',
				[
					'status'  => 1,
					'user_id' => $this->user_info['id']
				]
			);

			$num_entities = $this->smcFunc['db_fetch_assoc']($request);

			$this->smcFunc['db_free_result']($request);
			$this->context['lp_num_queries']++;

			$this->cache()->put('num_active_entities_u' . $this->user_info['id'], $num_entities);
		}

		return array_values($num_entities);
	}
}
