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
	 * Addon type
	 *
	 * @var string
	 */
	public $type = 'block';

	/**
	 * Block icon
	 *
	 * @var string
	 */
	public $icon = 'fas fa-puzzle-piece';

	/**
	 * Addon author
	 *
	 * @var string
	 */
	public $author = '';

	/**
	 * Addon site link
	 *
	 * @var string
	 */
	public $link = '';

	/**
	 * List of required addons separated by comma
	 *
	 * @var array
	 */
	public $requires = [];

	/**
	 * @return string
	 */
	public function getName(): string
	{
		return (new ReflectionClass(get_called_class()))->getShortName();
	}

	/**
	 * @return string
	 */
	public function getSnakeName(): string
	{
		return Helpers::getSnakeName($this->getName());
	}

	/**
	 * @param string $template
	 * @return void
	 */
	public function loadTemplate(string $template = 'template')
	{
		$path = dirname((new ReflectionClass(get_called_class()))->getFileName()) . DIRECTORY_SEPARATOR . $template . '.php';

		if (is_file($path))
			require_once $path;
	}
}
