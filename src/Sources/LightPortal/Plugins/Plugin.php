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

use Bugo\Compat\{Config, Db, ServerSideIncludes, Theme, Utils};
use Bugo\LightPortal\Repositories\PluginRepository;
use Bugo\LightPortal\Utils\CacheTrait;
use Bugo\LightPortal\Utils\EntityDataTrait;
use Bugo\LightPortal\Utils\RequestTrait;
use Bugo\LightPortal\Utils\SessionTrait;
use Bugo\LightPortal\Utils\SMFHookTrait;
use Bugo\LightPortal\Utils\Str;
use ReflectionClass;

use function array_column;
use function array_filter;
use function array_flip;
use function dirname;
use function explode;
use function is_file;

if (! defined('SMF'))
	die('No direct access...');

abstract class Plugin
{
	use CacheTrait;
	use EntityDataTrait;
	use RequestTrait;
	use SMFHookTrait;
	use SessionTrait;

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

	public function setTemplate(string $name = 'template'): self
	{
		$path = dirname($this->getCalledClass()->getFileName()) . DIRECTORY_SEPARATOR . $name . '.php';

		if (is_file($path)) {
			require_once $path;
		}

		return $this;
	}

	public function withSubTemplate(string $template): self
	{
		Utils::$context['sub_template'] = $template;

		return $this;
	}

	public function withLayer(string $layer): self
	{
		Utils::$context['template_layers'][] = $layer;

		return $this;
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
		$snakeName = Str::getSnakeName($this->getName());

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

	public function getForumThemes(): array
	{
		$themes = $this->cache()->get('forum_themes');

		if ($themes === null) {
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
