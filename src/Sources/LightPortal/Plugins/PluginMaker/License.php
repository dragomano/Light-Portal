<?php declare(strict_types=1);

/**
 * @package PluginMaker (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2021-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category plugin
 * @version 09.01.25
 */

namespace Bugo\LightPortal\Plugins\PluginMaker;

enum License: string
{
	case MIT = 'mit';
	case BSD = 'bsd';
	case GPL = 'gpl';

	public function getName(): string
	{
		return match ($this) {
			self::MIT => 'MIT',
			self::BSD => 'BSD-3-Clause',
			self::GPL => 'GPL-3.0-or-later',
		};
	}

	public function getLink(): string
	{
		return match ($this) {
			self::MIT => 'https://opensource.org/licenses/MIT',
			self::BSD => 'https://opensource.org/licenses/BSD-3-Clause',
			self::GPL => 'https://spdx.org/licenses/GPL-3.0-or-later.html',
		};
	}
}
