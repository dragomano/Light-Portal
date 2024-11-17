<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.8
 */

namespace Bugo\LightPortal\Areas\Configs;

use Bugo\Compat\{Actions\ACP, Config, Lang, Theme};
use Bugo\Compat\{Time, User, Utils, WebFetchApi};
use Bugo\LightPortal\Actions\FrontPage;
use Bugo\LightPortal\Areas\Partials\ActionSelect;
use Bugo\LightPortal\Areas\Traits\QueryTrait;
use Bugo\LightPortal\Enums\{PortalHook, VarType};
use Bugo\LightPortal\EventManager;
use Bugo\LightPortal\Plugins\Event;
use Bugo\LightPortal\Utils\CacheTrait;
use Bugo\LightPortal\Utils\RequestTrait;
use Bugo\LightPortal\Utils\SessionTrait;
use Bugo\LightPortal\Utils\Str;

use function array_combine;
use function array_map;
use function sprintf;
use function str_replace;
use function strtotime;
use function version_compare;

use const LP_VERSION;

if (! defined('SMF'))
	die('No direct access...');

final class BasicConfig extends AbstractConfig
{
	use CacheTrait;
	use QueryTrait;
	use RequestTrait;
	use SessionTrait;

	public const TAB_BASE = 'base';

	public const TAB_CARDS = 'cards';

	public const TAB_STANDALONE = 'standalone';

	public const TAB_PERMISSIONS = 'permissions';

