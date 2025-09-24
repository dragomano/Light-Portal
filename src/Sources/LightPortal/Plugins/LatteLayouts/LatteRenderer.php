<?php declare(strict_types=1);

/**
 * @package LatteLayouts (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2023-2025 Bugo
 * @license https://opensource.org/licenses/MIT MIT
 *
 * @category plugin
 * @version 24.09.25
 */

namespace Bugo\LightPortal\Plugins\LatteLayouts;

use Bugo\Compat\Config;
use Bugo\Compat\ErrorHandler;
use Bugo\Compat\Sapi;
use Bugo\LightPortal\Renderers\AbstractRenderer;
use Bugo\LightPortal\Utils\Icon;
use Exception;
use Latte\Engine;
use Latte\Essential\RawPhpExtension;
use Latte\Loaders\FileLoader;
use Latte\Runtime\Html;
use Latte\RuntimeException;

class LatteRenderer extends AbstractRenderer
{
	public const DEFAULT_TEMPLATE = 'default.latte';

	public function render(string $layout, array $params = []): string
	{
		if (empty($layout))
			return '';

		require_once __DIR__ . '/vendor/autoload.php';

		ob_start();

		try {
			$latte = new Engine;
			$latte->setTempDirectory(empty(Config::$modSettings['cache_enable']) ? null : Sapi::getTempDir());
			$latte->setLoader(new FileLoader($this->customDir));
			$latte->addExtension(new RawPhpExtension());

			$latte->addFunction('teaser', static function (string $text, int $length = 150) use ($latte): string {
				$text = $latte->invokeFilter('stripHtml', [$text]);

				return $latte->invokeFilter('truncate', [$text, $length]);
			});

			$latte->addFunction('icon', static function (string $name, string $title = ''): Html {
				$icon = Icon::get($name);

				if (empty($title)) {
					return new Html($icon);
				}

				return new Html(str_replace(' class=', ' title="' . $title . '" class=', $icon));
			});

			$latte->render($layout, $params);
		} catch (RuntimeException | Exception $e) {
			ErrorHandler::fatal($e->getMessage(), false);
		}

		return ob_get_clean();
	}
}
