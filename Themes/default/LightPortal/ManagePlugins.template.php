<?php

function template_manage_plugins()
{
	global $context, $scripturl, $txt;

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
						<option value="', $type, '"', $context['current_filter'] === $type ? ' selected' : '', '>', $title, '</option>';
	}

	echo '
					</select>
				</form>
			</span>
		</h3>
	</div>
	<script src="https://cdn.jsdelivr.net/npm/@shat/stylenames@v1/lib/index.umd.js"></script>
	<div class="information" x-data>
		', $txt['lp_plugins_desc'], '
		<div class="hidden-xs floatright" style="cursor: pointer">
			', str_replace(' class=', ' @click="plugin.toggleToListView($event.target)" :style="styleNames({ opacity: plugin.isCardView() ? \'.5\' : \'1\' })" class=', $context['lp_icon_set']['simple']), ' ', str_replace(' class=', ' @click="plugin.toggleToCardView($event.target)" :style="styleNames({ opacity: plugin.isCardView() ? \'1\' : \'.5\' })" class=', $context['lp_icon_set']['tile']), '
		</div>
	</div>';

	if (! empty($context['lp_addon_chart'])) {
		echo '
	<canvas id="addonChart"></canvas>';
	}

	echo '
	<div id="addon_list" x-data :class="{ \'addon_list\': plugin.isCardView() }">';

	foreach ($context['all_lp_plugins'] as $id => $plugin) {
		echo '
		<div class="windowbg">
			<div class="features" data-id="', $id, '" x-data>
				<div class="floatleft">
					<h4>', $plugin['name'];

		foreach ($plugin['types'] as $type => $label_class) {
			echo '
						<strong class="new_posts', $label_class, '">', $type, '</strong>';
		}

		echo '
					</h4>
					<div>
						<p>';

		if (! empty($plugin['special'])) {
			if ($plugin['special'] === $txt['lp_can_donate']) {
				$lang = $context['lp_can_donate'][$plugin['name']]['languages'];
				echo $lang[$context['user']['language']] ?? $lang['english'] ?? '';
			} elseif ($plugin['special'] === $txt['lp_can_download']) {
				$lang = $context['lp_can_download'][$plugin['name']]['languages'];
				echo $lang[$context['user']['language']] ?? $lang['english'] ?? '';
			}
		} else {
			echo $plugin['desc'];
		}

		echo '
						</p>';

		if (! empty($plugin['author'])) {
			echo '
						<p>
							', $plugin['author'], (empty($plugin['link']) ? '' : (' | <a class="bbc_link" href="' . $plugin['link'] . '" target="_blank" rel="noopener">' . $plugin['link'] . '</a>')), '
						</p>';
		}

		echo '
					</div>
				</div>
				<div class="floatright">';

		if (! empty($plugin['settings'])) {
			echo str_replace(' class="', ' @click="plugin.showSettings($event.target)" data-id="' . $plugin['snake_name'] . '_' . $context['session_id'] . '" class="gear ', $context['lp_icon_set']['gear']);
		}

		if (! empty($plugin['special'])) {
			if ($plugin['special'] === $txt['lp_can_donate']) {
				echo '
					<a href="', $context['lp_can_donate'][$plugin['name']]['link'], '" rel="noopener" target="_blank">', $context['lp_icon_set']['donate'], '</a>';
			} elseif ($plugin['special'] === $txt['lp_can_download']) {
				echo '
					<a href="', $context['lp_can_download'][$plugin['name']]['link'], '" rel="noopener" target="_blank">', $context['lp_icon_set']['download'], '</a>';
			}
		} else {
			echo str_replace([' class="', 'toggle-'], [' @click.self="plugin.toggle($event.target)" data-toggle="' . $plugin['status'] . '" class="', 'toggle-' . $plugin['status']], $context['lp_icon_set']['toggle']);
		}

		echo '
				</div>';

		show_plugin_settings($plugin);

		echo '
			</div>
		</div>';
	}

	echo '
	</div>
	<script>
		const plugin = new Plugin();
	</script>';
}

