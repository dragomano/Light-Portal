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
use Bugo\Compat\Utils;
use Exception;

if (! defined('SMF'))
	die('No direct access...');

class PurePHP extends AbstractRenderer
{
	public const DEFAULT_TEMPLATE = 'default.php';

	public const DEFAULT_EXTENSION = '.php';

	public function render(string $layout, array $params = []): string
	{
		if (empty($layout)) {
			return '';
		}

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

	public function renderString(string $string, array $params = []): string
	{
		if (empty($string)) {
			return '';
		}

		ob_start();

		try {
			extract($params, EXTR_SKIP);

			$tempFile = $this->createTempFile($string);

			require $tempFile;

			unlink($tempFile);
		} catch (Exception $e) {
			ErrorHandler::fatal($e->getMessage(), false);
		}

		return ob_get_clean();
	}

	private function createTempFile(string $content): string
	{
		$content = trim(Utils::htmlspecialcharsDecode($content) ?? '');
		$content = preg_replace('/^<\?php\s*/i', '', $content);
		$content = preg_replace('/\?>\s*$/i', '', $content);

		$tempFile = tempnam(Sapi::getTempDir(), 'lp_render_');

		file_put_contents($tempFile, '<?php ' . $content);

		return $tempFile;
	}
}
