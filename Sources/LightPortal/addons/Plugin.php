<?php

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

namespace Bugo\LightPortal\Addons;

use ReflectionClass;
use Bugo\LightPortal\Helpers;

if (!defined('SMF'))
	die('Hacking attempt...');

abstract class Plugin
{
	/**
	 * @var string
	 */
	public $type = 'block';

	/**
	 * @var string
	 */
	public $icon = 'fas fa-puzzle-piece';

	/**
	 * List of required addons separated by comma
	 *
	 * @var array
	 */
	protected $requires = [];

	public function getName()
	{
		return (new ReflectionClass(get_called_class()))->getShortName();
	}

	public function getSnakeName()
	{
		return Helpers::getSnakeName($this->getName());
	}

	public function loadTemplate(string $template = 'template')
	{
		require_once dirname((new ReflectionClass(get_called_class()))->getFileName()) . DIRECTORY_SEPARATOR . $template . '.php';
	}
}
