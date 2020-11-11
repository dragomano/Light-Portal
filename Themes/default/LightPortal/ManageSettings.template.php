<?php

/**
 * Callback template to configure panel layouts
 *
 * Callback-шаблон для настройки макета панелей
 *
 * @return void
 */
function template_callback_panel_layout()
{
	global $txt, $modSettings, $context;

	echo '
	<dt style="width: 0"></dt>
	<dd style="width: 100%">
		<div class="infobox">', $txt['lp_panel_layout_note'], '</div>
		<table class="table_grid centertext">
			<thead>
				<tr class="title_bar">
					<th>', $txt['lp_browser_width'], '</th>
					<th>', $txt['lp_used_class'], '</th>
				</tr>
			</thead>
			<tbody>
				<tr class="windowbg">
					<td> >= 0px</td>
					<td>col-xs-* <span class="hidden-sm hidden-md hidden-lg hidden-xl"><i class="fas fa-grin"></i></span></td>
				</tr>
				<tr class="windowbg">
					<td> >= 576px</td>
					<td>col-sm-* <span class="hidden-xs hidden-md hidden-lg hidden-xl"><i class="fas fa-grin"></i></span></td>
				</tr>
				<tr class="windowbg">
					<td> >= 768px</td>
					<td>col-md-* <span class="hidden-xs hidden-sm hidden-lg hidden-xl"><i class="fas fa-grin"></i></span></td>
				</tr>
				<tr class="windowbg">
					<td> >= 992px</td>
					<td>col-lg-* <span class="hidden-xs hidden-sm hidden-md hidden-xl"><i class="fas fa-grin"></i></span></td>
				</tr>
				<tr class="windowbg">
					<td> >= 1200px</td>
					<td>col-xl-* <span class="hidden-xs hidden-sm hidden-md hidden-lg"><i class="fas fa-grin"></i></span></td>
				</tr>
			</tbody>
		</table>
		<br>
		<div class="infobox">', $txt['lp_panel_layout_preview'], '</div>
		<div class="centertext', !empty($modSettings['lp_swap_header_footer']) ? ' row column-reverse' : '', '">
			<div class="row center-xs">
				<div class="col-xs-', $context['lp_header_panel_width'], '">
					<div class="title_bar">
						<h3 class="titlebg">', $txt['lp_block_placement_set']['header'], '</h3>
					</div>
					<div class="information">
						<label class="centericon" for="lp_header_panel_width">col-xs-</label>
						<select id="lp_header_panel_width" name="lp_header_panel_width">';

		foreach ($context['lp_header_footer_width_values'] as $value) {
			echo '
							<option value="', $value, '"', $context['lp_header_panel_width'] == $value ? ' selected' : '', '>
								', $value, '
							</option>';
		}

		echo '
						</select>
					</div>
				</div>
			</div>
			<div class="row', !empty($modSettings['lp_swap_left_right']) ? ' reverse' : '', '">
				<div class="col-xs-12 col-sm-12 col-md-', $context['lp_left_panel_width']['md'], ' col-lg-', $context['lp_left_panel_width']['lg'], ' col-xl-', $context['lp_left_panel_width']['xl'], '">
					<div class="title_bar">
						<h3 class="titlebg">', $txt['lp_block_placement_set']['left'], '</h3>
					</div>
					<div class="information">
						<ul class="righttext">
							<li>col-xs-12</li>
							<li>col-sm-12</li>
							<li>
								<label class="centericon" for="lp_left_panel_width[md]">col-md-</label>
								<select id="lp_left_panel_width[md]" name="lp_left_panel_width[md]">';

	foreach ($context['lp_left_right_width_values'] as $value) {
		echo '
									<option value="', $value, '"', $context['lp_left_panel_width']['md'] == $value ? ' selected' : '', '>
										', $value, '
									</option>';
	}

	echo '
								</select>
							</li>
							<li>
								<label class="centericon" for="lp_left_panel_width[lg]">col-lg-</label>
								<select id="lp_left_panel_width[lg]" name="lp_left_panel_width[lg]">';

	foreach ($context['lp_left_right_width_values'] as $value) {
		echo '
									<option value="', $value, '"', $context['lp_left_panel_width']['lg'] == $value ? ' selected' : '', '>
										', $value, '
									</option>';
	}

	echo '
								</select>
							</li>
							<li>
								<label class="centericon" for="lp_left_panel_width[xl]">col-xl-</label>
								<select id="lp_left_panel_width[xl]" name="lp_left_panel_width[xl]">';

	foreach ($context['lp_left_right_width_values'] as $value) {
		echo '
									<option value="', $value, '"', $context['lp_left_panel_width']['xl'] == $value ? ' selected' : '', '>
										', $value, '
									</option>';
	}

echo '
								</select>
							</li>
						</ul>
						<hr>
						<label for="lp_left_panel_sticky">', $txt['lp_left_panel_sticky'], '</label>
						<input type="checkbox" id="lp_left_panel_sticky" name="lp_left_panel_sticky"', !empty($modSettings['lp_left_panel_sticky']) ? ' checked="checked"' : '', '>
					</div>
				</div>
				<div class="col-xs">
					<div class="windowbg', !empty($modSettings['lp_swap_top_bottom']) ? ' row column-reverse' : '', '">
						<strong>col-xs (auto)</strong>
						<div class="row">
							<div class="col-xs">
								<div class="title_bar">
									<h3 class="titlebg">', $txt['lp_block_placement_set']['top'], '</h3>
								</div>
								<div class="information">
									col-xs (auto)
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-xs">
								<div class="descbox alternative">
									<strong><i class="far fa-newspaper fa-2x"></i></i></strong>
									<div>', $txt['lp_content'], '</div>
									col-xs (auto)
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-xs">
								<div class="title_bar">
									<h3 class="titlebg">', $txt['lp_block_placement_set']['bottom'], '</h3>
								</div>
								<div class="information">
									col-xs (auto)
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="col-xs-12 col-sm-12 col-md-', $context['lp_right_panel_width']['md'], ' col-lg-', $context['lp_right_panel_width']['lg'], ' col-xl-', $context['lp_right_panel_width']['xl'], '">
					<div class="title_bar">
						<h3 class="titlebg">', $txt['lp_block_placement_set']['right'], '</h3>
					</div>
					<div class="information">
						<ul class="righttext">
							<li>col-xs-12</li>
							<li>col-sm-12</li>
							<li>
								<label class="centericon" for="lp_right_panel_width[md]">col-md-</label>
								<select id="lp_right_panel_width[md]" name="lp_right_panel_width[md]">';

		foreach ($context['lp_left_right_width_values'] as $value) {
			echo '
									<option value="', $value, '"', $context['lp_right_panel_width']['md'] == $value ? ' selected' : '', '>
										', $value, '
									</option>';
		}

		echo '
								</select>
							</li>
							<li>
								<label class="centericon" for="lp_right_panel_width[lg]">col-lg-</label>
								<select id="lp_right_panel_width[lg]" name="lp_right_panel_width[lg]">';

		foreach ($context['lp_left_right_width_values'] as $value) {
			echo '
									<option value="', $value, '"', $context['lp_right_panel_width']['lg'] == $value ? ' selected' : '', '>
										', $value, '
									</option>';
		}

		echo '
								</select>
							</li>
							<li>
								<label class="centericon" for="lp_right_panel_width[xl]">col-xl-</label>
								<select id="lp_right_panel_width[xl]" name="lp_right_panel_width[xl]">';

		foreach ($context['lp_left_right_width_values'] as $value) {
			echo '
									<option value="', $value, '"', $context['lp_right_panel_width']['xl'] == $value ? ' selected' : '', '>
										', $value, '
									</option>';
		}

		echo '
								</select>
							</li>
						</ul>
						<hr>
						<label for="lp_right_panel_sticky">', $txt['lp_right_panel_sticky'], '</label>
						<input type="checkbox" id="lp_right_panel_sticky" name="lp_right_panel_sticky"', !empty($modSettings['lp_right_panel_sticky']) ? ' checked="checked"' : '', '>
					</div>
				</div>
			</div>
			<div class="row center-xs">
				<div class="col-xs-', $context['lp_footer_panel_width'], '">
					<div class="title_bar">
						<h3 class="titlebg">', $txt['lp_block_placement_set']['footer'], '</h3>
					</div>
					<div class="information">
						<label class="centericon" for="lp_footer_panel_width">col-xs-</label>
						<select id="lp_footer_panel_width" name="lp_footer_panel_width">';

		foreach ($context['lp_header_footer_width_values'] as $value) {
			echo '
							<option value="', $value, '"', $context['lp_footer_panel_width'] == $value ? ' selected' : '', '>
								', $value, '
							</option>';
		}

		echo '
						</select>
					</div>
				</div>
			</div>
		</div>
	</dd>';
}

