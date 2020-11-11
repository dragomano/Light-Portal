<?php

namespace Bugo\LightPortal\Impex;

/**
 * Import.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2020 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 1.3
 */

abstract class Import implements IImport
{
	abstract protected static function run();
}