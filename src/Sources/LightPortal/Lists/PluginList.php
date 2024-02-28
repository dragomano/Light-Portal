<?php

declare(strict_types=1);

/**
 * PluginList.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.6
 */

namespace Bugo\LightPortal\Lists;

use Bugo\LightPortal\AddonHandler;

if (! defined('SMF'))
	die('No direct access...');

final class PluginList implements ListInterface
{
	public function __invoke(): array
	{
		return $this->getAll();
	}

	public function getAll(): array
	{
		return AddonHandler::getInstance()->getAll();
	}
}