/**
 * Callback template for selecting the direction of blocks inside panels
 *
 * Callback-шаблон для выбора направления блоков внутри панелей
 *
 * @return void
 */
function template_callback_panel_direction()
{
	global $txt, $context, $modSettings;

	echo '
	<dt style="width: 0"></dt>
	<dd style="width: 100%">
		<div class="infobox">', $txt['lp_panel_direction_note'], '</div>
		<table class="table_grid centertext">
			<thead>
				<tr class="title_bar">
					<th colspan="2">', $txt['lp_panel_direction'], '</th>
				</tr>
			</thead>
			<tbody>';

	foreach ($context['lp_panels'] as $key => $label) {
		echo '
				<tr class="windowbg">
					<td>
						<label for="lp_panel_direction_' . $key . '">', $label, '</label>
					</td>
					<td>
						<select id="lp_panel_direction[' . $key . ']" name="lp_panel_direction[' . $key . ']">';

		foreach ($txt['lp_panel_direction_set'] as $value => $direction) {
			echo '
							<option value="', $value, '"', !empty($context['lp_panel_direction'][$key]) && $context['lp_panel_direction'][$key] == $value ? ' selected' : '', '>', $direction, '</option>';
		}

		echo '
						</select>
					</td>
				</tr>';
	}

	echo '
			</tbody>
		</table>
	</dd>';
}

