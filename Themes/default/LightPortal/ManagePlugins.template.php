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
	global $context, $scripturl, $txt, $settings;

	echo '
	<div class="cat_bar">
		<h3 class="catbg">', $context['lp_plugins_extra'];

	echo '
			<span class="floatright">
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
			</span>
		</h3>
	</div>
	<div class="information">', $txt['lp_plugins_desc'], '</div>';

	// This is a magic! Пошла магия!
	foreach ($context['all_lp_plugins'] as $id => $plugin) {
		echo '
	<div class="windowbg">
		<div class="features" data-id="', $id, '" x-data>
			<div class="floatleft">
				<h4>', $plugin['name'], ' <strong class="new_posts">', $plugin['types'], '</strong></h4>
				<div>
					<p>', $plugin['desc'], '</p>';

		if (!empty($plugin['requires'])) {
			echo '
					<p class="roundframe">
						<span class="infobox">
							<strong>', $txt['lp_plugins_requires'], '</strong>: ';

			echo implode(', ', $plugin['requires']);

			echo '
						</span>
					</p>';
		}

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
				<img class="lp_plugin_settings" data-id="', $plugin['snake_name'], '_', $context['session_id'], '" src="', $settings['default_images_url'], '/icons/config_hd.png" alt="', $txt['settings'], '" @click="plugin.showSettings($event.target)">';
		}

		if ($plugin['types'] === $txt['lp_sponsorable']) {
			echo '
				<a href="', $context['lp_can_donate'][$plugin['name']], '" rel="noopener" target="_blank"><i class="fas fa-3x fa-donate"></i></a>';
		} elseif ($plugin['types'] === $txt['lp_downloadable']) {
			echo '
				<a href="', $context['lp_can_download'][$plugin['name']], '" rel="noopener" target="_blank"><i class="fas fa-3x fa-download"></i></a>';
		} else {
			echo '
				<i class="lp_plugin_toggle fas fa-3x fa-toggle-', $plugin['status'], '" data-toggle="', $plugin['status'], '" @click="plugin.toggle($event.target)"></i>';
		}

		echo '
			</div>';

		if (!empty($plugin['settings']))
			show_plugin_settings($plugin['snake_name'], $plugin['settings']);

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
function show_plugin_settings(string $plugin_name, array $settings)
{
	global $txt, $context, $modSettings;

	echo '
	<br class="clear">
	<div class="roundframe" id="', $plugin_name, '_', $context['session_id'], '_settings" style="display: none" x-data="{success: false}">
		<div class="title_bar">
			<h5 class="titlebg">', $txt['settings'], '</h5>
		</div>
		<div class="noticebox">
			<form id="', $plugin_name, '_form_', $context['session_id'], '" class="form_settings" action="', $context['post_url'], '" method="post" accept-charset="', $context['character_set'], '" @submit.prevent="success = plugin.saveSettings($event.target, $refs)">
				<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '">
				<input type="hidden" name="', $context['admin-dbsc_token_var'], '" value="', $context['admin-dbsc_token'], '">';

	foreach ($settings as $value) {
		$label = $txt['lp_' . $plugin_name][$value[1]] ?? '';
		$value[1] = 'lp_' . $plugin_name . '_addon_' . $value[1];

		echo '
				<div>';

		if (!in_array($value[0], array('callback', 'desc', 'check'))) {
			echo '
					<label', $value[0] != 'multicheck' ? (' for="' . $value[1] . '"') : '', '><strong>', $label, '</strong></label>';
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
			$min = ' min="' . ($value['min'] ?? 0) . '"';
			$max = isset($value['max']) ? ' max="' . $value['max'] . '"' : '';
			$step = ' step="' . ($value['step'] ?? 1) . '"';

			echo '
					<br><input type="number"', $min, $max, $step, ' name="', $value[1], '" id="', $value[1], '" value="', $modSettings[$value[1]] ?? 0, '">';
		} elseif ($value[0] == 'float') {
			$min = ' min="' . ($value['min'] ?? 0) . '"';
			$max = isset($value['max']) ? ' max="' . $value['max'] . '"' : '';
			$step = ' step="' . ($value['step'] ?? 0.01) . '"';

			echo '
					<br><input type="number"', $min, $max, $step, ' name="', $value[1], '" id="', $value[1], '" value="', $modSettings[$value[1]] ?? 0, '">';
		} elseif ($value[0] == 'check') {
			echo '
					<input type="checkbox" name="', $value[1], '" id="', $value[1], '"', !empty($modSettings[$value[1]]) ? ' checked' : '', ' value="1" class="checkbox">
					<label class="label" for="', $value[1], '"><strong>', $label, '</strong></label>';
		} elseif ($value[0] == 'callback' && !empty($value[2])) {
			if (isset($value[2][0]) && isset($value[2][1]) && method_exists($value[2][0], $value[2][1])) {
				call_user_func($value[2]);
			}
		} elseif ($value[0] == 'desc') {
			echo '
					<div class="roundframe">', $label, '</div>';
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
			$multiple = $value['multiple'] ?? false;

			echo '
					<br>
					<select name="', $value[1], !empty($multiple) ? '[]' : '', '" id="', $value[1], '"', !empty($multiple) ? ' multiple style="height: auto"' : '', '>';

			if (!empty($multiple)) {
				if (!empty($modSettings[$value[1]])) {
					$modSettings[$value[1]] = json_decode($modSettings[$value[1]], true);

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

		if (!empty($value['postfix'])) {
			echo '
					', $value['postfix'];
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
