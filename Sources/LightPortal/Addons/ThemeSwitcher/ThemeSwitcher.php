<?php

/**
 * ThemeSwitcher.php
 *
 * @package ThemeSwitcher (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2022 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 31.12.21
 */

namespace Bugo\LightPortal\Addons\ThemeSwitcher;

use Bugo\LightPortal\Addons\Plugin;

class ThemeSwitcher extends Plugin
{
	public string $icon = 'fas fa-desktop';

	public function init()
	{
		add_integration_function('integrate_manage_themes', __CLASS__ . '::manageThemes#', false, __FILE__);
	}

	public function manageThemes()
	{
		if ($this->request()->only(['done', 'do']))
			$this->cache()->flush();
	}

	public function getAvailableThemes(): array
	{
		return empty($this->modSettings['knownThemes']) ? [] : array_intersect_key($this->getForumThemes(), array_flip(explode(',', $this->modSettings['knownThemes'])));
	}

	public function prepareContent(string $type, int $block_id, int $cache_time)
	{
		if ($type !== 'theme_switcher')
			return;

		$available_themes = $this->cache('theme_switcher_addon')
			->setLifeTime($cache_time)
			->setFallback(__CLASS__, 'getAvailableThemes');

		if (empty($available_themes))
			return;

		echo '
			<div class="themeswitcher centertext">
				<select id="lp_block_', $block_id, '_themeswitcher" onchange="lp_block_', $block_id, '_themeswitcher_change();"', count($available_themes) < 2 ? ' disabled' : '', '>';

		foreach ($available_themes as $theme_id => $name) {
			echo '
					<option value="', $theme_id, '"', $this->settings['theme_id'] == $theme_id ? ' selected="selected"' : '', '>', $name, '</option>';
		}

		echo '
				</select>
				<script>
					function lp_block_', $block_id, '_themeswitcher_change() {
						let lp_block_', $block_id, '_themeswitcher_theme_id = document.getElementById("lp_block_', $block_id, '_themeswitcher").value;
						let search = window.location.search.split(";");
						let search_args = search.filter(function (item) {
							return ! item.startsWith("theme=") && ! item.startsWith("?theme=")
						});
						search = search_args.join(";");
						search = search != "" ? search + ";" : "?";
						window.location = window.location.origin + window.location.pathname + search + "theme=" + lp_block_', $block_id, '_themeswitcher_theme_id;
					}
				</script>
			</div>';
	}
}
