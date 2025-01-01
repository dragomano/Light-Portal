<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.9
 */

namespace Bugo\LightPortal\Compilers;

use Bugo\Compat\ErrorHandler;
use Exception;
use ScssPhp\ScssPhp\Compiler;
use ScssPhp\ScssPhp\Exception\SassException;
use ScssPhp\ScssPhp\OutputStyle;

use function file_put_contents;

final class Sass extends AbstractCompiler
{
	public const SOURCE_FILE = '/sass/portal.scss';

	public function compile(): void
	{
		if (! $this->isCompilationRequired())
			return;

		try {
			$compiler = new Compiler([
				'cacheDir' => $this->getTempDir(),
			]);

			$compiler->setOutputStyle(OutputStyle::COMPRESSED);

			$result = $compiler->compileFile($this->sourceFile);

			file_put_contents($this->targetFile, $result->getCss());
		} catch (SassException | Exception $e) {
			ErrorHandler::log($e->getMessage(), 'critical');
		}
	}
}
