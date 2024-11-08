<?php

use Bugo\Compat\{Config, Lang, Utils};
use Bugo\LightPortal\Utils\{Icon, Setting};

function template_callback_panel_layout(): void
{
	echo '
		</dl>
	</div>
	<div class="windowbg">', Lang::$txt['lp_panel_layout_preview'], '</div>
	<div class="generic_list_wrapper">
		<div class="centertext', empty(Config::$modSettings['lp_swap_header_footer']) ? '' : ' column reverse', '">
			<div class="row center-xs">
				<div class="col-xs-', Setting::getHeaderPanelWidth(), '">
					<div class="title_bar">
						<h3 class="titlebg">', Utils::$context['lp_block_placements']['header'], '</h3>
					</div>
					<div class="information">
						<label class="centericon" for="lp_header_panel_width">col-xs-</label>
						<select id="lp_header_panel_width" name="lp_header_panel_width">';

		foreach (Utils::$context['lp_header_footer_width_values'] as $value) {
			echo '
							<option value="', $value, '"', Setting::getHeaderPanelWidth() == $value ? ' selected' : '', '>
								', $value, '
							</option>';
		}

		echo '
						</select>
					</div>
				</div>
			</div>
			<div class="row', Setting::isSwapLeftRight() ? ' reverse' : '', '">
				<div class="col-xs-12 col-sm-12 col-md-12 col-lg-', Setting::getLeftPanelWidth()['lg'], ' col-xl-', Setting::getLeftPanelWidth()['xl'], '">
					<div class="title_bar">
						<h3 class="titlebg">', Utils::$context['lp_block_placements']['left'], '</h3>
					</div>
					<div class="information">
						<ul class="centertext">
							<li>col-xs-12</li>
							<li>col-sm-12</li>
							<li>col-md-12</li>
							<li>
								<label class="centericon" for="lp_left_panel_width[lg]">col-lg-</label>
								<select id="lp_left_panel_width[lg]" name="lp_left_panel_width[lg]">';

	foreach (Utils::$context['lp_left_right_width_values'] as $value) {
		echo '
									<option value="', $value, '"', Setting::getLeftPanelWidth()['lg'] == $value ? ' selected' : '', '>
										', $value, '
									</option>';
	}

	echo '
								</select>
							</li>
							<li>
								<label class="centericon" for="lp_left_panel_width[xl]">col-xl-</label>
								<select id="lp_left_panel_width[xl]" name="lp_left_panel_width[xl]">';

	foreach (Utils::$context['lp_left_right_width_values'] as $value) {
		echo '
									<option value="', $value, '"', Setting::getLeftPanelWidth()['xl'] == $value ? ' selected' : '', '>
										', $value, '
									</option>';
	}

	echo '
								</select>
							</li>
						</ul>
						<hr>
						<label for="lp_left_panel_sticky">', Lang::$txt['lp_left_panel_sticky'], '</label>
						<input type="checkbox" id="lp_left_panel_sticky" name="lp_left_panel_sticky"', empty(Config::$modSettings['lp_left_panel_sticky']) ? '' : ' checked="checked"', '>
					</div>
				</div>
				<div class="col-xs">
					<div class="windowbg', empty(Config::$modSettings['lp_swap_top_bottom']) ? '' : ' column reverse', '">
						<strong>col-xs (auto)</strong>
						<div class="row">
							<div class="col-xs">
								<div class="title_bar">
									<h3 class="titlebg">', Utils::$context['lp_block_placements']['top'], '</h3>
								</div>
								<div class="information">
									col-xs (auto)
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-xs">
								<div class="descbox alternative">
									<strong>', Icon::get('content'), '</strong>
									<div>', Lang::$txt['lp_content'], '</div>
									col-xs (auto)
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-xs">
								<div class="title_bar">
									<h3 class="titlebg">', Utils::$context['lp_block_placements']['bottom'], '</h3>
								</div>
								<div class="information">
									col-xs (auto)
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="col-xs-12 col-sm-12 col-md-12 col-lg-', Setting::getRightPanelWidth()['lg'], ' col-xl-', Setting::getRightPanelWidth()['xl'], '">
					<div class="title_bar">
						<h3 class="titlebg">', Utils::$context['lp_block_placements']['right'], '</h3>
					</div>
					<div class="information">
						<ul class="centertext">
							<li>col-xs-12</li>
							<li>col-sm-12</li>
							<li>col-md-12</li>
							<li>
								<label class="centericon" for="lp_right_panel_width[lg]">col-lg-</label>
								<select id="lp_right_panel_width[lg]" name="lp_right_panel_width[lg]">';

		foreach (Utils::$context['lp_left_right_width_values'] as $value) {
			echo '
									<option value="', $value, '"', Setting::getRightPanelWidth()['lg'] == $value ? ' selected' : '', '>
										', $value, '
									</option>';
		}

		echo '
								</select>
							</li>
							<li>
								<label class="centericon" for="lp_right_panel_width[xl]">col-xl-</label>
								<select id="lp_right_panel_width[xl]" name="lp_right_panel_width[xl]">';

		foreach (Utils::$context['lp_left_right_width_values'] as $value) {
			echo '
									<option value="', $value, '"', Setting::getRightPanelWidth()['xl'] == $value ? ' selected' : '', '>
										', $value, '
									</option>';
		}

		echo '
								</select>
							</li>
						</ul>
						<hr>
						<label for="lp_right_panel_sticky">', Lang::$txt['lp_right_panel_sticky'], '</label>
						<input type="checkbox" id="lp_right_panel_sticky" name="lp_right_panel_sticky"', empty(Config::$modSettings['lp_right_panel_sticky']) ? '' : ' checked="checked"', '>
					</div>
				</div>
			</div>
			<div class="row center-xs">
				<div class="col-xs-', Setting::getFooterPanelWidth(), '">
					<div class="title_bar">
						<h3 class="titlebg">', Utils::$context['lp_block_placements']['footer'], '</h3>
					</div>
					<div class="information">
						<label class="centericon" for="lp_footer_panel_width">col-xs-</label>
						<select id="lp_footer_panel_width" name="lp_footer_panel_width">';

		foreach (Utils::$context['lp_header_footer_width_values'] as $value) {
			echo '
							<option value="', $value, '"', Setting::getFooterPanelWidth() == $value ? ' selected' : '', '>
								', $value, '
							</option>';
		}

		echo '
						</select>
					</div>
				</div>
			</div>
		</div>
	</div>
	<br>';
}

function template_callback_panel_direction(): void
{
	echo '
	<div class="cat_bar">
		<h3 class="catbg">', Lang::$txt['lp_panel_direction'], '</h3>
	</div>
	<div class="information">', Lang::$txt['lp_panel_direction_note'], '</div>
	<div class="generic_list_wrapper">
		<table class="table_grid centertext">
			<tbody>';

	foreach (Utils::$context['lp_block_placements'] as $key => $label) {
		echo '
				<tr class="windowbg">
					<td>
						<label for="lp_panel_direction_' . $key . '">', $label, '</label>
					</td>
					<td>
						<select id="lp_panel_direction[' . $key . ']" name="lp_panel_direction[' . $key . ']">';

		foreach (Lang::$txt['lp_panel_direction_set'] as $value => $direction) {
			echo '
							<option value="', $value, '"', ! empty(Setting::getPanelDirection()[$key]) && Setting::getPanelDirection()[$key] == $value ? ' selected' : '', '>', $direction, '</option>';
		}

		echo '
						</select>
					</td>
				</tr>';
	}

	echo '
			</tbody>
		</table>
	<dl class="settings">';
}
