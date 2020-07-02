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
	global $context, $txt, $settings;

	echo '
	<div class="cat_bar">
		<h3 class="catbg">', $context['page_title'], '</h3>
	</div>
	<p class="information">', $txt['lp_plugins_desc'], '</p>';

	// This is a magic! Пошла магия!
	foreach ($context['all_lp_plugins'] as $id => $plugin) {
		echo '
	<div class="windowbg">
		<div class="features" data-id="', $id, '">
			<div class="floatleft">
				<h4>', $plugin['name'], '</h4>
				<div class="smalltext">
					<p>
						<strong class="new_posts">', $plugin['types'], '</strong>
						', $plugin['desc'], '
					</p>
				</div>
			</div>
			<div class="floatright">';

		if (!empty($plugin['settings'])) {
			echo '
				<img class="lp_plugin_settings" data-id="', $plugin['snake_name'], '" src="', $settings['default_images_url'], '/icons/config_hd.png" alt="', $txt['settings'], '">';
		}

		echo '
				<i class="lp_plugin_toggle fas fa-3x fa-toggle-', $plugin['status'], '" data-toggle="', $plugin['status'], '"></i>
			</div>';

		if (!empty($plugin['settings']))
			show_plugin_settings($plugin['snake_name'], $plugin['settings']);

		echo '
		</div>
	</div>';
	}

	echo '
	<script src="', $settings['default_theme_url'], '/scripts/light_portal/manage_plugins.js"></script>';
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
function show_plugin_settings($plugin_name, $settings)
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
					<label', $value[0] != 'multicheck' ? (' for="' . $value[1] . '"') : '', '><strong>', $txt[$value[1]], '</strong></label>';

		if ($value[0] == 'text') {
			echo '
					<br><input type="text" name="', $value[1], '" id="', $value[1], '" value="', $modSettings[$value[1]] ?? '', '">';
		} elseif ($value[0] == 'url') {
			echo '
					<br><input type="url" name="', $value[1], '" id="', $value[1], '" value="', $modSettings[$value[1]] ?? '', '">';
		} elseif ($value[0] == 'color') {
			echo '
					<br><input type="color" name="', $value[1], '" id="', $value[1], '" value="', $modSettings[$value[1]] ?? '', '">';
		} elseif ($value[0] == 'int') {
			echo '
					<br><input type="number" min="0" step="1" name="', $value[1], '" id="', $value[1], '" value="', $modSettings[$value[1]] ?? 0, '">';
		} elseif ($value[0] == 'check') {
			echo '
					<input type="checkbox" name="', $value[1], '" id="', $value[1], '"', !empty($modSettings[$value[1]]) ? ' checked' : '', ' value="1">';
		} elseif ($value[0] == 'multicheck') {
			echo '
					<fieldset>
						<ul>';

			$temp[$value[1] . '_options'] = !empty($modSettings[$value[1]]) ? json_decode($modSettings[$value[1]], true) : [];
			foreach ($context[$value[1] . '_options'] as $key => $option_label) {
				echo '
							<li>
								<label for="', $value[1], '[', $key, ']">
									<input type="checkbox" name="', $value[1], '[', $key, ']" id="', $value[1], '[', $key, ']"', !empty($temp[$value[1] . '_options'][$key]) ? ' checked' : '', ' value="1"> ', $option_label, '
								</label>
							</li>';
			}

			echo '
						</ul>
					</fieldset>';
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
			<span class="infobox floatleft">', $txt['settings_saved'], '</span>
			<span class="errorbox floatleft">', $txt['error_occured'], '</span>
			<button type="button" class="close_settings button">', $txt['find_close'], '</button>
			<button form="', $plugin_name, '_form" type="submit" class="button">', $txt['save'], '</button>
		</div>
	</div>';
}
