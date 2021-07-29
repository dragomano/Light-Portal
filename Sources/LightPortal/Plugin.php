<?php

namespace Bugo\LightPortal;

/**
 * Plugin.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2021 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 1.8
 */

if (!defined('SMF'))
	die('Hacking attempt...');

abstract class Plugin
{
	public function loadTemplate($dir)
	{
		require_once $dir . DIRECTORY_SEPARATOR . 'template.php';
	}
}
