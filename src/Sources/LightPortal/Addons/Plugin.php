<?php declare(strict_types=1);

/**
 * Plugin.php
 *
 * @package Light Portal
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.4
 */

namespace Bugo\LightPortal\Addons;

use Bugo\LightPortal\Helper;
use Bugo\LightPortal\Repositories\PluginRepository;
use Bugo\LightPortal\Utils\{ServerSideIncludes, Theme, Utils};
use ReflectionClass;

if (! defined('SMF'))
	die('No direct access...');

abstract class Plugin
{
	use Helper;

	public string $type = 'block';

	public string $icon = 'fas fa-puzzle-piece';

	public function getCalledClass(): ReflectionClass
	{
		return new ReflectionClass(static::class);
	}

	public function getName(): string
	{
		return $this->getCalledClass()->getShortName();
	}

	public function setTemplate(string $sub_template = ''): Plugin
	{
		$path = dirname($this->getCalledClass()->getFileName()) . DIRECTORY_SEPARATOR . 'template.php';

		if (is_file($path))
			require_once $path;

		if ($sub_template)
			Utils::$context['sub_template'] = $sub_template;

		return $this;
	}

	public function withLayer(string $layer): void
	{
		Utils::$context['template_layers'][] = $layer;
	}

	public function getFromTemplate(string $function, ...$params): string
	{
		$this->setTemplate();

		return $function(...$params);
	}

	public function getFromSsi(string $function, ...$params)
	{
		require_once dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'SSI.php';

		return ServerSideIncludes::{$function}(...$params);
	}

	public function addDefaultValues(array $values): void
	{
		$snake_name = $this->getSnakeName($this->getName());

		$settings = [];
		foreach ($values as $option_name => $value) {
			if (! isset(Utils::$context['lp_' . $snake_name . '_plugin'][$option_name])) {
				$settings[] = [
					'name'   => $snake_name,
					'option' => $option_name,
					'value'  => $value,
				];

				Utils::$context['lp_' . $snake_name . '_plugin'][$option_name] = $value;
			}
		}

		(new PluginRepository)->addSettings($settings);
	}

	public function isDarkTheme(?string $option): bool
	{
		if (empty($option))
			return false;

		$dark_themes = array_flip(array_filter(explode(',', $option)));

		return $dark_themes && isset($dark_themes[Theme::$current->settings['theme_id']]);
	}
}
