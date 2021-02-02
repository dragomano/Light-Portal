<?php

namespace Bugo\LightPortal\Front;

/**
 * AbstractArticle.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2021 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 1.6
 */

if (!defined('SMF'))
	die('Hacking attempt...');

abstract class AbstractArticle
{
	protected $columns = [];
	protected $tables  = [];
	protected $wheres  = [];
	protected $params  = [];
	protected $orders  = [];

	public static function load($class)
	{
		return new $class;
	}

	abstract public function init();
	abstract public function getData(int $start, int $limit);
	abstract public function getTotalCount();
}
