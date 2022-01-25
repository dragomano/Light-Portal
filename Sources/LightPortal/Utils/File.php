<?php declare(strict_types=1);

/**
 * File.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2022 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.0
 */

namespace Bugo\LightPortal\Utils;

if (! defined('SMF'))
	die('No direct access...');

final class File extends AbstractRequest
{
	public function __construct()
	{
		$this->storage = &$_FILES;
	}

	public function free(string $key)
	{
		unset($this->storage[$key]);
	}
}
