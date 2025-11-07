@php use LightPortal\Utils\Icon; @endphp

@unless (empty($id) || empty($data))
	@php
		$title = ($data['description'] ?: $data['title']) ?: '';
		$status = empty($data['status']) ? 'false' : 'true';
	@endphp

	<tr
		class="windowbg"
		data-id="{{ $id }}"
		x-data="{ status: {{ $status }}, showContextMenu: false }"
		x-init="$watch('status', () => block.toggleStatus($el))"
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
						<td>{{ $txt['lp_' . $data['type']]['title'] ?? $context['lp_missing_block_types'][$data['type']] }}</td>
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
			{{ $data['priority'] }}
			@php
				$sortIcon = Icon::get('sort');
				echo str_replace(' class="', ' title="' . $txt['lp_action_move'] . '" class="handle ', $sortIcon);
			@endphp
		</td>
		<td class="status">
            <span
				:class="{ 'on': status, 'off': !status }"
				:title="status ? '{{ $txt['lp_action_off'] }}' : '{{ $txt['lp_action_on'] }}'"
				@click.prevent="status = !status"
			></span>
		</td>
		<td class="actions">
			<div class="context_menu" @click.outside="showContextMenu = false">
				<button class="button floatnone" @click.prevent="showContextMenu = true">
					@icon('ellipsis')
				</button>
				<div class="roundframe" x-show="showContextMenu" x-transition.duration.500ms>
					<ul>
						<li>
							<a @click.prevent="block.clone($root)" class="button">
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
							<a @click.prevent="showContextMenu = false; block.remove($root)" class="button error">
								{{ $txt['remove'] }}
							</a>
						</li>
					</ul>
				</div>
			</div>
		</td>
	</tr>
@endunless
