@php echo '</dl></div>'; @endphp

<div class="windowbg">{{ $txt['lp_panel_layout_preview'] }}</div>

@php use LightPortal\Utils\Setting; @endphp

<div class="generic_list_wrapper">
	<div class="centertext{{ empty($modSettings['lp_swap_header_footer']) ? '' : ' column reverse' }}">
		<div class="row center-xs">
			<div class="col-xs-{{ Setting::getHeaderPanelWidth() }}">
				<div class="title_bar">
					<h3 class="titlebg">{{ $context['lp_block_placements']['header'] }}</h3>
				</div>
				<div class="information">
					<label class="centericon" for="lp_header_panel_width">col-xs-</label>
					<select id="lp_header_panel_width" name="lp_header_panel_width">
						@foreach ($context['lp_header_footer_width_values'] as $value)
							@php $selected = Setting::getHeaderPanelWidth() == $value ? ' selected' : '' @endphp
							<option value="{{ $value }}"{{ $selected }}>{{ $value }}</option>
						@endforeach
					</select>
				</div>
			</div>
		</div>
		<div class="row{{ Setting::isSwapLeftRight() ? ' reverse' : '' }}">
			<div class="col-xs-12 col-sm-12 col-md-12 col-lg-{{ Setting::getLeftPanelWidth()['lg'] }} col-xl-{{ Setting::getLeftPanelWidth()['xl'] }}">
				<div class="title_bar">
					<h3 class="titlebg">{{ $context['lp_block_placements']['left'] }}</h3>
				</div>
				<div class="information">
					<ul class="centertext">
						<li>col-xs-12</li>
						<li>col-sm-12</li>
						<li>col-md-12</li>
						<li>
							<label class="centericon" for="lp_left_panel_width[lg]">col-lg-</label>
							<select id="lp_left_panel_width[lg]" name="lp_left_panel_width[lg]">
								@foreach ($context['lp_left_right_width_values'] as $value)
									@php $selected = Setting::getLeftPanelWidth()['lg'] == $value ? ' selected' : '' @endphp
									<option value="{{ $value }}"{{ $selected }}>{{ $value }}</option>
								@endforeach
							</select>
						</li>
						<li>
							<label class="centericon" for="lp_left_panel_width[xl]">col-xl-</label>
							<select id="lp_left_panel_width[xl]" name="lp_left_panel_width[xl]">
								@foreach ($context['lp_left_right_width_values'] as $value)
									@php $selected = Setting::getLeftPanelWidth()['xl'] == $value ? ' selected' : '' @endphp
									<option value="{{ $value }}"{{ $selected }}>{{ $value }}</option>
								@endforeach
							</select>
						</li>
					</ul>
					<hr>
					<label for="lp_left_panel_sticky">{{ $txt['lp_left_panel_sticky'] }}</label>
					<input
						type="checkbox"
						id="lp_left_panel_sticky"
						name="lp_left_panel_sticky"
						{{ empty($modSettings['lp_left_panel_sticky']) ? '' : ' checked="checked"' }}
					>
				</div>
			</div>
			<div class="col-xs">
				<div class="windowbg{{ empty($modSettings['lp_swap_top_bottom']) ? '' : ' column reverse' }}">
					<strong>col-xs (auto)</strong>
					<div class="row">
						<div class="col-xs">
							<div class="title_bar">
								<h3 class="titlebg">{{ $context['lp_block_placements']['top'] }}</h3>
							</div>
							<div class="information">
								col-xs (auto)
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-xs">
							<div class="descbox alternative">
								<strong>@icon('content')</strong>
								<div>{{ $txt['lp_content'] }}</div>
								col-xs (auto)
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-xs">
							<div class="title_bar">
								<h3 class="titlebg">{{ $context['lp_block_placements']['bottom'] }}</h3>
							</div>
							<div class="information">
								col-xs (auto)
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="col-xs-12 col-sm-12 col-md-12 col-lg-{{ Setting::getRightPanelWidth()['lg'] }} col-xl-{{ Setting::getRightPanelWidth()['xl'] }}">
				<div class="title_bar">
					<h3 class="titlebg">{{ $context['lp_block_placements']['right'] }}</h3>
				</div>
				<div class="information">
					<ul class="centertext">
						<li>col-xs-12</li>
						<li>col-sm-12</li>
						<li>col-md-12</li>
						<li>
							<label class="centericon" for="lp_right_panel_width[lg]">col-lg-</label>
							<select id="lp_right_panel_width[lg]" name="lp_right_panel_width[lg]">
								@foreach ($context['lp_left_right_width_values'] as $value)
									@php $selected = Setting::getRightPanelWidth()['lg'] == $value ? ' selected' : '' @endphp
									<option value="{{ $value }}"{{ $selected }}>{{ $value }}</option>
								@endforeach
							</select>
						</li>
						<li>
							<label class="centericon" for="lp_right_panel_width[xl]">col-xl-</label>
							<select id="lp_right_panel_width[xl]" name="lp_right_panel_width[xl]">
								@foreach ($context['lp_left_right_width_values'] as $value)
									@php $selected = Setting::getRightPanelWidth()['xl'] == $value ? ' selected' : '' @endphp
									<option value="{{ $value }}"{{ $selected }}>{{ $value }}</option>
								@endforeach
							</select>
						</li>
					</ul>
					<hr>
					<label for="lp_right_panel_sticky">{{ $txt['lp_right_panel_sticky'] }}</label>
					<input
						type="checkbox"
						id="lp_right_panel_sticky"
						name="lp_right_panel_sticky"
						{{ empty($modSettings['lp_right_panel_sticky']) ? '' : ' checked="checked"' }}
					>
				</div>
			</div>
		</div>
		<div class="row center-xs">
			<div class="col-xs-{{ Setting::getFooterPanelWidth() }}">
				<div class="title_bar">
					<h3 class="titlebg">{{ $context['lp_block_placements']['footer'] }}</h3>
				</div>
				<div class="information">
					<label class="centericon" for="lp_footer_panel_width">col-xs-</label>
					<select id="lp_footer_panel_width" name="lp_footer_panel_width">
						@foreach ($context['lp_header_footer_width_values'] as $value)
							@php $selected = Setting::getFooterPanelWidth() == $value ? ' selected' : '' @endphp
							<option value="{{ $value }}"{{ $selected }}>{{ $value }}</option>
						@endforeach
					</select>
				</div>
			</div>
		</div>
	</div>
</div>
<br>