/**
 * Callback template for selecting themes support FontAwesome icons
 *
 * Callback-шаблон для выбора тем, поддерживающих иконки FontAwesome
 *
 * @return void
 */
function template_callback_compat_themes()
{
	global $context, $scripturl, $txt, $modSettings;

	if (empty($context['lp_fontawesome_compat_themes']))
		return;

	echo '
		<dt class="descbox" style="width: 100%">
			<a id="setting_lp_show_debug_info_help" href="', $scripturl, '?action=helpadmin;help=lp_fontawesome_compat_themes_help" onclick="return reqOverlayDiv(this.href);">
				<span class="main_icons help" title="', $txt['help'], '"></span>
			</a>
			<a id="setting_lp_fontawesome_compat_themes"></a>
			<span><label for="lp_fontawesome_compat_themes">', $txt['lp_fontawesome_compat_themes'], '</label></span>
		</dt>
		<dd class="roundframe" style="width: 100%">';

	$compat_themes = !empty($modSettings['lp_fontawesome_compat_themes']) ? json_decode($modSettings['lp_fontawesome_compat_themes'], true) : [];

	foreach ($context['lp_fontawesome_compat_themes'] as $id => $theme) {
		echo '
			<label for="lp_fontawesome_compat_themes_' . $id . '">
				<input type="checkbox" name="lp_fontawesome_compat_themes[' . $id . ']" id="lp_fontawesome_compat_themes_' . $id . '" value="1"', !empty($compat_themes[$id]) ? ' checked' : '', '> ', $theme, '
			</label>
			<br>';
	}

	echo '
		</dd>';
}

/**
 * Display settings on multiple tabs
 *
 * Вывод настроек на нескольких вкладках
 *
 * @param string $tab
 * @return void
 */
