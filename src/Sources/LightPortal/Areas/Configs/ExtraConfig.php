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
 * @version 2.6
 */

namespace Bugo\LightPortal\Areas\Configs;

use Bugo\Compat\{ACP, Config, Lang, Theme, User, Utils};

if (! defined('SMF'))
	die('No direct access...');

final class ExtraConfig extends AbstractConfig
{
	/**
	 * Output page and block settings
	 *
	 * Выводим настройки страниц и блоков
	 */
	public function show(): void
	{
		Utils::$context['page_title'] = Utils::$context['settings_title'] = Lang::$txt['lp_extra'];
		Utils::$context['post_url']   = Config::$scripturl . '?action=admin;area=lp_settings;sa=extra;save';

		Lang::$txt['lp_show_comment_block_set']['none']    = Lang::$txt['lp_show_comment_block_set'][0];
		Lang::$txt['lp_show_comment_block_set']['default'] = Lang::$txt['lp_show_comment_block_set'][1];

		unset(Lang::$txt['lp_show_comment_block_set'][0], Lang::$txt['lp_show_comment_block_set'][1]);
		asort(Lang::$txt['lp_show_comment_block_set']);

		Lang::$txt['lp_fa_source_title'] .= ' <img class="floatright" src="https://data.jsdelivr.com/v1/package/npm/@fortawesome/fontawesome-free/badge?style=rounded" alt="">';

		$this->addDefaultValues([
			'lp_num_comments_per_page' => 10,
			'lp_page_maximum_tags'     => 10,
		]);

		$configVars = [
			['check', 'lp_show_tags_on_page'],
			['select', 'lp_page_og_image', Lang::$txt['lp_page_og_image_set']],
			['check', 'lp_show_prev_next_links'],
			['check', 'lp_show_related_pages'],
			'',
			['callback', 'comment_settings_before'],
			[
				'select',
				'lp_show_comment_block',
				Lang::$txt['lp_show_comment_block_set'],
				'javascript' => '@change="comment_block = $event.target.value"'
			],
			[
				'int',
				'lp_time_to_change_comments',
				'postinput' => Lang::$txt['manageposts_minutes'],
				'javascript' => ':disabled="comment_block !== \'default\'"'
			],
			[
				'int',
				'lp_num_comments_per_page',
				'javascript' => ':disabled="comment_block !== \'default\'"'
			],
			[
				'select',
				'lp_comment_sorting',
				[Lang::$txt['lp_sort_by_created'], Lang::$txt['lp_sort_by_created_desc']],
				'javascript' => ':disabled="comment_block !== \'default\'"'
			],
			['callback', 'comment_settings_after'],
			'',
			['check', 'lp_show_items_as_articles'],
			['int', 'lp_page_maximum_tags', 'min' => 1],
			['select', 'lp_permissions_default', Lang::$txt['lp_permissions']],
			['check', 'lp_hide_blocks_in_acp'],
			['title', 'lp_fa_source_title'],
			[
				'select',
				'lp_fa_source',
				[
					'none'      => Lang::$txt['no'],
					'css_cdn'   => Lang::$txt['lp_fa_source_css_cdn'],
					'css_local' => Lang::$txt['lp_fa_source_css_local'],
					'custom'    => Lang::$txt['lp_fa_custom'],
					'kit'       => Lang::$txt['lp_fa_kit'],
				],
				'onchange' => 'document.getElementById(\'lp_fa_custom\').disabled = this.value !== \'custom\';
					document.getElementById(\'lp_fa_kit\').disabled = this.value !== \'kit\';'
			],
			[
				'text',
				'lp_fa_custom',
				'disabled' => isset(Config::$modSettings['lp_fa_source']) && Config::$modSettings['lp_fa_source'] !== 'custom',
				'size' => 75
			],
			[
				'text',
				'lp_fa_kit',
				'disabled' => isset(Config::$modSettings['lp_fa_kit']) && Config::$modSettings['lp_fa_source'] !== 'kit',
				'placeholder' => 'https://kit.fontawesome.com/xxx.js',
				'size' => 75
			],
		];

		Theme::loadTemplate('LightPortal/ManageSettings');

		// Save
		if ($this->request()->has('save')) {
			User::$me->checkSession();

			if ($this->request()->isNotEmpty('lp_fa_custom'))
				$this->post()->put('lp_fa_custom', $this->filterVar($this->request('lp_fa_custom'), 'url'));

			if ($this->request()->isNotEmpty('lp_fa_kit'))
				$this->post()->put('lp_fa_kit', $this->filterVar($this->request('lp_fa_kit'), 'url'));

			$saveVars = $configVars;
			ACP::saveDBSettings($saveVars);

			$this->session()->put('adm-save', true);
			$this->cache()->flush();

			Utils::redirectexit('action=admin;area=lp_settings;sa=extra');
		}

		ACP::prepareDBSettingContext($configVars);
	}
}
