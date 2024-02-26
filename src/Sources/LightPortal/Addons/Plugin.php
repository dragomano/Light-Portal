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
 * @version 2.5
 */

namespace Bugo\LightPortal\Addons;

use Bugo\Compat\{ServerSideIncludes, Theme, Utils};
use Bugo\LightPortal\Helper;
use Bugo\LightPortal\Repositories\PluginRepository;
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

	public function setTemplate(string $sub_template = ''): self
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
		$snakeName = $this->getSnakeName($this->getName());

		$settings = [];
		foreach ($values as $option => $value) {
			if (! isset(Utils::$context['lp_' . $snakeName . '_plugin'][$option])) {
				$settings[] = [
					'name'   => $snakeName,
					'option' => $option,
					'value'  => $value,
				];

				Utils::$context['lp_' . $snakeName . '_plugin'][$option] = $value;
			}
		}

		(new PluginRepository())->addSettings($settings);
	}

	public function isDarkTheme(?string $option): bool
	{
		if (empty($option))
			return false;

		$themes = array_flip(array_filter(explode(',', $option)));

		return $themes && isset($themes[Theme::$current->settings['theme_id']]);
	}

	public function addInlineJS(string $javascript, $defer = false): void
	{
		Theme::addInlineJavaScript($javascript, $defer);
	}

	public function addInlineCSS(string $css): void
	{
		Theme::addInlineCss($css);
	}

	public function loadJSFile(string $fileName, array $params = [], string $id = ''): void
	{
		Theme::loadJavaScriptFile($fileName, $params, $id);
	}

	public function loadExtCSS(string $fileName, array $params = [], string $id = ''): void
	{
		Theme::loadCSSFile($fileName, array_merge($params, ['external' => true]), $id);
	}

	public function loadExtJS(string $fileName, array $params = [], string $id = ''): void
	{
		$this->loadJSFile($fileName, array_merge($params, ['external' => true]), $id);
	}
}
