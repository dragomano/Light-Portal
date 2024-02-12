<?php declare(strict_types=1);

/**
 * Less.php
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
use Less_Exception_Parser;
use Less_Parser;

class Less extends AbstractCompiler
{
	public function compile(): void
	{
		$cssFile  = $this->getCssDirPath() . '/portal.css';
		$lessFile = $this->getCssDirPath() . '/less/portal.less';

		if (! is_file($lessFile))
			return;

		if (is_file($cssFile) && filemtime($lessFile) < filemtime($cssFile))
			return;

		try {
			$parser = new Less_Parser([
				'compress'  => true,
				'cache_dir' => empty(Config::$modSettings['cache_enable']) ? null : Sapi::getTempDir(),
			]);

			$parser->parseFile($lessFile);

			file_put_contents($cssFile, $parser->getCss());
		} catch (Less_Exception_Parser | Exception $e) {
			ErrorHandler::log($e->getMessage(), 'critical');
		}
	}

	public function cleanCache(): void
	{
		$file = $this->getCssDirPath() . '/less/portal.less';

		if (is_file($file)) {
			touch($file);
		}
	}
}
