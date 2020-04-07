<?php

/**
 * Template for the plugin management page
 *
 * Шаблон страницы управления плагинами
 *
 * @return void
 */
function template_plugin_settings()
{
	global $txt, $context, $settings;

	echo '
	<div class="cat_bar">
		<h3 class="catbg">', $context['page_title'], '</h3>
	</div>
	<p class="information">', $txt['lp_plugins_desc'], '</p>';

	// This is a magic! Пошла магия!
	foreach ($context['lp_plugins'] as $id => $plugin) {
		$toggle = in_array($plugin, $context['lp_enabled_plugins']) ? 'on' : 'off';
		$plugin = explode("\\", $plugin)[0];
		$plugin_snake_name = get_lp_snake_name($plugin);
		$options = [];

		foreach ($context['lp_plugin_settings'] as $plugin_settings) {
			$plugin_id   = explode('_addon_', substr($plugin_settings[1], 3))[0];
			$plugin_name = str_replace('_', '', ucwords($plugin_id, '_'));
			if ($plugin_name == $plugin)
				$options[] = $plugin_settings;
		}

		echo '
	<div class="windowbg">
		<div class="features" data-id="', $id, '">
			<div class="floatleft">
				<h4>', $plugin, '</h4>
				<div class="smalltext">
					<strong class="new_posts">
						', !empty($txt['lp_' . $plugin_snake_name . '_type']) ? get_lp_plugin_types($txt['lp_' . $plugin_snake_name . '_type']) : $txt['not_applicable'], '
					</strong>
				</div>
			</div>
			<div class="floatright">';

		if (!empty($options)) {
			echo '
				<img class="lp_plugin_settings" data-id="', $plugin_snake_name, '" src="', $settings['default_images_url'], '/icons/config_hd.png" alt="', $txt['settings'], '">';
		}

		echo '
				<i class="lp_plugin_toggle fas fa-3x fa-toggle-', $toggle, '" data-toggle="', $toggle, '"></i>
			</div>';

		if (!empty($options))
			show_lp_plugin_settings($plugin_snake_name, $options);

		echo '
		</div>
	</div>';
	}

	echo '
	<script src="', $settings['default_theme_url'], '/scripts/light_portal/manage_plugins.js"></script>';
}

/**
 * Getting a string converted to snake_case
 *
 * Получаем строку, преобразованную в snake_case
 *
 * @param string $str
 * @param string $glue
 * @return string
 */
function get_lp_snake_name($str, $glue = '_')
{
	$counter  = 0;
	$uc_chars = '';
	$new_str  = array();
	$str_len  = strlen($str);

	for ($x = 0; $x < $str_len; ++$x) {
		$ascii_val = ord($str[$x]);

		if ($ascii_val >= 65 && $ascii_val <= 90)
			$uc_chars .= $str[$x];
	}

	$tok = strtok($str, $uc_chars);

	while ($tok !== false) {
		$new_char  = chr(ord($uc_chars[$counter]) + 32);
		$new_str[] = $new_char . $tok;
		$tok       = strtok($uc_chars);

		++$counter;
	}

	return implode($new_str, $glue);
}

/**
 * Get all types of the plugin
 *
 * Получаем все типы плагина
 *
 * @param array|string $data
 * @return string
 */
function get_lp_plugin_types($data)
{
	global $txt;

	if (is_array($data)) {
		$all_types = [];
		foreach ($data as $type)
			$all_types[] = $txt['lp_plugins_hooks_types'][$type];

		return implode(' + ', $all_types);
	}

	return $txt['lp_plugins_hooks_types'][$data];
}

/**
 * Block with the plugin's settings
 *
 * Блок с настройками плагина
 *
 * @param string $plugin_name
 * @param array $settings
 * @return void
 */
function show_lp_plugin_settings($plugin_name, $settings)
{
	global $txt, $context, $modSettings;

	echo '
	<br class="clear">
	<div class="roundframe" id="', $plugin_name, '_settings" style="display: none">
		<div class="title_bar">
			<h5 class="titlebg">', $txt['settings'], '</h5>
		</div>
		<div class="noticebox">
			<form id="', $plugin_name, '_form" class="form_settings" action="', $context['post_url'], '" method="post" accept-charset="', $context['character_set'], '">
				<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '">
				<input type="hidden" name="', $context['admin-dbsc_token_var'], '" value="', $context['admin-dbsc_token'], '">';

	foreach ($settings as $id => $value) {
		echo '
				<div>
					<label for="', $value[1], '">', $txt[$value[1]], '</label>';

		if ($value[0] == 'text') {
			echo '
					<br><input type="text" name="', $value[1], '" id="', $value[1], '" value="', $modSettings[$value[1]] ?? '', '">';
		} elseif ($value[0] == 'int') {
			echo '
					<br><input type="number" min="0" step="1" name="', $value[1], '" id="', $value[1], '" value="', $modSettings[$value[1]] ?? 0, '">';
		} elseif ($value[0] == 'check') {
			echo '
					<input type="checkbox" name="', $value[1], '" id="', $value[1], '"', !empty($modSettings[$value[1]]) ? ' checked' : '', ' value="1">';
		} else {
			$multiple = false;

			echo '
					<br><select name="', $value[1], !empty($multiple) ? '[]' : '', '" id="', $value[1], '"', !empty($multiple) ? ' multiple style="height: auto"' : '', '>';

			if (!empty($multiple)) {
				if (!empty($modSettings[$value[1]])) {
					$modSettings[$value[1]] = unserialize($modSettings[$value[1]]);

					foreach ($value[2] as $option => $option_title) {
						echo '
							<option value="', $option, '"', !empty($modSettings[$value[1]]) && is_array($modSettings[$value[1]]) && in_array($option, $modSettings[$value[1]]) ? ' selected' : '', '>', $option_title, '</option>';
					}
				} else {
					foreach ($value[2] as $option => $option_title) {
						echo '
							<option value="', $option, '">', $option_title, '</option>';
					}
				}
			} else {
				foreach ($value[2] as $option => $option_title) {
					echo '
							<option value="', $option, '"', !empty($modSettings[$value[1]]) && $modSettings[$value[1]] == $option ? ' selected' : '', '>', $option_title, '</option>';
				}
			}

			echo '
					</select>';
		}

		if (!empty($value['subtext'])) {
			echo '
					<div class="information">', $value['subtext'], '</div>';
		}

		echo '
				</div>';
	}

	echo '
			</form>
		</div>
		<div class="footer">
			<span class="infobox floatleft">Настройки успешно сохранены!</span>
			<span class="errorbox floatleft">Ошибка при сохранении!</span>
			<button type="button" class="close_settings button">', $txt['find_close'], '</button>
			<button form="', $plugin_name, '_form" type="submit" class="button">', $txt['save'], '</button>
		</div>
	</div>';
}