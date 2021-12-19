<?php

/**
 * Plugin.php
 *
 * @package Light Portal
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2022 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.0
 */

namespace Bugo\LightPortal\Addons;

use ReflectionClass;

abstract class Plugin
{
	/** Addon type */
	public string $type = 'block';

	/** Block icon */
	public string $icon = 'fas fa-puzzle-piece';

	/** Addon author */
	public string $author = '';

	/** Addon site link */
	public string $link = '';

	/** List of required addons separated by comma */
	public array $requires = [];

	/** Addon list those will be disabled on enabling */
	public array $disables = [];

	public function getCalledClass(): ReflectionClass
	{
		return new ReflectionClass(get_called_class());
	}

	public function getName(): string
	{
		return $this->getCalledClass()->getShortName();
	}

	public function loadTemplate(string $template = 'template')
	{
		$path = dirname($this->getCalledClass()->getFileName()) . DIRECTORY_SEPARATOR . $template . '.php';

		if (is_file($path))
			require_once $path;
	}

	public function loadSsi()
	{
		$path = dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'SSI.php';

		if (is_file($path))
			require_once $path;
	}
}
