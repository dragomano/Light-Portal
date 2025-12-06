<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 3.0
 */

namespace LightPortal\Areas\Configs;

use Bugo\Bricks\Settings\CallbackConfig;
use Bugo\Bricks\Settings\CheckConfig;
use Bugo\Bricks\Settings\ConfigBuilder;
use Bugo\Bricks\Settings\IntConfig;
use Bugo\Bricks\Settings\PermissionsConfig;
use Bugo\Bricks\Settings\SelectConfig;
use Bugo\Bricks\Settings\TextConfig;
use Bugo\Compat\{Config, Lang, Theme};
use Bugo\Compat\{Time, User, Utils};
use Bugo\Compat\Actions\Admin\ACP;
use Bugo\Compat\WebFetch\WebFetchApi;
use LightPortal\Enums\FrontPageMode;
use LightPortal\Enums\PortalHook;
use LightPortal\Events\EventDispatcherInterface;
use LightPortal\Renderers\RendererInterface;
use LightPortal\UI\TemplateLoader;
use LightPortal\UI\Partials\SelectFactory;
use LightPortal\Utils\InputFilter;
use LightPortal\Utils\Str;

use function LightPortal\app;

use const LP_VERSION;

if (! defined('SMF'))
	die('No direct access...');

final class BasicConfig extends AbstractConfig
{
	public const TAB_BASE = 'base';

	public const TAB_CARDS = 'cards';

	public const TAB_STANDALONE = 'standalone';

	public const TAB_PERMISSIONS = 'permissions';

	public function __construct(
		private readonly EventDispatcherInterface $dispatcher,
		private readonly InputFilter $inputFilter
	) {}

