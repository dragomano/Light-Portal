<?php declare(strict_types=1);

/**
 * Sass.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.5
 */

namespace Bugo\LightPortal\Compilers;

use Bugo\Compat\Config;
use Bugo\Compat\ErrorHandler;
use Bugo\Compat\Sapi;
use Exception;
use ScssPhp\ScssPhp\Compiler;
use ScssPhp\ScssPhp\Exception\SassException;
use ScssPhp\ScssPhp\OutputStyle;

class Sass extends AbstractCompiler
{
	public function compile(): void
	{
		$cssFile  = $this->getCssDirPath() . '/portal.css';
		$scssFile = $this->getCssDirPath() . '/sass/portal.scss';

		if (! is_file($scssFile))
			return;

		if (is_file($cssFile) && filemtime($scssFile) < filemtime($cssFile))
			return;

		try {
			$compiler = new Compiler([
				'cacheDir' => empty(Config::$modSettings['cache_enable']) ? null : Sapi::getTempDir(),
			]);

			$compiler->setOutputStyle(OutputStyle::COMPRESSED);

			$result = $compiler->compileFile($scssFile);

			file_put_contents($cssFile, $result->getCss());
		} catch (SassException | Exception $e) {
			ErrorHandler::log($e->getMessage(), 'critical');
		}
	}

	public function cleanCache(): void
	{
		$file = $this->getCssDirPath() . '/sass/portal.scss';

		if (is_file($file)) {
			touch($file);
		}
	}
}
