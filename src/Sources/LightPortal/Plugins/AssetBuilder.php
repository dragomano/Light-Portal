<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2026 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 3.0
 */

namespace LightPortal\Plugins;

if (! defined('LP_NAME'))
	die('No direct access...');

readonly class AssetBuilder
{
	private string $pluginName;

	private string $snakeName;

	private ScriptCollection $scripts;

	private CssCollection $css;

	private ImageCollection $images;

	public function __construct(Plugin $plugin)
	{
		$this->pluginName = $plugin->getCamelName();
		$this->snakeName  = $plugin->getSnakeName();

		$this->scripts = new ScriptCollection($this->pluginName);
		$this->css     = new CssCollection($this->pluginName);
		$this->images  = new ImageCollection($this->pluginName);
	}

	public function scripts(): ScriptCollection
	{
		return $this->scripts;
	}

	public function css(): CssCollection
	{
		return $this->css;
	}

	public function images(): ImageCollection
	{
		return $this->images;
	}

	public function toArray(): array
	{
		return [
			'scripts' => [$this->snakeName => $this->scripts->toArray()],
			'css'     => [$this->snakeName => $this->css->toArray()],
			'images'  => [$this->snakeName => $this->images->toArray()],
		];
	}

	public function appendTo(array &$assets): void
	{
		$assets['scripts'][$this->snakeName] = $this->scripts->toArray();
		$assets['css'][$this->snakeName]     = $this->css->toArray();
		$assets['images'][$this->snakeName]  = $this->images->toArray();
	}
}
