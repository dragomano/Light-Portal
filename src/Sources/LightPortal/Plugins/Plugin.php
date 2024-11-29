<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.8
 */

namespace Bugo\LightPortal\Plugins;

use Bugo\Compat\{Lang, ServerSideIncludes, Utils};
use Bugo\LightPortal\Repositories\PluginRepository;
use Bugo\LightPortal\Utils\{CacheTrait, EntityDataTrait, HasTemplateAware};
use Bugo\LightPortal\Utils\{RequestTrait, SessionTrait, SMFHookTrait, Str};

use function basename;
use function dirname;
use function str_replace;

if (! defined('LP_NAME'))
	die('No direct access...');

abstract class Plugin implements PluginInterface
{
	use CacheTrait;
	use EntityDataTrait;
	use HasTemplateAware;
	use RequestTrait;
	use SMFHookTrait;
	use SessionTrait;

	public string $icon = 'fas fa-puzzle-piece';

	public bool $saveable = true;

	protected string $name;

	protected array $context;

	protected array $txt;

	public function __construct()
	{
		$this->name = $this->getShortName();

		$this->context = &Utils::$context['lp_' . $this->name . '_plugin'];

		$this->txt = &Lang::$txt['lp_' . $this->name];
	}

	public function getShortName(): string
	{
		return Str::getSnakeName(basename(str_replace('\\', '/', static::class)));
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

		(new PluginRepository())->addSettings($settings);
	}
}
