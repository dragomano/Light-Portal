<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.9
 */

namespace Bugo\LightPortal\Plugins;

use Bugo\Compat\ErrorHandler;
use Bugo\Compat\Lang;
use Bugo\Compat\ServerSideIncludes;
use Bugo\Compat\Theme;
use Bugo\Compat\Utils;
use Bugo\LightPortal\Repositories\PluginRepository;
use Bugo\LightPortal\Utils\Str;
use Bugo\LightPortal\Utils\Traits\HasCache;
use Bugo\LightPortal\Utils\Traits\HasBreadcrumbs;
use Bugo\LightPortal\Utils\Traits\HasTemplate;
use Bugo\LightPortal\Utils\Traits\HasRequest;
use Bugo\LightPortal\Utils\Traits\HasResponse;
use Bugo\LightPortal\Utils\Traits\HasSession;
use Bugo\LightPortal\Utils\Traits\HasForumHooks;
use Stringable;

use function basename;
use function dirname;
use function sprintf;
use function str_replace;

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
	use HasTemplate;

	public string $type;

	public string $icon = 'fas fa-puzzle-piece';

	public bool $saveable = true;

	protected string $name;

	protected array $context;

	protected array $txt;

	public function __construct()
	{
		$this->name = $this->getSnakeName();

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

	public function getFromSSI(string $function, ...$params)
	{
		require_once dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'SSI.php';

		return ServerSideIncludes::{$function}(...$params);
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
}
