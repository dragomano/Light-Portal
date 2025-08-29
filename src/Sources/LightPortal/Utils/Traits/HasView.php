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

namespace Bugo\LightPortal\Utils\Traits;

use Bugo\Compat\Utils;
use Bugo\LightPortal\UI\View;

use function dirname;

if (! defined('SMF'))
	die('No direct access...');

trait HasView
{
	use HasReflection;

	private ?View $view = null;

	public function useLayerAbove(string $template = 'default', array $params = []): void
	{
		Utils::$context['template_layers'][] = 'custom';
		Utils::$context['lp_layer_above_content'] = $this->view($template, $params);
	}

	public function useCustomTemplate(string $template = 'default', array $params = []): void
	{
		Utils::$context['sub_template'] = 'custom';
		Utils::$context['lp_custom_content'] = $this->view($template, $params);
	}

	public function view(string $template = 'default', array $params = []): string
	{
		return $this->viewInstance()->render($template, $params);
	}

	protected function viewInstance(): View
	{
		if (! $this->view) {
			$baseDir = dirname($this->getCalledClass()->getFileName());
			$this->view = new View($baseDir);
		}

		return $this->view;
	}
}
