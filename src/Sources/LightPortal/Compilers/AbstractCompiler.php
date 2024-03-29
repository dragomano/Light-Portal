<?php declare(strict_types=1);

/**
 * AbstractCompiler.php
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

use Bugo\Compat\Config;
use Bugo\Compat\Sapi;
use Bugo\Compat\Theme;
use Bugo\LightPortal\Helper;

abstract class AbstractCompiler implements CompilerInterface
{
	use Helper;

	public const TARGET_FILE = '/portal.css';

	protected string $sourceFile;

	protected string $targetFile;

	public function __construct()
	{
		$this->sourceFile = $this->getCssDirPath() . static::SOURCE_FILE;
		$this->targetFile = $this->getCssDirPath() . static::TARGET_FILE;

		$this->applyHook('clean_cache');
	}

	public function cleanCache(): void
	{
		if (is_file($this->sourceFile)) {
			touch($this->sourceFile);
		}
	}

	protected function isCompilationRequired(): bool
	{
		if (! is_file($this->sourceFile))
			return false;

		if (is_file($this->targetFile) && filemtime($this->sourceFile) < filemtime($this->targetFile))
			return false;

		return true;
	}

	protected function getTempDir(): ?string
	{
		return empty(Config::$modSettings['cache_enable']) ? null : Sapi::getTempDir();
	}

	protected function getCssDirPath(): string
	{
		return Theme::$current->settings['default_theme_dir'] . '/css/light_portal';
	}
}