	public function show(): void
	{
		Utils::$context['page_title']  = Utils::$context['settings_title'] = Lang::$txt['lp_base'];
		Utils::$context['form_action'] = Config::$scripturl . '?action=admin;area=lp_settings;sa=basic';
		Utils::$context['post_url']    = Utils::$context['form_action'] . ';save';

		$this->showInfoAboutNewRelease();

		$this->addDefaultValues([
			'lp_frontpage_title'           => str_replace(["'", "\""], "", (string) Utils::$context['forum_name']),
			'lp_show_views_and_comments'   => 1,
			'lp_frontpage_article_sorting' => 'created;desc',
			'lp_num_items_per_page'        => 10,
			'lp_standalone_url'            => Config::$boardurl . '/portal.php',
		]);

		$vars = ConfigBuilder::make()->addVars([
			...$this->getBaseTabSettings(),
			...$this->getCardsTabSettings(),
			...$this->getStandaloneTabSettings(),
			...$this->getPermissionsTabSettings(),
		]);

		$configVars = $vars->build();

		$this->dispatcher->dispatch(PortalHook::extendBasicConfig, ['configVars' => &$configVars]);

		// Save
		if ($this->request()->has('save')) {
			User::$me->checkSession();

			$specialSettings = $this->inputFilter->filter([
				['url', 'lp_image_placeholder'],
				['url', 'lp_standalone_url'],
			]);

			foreach ($specialSettings as $key => $value) {
				if ($value !== false) {
					$this->post()->put($key, $value);
				} else {
					$this->post()->put($key, '');
				}
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

			$this->response()->redirect('action=admin;area=lp_settings;sa=basic');
		}

		ACP::prepareDBSettingContext($configVars);

		$this->prepareConfigFields($configVars);

		TemplateLoader::fromFile('admin/basic_settings');
	}

	private function isNewVersionAvailable(): array|bool
	{
		$cacheTTL = 7 * 24 * 60 * 60;

		if (($xml = $this->cache()->get('repo_data', $cacheTTL)) === null) {
			$repoData = WebFetchApi::fetch('https://api.github.com/repos/dragomano/Light-Portal/releases/latest');

			$xml = empty($repoData) ? [] : Utils::jsonDecode($repoData, true);

			$this->cache()->put('repo_data', $xml, $cacheTTL);
		}

		if (empty($xml) || empty($xml['name'])) {
			return false;
		}

		$currentVersion = preg_replace('/^(\d+)\.(\d+)(?!\.)/', '$1.$2.0', LP_VERSION);

		if (version_compare('v' . $currentVersion, $xml['name'], '<')) {
			return $xml;
		}

		return false;
	}

	private function showInfoAboutNewRelease(): void
	{
		if ($info = $this->isNewVersionAvailable()) {
			Utils::$context['settings_message'] = [
				'tag'   => 'div',
				'class' => 'errorbox',
				'label' => Lang::getTxt('lp_new_version', [
					$info['tag_name'],
					Time::stringFromUnix(strtotime($info['published_at'])),
				]),
			];
		}
	}

	private function getBaseTabSettings(): array
	{
		$this->prepareTopicList();

		return [
			SelectConfig::make('lp_frontpage_mode')
				->setOptions(FrontPageMode::getSelectOptions())
				->setAttribute('@change', '$dispatch(\'change-mode\', { front: $event.target.value })')
				->setTab(self::TAB_BASE),
			CallbackConfig::make('frontpage_mode_settings')
				->setTab(self::TAB_BASE),
			TextConfig::make('lp_frontpage_title')
				->setPlaceholder($this->getDefaultTitle())
				->setTab(self::TAB_BASE),
			SelectConfig::make('lp_frontpage_article_sorting')
				->setOptions(Lang::$txt['lp_frontpage_article_sorting_set'])
				->setTab(self::TAB_BASE),
			CheckConfig::make('lp_show_layout_switcher')
				->setTab(self::TAB_BASE),
			CheckConfig::make('lp_show_sort_dropdown')
				->setTab(self::TAB_BASE),
			SelectConfig::make('lp_frontpage_num_columns')
				->setOptions($this->getColumnsOptions())
				->setTab(self::TAB_BASE),
			SelectConfig::make('lp_show_pagination')
				->setOptions(Lang::$txt['lp_show_pagination_set'])
				->setTab(self::TAB_BASE),
			CheckConfig::make('lp_use_simple_pagination')
				->setTab(self::TAB_BASE),
			IntConfig::make('lp_num_items_per_page')
				->setMin(1)
				->setTab(self::TAB_BASE),
		];
	}

	private function getCardsTabSettings(): array
	{

		return [
			CheckConfig::make('lp_show_images_in_articles')
				->setTab(self::TAB_CARDS),
			TextConfig::make('lp_image_placeholder')
				->setPlaceholder($this->getImagePlaceholder())
				->setTab(self::TAB_CARDS),
			CheckConfig::make('lp_show_teaser')
				->setTab(self::TAB_CARDS),
			CheckConfig::make('lp_show_author')
				->setHelp('lp_show_author_help')
				->setTab(self::TAB_CARDS),
			CheckConfig::make('lp_show_views_and_comments')
				->setTab(self::TAB_CARDS),
			SelectConfig::make('lp_frontpage_layout')
				->setOptions(app(RendererInterface::class)->getLayouts())
				->setPostInput($this->getTemplateEditLink())
				->setTab(self::TAB_CARDS),
		];
	}

	private function getStandaloneTabSettings(): array
	{
		Lang::$txt['lp_standalone_url_help'] = Lang::getTxt('lp_standalone_url_help', [
			Config::$boardurl . '/portal.php',
			Config::$scripturl,
		]);

		return [
			CheckConfig::make('lp_standalone_mode')
				->setLabel(Lang::$txt['lp_action_on'])
				->setTab(self::TAB_STANDALONE),
			TextConfig::make('lp_standalone_url')
				->setHelp('lp_standalone_url_help')
				->setPlaceholder(Lang::$txt['lp_example'] . Config::$boardurl . '/portal.php')
				->setTab(self::TAB_STANDALONE),
			CallbackConfig::make('standalone_mode_settings_after')
				->setLabel(Lang::$txt['lp_disabled_actions'])
				->setHelp('lp_disabled_actions_help')
				->setCallback(SelectFactory::action(...))
				->setTab(self::TAB_STANDALONE),
		];
	}

	private function getPermissionsTabSettings(): array
	{
		$permissions = [
			'light_portal_view'             => 'groups_light_portal_view',
			'light_portal_manage_pages_own' => 'groups_light_portal_manage_pages_own',
			'light_portal_manage_pages_any' => 'groups_light_portal_manage_pages_any',
			'light_portal_approve_pages'    => 'groups_light_portal_approve_pages',
		];

		return array_map(
			fn($key, $group) => PermissionsConfig::make($key)
				->setPostInput('<small class="floatright">' . Lang::$txt[$group] . '</small>')
				->setHelp('permissionhelp_' . $key)
				->setTab(self::TAB_PERMISSIONS),
			array_keys($permissions),
			$permissions
		);
	}

	private function getDefaultTitle(): string
	{
		return str_replace(["'", "\""], "", (string) Utils::$context['forum_name'])
			. ' - ' . Lang::$txt['lp_portal'];
	}

	private function getColumnsOptions(): array
	{
		return array_map(
			static fn($item) => Lang::getTxt('lp_frontpage_num_columns_set', ['columns' => $item]),
			[1, 2, 3, 4, 6]
		);
	}

	private function getImagePlaceholder(): string
	{
		return implode('', [
			Lang::$txt['lp_example'],
			Theme::$current->settings['default_images_url'],
			'/smflogo.svg',
		]);
	}

	private function getTemplateEditLink(): string
	{
		$a = Str::html('a', [
			'class'  => 'button active',
			'target' => '_blank',
			'href'   => '%s?action=admin;area=theme;th=1;%s=%s;sa=edit;directory=LightPortal/layouts',
		]);

		return sprintf('&nbsp;' . $a->setText(Lang::$txt['lp_template_edit_link']),
			Config::$scripturl,
			Utils::$context['session_var'],
			Utils::$context['session_id'],
		);
	}
}
