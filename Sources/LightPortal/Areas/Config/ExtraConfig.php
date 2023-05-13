<?php declare(strict_types=1);

/**
 * ExtraConfig.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2023 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.2
 */

namespace Bugo\LightPortal\Areas\Config;

use Bugo\LightPortal\Helper;

if (! defined('SMF'))
	die('No direct access...');

final class ExtraConfig
{
	use Helper;

	/**
	 * Output page and block settings
	 *
	 * Выводим настройки страниц и блоков
	 */
	public function show(): void
	{
		$this->context['page_title'] = $this->context['settings_title'] = $this->txt['lp_extra'];
		$this->context['post_url']   = $this->scripturl . '?action=admin;area=lp_settings;sa=extra;save';

		$this->txt['lp_show_comment_block_set']['none']    = $this->txt['lp_show_comment_block_set'][0];
		$this->txt['lp_show_comment_block_set']['default'] = $this->txt['lp_show_comment_block_set'][1];

		unset($this->txt['lp_show_comment_block_set'][0], $this->txt['lp_show_comment_block_set'][1]);
		asort($this->txt['lp_show_comment_block_set']);

		$this->txt['lp_fa_source_title'] .= ' <img class="floatright" src="https://data.jsdelivr.com/v1/package/npm/@fortawesome/fontawesome-free/badge?style=rounded" alt="">';

		// Initial settings
		$addSettings = [];
		if (! isset($this->modSettings['lp_num_comments_per_page']))
			$addSettings['lp_num_comments_per_page'] = 10;
		if (! isset($this->modSettings['lp_page_maximum_keywords']))
			$addSettings['lp_page_maximum_keywords'] = 10;
		$this->updateSettings($addSettings);

		$config_vars = [
			['check', 'lp_show_tags_on_page'],
			['select', 'lp_page_og_image', $this->txt['lp_page_og_image_set']],
			['check', 'lp_show_prev_next_links'],
			['check', 'lp_show_related_pages'],
			'',
			['callback', 'comment_settings'],
			'',
			['check', 'lp_show_items_as_articles'],
			['select', 'lp_page_editor_type_default', $this->context['lp_content_types']],
			['int', 'lp_page_maximum_keywords', 'min' => 1],
			['select', 'lp_permissions_default', $this->txt['lp_permissions']],
			['check', 'lp_hide_blocks_in_acp'],
			['title', 'lp_fa_source_title'],
			[
				'select',
				'lp_fa_source',
				[
					'none'      => $this->txt['no'],
					'css_cdn'   => $this->txt['lp_fa_source_css_cdn'],
					'css_local' => $this->txt['lp_fa_source_css_local'],
					'custom'    => $this->txt['lp_fa_custom'],
					'kit'       => $this->txt['lp_fa_kit']
				],
				'onchange' => 'document.getElementById(\'lp_fa_custom\').disabled = this.value !== \'custom\';'
			],
			[
				'text',
				'lp_fa_custom',
				'disabled' => isset($this->modSettings['lp_fa_source']) && $this->modSettings['lp_fa_source'] !== 'custom',
				'size' => 75
			],
		];

		$this->loadTemplate('LightPortal/ManageSettings');

		$this->prepareBbcodes();

		// Save
		if ($this->request()->has('save')) {
			$this->checkSession();

			// Clean up the tags
			$parse_tags = (array) $this->parseBbc(false);
			$bbcTags = array_map(fn($tag): string => $tag['tag'], $parse_tags);

			if ($this->request()->hasNot('lp_disabled_bbc_in_comments_enabledTags')) {
				$this->post()->put('lp_disabled_bbc_in_comments_enabledTags', '');
			} elseif (! is_array($this->request('lp_disabled_bbc_in_comments_enabledTags'))) {
				$this->post()->put('lp_disabled_bbc_in_comments_enabledTags', $this->request('lp_disabled_bbc_in_comments_enabledTags'));
			}

			$this->post()->put('lp_enabled_bbc_in_comments', $this->request('lp_disabled_bbc_in_comments_enabledTags'));
			$this->post()->put('lp_disabled_bbc_in_comments', implode(',', array_diff($bbcTags, explode(',', $this->request('lp_disabled_bbc_in_comments_enabledTags') ?? ''))));

			if ($this->request()->isNotEmpty('lp_fa_custom'))
				$this->post()->put('lp_fa_custom', $this->validate($this->request('lp_fa_custom'), 'url'));

			$save_vars = $config_vars;
			$save_vars[] = ['text', 'lp_show_comment_block'];
			$save_vars[] = ['text', 'lp_enabled_bbc_in_comments'];
			$save_vars[] = ['text', 'lp_disabled_bbc_in_comments'];
			$save_vars[] = ['int', 'lp_time_to_change_comments'];
			$save_vars[] = ['int', 'lp_num_comments_per_page'];
			$save_vars[] = ['int', 'lp_comment_sorting'];

			$this->saveDBSettings($save_vars);

			$this->session()->put('adm-save', true);
			$this->cache()->flush();

			$this->redirect('action=admin;area=lp_settings;sa=extra');
		}

		$this->prepareDBSettingContext($config_vars);
	}

	private function prepareBbcodes(): void
	{
		$disabledBbc = empty($this->modSettings['lp_disabled_bbc_in_comments']) ? [] : explode(',', $this->modSettings['lp_disabled_bbc_in_comments']);
		$disabledBbc = isset($this->modSettings['disabledBBC']) ? [...$disabledBbc, ...explode(',', $this->modSettings['disabledBBC'])] : $disabledBbc;

		$temp = $this->parseBbc(false);
		$bbcTags = [];
		foreach ($temp as $tag) {
			if (! isset($tag['require_parents']))
				$bbcTags[] = $tag['tag'];
		}

		$bbcTags = array_unique($bbcTags);

		$this->context['bbc_sections'] = [
			'title'        => $this->txt['enabled_bbc_select'],
			'disabled'     => $disabledBbc ?: [],
			'all_selected' => empty($disabledBbc),
			'columns'      => []
		];

		$sectionTags = array_diff($bbcTags, $this->context['legacy_bbc']);

		foreach ($sectionTags as $tag) {
			$this->context['bbc_sections']['columns'][] = [
				'tag' => $tag
			];
		}
	}
}
