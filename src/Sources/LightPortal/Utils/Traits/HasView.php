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

namespace LightPortal\Utils\Traits;

use LightPortal\UI\TemplateLoader;
use LightPortal\UI\View;
use LightPortal\UI\ViewInterface;
use ReflectionClass;

trait HasView
{
	private ?ViewInterface $view = null;

	public function useCustomTemplate(string $template = 'default', array $params = []): void
	{
		TemplateLoader::fromFile('partials/_content', ['content' => $this->view($template, $params)]);
	}

	public function view(string $template = 'default', array $params = []): string
	{
		return $this->viewInstance()->render($template, $params);
	}

	protected function viewInstance(): ViewInterface
	{
		if (! $this->view) {
			$reflection = new ReflectionClass(static::class);
			$this->view = new View(dirname($reflection->getFileName()) . '/views');
		}

		return $this->view;
	}
}
