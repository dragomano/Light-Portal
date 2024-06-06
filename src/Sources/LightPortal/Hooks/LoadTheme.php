<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.6
 */

namespace Bugo\LightPortal\Hooks;

use Bugo\Compat\Config;
use Bugo\Compat\Lang;
use Bugo\Compat\Theme;
use Bugo\Compat\User;
use Bugo\Compat\Utils;
use Bugo\LightPortal\Actions\Block;
use Bugo\LightPortal\AddonHandler;
use Bugo\LightPortal\Compilers\CompilerInterface;
use Bugo\LightPortal\Enums\ContentClass;
use Bugo\LightPortal\Enums\ContentType;
use Bugo\LightPortal\Enums\Placement;
use Bugo\LightPortal\Enums\PluginType;
use Bugo\LightPortal\Enums\TitleClass;
use Bugo\LightPortal\Utils\SessionManager;

use function array_combine;
use function array_map;
use function dirname;
use function explode;

if (! defined('SMF'))
	die('No direct access...');

class LoadTheme
{
	use CommonChecks;

	private array $config;

	public function __construct()
	{
		$this->config = require_once dirname(__DIR__) . '/config/config.php';
	}

	public function __invoke(): void
	{
		if ($this->isPortalCanBeLoaded() === false)
			return;

		Lang::load('LightPortal/LightPortal');

		$this->defineVars();

		$this->loadAssets(new $this->config[CompilerInterface::class]);

		// Run all init methods for plugins
		AddonHandler::getInstance()->run();
	}

	protected function defineVars(): void
	{
		Utils::$context['allow_light_portal_view']             = User::hasPermission('light_portal_view');
		Utils::$context['allow_light_portal_manage_pages_own'] = User::hasPermission('light_portal_manage_pages_own');
		Utils::$context['allow_light_portal_manage_pages_any'] = User::hasPermission('light_portal_manage_pages_any');
		Utils::$context['allow_light_portal_approve_pages']    = User::hasPermission('light_portal_approve_pages');

		$this->calculateNumberOfEntities();

		Utils::$context['lp_all_title_classes']   = TitleClass::values();
		Utils::$context['lp_all_content_classes'] = ContentClass::values();
		Utils::$context['lp_block_placements']    = Placement::all();
		Utils::$context['lp_plugin_types']        = PluginType::all();
		Utils::$context['lp_content_types']       = ContentType::all();

		Utils::$context['lp_enabled_plugins'] = empty(Config::$modSettings['lp_enabled_plugins'])
			? [] : explode(',', (string) Config::$modSettings['lp_enabled_plugins']);

		Utils::$context['lp_frontpage_pages'] = empty(Config::$modSettings['lp_frontpage_pages'])
			? [] : explode(',', (string) Config::$modSettings['lp_frontpage_pages']);

		Utils::$context['lp_frontpage_topics'] = empty(Config::$modSettings['lp_frontpage_topics'])
			? [] : explode(',', (string) Config::$modSettings['lp_frontpage_topics']);

		Utils::$context['lp_header_panel_width'] = empty(Config::$modSettings['lp_header_panel_width'])
			? 12 : (int) Config::$modSettings['lp_header_panel_width'];

		Utils::$context['lp_left_panel_width'] = empty(Config::$modSettings['lp_left_panel_width'])
			? ['lg' => 3, 'xl' => 2]
			: Utils::jsonDecode(Config::$modSettings['lp_left_panel_width'], true);

		Utils::$context['lp_right_panel_width'] = empty(Config::$modSettings['lp_right_panel_width'])
			? ['lg' => 3, 'xl' => 2]
			: Utils::jsonDecode(Config::$modSettings['lp_right_panel_width'], true);

		Utils::$context['lp_footer_panel_width'] = empty(Config::$modSettings['lp_footer_panel_width'])
			? 12 : (int) Config::$modSettings['lp_footer_panel_width'];

		Utils::$context['lp_swap_left_right'] = empty(Lang::$txt['lang_rtl'])
			? ! empty(Config::$modSettings['lp_swap_left_right'])
			: empty(Config::$modSettings['lp_swap_left_right']);

		Utils::$context['lp_panel_direction'] = Utils::jsonDecode(
			Config::$modSettings['lp_panel_direction'] ?? '', true
		);

		Utils::$context['lp_active_blocks'] = (new Block())->getActive();
	}

	protected function loadAssets(CompilerInterface $compiler): void
	{
		$this->loadFontAwesome();

		$compiler->compile();

		Theme::loadCSSFile('light_portal/flexboxgrid.css');
		Theme::loadCSSFile('light_portal/portal.css');
		Theme::loadCSSFile('light_portal/plugins.css');
		Theme::loadCSSFile('portal_custom.css');

		Theme::loadJavaScriptFile('light_portal/plugins.js', ['minimize' => true]);
	}

	protected function loadFontAwesome(): void
	{
		if (empty(Config::$modSettings['lp_fa_source']) || Config::$modSettings['lp_fa_source'] === 'none')
			return;

		if (Config::$modSettings['lp_fa_source'] === 'css_local') {
			Theme::loadCSSFile('all.min.css', [], 'portal_fontawesome');
		} elseif (Config::$modSettings['lp_fa_source'] === 'custom' && isset(Config::$modSettings['lp_fa_custom'])) {
			Theme::loadCSSFile(
				Config::$modSettings['lp_fa_custom'],
				[
					'external' => true,
					'seed'     => false,
				],
				'portal_fontawesome'
			);
		} elseif (isset(Config::$modSettings['lp_fa_kit'])) {
			Theme::loadJavaScriptFile(
				Config::$modSettings['lp_fa_kit'],
				[
					'attributes' => ['crossorigin' => 'anonymous'],
					'external'   => true,
				]
			);
		}
	}

	private function calculateNumberOfEntities(): void
	{
		$sessionManager = new SessionManager();

		$entities = [
			'active_blocks', 'active_pages', 'my_pages', 'unapproved_pages',
			'internal_pages', 'active_categories', 'active_tags',
		];

		Utils::$context['lp_quantities'] = array_map(
			static fn($key) => $sessionManager($key), array_combine($entities, $entities)
		);
	}
}
