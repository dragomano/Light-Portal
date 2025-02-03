<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.9
 */

namespace Bugo\LightPortal\Hooks;

use Bugo\Compat\Config;
use Bugo\Compat\Lang;
use Bugo\Compat\Theme;
use Bugo\Compat\User;
use Bugo\Compat\Utils;
use Bugo\LightPortal\Enums\ContentClass;
use Bugo\LightPortal\Enums\ContentType;
use Bugo\LightPortal\Enums\EntryType;
use Bugo\LightPortal\Enums\Placement;
use Bugo\LightPortal\Enums\PluginType;
use Bugo\LightPortal\Enums\PortalHook;
use Bugo\LightPortal\Enums\TitleClass;
use Bugo\LightPortal\Events\EventManagerFactory;
use Bugo\LightPortal\Repositories\BlockRepository;
use Bugo\LightPortal\Utils\RequestTrait;
use Bugo\LightPortal\Utils\SessionManager;

if (! defined('SMF'))
	die('No direct access...');

class LoadTheme
{
	use CommonChecks;
	use RequestTrait;

	public function __invoke(): void
	{
		if ($this->isPortalCanBeLoaded() === false)
			return;

		Lang::load('LightPortal/LightPortal');

		$this->defineVars();
		$this->loadAssets();

		// Run all init methods for active plugins
		app(EventManagerFactory::class)()->dispatch(PortalHook::init);
	}

	protected function defineVars(): void
	{
		Utils::$context['allow_light_portal_view']             = User::hasPermission('light_portal_view');
		Utils::$context['allow_light_portal_manage_pages_own'] = User::hasPermission('light_portal_manage_pages_own');
		Utils::$context['allow_light_portal_manage_pages_any'] = User::hasPermission('light_portal_manage_pages_any');
		Utils::$context['allow_light_portal_approve_pages']    = User::hasPermission('light_portal_approve_pages');

		Utils::$context['lp_quantities'] = app(SessionManager::class);

		Utils::$context['lp_all_title_classes'] = TitleClass::values();
		Utils::$context['lp_all_content_classes'] = ContentClass::values();

		Utils::$context['lp_block_placements'] = Placement::all();

		Utils::$context['lp_plugin_types'] = PluginType::all();
		Utils::$context['lp_content_types'] = ContentType::all();
		Utils::$context['lp_page_types'] = EntryType::all();

		Utils::$context['lp_active_blocks'] = app(BlockRepository::class)->getActive();
	}

	protected function loadAssets(): void
	{
		$this->loadFontAwesome();

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
}
