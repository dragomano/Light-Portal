<?php

/**
 * Giscus.php
 *
 * @package Giscus (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2023-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 17.01.24
 */

namespace Bugo\LightPortal\Addons\Giscus;

use Bugo\LightPortal\Addons\Plugin;
use Bugo\LightPortal\Utils\{Config, Lang, Utils};

if (! defined('LP_NAME'))
	die('No direct access...');

/**
 * Generated by PluginMaker
 */
class Giscus extends Plugin
{
	public string $type = 'comment';

	private string $url = 'https://giscus.app';

	private array $themes = [
		'light'               => 'GitHub Light',
		'light_high_contrast' => 'GitHub Light High Contrast',
		'light_protanopia'    => 'GitHub Light Protanopia & Deuteranopia',
		'light_tritanopia'    => 'GitHub Light Tritanopia',
		'dark'                => 'GitHub Dark',
		'dark_high_contrast'  => 'GitHub Dark High Contrast',
		'dark_protanopia'     => 'GitHub Dark Protanopia & Deuteranopia',
		'dark_tritanopia'     => 'GitHub Dark Tritanopia',
		'dark_dimmed'         => 'GitHub Dark Dimmed',
		'transparent_dark'    => 'Transparent Dark',
		'cobalt'              => 'RStudio Cobalt',
	];

	public function init(): void
	{
		Lang::$txt['lp_show_comment_block_set']['giscus'] = 'Giscus';
	}

	public function addSettings(array &$config_vars): void
	{
		$this->addDefaultValues([
			'theme' => 'light'
		]);

		$config_vars['giscus'][] = ['text', 'repo', 'subtext' => sprintf(Lang::$txt['lp_giscus']['repo_subtext'], $this->url), 'required' => true];
		$config_vars['giscus'][] = ['text', 'repo_id', 'subtext' => sprintf(Lang::$txt['lp_giscus']['repo_id_subtext'], $this->url), 'required' => true];
		$config_vars['giscus'][] = ['text', 'category', 'subtext' => sprintf(Lang::$txt['lp_giscus']['category_subtext'], $this->url), 'required' => true];
		$config_vars['giscus'][] = ['text', 'category_id', 'subtext' => sprintf(Lang::$txt['lp_giscus']['category_id_subtext'], $this->url), 'required' => true];
		$config_vars['giscus'][] = ['select', 'theme', $this->themes];
	}

	public function comments(): void
	{
		if (! empty(Config::$modSettings['lp_show_comment_block']) && Config::$modSettings['lp_show_comment_block'] === 'giscus'
			&& ! empty(Utils::$context['lp_giscus_plugin']['repo'])
			&& ! empty(Utils::$context['lp_giscus_plugin']['repo_id'])
			&& ! empty(Utils::$context['lp_giscus_plugin']['category'])
			&& ! empty(Utils::$context['lp_giscus_plugin']['category_id'])) {
			Utils::$context['lp_giscus_comment_block'] = /** @lang text */
				'
			<div class="giscus windowbg"></div>
			<script src="https://giscus.app/client.js"
				data-repo="' . Utils::$context['lp_giscus_plugin']['repo'] . '"
				data-repo-id="' . Utils::$context['lp_giscusplugin']['repo_id'] . '"
				data-category="' . Utils::$context['lp_giscus_plugin']['category'] . '"
				data-category-id="' . Utils::$context['lp_giscus_plugin']['category_id'] . '"
				data-mapping="title"
				data-strict="1"
				data-reactions-enabled="1"
				data-emit-metadata="0"
				data-input-position="bottom"
				data-theme="' . Utils::$context['lp_giscus_plugin']['theme'] . '"
				data-lang="' . Lang::$txt['lang_dictionary'] . '"
				data-loading="lazy"
				crossorigin="anonymous"
				async>
			</script>';
		}
	}
}
