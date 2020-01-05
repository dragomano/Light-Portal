<?php

namespace Bugo\LightPortal\Addons\ThemeSwitcher;

/**
 * ThemeSwitcher
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2020 Bugo
 * @license https://opensource.org/licenses/BSD-3-Clause BSD
 *
 * @version 0.1
 */

if (!defined('SMF'))
	die('Hacking attempt...');

class ThemeSwitcher
{
	/**
	 * Добавляем заголовок и описание блока
	 *
	 * @return void
	 */
	public static function block()
	{
		global $user_info, $txt;

		require_once(__DIR__ . '/langs/' . $user_info['language'] . '.php');

		$txt['lp_block_types']['themeswitcher'] = $txt['lp_themeswitcher_addon_title'];
		$txt['lp_block_types_descriptions']['themeswitcher'] = $txt['lp_themeswitcher_addon_desc'];
	}

	/**
	 * Добавляем параметры блока
	 *
	 * @param array $options
	 * @return void
	 */
	public static function blockOptions(&$options)
	{
		$options['themeswitcher'] = array();
	}

	/**
	 * Формируем контент блока
	 *
	 * @param string $content
	 * @param string $type
	 * @param int $block_id
	 * @return void
	 */
	public static function prepareContent(&$content, $type, $block_id)
	{
		global $smcFunc, $modSettings, $settings;

		if ($type !== 'themeswitcher')
			return;

		if (($available_themes = cache_get_data('light_portal_themeswitcher_addon', 3600)) == null) {
			$request = $smcFunc['db_query']('', '
				SELECT id_theme, value
				FROM {db_prefix}themes
				WHERE id_member = 0
					AND variable = "name"
					AND id_theme IN ({array_int:themes})',
				array(
					'themes' => explode(',', $modSettings['knownThemes'])
				)
			);

			$available_themes = [];
			while ($row = $smcFunc['db_fetch_row']($request))
				$available_themes[$row[0]] = $row[1];

			$smcFunc['db_free_result']($request);

			cache_put_data('light_portal_themeswitcher_addon', $available_themes, 3600);
		}

		ob_start();

		if (!empty($available_themes)) {
			echo '
			<div class="centertext">
				<select id="lp_block_', $block_id, '_themeswitcher" onchange="lp_block_', $block_id, '_themeswitcher_change();" style="cursor: pointer">';

			foreach ($available_themes as $theme_id => $name) {
				echo '
					<option value="', $theme_id, '"', $settings['theme_id'] == $theme_id ? ' selected="selected"' : '', '>', $name, '</option>';
			}

			echo '
				</select>
				<script>
					function lp_block_', $block_id, '_themeswitcher_change() {
						let lp_block_', $block_id, '_themeswitcher_theme_id = document.getElementById("lp_block_', $block_id, '_themeswitcher").value;
						window.location = smf_prepareScriptUrl(smf_scripturl) + "theme=" + lp_block_', $block_id, '_themeswitcher_theme_id;
					}
				</script>';

			echo '
			</div>';
		}

		$content = ob_get_clean();
	}
}
