<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.7
 */

namespace Bugo\LightPortal\Hooks;

use Bugo\Compat\Config;
use Bugo\Compat\Utils;
use Bugo\LightPortal\Enums\PortalHook;
use Bugo\LightPortal\EventManager;
use Bugo\LightPortal\Plugins\Event;
use Nette\Utils\Html;

if (! defined('SMF'))
	die('No direct access...');

class PreCssOutput
{
	public function __invoke(): void
	{
		echo "\n\t" . Html::el('link')
			->rel('preconnect')
			->href('//cdn.jsdelivr.net')
			->toHtml();

		if (! empty(Utils::$context['portal_next_page'])) {
			echo "\n\t" . Html::el('link')
				->rel('prerender')
				->href(Utils::$context['portal_next_page'])
				->toHtml();
		}

		$styles = [];

		if (empty(Config::$modSettings['lp_fa_source']) || Config::$modSettings['lp_fa_source'] === 'css_cdn') {
			$styles[] = 'https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6/css/all.min.css';
		}

		EventManager::getInstance()->dispatch(
			PortalHook::preloadStyles,
			new Event(new class ($styles) {
				public function __construct(public array &$styles) {}
			})
		);

		foreach ($styles as $style) {
			echo "\n\t" . Html::el('link', [
				'rel'    => 'preload',
				'href'   => $style,
				'as'     => 'style',
				'onload' => "this.onload=null;this.rel='stylesheet'",
			])->toHtml();
		}
	}
}
