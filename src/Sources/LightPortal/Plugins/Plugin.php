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

namespace LightPortal\Plugins;

use Bugo\Compat\ErrorHandler;
use Bugo\Compat\Lang;
use Bugo\Compat\Theme;
use Bugo\Compat\Utils;
use LightPortal\Database\PortalSqlInterface;
use LightPortal\Enums\PluginType;
use LightPortal\Repositories\PluginRepositoryInterface;
use LightPortal\Utils\Setting;
use LightPortal\Utils\Str;
use LightPortal\Utils\Traits\HasBreadcrumbs;
use LightPortal\Utils\Traits\HasCache;
use LightPortal\Utils\Traits\HasForumHooks;
use LightPortal\Utils\Traits\HasPortalSql;
use LightPortal\Utils\Traits\HasRequest;
use LightPortal\Utils\Traits\HasResponse;
use LightPortal\Utils\Traits\HasSession;
use ReflectionClass;
use Stringable;

use function LightPortal\app;

if (! defined('LP_NAME'))
	die('No direct access...');

/**
 * @method void addLayerAbove(Event $e)
 * @method void addLayerBelow(Event $e)
 * @method void addSettings(Event $e)
 * @method void afterPageContent(Event $e)
 * @method void beforePageContent(Event $e)
 * @method void changeIconSet(Event $e)
 * @method void commentButtons(Event $e)
 * @method void comments(Event $e)
 * @method void credits(Event $e)
 * @method void downloadRequest(Event $e)
 * @method void extendAdminAreas(Event $e)
 * @method void extendBasicConfig(Event $e)
 * @method void extendBlockAreas(Event $e)
 * @method void extendCategoryAreas(Event $e)
 * @method void extendPageAreas(Event $e)
 * @method void extendPluginAreas(Event $e)
 * @method void extendTagAreas(Event $e)
 * @method void findBlockErrors(Event $e)
 * @method void findPageErrors(Event $e)
 * @method void frontAssets(Event $e)
 * @method void frontBoards(Event $e)
 * @method void frontBoardsRow(Event $e)
 * @method void frontLayouts(Event $e)
 * @method void frontModes(Event $e)
 * @method void frontPages(Event $e)
 * @method void frontPagesRow(Event $e)
 * @method void frontTopics(Event $e)
 * @method void frontTopicsRow(Event $e)
 * @method void init(Event $e)
 * @method void layoutExtensions(Event $e)
 * @method void onBlockRemoving(Event $e)
 * @method void onBlockSaving(Event $e)
 * @method void onCustomPageImport(Event $e)
 * @method void onPageRemoving(Event $e)
 * @method void onPageSaving(Event $e)
 * @method void parseContent(Event $e)
 * @method void preloadStyles(Event $e)
 * @method void prepareAssets(Event $e)
 * @method void prepareBlockFields(Event $e)
 * @method void prepareBlockParams(Event $e)
 * @method void prepareContent(Event $e)
 * @method void prepareEditor(Event $e)
 * @method void prepareIconList(Event $e)
 * @method void prepareIconTemplate(Event $e)
 * @method void preparePageData(Event $e)
 * @method void preparePageFields(Event $e)
 * @method void preparePageParams(Event $e)
 * @method void saveSettings(Event $e)
 * @method void validateBlockParams(Event $e)
 * @method void validatePageParams(Event $e)
 */
abstract class Plugin implements PluginInterface, Stringable
{
	use HasBreadcrumbs;
	use HasCache;
	use HasForumHooks;
	use HasPortalSql;
	use HasRequest;
	use HasResponse;
	use HasSession;

	protected PortalSqlInterface $sql;

	protected string $name;

	protected array $context;

	protected array $txt;

	public function __construct(
		public string $type = 'other',
		public string $icon = 'fas fa-puzzle-piece',
		public bool $showSaveButton = true,
	)
	{
		$this->name = $this->getSnakeName();
		$this->type = $this->getPluginType();
		$this->icon = $this->getPluginIcon();
		$this->showSaveButton = $this->isPluginHasSaveButton();

		$this->sql = $this->getPortalSql();

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

		if (is_array($type)) {
			return implode(' ', array_map(fn(PluginType $t) => $t->name(), $type));
		}

		return $type instanceof PluginType ? $type->name() : $type;
	}

	public function getPluginIcon(): string
	{
		$pluginAttr = $this->getPluginAttribute();

		return $pluginAttr->icon ?? $this->icon;
	}

	public function isPluginHasSaveButton(): bool
	{
		$pluginAttr = $this->getPluginAttribute();

		return $pluginAttr->showSaveButton ?? $this->showSaveButton;
	}

	public function isEnabled(): bool
	{
		return in_array($this->getCamelName(), Setting::getEnabledPlugins());
	}

	public function addDefaultValues(array $values): void
	{
		$new = [];
		foreach ($values as $config => $value) {
			if (! isset($this->context[$config])) {
				$new[$config] = $value;
				$this->context[$config] = $value;
			}
		}

		if ($new) {
			app(PluginRepositoryInterface::class)->changeSettings($this->name, $new);
		}
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
		$inheritedShowSaveButton = null;

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

			if ($attr->showSaveButton !== null) {
				$inheritedShowSaveButton = $attr->showSaveButton;
			}
		}

		return new PluginAttribute(
			type: $inheritedType,
			icon: $inheritedIcon,
			showSaveButton: $inheritedShowSaveButton
		);
	}
}
