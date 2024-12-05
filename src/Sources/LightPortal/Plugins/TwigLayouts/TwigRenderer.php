<?php declare(strict_types=1);

/**
 * @package TwigLayouts (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2023-2024 Bugo
 * @license https://opensource.org/licenses/MIT MIT
 *
 * @category plugin
 * @version 03.12.24
 */

namespace Bugo\LightPortal\Plugins\TwigLayouts;

use Bugo\Compat\BBCodeParser;
use Bugo\Compat\Config;
use Bugo\Compat\ErrorHandler;
use Bugo\Compat\Sapi;
use Bugo\LightPortal\Renderers\AbstractRenderer;
use Bugo\LightPortal\Utils\Icon;
use Twig\Environment;
use Twig\Error\Error;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFunction;

use function ob_get_clean;
use function ob_start;
use function print_r;
use function show_pagination;
use function str_replace;

class TwigRenderer extends AbstractRenderer
{
	public const DEFAULT_TEMPLATE = 'default.twig';

	public function render(string $layout, array $params = []): string
	{
		if (empty($layout))
			return '';

		require_once __DIR__ . '/vendor/autoload.php';

		ob_start();

		try {
			$loader = new FilesystemLoader($this->customDir);

			$twig = new Environment($loader, [
				'cache' => empty(Config::$modSettings['cache_enable']) ? false : Sapi::getTempDir(),
				'debug' => false
			]);

			$twig->addFunction(new TwigFunction('show_pagination', static function (string $position = 'top') {
				show_pagination($position);
			}));

			$twig->addFunction(new TwigFunction('icon', static function (string $name, string $title = '') {
				$icon = Icon::get($name);

				if (empty($title)) {
					echo $icon;
					return;
				}

				echo str_replace(' class=', ' title="' . $title . '" class=', $icon);
			}));

			$twig->addFunction(new TwigFunction('debug', static function (mixed $data) {
				echo BBCodeParser::load()->parse('[code]' . print_r($data, true) . '[/code]');
			}));

			echo $twig->render($layout, $params);
		} catch (Error $e) {
			ErrorHandler::fatal($e->getMessage(), false);
		}

		return ob_get_clean();
	}
}
