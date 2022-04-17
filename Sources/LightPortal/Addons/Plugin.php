<?php declare(strict_types=1);

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

if (! defined('SMF'))
	die('No direct access...');

abstract class Plugin
{
	use Helper;

	public string $type = 'block';

	public string $icon = 'fas fa-puzzle-piece';

	public string $author = '';

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

		if ($sub_template)
			$this->context['sub_template'] = $sub_template;

		return $this;
	}

	public function withLayer(string $layer)
	{
		$this->context['template_layers'][] = $layer;
	}

	public function getFromSsi(string $function, ...$params)
	{
		require_once dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'SSI.php';

		$function = 'ssi_' . $function;

		if (function_exists($function))
			return $function(...$params);

		return false;
	}

	public function addDefaultValues(array $values)
	{
		$snake_name = $this->getSnakeName($this->getName());

		$params = [];
		foreach ($values as $option_name => $value) {
			if (! isset($this->context['lp_' . $snake_name . '_plugin'][$option_name])) {
				$params[] = [
					'name'   => $snake_name,
					'option' => $option_name,
					'value'  => $value,
				];

				$this->context['lp_' . $snake_name . '_plugin'][$option_name] = $value;
			}
		}

		$this->smcFunc['db_insert']('replace',
			'{db_prefix}lp_plugins',
			[
				'name'   => 'string',
				'option' => 'string',
				'value'  => 'string',
			],
			$params,
			['name', 'option']
		);

		$this->context['lp_num_queries']++;

		$this->cache()->forget('plugin_settings');
	}
}
