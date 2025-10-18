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

namespace LightPortal\Utils;

if (! defined('SMF'))
	die('No direct access...');

class File extends GlobalArray implements FileInterface
{
	public function __construct()
	{
		$this->storage = &$_FILES;
	}

	public function free(string $key): void
	{
		unset($this->storage[$key]);
	}
}
