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
			<span class="floatright">
				<a href="', $scripturl, '?action=admin;area=lp_plugins;sa=add;', $context['session_var'], '=', $context['session_id'], '" x-data>
					<i class="fas fa-plus" @mouseover="plugin.toggleSpin($event.target)" @mouseout="plugin.toggleSpin($event.target)" title="', $txt['lp_plugins_add'], '"></i>
				</a>
			</span>
			', $txt['lp_plugins_extra'], '
		</h3>
	</div>
	<p class="information">', $txt['lp_plugins_desc'], '</p>';

	// This is a magic! Пошла магия!
	foreach ($context['all_lp_plugins'] as $id => $plugin) {
		echo '
	<div class="windowbg">
		<div class="features" data-id="', $id, '" x-data>
			<div class="floatleft">
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
				<img class="lp_plugin_settings" data-id="', $plugin['snake_name'], '" src="', $settings['default_images_url'], '/icons/config_hd.png" alt="', $txt['settings'], '" @click="plugin.showSettings($event.target)">';
		}

		echo '
				<i class="lp_plugin_toggle fas fa-3x fa-toggle-', $plugin['status'], '" data-toggle="', $plugin['status'], '" @click="plugin.toggle($event.target)"></i>
			</div>';

		if (!empty($plugin['settings']))
			show_plugin_settings($plugin['snake_name'], $plugin['settings']);

		echo '
		</div>
	</div>';
	}
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

/**
 * The page creation/editing template
 *
 * Шаблон создания/редактирования страницы
 *
 * @return void
 */
function template_plugin_post()
{
	global $context, $txt;

	if (!empty($context['lp_addon_dir_is_not_writable'])) {
		echo '
	<div class="errorbox">', $txt['lp_addon_add_failed'], '</div>';
	}

	echo '
	<div class="cat_bar">
		<h3 class="catbg">', $context['page_area_title'], '</h3>
	</div>';

	if (!empty($context['post_errors'])) {
		echo '
	<div class="errorbox">
		<ul>';

		foreach ($context['post_errors'] as $error) {
			echo '
			<li>', $error, '</li>';
		}

		echo '
		</ul>
	</div>';
	}

	$fields = $context['posting_fields'];

	echo '
	<form id="lp_post" action="', $context['canonical_url'], '" method="post" accept-charset="', $context['character_set'], '" onsubmit="submitonce(this);" x-data>
		<div class="roundframe noup">
			<div class="lp_tabs">
				<input id="tab1" type="radio" name="tabs" checked>
				<label for="tab1" class="bg odd">', $txt['lp_plugins_tab_content'], '</label>
				<input id="tab2" type="radio" name="tabs">
				<label for="tab2" class="bg odd">', $txt['lp_plugins_tab_copyrights'], '</label>
				<input id="tab3" type="radio" name="tabs">
				<label for="tab3" class="bg odd">', $txt['lp_plugins_tab_settings'], '</label>
				<input id="tab4" type="radio" name="tabs">
				<label for="tab4" class="bg odd">', $txt['lp_plugins_tab_tuning'], '</label>
				<section id="content-tab1" class="bg even">
					', template_post_tab($fields), '
				</section>
				<section id="content-tab2" class="bg even">
					', template_post_tab($fields, 'copyrights'), '
				</section>
				<section id="content-tab3" class="bg even">
					<table class="add_option centertext" x-data="plugin.handleOptions()">
						<tbody>
							<template x-for="(option, index) in options" :key="index">
								<tr class="windowbg">
									<td colspan="4">
										<table class="plugin_options table_grid">
											<thead>
												<tr class="title_bar">
													<th>#</th>
													<th colspan="3">', $txt['lp_plugin_option_name'], '</th>
												</tr>
											</thead>
											<tbody>
												<tr class="windowbg">
													<td x-text="index + 1"></td>
													<td colspan="2">
														<input type="text" x-model="option.name" name="option_name[]" pattern="^[a-z][a-z_]+$" maxlength="255" placeholder="option_name">
													</td>
													<td>
														<button type="button" class="button floatnone" @click="removeOption(index)">
															<span class="main_icons delete"></span> ', $txt['remove'], '
														</button>
													</td>
												</tr>
												<tr class="windowbg">
													<td><strong>', $txt['lp_plugin_option_type'], '</strong></td>
													<td>
														<select x-model="option.type" name="option_type[]">';

	foreach ($txt['lp_plugin_option_types'] as $type => $name) {
		echo '
															<option value="', $type, '">', $name, '</option>';
	}

	echo '
														</select>
													</td>
													<td><strong>', $txt['lp_plugin_option_default_value'], '</strong></td>
													<td>
														<template x-if="option.type == \'text\'">
															<input name="option_defaults[]">
														</template>
														<template x-if="option.type == \'url\'">
															<input type="url" name="option_defaults[]">
														</template>
														<template x-if="option.type == \'color\'">
															<input type="color" name="option_defaults[]">
														</template>
														<template x-if="option.type == \'int\'">
															<input type="number" min="0" step="1" name="option_defaults[]">
														</template>
														<template x-if="option.type == \'check\'">
															<input type="checkbox" name="option_defaults[]">
														</template>
														<template x-if="option.type == \'multicheck\'">
															<input name="option_defaults[]">
														</template>
														<template x-if="option.type == \'select\'">
															<input name="option_defaults[]">
														</template>
													</td>
												</tr>
												<template x-if="[\'multicheck\', \'select\'].includes(option.type)">
													<tr class="windowbg">
														<td colspan="1"><strong>', $txt['lp_plugin_option_variants'], '</strong></td>
														<td colspan="3">
															<input x-model="option.variants" name="option_variants[]" placeholder="', $txt['lp_plugin_option_variants_placeholder'], '">
														</td>
													</tr>
												</template>
												<tr class="windowbg">
													<td colspan="1"><strong>', $txt['lp_plugin_option_translations'], '</strong></td>
													<td colspan="3">
														<table class="table_grid">
															<tbody>';

	foreach ($context['languages'] as $lang) {
		echo '
																<tr class="windowbg">
																	<td><strong>', $lang['filename'], '</strong></td>
																	<td>
																		<input type="text" name="option_translations[', $lang['filename'], '][]"', in_array($lang['filename'], array($context['user']['language'], 'english')) ? ' required' : '', ' placeholder="', $lang['filename'], '">
																	</td>
																</tr>';
	}

	echo '
															</tbody>
														</table>
													</td>
												</tr>
											</tbody>
										</table>
									</td>
								</tr>
							</template>
						</tbody>
						<tfoot>
							<tr class="windowbg">
								<td colspan="4">
									<button type="button" class="button" @click="addNewOption()"><span class="main_icons plus"></span> ', $txt['lp_plugin_new_option'] , '</button>
								</td>
							</tr>
						</tfoot>
					</table>
				</section>
				<section id="content-tab4" class="bg even">
					', template_post_tab($fields, 'tuning'), '
				</section>
			</div>
			<br class="clear">
			<div class="centertext">
				<div class="noticebox">', $txt['lp_plugins_add_information'], '</div>
				<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '">
				<input type="hidden" name="seqnum" value="', $context['form_sequence_number'], '">
				<button type="submit" class="button" name="save" @click="plugin.post($el)">', $txt['save'], '</button>
			</div>
		</div>
	</form>';
}