function template_post_tab($tab = 'content')
{
	global $context;

	echo '
					<dl>';

	foreach ($context['posting_fields'] as $pfid => $pf) {
		if (empty($pf['input']['tab']))
			$pf['input']['tab'] = 'tuning';

		if ($pf['input']['tab'] != $tab)
			continue;

		if (empty($pf['label']) || empty($pf['input']))
			continue;

		echo '
						<dt class="clear pf_', $pfid, '">';

		if (!empty($pf['label']['before']))
			echo '
							', $pf['label']['before'];

		if (!empty($pf['label']['html']))
			echo $pf['label']['html'];
		else
			echo '
							<label', ($pf['input']['type'] === 'radio_select' ? '' : ' for="' . (!empty($pf['input']['attributes']['id']) ? $pf['input']['attributes']['id'] : $pfid) . '"'), ' id="caption_', $pfid, '"', !empty($pf['label']['class']) ? ' class="' . $pf['label']['class'] . '"' : '', '>', $pf['label']['text'], '</label>';

		if (!empty($pf['label']['after']))
			echo '
							', $pf['label']['after'];

		echo '
						</dt>
						<dd class="pf_', $pfid, '">';

		if (!empty($pf['input']['before']))
			echo '
							', $pf['input']['before'];

		if (!empty($pf['input']['html'])) {
			echo $pf['input']['html'];
		} elseif (in_array($pf['input']['type'], array('text', 'password', 'color', 'date', 'datetime-local', 'email', 'month', 'number', 'range', 'tel', 'time', 'url', 'week', 'checkbox'))) {
			echo '
							<input type="', $pf['input']['type'], '"';

			if (empty($pf['input']['attributes']['id']))
				echo ' id="', $pfid, '"';

			if (empty($pf['input']['attributes']['name']))
				echo ' name="', $pfid, '"';

			if (!empty($pf['input']['attributes']) && is_array($pf['input']['attributes'])) {
				foreach ($pf['input']['attributes'] as $attribute => $value) {
					if (is_bool($value))
						echo $value ? ' ' . $attribute : '';
					else
						echo ' ', $attribute, '="', $value, '"';
				}
			}

			echo ' tabindex="', $context['tabindex']++, '">';
		} elseif ($pf['input']['type'] === 'textarea') {
			echo '
							<textarea';

			if (empty($pf['input']['attributes']['id']))
				echo ' id="', $pfid, '"';

			if (empty($pf['input']['attributes']['name']))
				echo ' name="', $pfid, '"';

			if (!empty($pf['input']['attributes']) && is_array($pf['input']['attributes'])) {
				foreach ($pf['input']['attributes'] as $attribute => $value) {
					if ($attribute === 'value')
						continue;
					elseif (is_bool($value))
						echo $value ? ' ' . $attribute : '';
					else
						echo ' ', $attribute, '="', $value, '"';
				}
			}

			echo ' tabindex="', $context['tabindex']++, '">', !empty($pf['input']['attributes']['value']) ? $pf['input']['attributes']['value'] : '', '</textarea>';
		} elseif ($pf['input']['type'] === 'select' && is_array($pf['input']['options'])) {
			echo '
							<select';

			if (empty($pf['input']['attributes']['id']))
				echo ' id="', $pfid, '"';

			if (empty($pf['input']['attributes']['name']))
				echo ' name="', $pfid, '"';

			if (!empty($pf['input']['attributes']) && is_array($pf['input']['attributes'])) {
				foreach ($pf['input']['attributes'] as $attribute => $value) {
					if (is_bool($value))
						echo $value ? ' ' . $attribute : '';
					else
						echo ' ', $attribute, '="', $value, '"';
				}
			}

			echo ' tabindex="', $context['tabindex']++, '">';

			foreach ($pf['input']['options'] as $optlabel => $option) {
				if (!empty($option['options']) && is_array($option['options'])) {
					echo '
								<optgroup';

					if (empty($option['label']))
						echo ' label="', $optlabel, '"';

					if (!empty(RC2_CLEAN ? $option['attributes'] : $option) && is_array(RC2_CLEAN ? $option['attributes'] : $option)) {
						foreach (RC2_CLEAN ? $option['attributes'] : $option as $attribute => $value) {
							if ($attribute === 'options')
								continue;
							elseif (is_bool($value))
								echo $value ? ' ' . $attribute : '';
							else
								echo ' ', $attribute, '="', $value, '"';
						}
					}

					echo '>';

					foreach ($option['options'] as $grouped_optlabel => $grouped_option) {
						echo '
									<option';

						foreach (RC2_CLEAN ? $grouped_option['attributes'] : $grouped_option as $attribute => $value) {
							if (is_bool($value))
								echo $value ? ' ' . $attribute : '';
							else
								echo ' ', $attribute, '="', $value, '"';
						}

						echo '>', $grouped_option['label'], '</option>';

					}

					echo '
								</optgroup>';
				} else {
					echo '
								<option';

					foreach (RC2_CLEAN ? $option['attributes'] : $option as $attribute => $value) {
						if (is_bool($value))
							echo $value ? (' ' . $attribute) : '';
						else
							echo ' ', $attribute, '="', $value, '"';
					}

					echo '>', $optlabel, '</option>';
				}
			}

			echo '
							</select>';
		} elseif ($pf['input']['type'] === 'radio_select' && is_array($pf['input']['options'])) {
			echo '
							<div';

			if (!empty($pf['input']['attributes']) && is_array($pf['input']['attributes'])) {
				foreach ($pf['input']['attributes'] as $attribute => $value) {
					if ($attribute === 'name')
						continue;
					elseif (is_bool($value))
						echo $value ? ' ' . $attribute : '';
					else
						echo ' ', $attribute, '="', $value, '"';
				}
			}

			echo '>';

			foreach ($pf['input']['options'] as $optlabel => $option) {
				echo '
							<label style="margin-right:2ch"><input type="radio" name="', !empty($pf['input']['attributes']['name']) ? $pf['input']['attributes']['name'] : $pfid, '"';

				foreach (RC2_CLEAN ? $option['attributes'] : $option as $attribute => $value) {
					if ($attribute === 'label')
						continue;
					elseif (is_bool($value))
						echo $value ? ' ' . ($attribute === 'selected' ? 'checked' : $attribute) : '';
					else
						echo ' ', $attribute, '="', $value, '"';
				}

				echo ' tabindex="', $context['tabindex']++, '"> ', isset($option['label']) ? $option['label'] : $optlabel, '</label>';
			}

			echo '
							</div>';
		}

		if (!empty($pf['input']['after']))
			echo '
							', $pf['input']['after'];

		echo '
						</dd>';
	}

	echo '
					</dl>';
}
