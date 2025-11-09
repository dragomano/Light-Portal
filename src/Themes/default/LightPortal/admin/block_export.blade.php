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
					{{ $txt['lp_block_note'] }} / {{ $txt['lp_title'] }}
				</th>
				<th scope="col" class="type hidden-xs">
					{{ $txt['lp_block_type'] }}
				</th>
				<th scope="col" class="placement">
					{{ $txt['lp_block_placement'] }}
				</th>
				<th scope="col" class="actions">
					<input type="checkbox" onclick="invertAll(this, this.form);">
				</th>
			</tr>
		</thead>
		<tbody>
			@forelse ($blocks as $placement => $allBlocks)
				@if (is_array($allBlocks))
					@foreach ($allBlocks as $id => $data)
						<tr class="windowbg{{ $data['status'] ? ' sticky' : '' }}">
							<td class="centertext">
								{{ $id }}
							</td>
							<td class="type centertext">
								{{ $data['description'] ?: ($data['titles'][$context['user']['language']] ?? $data['titles'][$language] ?? '') }}
							</td>
							<td class="type hidden-xs centertext">
								{!! $txt['lp_' . $data['type']]['title'] ?? $context['lp_missing_block_types'][$data['type']] !!}
							</td>
							<td class="placement centertext">
								{{ $context['lp_block_placements'][$placement] ?? ($txt['unknown'] . ' (' . $placement . ')') }}
							</td>
							<td class="actions centertext">
								<input type="checkbox" value="{{ $id }}" name="blocks[]">
							</td>
						</tr>
				    @endforeach
				@endif
			@empty
				<tr class="windowbg">
					<td colspan="5" class="centertext">{{ $txt['lp_no_items'] }}</td>
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
