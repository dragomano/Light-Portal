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

use Bugo\Compat\ErrorHandler;
use Bugo\Compat\Sapi;
use LightPortal\Utils\Icon;
use eftec\bladeone\BladeOne;
use Exception;

if (! defined('SMF'))
	die('No direct access...');

class Blade extends AbstractRenderer
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

			$blade->directiveRT('icon', static function ($name, $title = '') {
				if (is_array($name)) {
					[$n, $t] = count($name) > 1 ? $name : [$name[0], ''];
					$name = $n;
					$title = $t;
				}

				$icon = Icon::get($name);

				if (empty($title)) {
					echo $icon;
					return;
				}

				echo str_replace(' class=', ' title="' . $title . '" class=', $icon);
			});

			if (str_ends_with($layout, self::DEFAULT_EXTENSION)) {
				$layout = substr($layout, 0, -strlen(self::DEFAULT_EXTENSION));
			}

			echo $blade->run($layout, $params);
		} catch (Exception $e) {
			ErrorHandler::fatal($e->getMessage(), false);
		}

		return ob_get_clean();
	}
}
