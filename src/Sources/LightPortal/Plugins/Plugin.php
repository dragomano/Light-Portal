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

use Bugo\Compat\{Config, Db};
use Bugo\Compat\{Lang, ServerSideIncludes, Theme, Utils};
use Bugo\LightPortal\Repositories\PluginRepository;
use Bugo\LightPortal\Utils\{CacheTrait, EntityDataTrait};
use Bugo\LightPortal\Utils\{HasReflectionAware, HasTemplateAware};
use Bugo\LightPortal\Utils\{RequestTrait, SessionTrait, SMFHookTrait, Str};

use function array_column;
use function array_filter;
use function array_flip;
use function basename;
use function dirname;
use function explode;
use function str_replace;

if (! defined('LP_NAME'))
	die('No direct access...');

abstract class Plugin
{
	use CacheTrait;
	use EntityDataTrait;
	use HasReflectionAware;
	use HasTemplateAware;
	use RequestTrait;
	use SMFHookTrait;
	use SessionTrait;

	public string $name;

	public string $icon = 'fas fa-puzzle-piece';

	public bool $saveable = true;

	private static PluginRepository $repository;

	private static array $settings;

	protected array $context;

	protected array $txt;

	public function __construct()
	{
		$this->name = Str::getSnakeName(basename(str_replace('\\', '/', static::class)));

		self::$repository ??= new PluginRepository();

		self::$settings ??= self::$repository->getSettings();

		$this->context = self::$settings[$this->name] ?? [];

		$this->txt = Lang::$txt['lp_' . $this->name];

		// @TODO This variable is still needed in some templates
		Utils::$context['lp_' . $this->name . '_plugin'] = $this->context;
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

		self::$repository->addSettings($settings);
	}

	public function isDarkTheme(?string $option): bool
	{
		if (empty($option))
			return false;

		$themes = array_flip(array_filter(explode(',', $option)));

		return $themes && isset($themes[Theme::$current->settings['theme_id']]);
	}

	public function getForumThemes(): array
	{
		if (($themes = $this->cache()->get('forum_themes')) === null) {
			$result = Db::$db->query('', '
				SELECT id_theme, value
				FROM {db_prefix}themes
				WHERE id_theme IN ({array_int:themes})
					AND variable = {literal:name}',
				[
					'themes' => empty(Config::$modSettings['knownThemes'])
						? []
						: explode(',', (string) Config::$modSettings['knownThemes']),
				]
			);

			$themes = [];
			while ($row = Db::$db->fetch_assoc($result)) {
				$themes[$row['id_theme']] = [
					'id'   => (int) $row['id_theme'],
					'name' => $row['value'],
				];
			}

			Db::$db->free_result($result);

			$themes = array_column($themes, 'name', 'id');
			$this->cache()->put('forum_themes', $themes);
		}

		return $themes;
	}
}
