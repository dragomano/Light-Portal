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
 * @version 2.6
 */

namespace Bugo\LightPortal\Compilers;

use Bugo\Compat\ErrorHandler;
use Exception;
use Less_Exception_Parser;
use Less_Parser;

final class Less extends AbstractCompiler
{
	public const SOURCE_FILE = '/less/portal.less';

	public function compile(): void
	{
		if (! $this->isCompilationRequired())
			return;

		try {
			$parser = new Less_Parser([
				'compress'  => true,
				'cache_dir' => $this->getTempDir(),
			]);

			$parser->parseFile($this->sourceFile);

			file_put_contents($this->targetFile, $parser->getCss());
		} catch (Less_Exception_Parser | Exception $e) {
			ErrorHandler::log($e->getMessage(), 'critical');
		}
	}
}
