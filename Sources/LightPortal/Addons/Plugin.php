<?php

declare(strict_types = 1);

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

use Bugo\LightPortal\Helper;
use ReflectionClass;

abstract class Plugin
{
	use Helper;

	/** Addon type */
	public string $type = 'block';

	/** Block icon */
	public string $icon = 'fas fa-puzzle-piece';

	/** Addon author */
	public string $author = '';

	/** Addon site link */
	public string $link = '';

	public function getCalledClass(): ReflectionClass
	{
		return new ReflectionClass(get_called_class());
	}

	public function getName(): string
	{
		return $this->getCalledClass()->getShortName();
	}

	public function loadTemplate(string $sub_template = ''): Plugin
	{
		$path = dirname($this->getCalledClass()->getFileName()) . DIRECTORY_SEPARATOR . 'template.php';

		if (is_file($path))
			require_once $path;

		if (! empty($sub_template))
			$this->context['sub_template'] = $sub_template;

		return $this;
	}

	public function withLayer(string $layer)
	{
		$this->context['template_layers'][] = $layer;
	}

	public function loadSsi(): Plugin
	{
		$path = dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'SSI.php';

		if (is_file($path))
			require_once $path;

		return $this;
	}
}
