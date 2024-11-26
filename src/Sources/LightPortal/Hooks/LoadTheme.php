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

namespace Bugo\LightPortal\Hooks;

use Bugo\Compat\{Config, Lang, Theme};
use Bugo\Compat\{User, Utils};
use Bugo\LightPortal\Compilers\CompilerInterface;
use Bugo\LightPortal\Enums\{ContentClass, ContentType, EntryType};
use Bugo\LightPortal\Enums\{Placement, PluginType, PortalHook, TitleClass};
use Bugo\LightPortal\EventManagerFactory;
use Bugo\LightPortal\Repositories\BlockRepository;
use Bugo\LightPortal\Utils\RequestTrait;
use Bugo\LightPortal\Utils\SessionManager;

use function array_combine;
use function array_map;
use function dirname;

if (! defined('SMF'))
	die('No direct access...');

class LoadTheme
{
	use CommonChecks;
	use RequestTrait;

	private array $config;

	public function __construct()
	{
		$this->config = require dirname(__DIR__) . '/Settings/config.php';
	}

	public function __invoke(): void
	{
		if ($this->isPortalCanBeLoaded() === false)
			return;

		Lang::load('LightPortal/LightPortal');

		$this->defineVars();

		$this->loadAssets(new $this->config[CompilerInterface::class]);

		// Run all init methods for active plugins
		(new EventManagerFactory())()->dispatch(PortalHook::init);
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
		Utils::$context['lp_page_types']          = EntryType::all();

		Utils::$context['lp_active_blocks'] = (new BlockRepository())->getActive();
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
			'deleted_pages', 'active_categories', 'active_tags',
		];

		Utils::$context['lp_quantities'] = array_map(
			static fn($key) => $sessionManager($key), array_combine($entities, $entities)
		);
	}
}