	public function show(): void
	{
		Utils::$context['page_title']  = Utils::$context['settings_title'] = Lang::$txt['lp_base'];
		Utils::$context['form_action'] = Config::$scripturl . '?action=admin;area=lp_settings;sa=basic';
		Utils::$context['post_url']    = Utils::$context['form_action'] . ';save';

		$this->showInfoAboutNewRelease();

		$this->addDefaultValues([
			'lp_frontpage_title'           => str_replace(["'", "\""], "", (string) Utils::$context['forum_name']),
			'lp_show_views_and_comments'   => 1,
			'lp_frontpage_article_sorting' => 1,
			'lp_num_items_per_page'        => 10,
			'lp_standalone_url'            => Config::$boardurl . '/portal.php',
		]);

		Utils::$context['lp_frontpage_modes'] = array_combine(
			[0, 'chosen_page', 'all_pages', 'chosen_pages', 'all_topics', 'chosen_topics', 'chosen_boards'],
			Lang::$txt['lp_frontpage_mode_set'],
		);

		$this->prepareTopicList();

		Utils::$context['lp_column_set'] = array_map(
			static fn($item) => Lang::getTxt('lp_frontpage_num_columns_set', ['columns' => $item]),
			[1, 2, 3, 4, 6],
		);

		$templateEditLink = sprintf('&nbsp;' . Str::html('a', [
				'class' => 'button active',
				'target' => '_blank',
				'href' => '%s?action=admin;area=theme;th=1;%s=%s;sa=edit;directory=LightPortal/layouts',
			])->setText(Lang::$txt['lp_template_edit_link']),
			Config::$scripturl,
			Utils::$context['session_var'],
			Utils::$context['session_id'],
		);

		$configVars = [
			[
				'select',
				'lp_frontpage_mode',
				Utils::$context['lp_frontpage_modes'],
				'attributes' => [
					'@change' => '$dispatch(\'change-mode\', { front: $event.target.value })',
				],
				'tab' => self::TAB_BASE,
			],
			['callback', 'frontpage_mode_settings_middle', 'tab' => self::TAB_BASE],
			[
				'text',
				'lp_frontpage_title',
				'placeholder' => str_replace(
						["'", "\""], "", (string) Utils::$context['forum_name']
					) . ' - ' . Lang::$txt['lp_portal'],
				'tab' => self::TAB_BASE,
			],
			[
				'check',
				'lp_show_images_in_articles',
				'help' => 'lp_show_images_in_articles_help',
				'tab' => self::TAB_CARDS,
			],
			[
				'text',
				'lp_image_placeholder',
				'placeholder' => Lang::$txt['lp_example'] .
					Theme::$current->settings['default_images_url'] . '/smflogo.svg',
				'tab' => self::TAB_CARDS,
			],
			[
				'check',
				'lp_show_teaser',
				'tab' => self::TAB_CARDS,
			],
			[
				'check',
				'lp_show_author',
				'help' => 'lp_show_author_help',
				'tab' => self::TAB_CARDS,
			],
			[
				'check',
				'lp_show_views_and_comments',
				'tab' => self::TAB_CARDS,
			],
			[
				'check',
				'lp_frontpage_order_by_replies',
				'tab' => self::TAB_BASE,
			],
			[
				'select',
				'lp_frontpage_article_sorting',
				Lang::$txt['lp_frontpage_article_sorting_set'],
				'help' => 'lp_frontpage_article_sorting_help',
				'tab' => self::TAB_BASE,
			],
			[
				'select',
				'lp_frontpage_layout',
				(new FrontPage())->getLayouts(),
				'postinput' => $templateEditLink,
				'tab' => self::TAB_CARDS,
			],
			[
				'check',
				'lp_show_layout_switcher',
				'tab' => self::TAB_BASE,
			],
			[
				'select',
				'lp_frontpage_num_columns',
				Utils::$context['lp_column_set'],
				'tab' => self::TAB_BASE,
			],
			[
				'select',
				'lp_show_pagination',
				Lang::$txt['lp_show_pagination_set'],
				'tab' => self::TAB_BASE,
			],
			[
				'check',
				'lp_use_simple_pagination',
				'tab' => self::TAB_BASE,
			],
			[
				'int',
				'lp_num_items_per_page',
				'min' => 1,
				'tab' => self::TAB_BASE,
			],
			[
				'check',
				'lp_standalone_mode',
				'label' => Lang::$txt['lp_action_on'],
				'tab' => self::TAB_STANDALONE,
			],
			[
				'text',
				'lp_standalone_url',
				'help' => 'lp_standalone_url_help',
				'placeholder' => Lang::$txt['lp_example'] . Config::$boardurl . '/portal.php',
				'tab' => self::TAB_STANDALONE,
			],
			[
				'callback',
				'standalone_mode_settings_after',
				'label' => Lang::$txt['lp_disabled_actions'],
				'help' => 'lp_disabled_actions_help',
				'callback' => static fn() => new ActionSelect(),
				'tab' => self::TAB_STANDALONE
			],
			[
				'permissions',
				'light_portal_view',
				'help' => 'permissionhelp_light_portal_view',
				'tab' => self::TAB_PERMISSIONS,
			],
			[
				'permissions',
				'light_portal_manage_pages_own',
				'help' => 'permissionhelp_light_portal_manage_pages_own',
				'tab' => self::TAB_PERMISSIONS,
			],
			[
				'permissions',
				'light_portal_manage_pages_any',
				'help' => 'permissionhelp_light_portal_manage_pages',
				'tab' => self::TAB_PERMISSIONS,
			],
			[
				'permissions',
				'light_portal_approve_pages',
				'help' => 'permissionhelp_light_portal_approve_pages',
				'tab' => self::TAB_PERMISSIONS,
			],
		];

		Theme::loadTemplate('LightPortal/ManageSettings');

		Utils::$context['sub_template'] = 'portal_basic_settings';

		EventManager::getInstance()->dispatch(
			PortalHook::extendBasicConfig,
			new Event(new class ($configVars) {
				public function __construct(public array &$configVars) {}
			})
		);

		// Save
		if ($this->request()->has('save')) {
			User::$me->checkSession();

			if ($this->request()->isNotEmpty('lp_image_placeholder')) {
				$this->post()->put(
					'lp_image_placeholder', VarType::URL->filter($this->request('lp_image_placeholder'))
				);
			}

			if ($this->request()->isNotEmpty('lp_standalone_url')) {
				$this->post()->put(
					'lp_standalone_url', VarType::URL->filter($this->request('lp_standalone_url'))
				);
			}

			$saveVars = $configVars;

			$saveVars[] = ['text', 'lp_frontpage_chosen_page'];
			$saveVars[] = ['text', 'lp_frontpage_categories'];
			$saveVars[] = ['text', 'lp_frontpage_boards'];
			$saveVars[] = ['text', 'lp_frontpage_pages'];
			$saveVars[] = ['text', 'lp_frontpage_topics'];
			$saveVars[] = ['text', 'lp_disabled_actions'];

			ACP::saveDBSettings($saveVars);

			$this->session()->put('adm-save', true);
			$this->cache()->flush();

			Utils::redirectexit('action=admin;area=lp_settings;sa=basic');
		}

		ACP::prepareDBSettingContext($configVars);

		$this->prepareConfigFields($configVars);
	}

	private function isNewVersionAvailable(): array|bool
	{
		if (($xml = $this->cache()->get('repo_data', 3 * 24 * 60 * 60)) === null) {
			$repoData = WebFetchApi::fetch('https://api.github.com/repos/dragomano/Light-Portal/releases/latest');

			$xml = empty($repoData) ? [] : Utils::jsonDecode($repoData, true);

			$this->cache()->put('repo_data', $xml, 3 * 24 * 60 * 60);
		}

		if (empty($xml))
			return false;

		if (version_compare('v' . LP_VERSION, $xml['tag_name'], '<')) {
			return $xml;
		}

		return false;
	}

	private function showInfoAboutNewRelease(): void
	{
		if ($info = $this->isNewVersionAvailable()) {
			Utils::$context['settings_message'] = [
				'tag' => 'div',
				'class' => 'errorbox',
				'label' => Lang::getTxt('lp_new_version', [
					$info['tag_name'],
					Time::timeformat(strtotime($info['published_at']), false)
				]),
			];
		}
	}
}
