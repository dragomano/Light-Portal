<div class="cat_bar">
	<h3 class="catbg">{{ $txt['lp_panel_direction'] }}</h3>
</div>

<div class="information">{{ $txt['lp_panel_direction_note'] }}</div>

@php use LightPortal\Utils\Setting; @endphp

@php echo '<div class="generic_list_wrapper">'; @endphp

<table class="table_grid centertext">
	<tbody>
		@foreach ($context['lp_block_placements'] as $key => $label)
			<tr class="windowbg">
				<td>
					<label for="lp_panel_direction_{{ $key }}">{{ $label }}</label>
				</td>
				<td>
					<select id="lp_panel_direction[{{ $key }}]" name="lp_panel_direction[{{ $key }}]">
						@foreach ($txt['lp_panel_direction_set'] as $value => $direction)
							@php $selected = Setting::getPanelDirection($key) == $value ? ' selected' : ''; @endphp
							<option value="{{ $value }}"{{ $selected }}>{{ $direction }}</option>
						@endforeach
					</select>
				</td>
			</tr>
		@endforeach
	</tbody>
</table>

@php echo '<dl class="settings">'; @endphp
