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

namespace Bugo\LightPortal\UI;

use Bugo\Compat\Config;
use Bugo\Compat\Lang;
use Bugo\Compat\Theme;
use Bugo\Compat\Utils;
use Bugo\LightPortal\Renderers\Blade as GlobalBlade;
use Bugo\LightPortal\Renderers\PurePHP as GlobalPurePHP;
use Bugo\LightPortal\Renderers\RendererInterface;

if (! defined('LP_NAME'))
	die('No direct access...');

final readonly class View
{
	public function __construct(private string $baseDir) {}

	public function render(string $template = 'default', array $params = []): string
	{
		$tpl = str_replace('.', DIRECTORY_SEPARATOR, $template);
		$views = $this->baseDir . DIRECTORY_SEPARATOR . 'views';

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

		if ($file === '') {
			return '';
		}

		$params = [
			'txt'         => Lang::$txt,
			'context'     => Utils::$context,
			'modSettings' => Config::$modSettings,
			'scripturl'   => Config::$scripturl,
			'settings'    => Theme::$current->settings,
		] + $params;

		$renderer = $this->makeRenderer($views, $file);

		$layout = str_replace($views . DIRECTORY_SEPARATOR, '', $file);

		if ($renderer instanceof GlobalBlade) {
			$layout = str_replace(DIRECTORY_SEPARATOR, '.', $layout);
			$layout = str_replace('.blade.php', '', $layout);
		}

		return $renderer->render($layout, $params);
	}

	private function makeRenderer(string $views, string $file): RendererInterface
	{
		$LocalBlade = new class($views) extends GlobalBlade {
			public function __construct(private readonly string $dir)
			{
				parent::__construct();

				$this->templateDir = $this->dir;
				$this->customDir   = $this->dir;
			}
		};

		$LocalPHP = new class($views) extends GlobalPurePHP {
			public function __construct(private readonly string $dir)
			{
				parent::__construct();

				$this->templateDir = $this->dir;
				$this->customDir   = $this->dir;
			}
		};

		return str_ends_with($file, '.blade.php') ? $LocalBlade : $LocalPHP;
	}
}
