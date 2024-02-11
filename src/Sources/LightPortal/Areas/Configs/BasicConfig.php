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
 * @version 2.5
 */

namespace Bugo\LightPortal\Areas\Configs;

use Bugo\Compat\{ACP, Config, Lang, Theme, User, Utils};
use Bugo\LightPortal\Areas\Partials\{ActionSelect, BoardSelect, CategorySelect};
use Bugo\LightPortal\Areas\Partials\{PageAliasSelect, PageSelect, TopicSelect};
use Bugo\LightPortal\Areas\Query;
use Bugo\LightPortal\Actions\FrontPage;
use IntlException;

if (! defined('SMF'))
	die('No direct access...');

final class BasicConfig extends AbstractConfig
{
	use Query;

	/**
	 * Output general settings
	 *
	 * Выводим общие настройки
	 * @throws IntlException
	 */
	public function show(): void
	{
		Utils::$context['page_title']    = Utils::$context['settings_title'] = Lang::$txt['lp_base'];
		Utils::$context['canonical_url'] = Config::$scripturl . '?action=admin;area=lp_settings;sa=basic';
		Utils::$context['post_url']      = Utils::$context['canonical_url'] . ';save';

		$this->addDefaultValues([
			'lp_frontpage_title'           => str_replace(["'", "\""], "", Utils::$context['forum_name']),
			'lp_show_views_and_comments'   => 1,
			'lp_frontpage_article_sorting' => 1,
			'lp_num_items_per_page'        => 10,
			'lp_standalone_url'            => Config::$boardurl . '/portal.php',
		]);

		Utils::$context['lp_frontpage_modes'] = array_combine(
			[0, 'chosen_page', 'all_pages', 'chosen_pages', 'all_topics', 'chosen_topics', 'chosen_boards'],
			Lang::$txt['lp_frontpage_mode_set']
		);

		$this->prepareTopicList();

		Utils::$context['lp_column_set'] = array_map(
			fn($item) => Lang::getTxt('lp_frontpage_num_columns_set', ['columns' => $item]),
			[1, 2, 3, 4, 6]
		);

		Utils::$context['lp_frontpage_layouts']           = (new FrontPage)->getLayouts();
		Utils::$context['lp_frontpage_alias_select']      = new PageAliasSelect;
		Utils::$context['lp_frontpage_categories_select'] = new CategorySelect;
		Utils::$context['lp_frontpage_boards_select']     = new BoardSelect;
		Utils::$context['lp_frontpage_topics_select']     = new TopicSelect;
		Utils::$context['lp_frontpage_pages_select']      = new PageSelect;
		Utils::$context['lp_disabled_actions_select']     = new ActionSelect;

		$javascript = ':disabled="[\'0\', \'chosen_page\'].includes(frontpage_mode)"';

		$config_vars = [
			['callback', 'frontpage_mode_settings_before'],
			[
				'select',
				'lp_frontpage_mode',
				Utils::$context['lp_frontpage_modes'],
				'javascript' => '
					@change="frontpage_mode = $event.target.value; $dispatch(\'change-mode\', {front: frontpage_mode})"
				'
			],
			[
				'text',
				'lp_frontpage_title',
				'size' => '80" placeholder="' . str_replace(
					["'", "\""], "", Utils::$context['forum_name']
				) . ' - ' . Lang::$txt['lp_portal'],
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
				'size' => '80" placeholder="' . Lang::$txt['lp_example'] . Theme::$current->settings['default_images_url'] . '/smflogo.svg',
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
				Lang::$txt['lp_frontpage_article_sorting_set'],
				'help' => 'lp_frontpage_article_sorting_help',
				'javascript' => $javascript
			],
			[
				'select',
				'lp_frontpage_layout',
				Utils::$context['lp_frontpage_layouts'],
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
				Utils::$context['lp_column_set'],
				'javascript' => $javascript
			],
			[
				'select',
				'lp_show_pagination',
				Lang::$txt['lp_show_pagination_set'],
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
				'label' => Lang::$txt['lp_action_on'],
				'javascript' => '
					@change="standalone_mode = ! standalone_mode"
					:disabled="[\'0\', \'chosen_page\'].includes(frontpage_mode)"
				'
			],
			[
				'text',
				'lp_standalone_url',
				'help' => 'lp_standalone_url_help',
				'size' => '80" placeholder="' . Lang::$txt['lp_example'] . Config::$boardurl . '/portal.php',
				'javascript' => ':disabled="! standalone_mode || [\'0\', \'chosen_page\'].includes(frontpage_mode)"'
			],
			['callback', 'standalone_mode_settings_after'],
			['title', 'edit_permissions'],
			['permissions', 'light_portal_view', 'help' => 'permissionhelp_light_portal_view'],
			['permissions', 'light_portal_manage_pages_own', 'help' => 'permissionhelp_light_portal_manage_pages_own'],
			['permissions', 'light_portal_manage_pages_any', 'help' => 'permissionhelp_light_portal_manage_pages'],
			['permissions', 'light_portal_approve_pages', 'help' => 'permissionhelp_light_portal_approve_pages'],
		];

		Theme::loadTemplate('LightPortal/ManageSettings');

		// Save
		if ($this->request()->has('save')) {
			User::$me->checkSession();

			if ($this->request()->isNotEmpty('lp_image_placeholder')) {
				$this->post()->put(
					'lp_image_placeholder', $this->filterVar($this->request('lp_image_placeholder'), 'url')
				);
			}

			if ($this->request()->isNotEmpty('lp_standalone_url')) {
				$this->post()->put(
					'lp_standalone_url', $this->filterVar($this->request('lp_standalone_url'), 'url')
				);
			}

			$save_vars = $config_vars;

			$save_vars[] = ['text', 'lp_frontpage_alias'];
			$save_vars[] = ['text', 'lp_frontpage_categories'];
			$save_vars[] = ['text', 'lp_frontpage_boards'];
			$save_vars[] = ['text', 'lp_frontpage_pages'];
			$save_vars[] = ['text', 'lp_frontpage_topics'];
			$save_vars[] = ['text', 'lp_disabled_actions'];

			ACP::saveDBSettings($save_vars);
			$this->session()->put('adm-save', true);
			$this->cache()->flush();

			Utils::redirectexit('action=admin;area=lp_settings;sa=basic');
		}

		ACP::prepareDBSettingContext($config_vars);
	}
}
