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
 * @version 2.5
 */

namespace Bugo\LightPortal\Compilers;

use Bugo\Compat\Theme;
use Bugo\LightPortal\Helper;

abstract class AbstractCompiler implements CompilerInterface
{
	use Helper;

	public function __construct()
	{
		$this->applyHook('clean_cache');
	}

	protected function getCssDirPath(): string
	{
		return Theme::$current->settings['default_theme_dir'] . '/css/light_portal';
	}
}
