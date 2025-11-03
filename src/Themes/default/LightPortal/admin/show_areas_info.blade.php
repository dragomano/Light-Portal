<table class="table_grid">
	<thead>
		<tr class="title_bar">
			<th colspan="2">{{ $txt['lp_block_areas_th'] }}</th>
		</tr>
	</thead>
	<tbody>
		@foreach ($context['lp_possible_areas'] as $area => $where_to_display)
			<tr class="windowbg">
				<td class="righttext"><strong>{{ $area }}</strong></td>
				<td class="lefttext">{{ $where_to_display }}</td>
			</tr>
		@endforeach
	</tbody>
</table>
