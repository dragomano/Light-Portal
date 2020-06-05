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
		<div class="centertext', !empty($modSettings['lp_swap_header_footer']) ? ' row reverse2' : '', '">
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
					</div>
				</div>
				<div class="col-xs">
					<div class="windowbg', !empty($modSettings['lp_swap_top_bottom']) ? ' row reverse2' : '', '">
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
									<br>col-xs (auto)
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
