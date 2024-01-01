<?php declare(strict_types=1);

/**
 * ExtraConfig.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.4
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

		$this->addDefaultValues([
			'lp_num_comments_per_page' => 10,
			'lp_page_maximum_keywords' => 10,
		]);

		$config_vars = [
			['check', 'lp_show_tags_on_page'],
			['select', 'lp_page_og_image', $this->txt['lp_page_og_image_set']],
			['check', 'lp_show_prev_next_links'],
			['check', 'lp_show_related_pages'],
			'',
			['callback', 'comment_settings'],
			'',
			['check', 'lp_show_items_as_articles'],
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
				'onchange' => 'document.getElementById(\'lp_fa_custom\').disabled = this.value !== \'custom\';document.getElementById(\'lp_fa_kit\').disabled = this.value !== \'kit\';'
			],
			[
				'text',
				'lp_fa_custom',
				'disabled' => isset($this->modSettings['lp_fa_source']) && $this->modSettings['lp_fa_source'] !== 'custom',
				'size' => 75
			],
			[
				'text',
				'lp_fa_kit',
				'disabled' => isset($this->modSettings['lp_fa_kit']) && $this->modSettings['lp_fa_source'] !== 'kit',
				'placeholder' => 'https://kit.fontawesome.com/xxx.js',
				'size' => 75
			],
		];

		$this->loadTemplate('LightPortal/ManageSettings');

		// Save
		if ($this->request()->has('save')) {
			$this->checkSession();

			if ($this->request()->isNotEmpty('lp_fa_custom'))
				$this->post()->put('lp_fa_custom', $this->filterVar($this->request('lp_fa_custom'), 'url'));

			if ($this->request()->isNotEmpty('lp_fa_kit'))
				$this->post()->put('lp_fa_kit', $this->filterVar($this->request('lp_fa_kit'), 'url'));

			$save_vars = $config_vars;
			$save_vars[] = ['text', 'lp_show_comment_block'];
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
}
