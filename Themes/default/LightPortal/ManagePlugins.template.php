<?php

/**
 * Template for the plugin management page
 *
 * Шаблон страницы управления плагинами
 *
 * @return void
 */
function template_manage_plugins()
{
	global $scripturl, $context, $txt, $settings;

	echo '
	<div class="cat_bar">
		<h3 class="catbg">
			', $context['lp_plugins_extra'], '
		</h3>
	</div>
	<div class="information">
		', $txt['lp_plugins_desc'];

	echo '
		<div class="floatright">
			<form action="', $scripturl . '?action=admin;area=lp_plugins" method="post">
				<label for="filter">', $txt['apply_filter'], '</label>
				<select id="filter" name="filter" onchange="this.form.submit()">
					<option value="all"', $context['current_filter'] == 'all' ? ' selected' : '', '>', $txt['all'], '</option>';

	foreach ($context['lp_plugin_types'] as $type => $title) {
		echo '
					<option value="', $type, '"', $context['current_filter'] == $type ? ' selected' : '', '>', $title, '</option>';
	}

	echo '
				</select>
			</form>
		</div>
	</div>';

	// This is a magic! Пошла магия!
	$i = 0;
	foreach ($context['all_lp_plugins'] as $id => $plugin) {
		echo '
	<div class="windowbg">
		<div class="features" data-id="', $id, '" x-data>
			<div class="floatleft">
				<span class="counter">', ++$i, '</span>
				<h4>', $plugin['name'], '</h4>
				<div class="smalltext">
					<p>
						<strong class="new_posts">', $plugin['types'], '</strong>
						', $plugin['desc'], '
					</p>';

		if (!empty($plugin['author']) && $plugin['author'] !== 'Bugo') {
			echo '
					<p>', $plugin['author'], (!empty($plugin['link']) && $plugin['link'] !== 'https://dragomano.ru/mods/light-portal' ? (' | ' . $plugin['link']) : ''), '</p>';
		}

		echo '
				</div>
			</div>
			<div class="floatright">';

		if (!empty($plugin['settings'])) {
			echo '
				<img class="lp_plugin_settings" data-id="', $plugin['snake_name'], $context['session_id'], '" src="', $settings['default_images_url'], '/icons/config_hd.png" alt="', $txt['settings'], '" @click="plugin.showSettings($event.target)">';
		}

		if ($plugin['types'] === $txt['lp_sponsors_only'] ) {
			echo '
				<i class="fas fa-3x fa-donate"></i>';
		} else {
			echo '
				<i class="lp_plugin_toggle fas fa-3x fa-toggle-', $plugin['status'], '" data-toggle="', $plugin['status'], '" @click="plugin.toggle($event.target)"></i>';
		}

		echo '
			</div>';

		if (!empty($plugin['settings']))
			show_plugin_settings($plugin['snake_name'] . $context['session_id'], $plugin['settings']);

		echo '
		</div>
	</div>';
	}

	echo '
	<script>
		const plugin = new Plugin();
	</script>';
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
	<div class="roundframe" id="', $plugin_name, '_settings" style="display: none" x-data="{success: false}">
		<div class="title_bar">
			<h5 class="titlebg">', $txt['settings'], '</h5>
		</div>
		<div class="noticebox">
			<form id="', $plugin_name, '_form_', $context['session_id'], '" class="form_settings" action="', $context['post_url'], '" method="post" accept-charset="', $context['character_set'], '" @submit.prevent="success = plugin.saveSettings($event.target, $refs)">
				<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '">
				<input type="hidden" name="', $context['admin-dbsc_token_var'], '" value="', $context['admin-dbsc_token'], '">';

	foreach ($settings as $value) {
		echo '
				<div>';

		if (!in_array($value[0], array('callback', 'desc', 'check'))) {
			echo '
					<label', $value[0] != 'multicheck' ? (' for="' . $value[1] . '"') : '', '><strong>', $txt[$value[1]], '</strong></label>';
		}

		if ($value[0] == 'text') {
			echo '
					<br><input type="text" name="', $value[1], '" id="', $value[1], '" value="', $modSettings[$value[1]] ?? '', '"', !empty($value['pattern']) ? (' pattern="' . $value['pattern'] . '"') : '', '>';
		} elseif ($value[0] == 'large_text') {
			echo '
					<br><textarea name="', $value[1], '" id="', $value[1], '">', $modSettings[$value[1]] ?? '', '</textarea>';
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
					<input type="checkbox" name="', $value[1], '" id="', $value[1], '"', !empty($modSettings[$value[1]]) ? ' checked' : '', ' value="1" class="checkbox">
					<label class="label" for="', $value[1], '"><strong>', $txt[$value[1]], '</strong></label>';
		} elseif ($value[0] == 'callback' && !empty($value[2])) {
			if (isset($value[2][0]) && isset($value[2][1]) && method_exists($value[2][0], $value[2][1])) {
				call_user_func($value[2]);
			}
		} elseif ($value[0] == 'desc') {
			echo '
					<div class="roundframe">', $txt[$value[1]], '</div>';
		} elseif ($value[0] == 'multicheck') {
			echo '
					<fieldset>
						<ul>';

			$temp[$value[1] . '_options'] = !empty($modSettings[$value[1]]) ? json_decode($modSettings[$value[1]], true) : [];
			foreach ($value[2] as $key => $option_label) {
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
					<br>
					<select name="', $value[1], !empty($multiple) ? '[]' : '', '" id="', $value[1], '"', !empty($multiple) ? ' multiple style="height: auto"' : '', '>';

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
					<div class="roundframe">', $value['subtext'], '</div>';
		}

		echo '
				</div>';
	}

	echo '
			</form>
		</div>
		<div class="footer">
			<span x-ref="info" x-show.transition="success" class="infobox floatleft">', $txt['settings_saved'], '</span>
			<button type="button" class="button" @click="plugin.hideSettings($event.target)">', $txt['find_close'], '</button>
			<button form="', $plugin_name, '_form_', $context['session_id'], '" type="submit" class="button">', $txt['save'], '</button>
		</div>
	</div>';
}
