<?php declare(strict_types=1);

/**
 * BasicConfig.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2023 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.3
 */

namespace Bugo\LightPortal\Areas\Config;

use Bugo\LightPortal\Helper;
use Bugo\LightPortal\Areas\Query;
use Bugo\LightPortal\Entities\FrontPage;
use Bugo\LightPortal\Partials\{
	ActionSelect,
	BoardSelect,
	CategorySelect,
	PageAliasSelect,
	PageSelect,
	TopicSelect
};
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

		$this->context['permissions_excluded']['light_portal_manage_pages_own'] = [-1, 0];
		$this->context['permissions_excluded']['light_portal_manage_pages_any'] = [-1, 0];
		$this->context['permissions_excluded']['light_portal_approve_pages']    = [-1, 0];

		// Initial settings
		$addSettings = [];
		if (! isset($this->modSettings['lp_frontpage_title']))
			$addSettings['lp_frontpage_title'] = str_replace(["'", "\""], "", $this->context['forum_name']);
		if (! isset($this->modSettings['lp_show_views_and_comments']))
			$addSettings['lp_show_views_and_comments'] = 1;
		if (! isset($this->modSettings['lp_frontpage_article_sorting']))
			$addSettings['lp_frontpage_article_sorting'] = 1;
		if (! isset($this->modSettings['lp_num_items_per_page']))
			$addSettings['lp_num_items_per_page'] = 10;
		if (! isset($this->modSettings['lp_standalone_url']))
			$addSettings['lp_standalone_url'] = $this->boardurl . '/portal.php';
		$this->updateSettings($addSettings);

		$this->context['lp_frontpage_modes'] = array_combine(
			[0, 'chosen_page', 'all_pages', 'chosen_pages', 'all_topics', 'chosen_topics', 'chosen_boards'],
			$this->txt['lp_frontpage_mode_set']
		);

		$this->prepareTopicList();

		$this->context['lp_column_set'] = array_map(fn($item) => $this->translate('lp_frontpage_num_columns_set', ['columns' => $item]), [1, 2, 3, 4, 6]);

		$this->context['lp_frontpage_layouts'] = (new FrontPage)->getLayouts();

		$this->context['lp_frontpage_alias_select'] = (new PageAliasSelect)();

		$this->context['lp_frontpage_categories_select'] = (new CategorySelect)();

		$this->context['lp_frontpage_boards_select'] = (new BoardSelect)();

		$this->context['lp_frontpage_topics_select'] = (new TopicSelect)();

		$this->context['lp_frontpage_pages_select'] = (new PageSelect)();

		$this->context['lp_disabled_actions_select'] = (new ActionSelect)();

		$config_vars = [
			['callback', 'frontpage_mode_settings'],
			['title', 'lp_standalone_mode_title'],
			['callback', 'standalone_mode_settings'],
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
				$this->post()->put('lp_image_placeholder', $this->validate($this->request('lp_image_placeholder'), 'url'));

			if ($this->request()->isNotEmpty('lp_standalone_url'))
				$this->post()->put('lp_standalone_url', $this->validate($this->request('lp_standalone_url'), 'url'));

			$save_vars = $config_vars;
			$save_vars[] = ['text', 'lp_frontpage_mode'];

			if ($this->request()->has('lp_frontpage_alias'))
				$save_vars[] = ['text', 'lp_frontpage_alias'];

			if ($this->request()->isNotEmpty('lp_frontpage_mode') && $this->request()->hasNot('lp_frontpage_alias')) {
				$save_vars[] = ['text', 'lp_frontpage_title'];
				$save_vars[] = ['text', 'lp_frontpage_categories'];
				$save_vars[] = ['text', 'lp_frontpage_boards'];
				$save_vars[] = ['text', 'lp_frontpage_pages'];
				$save_vars[] = ['text', 'lp_frontpage_topics'];
				$save_vars[] = ['check', 'lp_show_images_in_articles'];
				$save_vars[] = ['text', 'lp_image_placeholder'];
				$save_vars[] = ['check', 'lp_show_teaser'];
				$save_vars[] = ['check', 'lp_show_author'];
				$save_vars[] = ['check', 'lp_show_views_and_comments'];
				$save_vars[] = ['check', 'lp_frontpage_order_by_replies'];
				$save_vars[] = ['int', 'lp_frontpage_article_sorting'];
				$save_vars[] = ['text', 'lp_frontpage_layout'];
				$save_vars[] = ['int', 'lp_frontpage_num_columns'];
				$save_vars[] = ['int', 'lp_show_pagination'];
				$save_vars[] = ['check', 'lp_use_simple_pagination'];
				$save_vars[] = ['int', 'lp_num_items_per_page'];
			}

			$save_vars[] = ['check', 'lp_standalone_mode'];
			$save_vars[] = ['text', 'lp_standalone_url'];
			$save_vars[] = ['text', 'lp_disabled_actions'];

			$this->saveDBSettings($save_vars);

			$this->session()->put('adm-save', true);
			$this->cache()->flush();

			$this->redirect('action=admin;area=lp_settings;sa=basic');
		}

		$this->prepareDBSettingContext($config_vars);
	}
}
