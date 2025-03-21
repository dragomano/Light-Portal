<?php declare(strict_types=1);

/**
 * @package BootstrapIcons (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2021-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category plugin
 * @version 22.12.24
 */

namespace Bugo\LightPortal\Plugins\BootstrapIcons;

use Bugo\Compat\Theme;
use Bugo\Compat\Utils;
use Bugo\LightPortal\Plugins\Event;
use Bugo\LightPortal\Plugins\Plugin;

if (! defined('LP_NAME'))
	die('No direct access...');

/**
 * Generated by PluginMaker
 */
class BootstrapIcons extends Plugin
{
	public string $type = 'icons';

	private string $prefix = 'bi bi-';

	public function init(): void
	{
		Theme::loadCSSFile(
			'https://cdn.jsdelivr.net/npm/bootstrap-icons@1/font/bootstrap-icons.min.css',
			[
				'external' => true,
				'seed'     => false,
			]
		);
	}

	public function prepareIconList(Event $e): void
	{
		$cacheTTL = 30 * 24 * 60 * 60;

		if (($biIcons = $this->cache()->get('all_bi_icons', $cacheTTL)) === null) {
			$content = file_get_contents('https://cdn.jsdelivr.net/npm/bootstrap-icons@1/font/bootstrap-icons.json');
			$json = array_flip(Utils::jsonDecode($content, true));

			$biIcons = [];
			foreach ($json as $icon) {
				$biIcons[] = $this->prefix . $icon;
			}

			$this->cache()->put('all_bi_icons', $biIcons, $cacheTTL);
		}

		$e->args->icons = array_merge($e->args->icons, $biIcons);
	}

	public function credits(Event $e): void
	{
		$e->args->links[] = [
			'title' => 'Bootstrap Icons',
			'link' => 'https://github.com/twbs/icons',
			'author' => 'The Bootstrap Authors',
			'license' => [
				'name' => 'the MIT License',
				'link' => 'https://github.com/twbs/icons/blob/main/LICENSE.md'
			]
		];
	}
}
