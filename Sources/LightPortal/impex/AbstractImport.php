<?php

declare(strict_types = 1);

/**
 * AbstractImport.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2022 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 * @version 2.0
 */

namespace Bugo\LightPortal\Impex;

abstract class AbstractImport implements ImportInterface
{
	abstract protected function run();
}