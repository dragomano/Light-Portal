<?php

/**
 * @package ThemeSwitcher (Light Portal)
 * @link https://custom.simplemachines.org/index.php?mod=4244
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2024 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @category addon
 * @version 05.06.24
 */

namespace Bugo\LightPortal\Addons\ThemeSwitcher;

use Bugo\Compat\Theme;
use Bugo\LightPortal\Addons\Block;
use Bugo\LightPortal\Enums\Hook;

if (! defined('LP_NAME'))
	die('No direct access...');

class ThemeSwitcher extends Block
{
	public string $icon = 'fas fa-desktop';

	public function init(): void
	{
		$this->applyHook(Hook::manageThemes);
	}

	public function manageThemes(): void
	{
		if ($this->request()->only(['done', 'do']))
			$this->cache()->flush();
	}

	public function prepareContent(object $data): void
	{
		if ($data->type !== 'theme_switcher')
			return;

		$themes = $this->getForumThemes();

		if (empty($themes))
			return;

		$id = $data->id;

		echo '
			<div class="themeswitcher centertext">
				<select id="lp_block_', $id, '_themeswitcher" onchange="lp_block_', $id, '_themeswitcher_change();"', count($themes) < 2 ? ' disabled' : '', '>';

		foreach ($themes as $themeId => $name) {
			echo '
					<option value="', $themeId, '"', Theme::$current->settings['theme_id'] == $themeId ? ' selected="selected"' : '', '>
						', $name, '
					</option>';
		}

		echo '
				</select>
				<script>
					function lp_block_', $id, '_themeswitcher_change() {
						let lp_block_', $id, '_themeswitcher_theme_id = document.getElementById("lp_block_', $id, '_themeswitcher").value;
						let search = window.location.search.split(";");
						let search_args = search.filter(function (item) {
							return ! item.startsWith("theme=") && ! item.startsWith("?theme=")
						});
						search = search_args.join(";");
						search = search != "" ? search + ";" : "?";
						window.location = window.location.origin + window.location.pathname + search + "theme=" + lp_block_', $id, '_themeswitcher_theme_id;
					}
				</script>
			</div>';
	}
}
