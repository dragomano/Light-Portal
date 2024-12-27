<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.8
 */

namespace Bugo\LightPortal\Renderers;

use Bugo\Compat\ErrorHandler;
use Bugo\Compat\Sapi;
use Bugo\LightPortal\Utils\Icon;
use eftec\bladeone\BladeOne;
use Exception;

use function count;
use function is_array;
use function ob_get_clean;
use function ob_start;
use function str_replace;
use function strstr;

final class Blade extends AbstractRenderer
{
	public const DEFAULT_TEMPLATE = 'default.blade.php';

	public const DEFAULT_EXTENSION = '.blade.php';

	public function render(string $layout, array $params = []): string
	{
		if (empty($layout))
			return '';

		ob_start();

		try {
			$blade = new BladeOne([$this->templateDir, $this->customDir], Sapi::getTempDir());

			$blade->directiveRT('icon', static function (array|string $expression) {
				if (is_array($expression)) {
					[$name, $title] = count($expression) > 1 ? $expression : [$expression[0], false];
				} else {
					$name = $expression;
				}

				$icon = Icon::get($name);

				if (empty($title)) {
					echo $icon;
					return;
				}

				echo str_replace(' class=', ' title="' . $title . '" class=', $icon);
			});

			$layout = strstr($layout, '.', true) ?: $layout;

			echo $blade->run($layout, $params);
		} catch (Exception $e) {
			ErrorHandler::fatal($e->getMessage(), false);
		}

		return ob_get_clean();
	}
}
