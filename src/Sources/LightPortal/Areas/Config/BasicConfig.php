<?php declare(strict_types=1);

/**
 * BasicConfig.php
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

use Bugo\LightPortal\Areas\Partials\CategorySelect;
use Bugo\LightPortal\Areas\Partials\ActionSelect;
use Bugo\LightPortal\Areas\Partials\BoardSelect;
use Bugo\LightPortal\Areas\Partials\PageAliasSelect;
use Bugo\LightPortal\Areas\Partials\PageSelect;
use Bugo\LightPortal\Areas\Partials\TopicSelect;
use Bugo\LightPortal\Areas\Query;
use Bugo\LightPortal\Actions\FrontPage;
use Bugo\LightPortal\Helper;
use IntlException;

if (! defined('SMF'))
	die('No direct access...');

final class BasicConfig
{
	use Helper;
	use Query;

	/**
	 * Output general settings
	 *
	 * Выводим общие настройки
	 * @throws IntlException
	 */
	public function show(): void
	{
		$this->context['page_title']    = $this->context['settings_title'] = $this->txt['lp_base'];
		$this->context['canonical_url'] = $this->scripturl . '?action=admin;area=lp_settings;sa=basic';
		$this->context['post_url']      = $this->context['canonical_url'] . ';save';

		$this->addDefaultValues([
			'lp_frontpage_title'           => str_replace(["'", "\""], "", $this->context['forum_name']),
			'lp_show_views_and_comments'   => 1,
			'lp_frontpage_article_sorting' => 1,
			'lp_num_items_per_page'        => 10,
			'lp_standalone_url'            => $this->boardurl . '/portal.php',
		]);

		$this->context['lp_frontpage_modes'] = array_combine(
			[0, 'chosen_page', 'all_pages', 'chosen_pages', 'all_topics', 'chosen_topics', 'chosen_boards'],
			$this->txt['lp_frontpage_mode_set']
		);

		$this->prepareTopicList();

		$this->context['lp_column_set'] = array_map(fn($item) => $this->translate('lp_frontpage_num_columns_set', ['columns' => $item]), [1, 2, 3, 4, 6]);

		$this->context['lp_frontpage_layouts']           = (new FrontPage)->getLayouts();
		$this->context['lp_frontpage_alias_select']      = new PageAliasSelect;
		$this->context['lp_frontpage_categories_select'] = new CategorySelect;
		$this->context['lp_frontpage_boards_select']     = new BoardSelect;
		$this->context['lp_frontpage_topics_select']     = new TopicSelect;
		$this->context['lp_frontpage_pages_select']      = new PageSelect;
		$this->context['lp_disabled_actions_select']     = new ActionSelect;

		$javascript = ':disabled="[\'0\', \'chosen_page\'].includes(frontpage_mode)"';

		$config_vars = [
			['callback', 'frontpage_mode_settings_before'],
			[
				'select',
				'lp_frontpage_mode',
				$this->context['lp_frontpage_modes'],
				'javascript' => '
					@change="frontpage_mode = $event.target.value; $dispatch(\'change-mode\', {front: frontpage_mode})"
				'
			],
			[
				'text',
				'lp_frontpage_title',
				'size' => '80" placeholder="' . str_replace(["'", "\""], "", $this->context['forum_name']) . ' - ' . $this->txt['lp_portal'],
				'javascript' => $javascript
			],
			['callback', 'frontpage_mode_settings_middle'],
			[
				'check',
				'lp_show_images_in_articles',
				'help' => 'lp_show_images_in_articles_help',
				'javascript' => $javascript
			],
			[
				'text',
				'lp_image_placeholder',
				'size' => '80" placeholder="' . $this->txt['lp_example'] . $this->settings['default_images_url'] . '/smflogo.svg',
				'javascript' => $javascript
			],
			[
				'check',
				'lp_show_teaser',
				'javascript' => $javascript
			],
			[
				'check',
				'lp_show_author',
				'help' => 'lp_show_author_help',
				'javascript' => $javascript
			],
			[
				'check',
				'lp_show_views_and_comments',
				'javascript' => $javascript
			],
			[
				'check',
				'lp_frontpage_order_by_replies',
				'javascript' => $javascript
			],
			[
				'select',
				'lp_frontpage_article_sorting',
				$this->txt['lp_frontpage_article_sorting_set'],
				'help' => 'lp_frontpage_article_sorting_help',
				'javascript' => $javascript
			],
			[
				'select',
				'lp_frontpage_layout',
				$this->context['lp_frontpage_layouts'],
				'javascript' => $javascript
			],
			[
				'check',
				'lp_show_layout_switcher',
				'javascript' => $javascript
			],
			[
				'select',
				'lp_frontpage_num_columns',
				$this->context['lp_column_set'],
				'javascript' => $javascript
			],
			[
				'select',
				'lp_show_pagination',
				$this->txt['lp_show_pagination_set'],
				'javascript' => $javascript
			],
			[
				'check',
				'lp_use_simple_pagination',
				'javascript' => $javascript
			],
			[
				'int',
				'lp_num_items_per_page',
				'min' => 1,
				'javascript' => $javascript
			],
			['callback', 'frontpage_mode_settings_after'],
			['title', 'lp_standalone_mode_title'],
			['callback', 'standalone_mode_settings_before'],
			[
				'check',
				'lp_standalone_mode',
				'label' => $this->txt['lp_action_on'],
				'javascript' => '
					@change="standalone_mode = ! standalone_mode"
					:disabled="[\'0\', \'chosen_page\'].includes(frontpage_mode)"
				'
			],
			[
				'text',
				'lp_standalone_url',
				'help' => 'lp_standalone_url_help',
				'size' => '80" placeholder="' . $this->txt['lp_example'] . $this->boardurl . '/portal.php',
				'javascript' => ':disabled="! standalone_mode || [\'0\', \'chosen_page\'].includes(frontpage_mode)"'
			],
			['callback', 'standalone_mode_settings_after'],
			['title', 'edit_permissions'],
			['permissions', 'light_portal_view', 'help' => 'permissionhelp_light_portal_view'],
			['permissions', 'light_portal_manage_pages_own', 'help' => 'permissionhelp_light_portal_manage_pages_own'],
			['permissions', 'light_portal_manage_pages_any', 'help' => 'permissionhelp_light_portal_manage_pages'],
			['permissions', 'light_portal_approve_pages', 'help' => 'permissionhelp_light_portal_approve_pages'],
		];

		$this->loadTemplate('LightPortal/ManageSettings');

		// Save
		if ($this->request()->has('save')) {
			$this->checkSession();

			if ($this->request()->isNotEmpty('lp_image_placeholder'))
				$this->post()->put('lp_image_placeholder', $this->filterVar($this->request('lp_image_placeholder'), 'url'));

			if ($this->request()->isNotEmpty('lp_standalone_url'))
				$this->post()->put('lp_standalone_url', $this->filterVar($this->request('lp_standalone_url'), 'url'));

			$save_vars = $config_vars;

			if ($this->request()->has('lp_frontpage_alias'))
				$save_vars[] = ['text', 'lp_frontpage_alias'];

			if ($this->request()->isNotEmpty('lp_frontpage_mode') && $this->request()->hasNot('lp_frontpage_alias')) {
				$save_vars[] = ['text', 'lp_frontpage_categories'];
				$save_vars[] = ['text', 'lp_frontpage_boards'];
				$save_vars[] = ['text', 'lp_frontpage_pages'];
				$save_vars[] = ['text', 'lp_frontpage_topics'];
			}

			$save_vars[] = ['text', 'lp_disabled_actions'];

			$this->saveDBSettings($save_vars);
			$this->session()->put('adm-save', true);
			$this->cache()->flush();

			$this->redirect('action=admin;area=lp_settings;sa=basic');
		}

		$this->prepareDBSettingContext($config_vars);
	}
}
