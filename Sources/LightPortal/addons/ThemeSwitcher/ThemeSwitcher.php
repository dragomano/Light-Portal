<?php

namespace Bugo\LightPortal\Addons\ThemeSwitcher;

use Bugo\LightPortal\Helpers;

/**
 * ThemeSwitcher
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2020 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 1.2
 */

if (!defined('SMF'))
	die('Hacking attempt...');

class ThemeSwitcher
{
	/**
	 * Specify an icon (from the FontAwesome Free collection)
	 *
	 * Указываем иконку (из коллекции FontAwesome Free)
	 *
	 * @var string
	 */
	public static $addon_icon = 'fas fa-desktop';

	/**
	 * Get the list of active themes
	 *
	 * Получаем список активных шаблонов форума
	 *
	 * @return array
	 */
	public static function getAvailableThemes()
	{
		global $modSettings, $smcFunc, $context;

		if (empty($modSettings['knownThemes']))
			return [];

		$request = $smcFunc['db_query']('', '
			SELECT id_theme, value
			FROM {db_prefix}themes
			WHERE id_member = 0
				AND variable = {string:name}
				AND id_theme IN ({array_int:themes})',
			array(
				'name'   => 'name',
				'themes' => explode(',', $modSettings['knownThemes'])
			)
		);

		$available_themes = [];
		while ($row = $smcFunc['db_fetch_row']($request))
			$available_themes[$row[0]] = $row[1];

		$smcFunc['db_free_result']($request);
		$context['lp_num_queries']++;

		return $available_themes;
	}

	/**
	 * Form the block content
	 *
	 * Формируем контент блока
	 *
	 * @param string $content
	 * @param string $type
	 * @param int $block_id
	 * @param int $cache_time
	 * @return void
	 */
	public static function prepareContent(&$content, $type, $block_id, $cache_time)
	{
		global $settings;

		if ($type !== 'theme_switcher')
			return;

		$available_themes = Helpers::getFromCache('theme_switcher_addon', 'getAvailableThemes', __CLASS__, $cache_time);

		if (!empty($available_themes)) {
			ob_start();

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
				</script>';

			echo '
			</div>';

			$content = ob_get_clean();
		}
	}
}
