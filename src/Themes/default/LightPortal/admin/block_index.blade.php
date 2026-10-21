@php use LightPortal\Utils\Icon; @endphp

@foreach ($context['lp_current_blocks'] as $placement => $blocks)
	@php
		$block_group_type = in_array($placement, ['header', 'top', 'left', 'right', 'bottom', 'footer']) ? 'default' : 'additional';
		$add_link = "{$context['form_action']};{$context['session_var']}={$context['session_id']};placement=$placement";
	@endphp

	<div class="cat_bar">
		<h3 class="catbg">
			<span class="floatright">
				<a href="{{ $add_link }}">
					@php
						$icon = Icon::get('plus', $txt['lp_blocks_add']);
						echo str_replace(' class=', ' data-on:mouseover="evt.target.classList.add(\'fa-spin\')" data-on:mouseout="evt.target.classList.remove(\'fa-spin\')" class=', $icon);
					@endphp
				</a>
			</span>
			{{ $context['lp_block_placements'][$placement] ?? $txt['not_applicable'] }}<span data-lp-count="{{ $placement }}">{{ is_array($blocks) ? ' (' . count($blocks) . ')' : '' }}</span>
		</h3>
	</div>

	<table class="lp_{{ $block_group_type }}_blocks table_grid centertext">
		@if (is_array($blocks))
			<thead>
				<tr class="title_bar">
					<th scope="col" class="icon hidden-xs hidden-sm">
						{{ $txt['custom_profile_icon'] }}
					</th>
					<th scope="col" class="title">
						{{ $txt['lp_block_note'] }} / {{ $txt['lp_title'] }}
					</th>
					<th scope="col" class="type hidden-xs hidden-sm hidden-md">
						{{ $txt['lp_block_type'] }}
					</th>
					<th scope="col" class="areas hidden-xs hidden-sm">
						{{ $txt['lp_block_areas'] }}
					</th>
					<th scope="col" class="priority hidden-xs">
						{{ $txt['lp_block_priority'] }}
					</th>
					<th scope="col" class="status">
						{{ $txt['status'] }}
					</th>
					<th scope="col" class="actions">
						{{ $txt['lp_actions'] }}
					</th>
				</tr>
			</thead>
			<tbody data-placement="{{ $placement }}">
				@foreach ($blocks as $id => $data)
					@include('admin.partials._block_entry', ['id' => $id, 'data' => $data])
				@endforeach
			</tbody>
		@else
			<tbody data-placement="{{ $placement }}">
				<tr class="windowbg centertext">
					<td>{{ $txt['lp_no_items'] }}</td>
				</tr>
			</tbody>
		@endif
	</table>
@endforeach

@php
	$sortUrl = $scripturl . '?action=admin&area=lp_blocks&actions&' . $context['session_var'] . '=' . $context['session_id'];
@endphp

<div hidden data-signals="{ sort_ids: '', sort_placement: '', sort_source: '' }">
	<input id="lp_block_sort_ids" type="hidden" data-bind="sort_ids">
	<input id="lp_block_sort_placement" type="hidden" data-bind="sort_placement">
	<input id="lp_block_sort_source" type="hidden" data-bind="sort_source">
	<button id="lp_block_sort_trigger" type="button" data-on:click="@post('{{ $sortUrl }}', {payload: {update_priority: $sort_ids, update_placement: $sort_placement, update_source: $sort_source}})"></button>
</div>

<script src="@asset('Sortable.min.js')"></script>
<script>
	const block = new Block(),
		defaultBlocks = document.querySelectorAll(".lp_default_blocks tbody"),
		additionalBlocks = document.querySelectorAll(".lp_additional_blocks tbody");

	defaultBlocks.forEach(function (el) {
		window['Sortable'].create(el, {
			group: "default_blocks",
			animation: 500,
			handle: ".handle",
			draggable: "tr.windowbg",
			onAdd: e => block.sort(e),
			onUpdate: e => block.sort(e),
		});
	});

	additionalBlocks.forEach(function (el) {
		window['Sortable'].create(el, {
			group: "additional_blocks",
			animation: 500,
			handle: ".handle",
			draggable: "tr.windowbg",
			onSort: e => block.sort(e)
		});
	});
</script>
