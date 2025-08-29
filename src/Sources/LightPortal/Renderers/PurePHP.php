<?php

declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 3.0
 */

namespace Bugo\LightPortal\Renderers;

use Bugo\Compat\ErrorHandler;
use Exception;

use function extract;
use function is_file;
use function ob_get_clean;
use function ob_start;

use const DIRECTORY_SEPARATOR;
use const EXTR_SKIP;

class PurePHP extends AbstractRenderer
{
	public const DEFAULT_TEMPLATE = 'default.php';

	public const DEFAULT_EXTENSION = '.php';

	public function render(string $layout, array $params = []): string
	{
		if (empty($layout))
			return '';

		ob_start();

		try {
			$path = is_file($this->customDir . DIRECTORY_SEPARATOR . $layout)
				? $this->customDir
				: $this->templateDir;

			extract($params, EXTR_SKIP);

			require $path . DIRECTORY_SEPARATOR . $layout;
		} catch (Exception $e) {
			ErrorHandler::fatal($e->getMessage(), false);
		}

		return ob_get_clean();
	}
}
