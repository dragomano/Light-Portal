<?php declare(strict_types=1);

/**
 * @package PrettyUrls (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2021-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category plugin
 * @version 01.10.25
 */

namespace Bugo\LightPortal\Plugins\PrettyUrls;

use Bugo\LightPortal\Enums\PluginType;
use Bugo\LightPortal\Enums\PortalHook;
use Bugo\LightPortal\Plugins\HookAttribute;
use Bugo\LightPortal\Plugins\PluginAttribute;
use Bugo\Compat\Config;
use Bugo\Compat\Utils;
use Bugo\LightPortal\Plugins\Plugin;
use Bugo\LightPortal\Utils\Str;

use const LP_ACTION;
use const LP_ALIAS_PATTERN;
use const LP_NAME;
use const LP_PAGE_PARAM;

if (! defined('LP_NAME'))
	die('No direct access...');

#[PluginAttribute(type: PluginType::SEO)]
class PrettyUrls extends Plugin
{
	private const PRIORITY = 30;

	#[HookAttribute(PortalHook::init)]
	public function init(): void
	{
		if (! is_file($file = Config::$sourcedir . '/Subs-PrettyUrls.php'))
			return;

		Utils::$context['pretty']['action_array'] = array_merge(
			Utils::$context['pretty']['action_array'] ?? [],
			[LP_ACTION]
		);

		$prettyFilters = unserialize(Config::$modSettings['pretty_filters']);

		if (isset($prettyFilters['lp-pages']))
			return;

		require_once $file;

		$prettyFilters['lp-pages'] = [
			'description' => 'Rewrite URLs for ' . LP_NAME . ' pages',
			'enabled' => 0,
			'filter' => [
				'priority' => self::PRIORITY,
				'callback' => [$this, 'filter'],
			],
			'rewrite' => [
				'priority' => self::PRIORITY,
				'rule' => 'RewriteRule ^pages/' . LP_ALIAS_PATTERN . ' ./index.php?pretty;' . LP_PAGE_PARAM . '=$1 [L,QSA]',
				"nginx" => 'rewrite ^pages/' . LP_ALIAS_PATTERN . ' "/index.php?pretty;' . LP_PAGE_PARAM . '=$1" last;'
			],
			'title' => Str::html('a')
				->href('https://custom.simplemachines.org/mods/index.php?mod=4244')
				->target('_blank')
				->rel('noopener')
				->setText(LP_NAME) . ' pages',
		];

		Config::updateModSettings(['pretty_filters' => serialize($prettyFilters)]);

		require_once __DIR__ . '/functions.php';

		pretty_update_filters();
	}

	public function filter(array $urls): array
	{
		$pattern = '`' . Config::$scripturl . '(.*)' . LP_PAGE_PARAM . '=([^;]+)`S';
		$replacement = Config::$boardurl . '/pages/$2/$1';

		foreach ($urls as $id => $url) {
			if (! isset($url['replacement']) && preg_match($pattern, (string) $url['url'])) {
				$urls[$id]['replacement'] = preg_replace($pattern, $replacement, (string) $url['url']);
			}
		}

		return $urls;
	}
}
