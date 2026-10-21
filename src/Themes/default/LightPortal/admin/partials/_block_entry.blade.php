@php use LightPortal\Utils\Icon; @endphp

@unless (empty($id) || empty($data))
	@php
		$title         = ($data['description'] ?: $data['title']) ?: '';
		$status        = empty($data['status']) ? 'false' : 'true';
		$isMissingType = ! isset($txt['lp_' . $data['type']]['title']);
		$workUrl       = $scripturl . '?action=admin&area=lp_blocks&actions&' . $context['session_var'] . '=' . $context['session_id'];
	@endphp

	<tr
		id="lp_block_{{ $id }}"
		class="windowbg"
		data-id="{{ $id }}"
		data-signals="{ st_{{ $id }}: {{ $status }}, ctx_{{ $id }}: false }"
	>
		<td class="icon hidden-xs hidden-sm">
			{!! $data['icon'] !!}
		</td>
		<td class="title">
			<div class="hidden-xs hidden-sm hidden-md">{{ $title }}</div>
			<div class="hidden-lg hidden-xl">
				<table class="table_grid">
					<tbody>
					@if ($title)
						<tr class="windowbg">
							<td colspan="2">{{ $title }}</td>
						</tr>
					@endif
					<tr class="windowbg">
						<td>{!! $txt['lp_' . $data['type']]['title'] ?? $context['lp_missing_block_types'][$data['type']] !!}</td>
						<td class="hidden-md">{{ $data['areas'] }}</td>
					</tr>
					</tbody>
				</table>
			</div>
		</td>
		<td class="type hidden-xs hidden-sm hidden-md">
			{!! $txt['lp_' . $data['type']]['title'] ?? $context['lp_missing_block_types'][$data['type']] !!}
		</td>
		<td class="areas hidden-xs hidden-sm">
			{{ $data['areas'] }}
		</td>
		<td class="priority hidden-xs">
			<span data-lp-priority>{{ $data['priority'] }}</span>
			@php
				$sortIcon = Icon::get('sort');
				echo str_replace(' class="', ' title="' . $txt['lp_action_move'] . '" class="handle ', $sortIcon);
			@endphp
		</td>
		<td class="status">
			@if (!$isMissingType)
				<span
					data-class:on="$st_{{ $id }}"
					data-class:off="!$st_{{ $id }}"
					data-attr:title="$st_{{ $id }} ? '{{ $txt['lp_action_off'] }}' : '{{ $txt['lp_action_on'] }}'"
					data-on:click="@post('{{ $workUrl }}', {payload: {toggle_item: {{ $id }}}})"
				></span>
			@endif
		</td>
		<td class="actions">
			<div class="context_menu" data-on:click__outside="$ctx_{{ $id }} = false">
				<button class="button floatnone" type="button" data-on:click="$ctx_{{ $id }} = true">
					@icon('ellipsis')
				</button>
				<div class="roundframe" data-show="$ctx_{{ $id }}">
					<ul>
						<li>
							<a data-on:click="@post('{{ $workUrl }}', {payload: {clone_block: {{ $id }}}})" class="button">
								{{ $txt['lp_action_clone'] }}
							</a>
						</li>

						@isset ($txt['lp_' . $data['type']]['title'])
							<li>
								<a href="{{ $scripturl }}?action=admin;area=lp_blocks;sa=edit;id={{ $id }}" class="button">
									{{ $txt['modify'] }}
								</a>
							</li>
						@endisset

						<li>
							<a data-on:click="if (!confirm(smf_you_sure)) return; @post('{{ $workUrl }}', {payload: {delete_item: {{ $id }}}})" class="button error">
								{{ $txt['remove'] }}
							</a>
						</li>
					</ul>
				</div>
			</div>
		</td>
	</tr>
@endunless
