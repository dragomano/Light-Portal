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

namespace LightPortal\Renderers;

use Bugo\Compat\Lang;
use Bugo\Compat\Theme;
use LightPortal\Enums\PortalHook;
use LightPortal\Events\EventDispatcherInterface;

use function LightPortal\app;

if (! defined('SMF'))
	die('No direct access...');

abstract class AbstractRenderer implements RendererInterface
{
	protected string $templateDir;

	protected string $customDir;

	public function __construct(protected ?EventDispatcherInterface $dispatcher = null)
	{
		Theme::loadEssential();

		$this->dispatcher = $dispatcher ?: app(EventDispatcherInterface::class);

		$path = Theme::$current->settings['default_theme_dir'];

		$this->setTemplateDir($path . '/LightPortal/layouts');
		$this->setCustomDir($path . '/portal_layouts');
	}

	abstract public function render(string $layout, array $params = []): string;

	abstract public function renderString(string $string, array $params = []): string;

	public function setTemplateDir(string $dir): static
	{
		$this->templateDir = $dir;

		return $this;
	}

	public function setCustomDir(string $dir): static
	{
		$this->customDir = $dir;

		return $this;
	}

	public function getLayouts(): array
	{
		$layouts = $this->collectLayouts();

		$default = $layouts[static::DEFAULT_TEMPLATE];
		unset($layouts[static::DEFAULT_TEMPLATE]);

		return array_merge([static::DEFAULT_TEMPLATE => $default], $layouts);
	}

	private function collectLayouts(): array
	{
		$layouts = glob($this->templateDir . '/*' . static::DEFAULT_EXTENSION);

		$extensions = [static::DEFAULT_EXTENSION];

		$this->dispatcher->dispatch(PortalHook::layoutExtensions, ['extensions' => &$extensions]);

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