function show_plugin_settings(array $plugin)
{
	global $context, $txt;

	if (empty($plugin['settings']))
		return;

	echo '
	<br class="clear">
	<div class="roundframe" id="', $plugin['snake_name'], '_', $context['session_id'], '_settings" style="display: none" x-data="{ success: false }">
		<div class="title_bar">
			<h5 class="titlebg">', $txt['settings'], '</h5>
		</div>
		<div class="noticebox">
			<form id="', $plugin['snake_name'], '_form_', $context['session_id'], '" class="form_settings" action="', $context['post_url'], '" method="post" accept-charset="', $context['character_set'], '" @submit.prevent="success = plugin.saveSettings($event.target, $refs)">
				<input type="hidden" name="plugin_name" value="', $plugin['snake_name'], '">
				<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '">';

	foreach ($plugin['settings'] as $value) {
		$label = $txt['lp_' . $plugin['snake_name']][$value[1]] ?? '';

		echo '
				<div>';

		if (! in_array($value[0], array('callback', 'title', 'desc', 'check'))) {
			echo '
					<label', $value[0] != 'multicheck' ? (' for="' . $value[1] . '"') : '', '>', $label, '</label>';
		}

		if ($value[0] === 'text') {
			echo '
					<br><input type="text" name="', $value[1], '" id="', $value[1], '" value="', $context['lp_' . $plugin['snake_name'] . '_plugin'][$value[1]] ?? '', '"', empty($value['pattern']) ? '' : (' pattern="' . $value['pattern'] . '"'), ' required>';
		} elseif ($value[0] === 'large_text') {
			echo '
					<br><textarea name="', $value[1], '" id="', $value[1], '">', $context['lp_' . $plugin['snake_name'] . '_plugin'][$value[1]] ?? '', '</textarea>';
		} elseif ($value[0] === 'url') {
			echo '
					<br><input type="url" name="', $value[1], '" id="', $value[1], '" value="', $context['lp_' . $plugin['snake_name'] . '_plugin'][$value[1]] ?? '', '"', empty($value['placeholder']) ? '' : (' placeholder="' . $value['placeholder'] . '"'), '>';
		} elseif ($value[0] === 'color') {
			echo '
					<br><input id="', $value[1], '" name="', $value[1], '" data-jscolor="{}" value="', $context['lp_' . $plugin['snake_name'] . '_plugin'][$value[1]] ?? '', '">';
		} elseif (in_array($value[0], ['float', 'int'])) {
			$min = ' min="' . ($value['min'] ?? 0) . '"';
			$max = isset($value['max']) ? ' max="' . $value['max'] . '"' : '';
			$step = ' step="' . ($value['step'] ?? ($value[0] === 'int' ? 1 : 0.01)) . '"';

			echo '
					<br><input type="number"', $min, $max, $step, ' name="', $value[1], '" id="', $value[1], '" value="', $context['lp_' . $plugin['snake_name'] . '_plugin'][$value[1]] ?? 0, '">';
		} elseif ($value[0] === 'check') {
			echo '
					<input type="checkbox" name="', $value[1], '" id="', $value[1], '"', empty($context['lp_' . $plugin['snake_name'] . '_plugin'][$value[1]]) ? '' : ' checked', ' value="1" class="checkbox">
					<label class="label" for="', $value[1], '">', $label, '</label>';
		} elseif ($value[0] === 'callback' && ! empty($value[2])) {
			if (isset($value[2][0]) && isset($value[2][1]) && method_exists($value[2][0], $value[2][1])) {
				call_user_func($value[2]);
			}
		} elseif ($value[0] === 'title') {
			echo '
					<div class="sub_bar"><h6 class="subbg">', $label, '</h6></div>';
		} elseif ($value[0] === 'desc') {
			echo '
					<div class="roundframe">', $label, '</div>';
		} elseif ($value[0] === 'multicheck') {
			echo '
					<fieldset>
						<ul>';

			$temp[$value[1] . '_options'] = empty($context['lp_' . $plugin['snake_name'] . '_plugin'][$value[1]]) ? [] : json_decode($context['lp_' . $plugin['snake_name'] . '_plugin'][$value[1]], true);
			foreach ($value[2] as $key => $option_label) {
				echo '
							<li>
								<label for="', $value[1], '[', $key, ']">
									<input type="checkbox" name="', $value[1], '[', $key, ']" id="', $value[1], '[', $key, ']"', empty($temp[$value[1] . '_options'][$key]) ? '' : ' checked', ' value="1"> ', $option_label, '
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
					<select name="', $value[1], empty($multiple) ? '' : '[]', '" id="', $value[1], '"', empty($multiple) ? '' : ' multiple style="height: auto"', '>';

			if (! empty($multiple)) {
				if (! empty($context['lp_' . $plugin['snake_name'] . '_plugin'][$value[1]])) {
					$context['lp_' . $plugin['snake_name'] . '_plugin'][$value[1]] = json_decode($context['lp_' . $plugin['snake_name'] . '_plugin'][$value[1]], true);

					foreach ($value[2] as $option => $option_title) {
						echo '
						<option value="', $option, '"', ! empty($context['lp_' . $plugin['snake_name'] . '_plugin'][$value[1]]) && is_array($context['lp_' . $plugin['snake_name'] . '_plugin'][$value[1]]) && in_array($option, $context['lp_' . $plugin['snake_name'] . '_plugin'][$value[1]]) ? ' selected' : '', '>', $option_title, '</option>';
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
						<option value="', $option, '"', ! empty($context['lp_' . $plugin['snake_name'] . '_plugin'][$value[1]]) && $context['lp_' . $plugin['snake_name'] . '_plugin'][$value[1]] == $option ? ' selected' : '', '>', $option_title, '</option>';
				}
			}

			echo '
					</select>';
		}

		if (! empty($value['postfix'])) {
			echo '
					', $value['postfix'];
		}

		if (! empty($value['subtext'])) {
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
			<span x-ref="info" x-show="success" x-transition class="infobox floatleft">', $txt['settings_saved'], '</span>
			<button type="button" class="button" @click="plugin.hideSettings($event.target)">', $context['lp_icon_set']['close'], $txt['find_close'], '</button>
			<button form="', $plugin['snake_name'], '_form_', $context['session_id'], '" type="submit" class="button">', $context['lp_icon_set']['save'], $txt['save'], '</button>
		</div>
	</div>';
}
