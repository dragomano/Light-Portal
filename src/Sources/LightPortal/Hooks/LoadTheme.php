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

namespace LightPortal\Hooks;

use Bugo\Compat\Config;
use Bugo\Compat\Lang;
use Bugo\Compat\Theme;
use Bugo\Compat\Utils;
use LightPortal\Enums\ContentType;
use LightPortal\Enums\EntryType;
use LightPortal\Enums\Placement;
use LightPortal\Enums\PluginType;
use LightPortal\Enums\PortalHook;
use LightPortal\Lists\BlockList;
use LightPortal\Utils\SessionManager;
use LightPortal\Utils\Traits\HasRequest;

use function LightPortal\app;

if (! defined('SMF'))
	die('No direct access...');

class LoadTheme extends AbstractHook
{
	use HasCommonChecks;
	use HasRequest;

	public function __invoke(): void
	{
		if ($this->isPortalCanBeLoaded() === false)
			return;

		Lang::load('LightPortal/LightPortal');

		$this->defineVars();
		$this->loadAssets();

		// Run all init methods for active plugins
		$this->dispatcher->dispatch(PortalHook::init);
	}

	protected function defineVars(): void
	{
		Utils::$context['lp_quantities']    = app(SessionManager::class)();
		Utils::$context['lp_active_blocks'] = app(BlockList::class)();

		Utils::$context['lp_block_placements'] = Placement::all();
		Utils::$context['lp_plugin_types']     = PluginType::all();
		Utils::$context['lp_content_types']    = ContentType::all();
		Utils::$context['lp_page_types']       = EntryType::all();
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
