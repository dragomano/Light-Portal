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

namespace LightPortal\UI;

use Bugo\Compat\Config;
use Bugo\Compat\Lang;
use Bugo\Compat\Theme;
use Bugo\Compat\Utils;
use LightPortal\Renderers\Blade;
use LightPortal\Renderers\PurePHP;
use LightPortal\Renderers\RendererInterface;

use function LightPortal\app;

if (! defined('LP_NAME'))
	die('No direct access...');

class View implements ViewInterface
{
	public function __construct(private string $templateDir = '') {}

	public function render(string $template = 'default', array $params = []): string
	{
		$tpl  = str_replace('.', DIRECTORY_SEPARATOR, $template);
		$file = $this->getFile($tpl);

		if ($file === '') {
			return '';
		}

		$params   = $this->getDefaultParams() + $params;
		$renderer = $this->makeRenderer($file);
		$layout   = $this->prepareLayout($file, $renderer);

		return $renderer->render($layout, $params);
	}

	public function setTemplateDir(string $dir): static
	{
		$this->templateDir = $dir;

		return $this;
	}

	private function getDefaultParams(): array
	{
		return [
			'context'     => Utils::$context,
			'language'    => Config::$language,
			'modSettings' => Config::$modSettings,
			'scripturl'   => Config::$scripturl,
			'settings'    => Theme::$current->settings,
			'txt'         => Lang::$txt,
		];
	}

	private function getFile(string $tpl): string
	{
		$candidates = [
			$tpl . '.blade.php',
			$tpl . '.php',
		];

		$file = '';
		foreach ($candidates as $candidate) {
			$path = $this->templateDir . DIRECTORY_SEPARATOR . $candidate;
			if (is_file($path)) {
				$file = $path;
				break;
			}
		}

		return $file;
	}

	private function makeRenderer(string $file): RendererInterface
	{
		$renderer = str_ends_with($file, '.blade.php') ? app(Blade::class) : app(PurePHP::class);
		$renderer->setTemplateDir($this->templateDir)->setCustomDir($this->templateDir);

		return $renderer;
	}

	private function prepareLayout(string $file, RendererInterface $renderer): string
	{
		$layout = str_replace($this->templateDir . DIRECTORY_SEPARATOR, '', $file);

		if ($renderer instanceof Blade) {
			$layout = str_replace(DIRECTORY_SEPARATOR, '.', $layout);
			$layout = str_replace('.blade.php', '', $layout);
			$layout = str_replace('/', '\\', $layout);
		}

		return $layout;
	}
}
