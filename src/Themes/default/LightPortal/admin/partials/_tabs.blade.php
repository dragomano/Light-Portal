@php $fields = $context['posting_fields'] @endphp

<div class="lp_tabs">
	<div data-navigation>
		@foreach ($tabs as $index => $tab)
			<div
				class="bg odd {{ $index === 0 ? 'active_navigation' : '' }}"
				data-tab="{{ $tab['id'] }}"
				@isset ($tab['show']) x-show="{{ $tab['show'] }}" @endisset
			>
				@icon($tab['icon']){{ $tab['title'] }}
			</div>
		@endforeach
	</div>
	<div data-content>
		@foreach ($tabs as $index => $tab)
			<section
				class="bg even {{ $index === 0 ? 'active_content' : '' }}"
				data-content="{{ $tab['id'] }}"
				@isset ($tab['show']) x-show="{{ $tab['show'] }}" @endisset
			>
				@portalTab($fields, $tab['type'] ?? 'content')
			</section>
		@endforeach
	</div>
</div>
