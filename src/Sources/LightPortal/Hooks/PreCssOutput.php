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
use Bugo\Compat\Utils;
use Bugo\LightPortal\Enums\PortalHook;
use Bugo\LightPortal\Events\HasEvents;
use Bugo\LightPortal\Utils\Str;

if (! defined('SMF'))
	die('No direct access...');

class PreCssOutput
{
	use HasEvents;

	public function __invoke(): void
	{
		if (isset(Utils::$context['uninstalling']))
			return;

		echo "\n\t" . Str::html('link')
			->rel('preconnect')
			->href('//cdn.jsdelivr.net');

		if (! empty(Utils::$context['portal_next_page'])) {
			echo "\n\t" . Str::html('link')
				->rel('prerender')
				->href(Utils::$context['portal_next_page']);
		}

		$styles = [];

		if (empty(Config::$modSettings['lp_fa_source']) || Config::$modSettings['lp_fa_source'] === 'css_cdn') {
			$styles[] = 'https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6/css/all.min.css';
		}

		$this->events()->dispatch(PortalHook::preloadStyles, ['styles' => &$styles]);

		foreach ($styles as $style) {
			echo "\n\t" . Str::html('link', [
				'rel'    => 'preload',
				'href'   => $style,
				'as'     => 'style',
				'onload' => "this.onload=null;this.rel='stylesheet'",
			]);
		}
	}
}
