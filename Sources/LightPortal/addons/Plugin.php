<?php

/**
 * Plugin.php
 *
 * @package Light Portal
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2021 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 1.10
 */

namespace Bugo\LightPortal\Addons;

use ReflectionClass;

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
	 * Addon list those will be disabled on enabling
	 *
	 * @var array
	 */
	public $disables = [];

	/**
	 * @return ReflectionClass
	 */
	public function getCalledClass()
	{
		return new ReflectionClass(get_called_class());
	}

	/**
	 * @return string
	 */
	public function getName(): string
	{
		return $this->getCalledClass()->getShortName();
	}

	/**
	 * @param string $template
	 * @return void
	 */
	public function loadTemplate(string $template = 'template')
	{
		$path = dirname($this->getCalledClass()->getFileName()) . DIRECTORY_SEPARATOR . $template . '.php';

		if (is_file($path))
			require_once $path;
	}

	/**
	 * @return void
	 */
	public function loadSsi()
	{
		$path = dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'SSI.php';

		if (is_file($path))
			require_once $path;
	}
}
