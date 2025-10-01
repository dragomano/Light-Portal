<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 3.0
 */

namespace Bugo\LightPortal\Plugins;

use Bugo\Compat\ErrorHandler;
use Bugo\Compat\Lang;
use Bugo\Compat\Theme;
use Bugo\Compat\Utils;
use Bugo\LightPortal\Enums\PluginType;
use Bugo\LightPortal\Repositories\PluginRepository;
use Bugo\LightPortal\Utils\Str;
use Bugo\LightPortal\Utils\Traits\HasCache;
use Bugo\LightPortal\Utils\Traits\HasBreadcrumbs;
use Bugo\LightPortal\Utils\Traits\HasRequest;
use Bugo\LightPortal\Utils\Traits\HasResponse;
use Bugo\LightPortal\Utils\Traits\HasSession;
use Bugo\LightPortal\Utils\Traits\HasForumHooks;
use ReflectionClass;
use Stringable;

use function Bugo\LightPortal\app;

if (! defined('LP_NAME'))
	die('No direct access...');

abstract class Plugin implements PluginInterface, Stringable
{
	use HasBreadcrumbs;
	use HasCache;
	use HasForumHooks;
	use HasRequest;
	use HasResponse;
	use HasSession;

	protected string $name;

	protected array $context;

	protected array $txt;

	public function __construct(
		public string $type = 'other',
		public string $icon = 'fas fa-puzzle-piece',
		public bool $saveable = true
	)
	{
		$this->name = $this->getSnakeName();
		$this->type = $this->getPluginType();
		$this->icon = $this->getPluginIcon();
		$this->saveable = $this->isPluginSaveable();

		$this->context = &Utils::$context['lp_' . $this->name . '_plugin'];

		$this->txt = &Lang::$txt['lp_' . $this->name];
	}

	public function __toString(): string
	{
		return $this->getCamelName();
	}

	public function getCamelName(): string
	{
		return basename(str_replace('\\', '/', static::class));
	}

	public function getSnakeName(): string
	{
		return Str::getSnakeName($this->getCamelName());
	}

	public function getPluginType(): string
	{
		$pluginAttr = $this->getPluginAttribute();

		$type = $pluginAttr->type ?? $this->type;

		return $type instanceof PluginType ? $type->name() : $type;
	}

	public function getPluginIcon(): string
	{
		$pluginAttr = $this->getPluginAttribute();

		return $pluginAttr->icon ?? $this->icon;
	}

	public function isPluginSaveable(): bool
	{
		$pluginAttr = $this->getPluginAttribute();

		return $pluginAttr->saveable ?? $this->saveable;
	}

	public function addDefaultValues(array $values): void
	{
		$settings = [];
		foreach ($values as $option => $value) {
			if (! isset($this->context[$option])) {
				$settings[] = [
					'name'   => $this->name,
					'option' => $option,
					'value'  => $value,
				];

				$this->context[$option] = $value;
			}
		}

		app(PluginRepository::class)->addSettings($settings);
	}

	public function loadExternalResources(array $resources): void
	{
		foreach ($resources as $resource) {
			$type = $resource['type'] ?? null;
			$url  = $resource['url'] ?? null;

			match ($type) {
				'css'   => Theme::loadCSSFile($url, ['external' => true]),
				'js'    => Theme::loadJavaScriptFile($url, ['external' => true]),
				default => ErrorHandler::log('[LP] ' . sprintf(Lang::$txt['lp_unsupported_resource_type'], $type)),
			};
		}
	}

	private function getPluginAttribute(): PluginAttribute
	{
		$reflection = new ReflectionClass($this);
		$inheritedType = null;
		$inheritedIcon = null;
		$inheritedSaveable = null;

		$classes = [];
		do {
			$classes[] = $reflection;
			$reflection = $reflection->getParentClass();
		} while ($reflection);

		$classes = array_reverse($classes);
		foreach ($classes as $classReflection) {
			$pluginAttrs = $classReflection->getAttributes(PluginAttribute::class);

			if (empty($pluginAttrs))
				continue;

			$attr = $pluginAttrs[0]->newInstance();

			if ($attr->type !== null) {
				$inheritedType = $attr->type;
			}

			if ($attr->icon !== null) {
				$inheritedIcon = $attr->icon;
			}

			if ($attr->saveable !== null) {
				$inheritedSaveable = $attr->saveable;
			}
		}

		return new PluginAttribute(
			type: $inheritedType,
			icon: $inheritedIcon,
			saveable: $inheritedSaveable
		);
	}
}
