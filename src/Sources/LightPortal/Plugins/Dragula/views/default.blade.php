@php
	use LightPortal\Enums\Placement;

	$sortUrl = $scripturl . '?action=admin&area=lp_blocks&actions&' . $context['session_var'] . '=' . $context['session_id'];
	$blocks  = $context['lp_blocks'] ?? [];
@endphp

<div hidden data-signals="{ sort_ids: '', sort_placement: '', sort_source: '' }">
	<input id="lp_dragula_sort_ids" type="hidden" data-bind="sort_ids">
	<input id="lp_dragula_sort_placement" type="hidden" data-bind="sort_placement">
	<input id="lp_dragula_sort_source" type="hidden" data-bind="sort_source">
	<button
		id="lp_dragula_sort_trigger"
		type="button"
		data-on:click="@post('{{ $sortUrl }}', {payload: {sort_items: $sort_ids, placement: $sort_placement, source: $sort_source, data_only: true}})"
	></button>
</div>

{{-- Panels without blocks are not rendered by the page, so they are replaced by dashed drop zones.
     The zones repeat the panel layout, so the whole map can be used to move blocks between the panels --}}
<div class="lp_dragula_slots">
	@foreach (Placement::all() as $panel => $label)
		<div class="lp_dragula_slot{{ empty($blocks[$panel]) ? '' : ' lp_dragula_slot_taken' }}" data-panel="{{ $panel }}">
			<span>{{ $label }}</span>
		</div>
	@endforeach
</div>
