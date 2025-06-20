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

use function dirname;
use function is_file;

if (! defined('SMF'))
	die('No direct access...');

trait HasTemplate
{
	use HasReflection;

	public function useTemplate(string $name = 'template'): self
	{
		$path = dirname($this->getCalledClass()->getFileName()) . DIRECTORY_SEPARATOR . $name . '.php';

		if (is_file($path)) {
			require_once $path;
		}

		return $this;
	}

	public function withSubTemplate(string $template): self
	{
		Utils::$context['sub_template'] = $template;

		return $this;
	}

	public function withLayer(string $layer): self
	{
		Utils::$context['template_layers'][] = $layer;

		return $this;
	}

	public function getFromTemplate(string $function, ...$params): string
	{
		$this->useTemplate();

		return $function(...$params);
	}
}
