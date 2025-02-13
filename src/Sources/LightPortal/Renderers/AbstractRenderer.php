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

namespace Bugo\LightPortal\Renderers;

use Bugo\Compat\Lang;
use Bugo\Compat\Theme;
use Bugo\LightPortal\Enums\PortalHook;
use Bugo\LightPortal\Events\HasEvents;

use function array_combine;
use function array_merge;
use function basename;
use function glob;
use function str_replace;
use function strstr;
use function ucfirst;

abstract class AbstractRenderer implements RendererInterface
{
	use HasEvents;

	protected string $templateDir;

	protected string $customDir;

	public function __construct()
	{
		if (empty(Theme::$current->settings['default_theme_dir']))
			return;

		$this->templateDir = Theme::$current->settings['default_theme_dir'] . '/LightPortal/layouts';

		$this->customDir = Theme::$current->settings['default_theme_dir'] . '/portal_layouts';
	}

	public function getLayouts(): array
	{
		Theme::loadTemplate('LightPortal/ViewFrontPage');

		$layouts = $this->collectLayouts();

		$default = $layouts[static::DEFAULT_TEMPLATE];
		unset($layouts[static::DEFAULT_TEMPLATE]);

		return array_merge([static::DEFAULT_TEMPLATE => $default], $layouts);
	}

	private function collectLayouts(): array
	{
		$layouts = glob($this->templateDir . '/*' . static::DEFAULT_EXTENSION);

		$extensions = [static::DEFAULT_EXTENSION];

		// You can add custom extensions for layouts
		$this->events()->dispatch(PortalHook::layoutExtensions, ['extensions' => &$extensions]);

		foreach ($extensions as $extension) {
			$layouts = array_merge(
				$layouts,
				glob($this->customDir . '/*' . $extension)
			);
		}

		return $this->processLayouts($layouts);
	}

	private function processLayouts(array $layouts): array
	{
		$values = $titles = [];

		foreach ($layouts as $layout) {
			$values[] = $title = basename((string) $layout);

			$shortName = ucfirst(strstr($title, '.', true) ?: $title);

			$titles[] = $title === static::DEFAULT_TEMPLATE
				? Lang::$txt['lp_default']
				: str_replace('_', ' ', $shortName);
		}

		return array_combine($values, $titles);
	}
}
