<form action="{{ $context['form_action'] }}" method="post" accept-charset="{{ $context['character_set'] }}">
	<div class="cat_bar">
		<h3 class="catbg">{{ $context['page_area_title'] }}</h3>
	</div>
	<div class="additional_row">
		<input type="hidden">
		<input type="submit" name="export_selection" value="{{ $txt['lp_export_selection'] }}" class="button">
		<input type="submit" name="export_all" value="{{ $txt['lp_export_all'] }}" class="button">
	</div>
	<table class="table_grid">
		<thead>
			<tr class="title_bar">
				<th scope="col">#</th>
				<th scope="col" class="type">
					{{ $txt['lp_plugin_name'] }}
				</th>
				<th scope="col" class="actions">
					<input type="checkbox" onclick="invertAll(this, this.form);">
				</th>
			</tr>
		</thead>
		<tbody>
			@forelse ($context['lp_plugins'] as $id => $name)
				<tr class="windowbg">
					<td class="centertext">
						{{ $id + 1 }}
					</td>
					<td class="name centertext">
						{{ $name }}
					</td>
					<td class="actions centertext">
						<input type="checkbox" value="{{ $name }}" name="plugins[]">
					</td>
				</tr>
			@empty
				<tr class="windowbg">
					<td colspan="3" class="centertext">{{ $txt['lp_no_items'] }}</td>
				</tr>
			@endforelse
		</tbody>
	</table>
	<div class="additional_row">
		<input type="hidden">
		<input type="submit" name="export_selection" value="{{ $txt['lp_export_selection'] }}" class="button">
		<input type="submit" name="export_all" value="{{ $txt['lp_export_all'] }}" class="button">
	</div>
</form>
