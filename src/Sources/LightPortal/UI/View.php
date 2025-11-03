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

readonly class View
{
	protected string $views;

	public function __construct(private string $baseDir, private string $viewDir = 'views')
	{
		$this->views = $this->baseDir . DIRECTORY_SEPARATOR . $this->viewDir;
	}

	public function render(string $template = 'default', array $params = []): string
	{
		$tpl  = str_replace('.', DIRECTORY_SEPARATOR, $template);
		$file = $this->getFile($tpl, $this->views);

		if ($file === '') {
			return '';
		}

		$params = $this->getDefaultParams() + $params;

		$renderer = $this->makeRenderer($file);

		$layout = $this->prepareLayout($file, $renderer);

		return $renderer->render($layout, $params);
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

	private function getFile(string $tpl, string $views): string
	{
		$candidates = [];
		if (! str_contains($tpl, '.')) {
			$candidates[] = $tpl . '.blade.php';
			$candidates[] = $tpl . '.php';
		} else {
			$candidates[] = $tpl;
		}

		$file = '';
		foreach ($candidates as $cand) {
			$path = $views . DIRECTORY_SEPARATOR . $cand;
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
		$renderer->setTemplateDir($this->views)->setCustomDir($this->views);

		return $renderer;
	}

	private function prepareLayout(string $file, RendererInterface $renderer): string
	{
		$layout = str_replace($this->views . DIRECTORY_SEPARATOR, '', $file);

		if ($renderer instanceof Blade) {
			$layout = str_replace(DIRECTORY_SEPARATOR, '.', $layout);
			$layout = str_replace('.blade.php', '', $layout);
			$layout = str_replace('/', '\\', $layout);
		}

		return $layout;
	}
}
