<?php

/**
 * ThemeSwitcher
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2021 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 1.9
 */

namespace Bugo\LightPortal\Addons\ThemeSwitcher;

use Bugo\LightPortal\Addons\Plugin;
use Bugo\LightPortal\Helpers;

class ThemeSwitcher extends Plugin
{
	/**
	 * @var string
	 */
	public $icon = 'fas fa-desktop';

	/**
	 * @return void
	 */
	public function init()
	{
		add_integration_function('integrate_manage_themes', __CLASS__ . '::manageThemes#', false, __FILE__);
	}

	/**
	 * Clean cache on install/uninistall/enabling/disabling themes
	 *
	 * Очищаем кэш при установке/удалении/включении/отключении тем
	 *
	 * @return void
	 */
	public function manageThemes()
	{
		if (Helpers::request()->only(['done', 'do']))
			Helpers::cache()->flush();
	}

	/**
	 * Get the list of active themes
	 *
	 * Получаем список активных шаблонов форума
	 *
	 * @return array
	 */
	public function getAvailableThemes(): array
	{
		global $modSettings;

		return empty($modSettings['knownThemes']) ? [] : array_intersect_key(Helpers::getForumThemes(), array_flip(explode(',', $modSettings['knownThemes'])));
	}

	/**
	 * @param string $type
	 * @param int $block_id
	 * @param int $cache_time
	 * @return void
	 */
	public function prepareContent(string $type, int $block_id, int $cache_time)
	{
		global $settings;

		if ($type !== 'theme_switcher')
			return;

		$available_themes = Helpers::cache('theme_switcher_addon')
			->setLifeTime($cache_time)
			->setFallback(__CLASS__, 'getAvailableThemes');

		if (empty($available_themes))
			return;

		echo '
			<div class="themeswitcher centertext">
				<select id="lp_block_', $block_id, '_themeswitcher" onchange="lp_block_', $block_id, '_themeswitcher_change();"', count($available_themes) < 2 ? ' disabled' : '', '>';

		foreach ($available_themes as $theme_id => $name) {
			echo '
					<option value="', $theme_id, '"', $settings['theme_id'] == $theme_id ? ' selected="selected"' : '', '>', $name, '</option>';
		}

		echo '
				</select>
				<script>
					function lp_block_', $block_id, '_themeswitcher_change() {
						let lp_block_', $block_id, '_themeswitcher_theme_id = document.getElementById("lp_block_', $block_id, '_themeswitcher").value;
						let search = window.location.search.split(";");
						let search_args = search.filter(function (item) {
							return !item.startsWith("theme=") && !item.startsWith("?theme=")
						});
						search = search_args.join(";");
						search = search != "" ? search + ";" : "?";
						window.location = window.location.origin + window.location.pathname + search + "theme=" + lp_block_', $block_id, '_themeswitcher_theme_id;
					}
				</script>
			</div>';
	}
}
