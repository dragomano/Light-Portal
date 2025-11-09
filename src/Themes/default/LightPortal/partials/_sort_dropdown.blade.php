@unless (empty($modSettings['lp_show_sort_dropdown']) || empty($context['lp_sorting_options']))
	<label for="sort">
		<i class="fa-solid fa-arrow-down-z-a" aria-hidden="true"></i>
		<span class="sr-only">{{ $txt['lp_sorting_label'] }}</span>
	</label>
	<select id="sort" name="sort" onchange="this.form.submit()">
		@foreach ($context['lp_sorting_options'] as $value => $label)
			<option value="{{ $value }}"{{ $context['lp_current_sorting'] === $value ? ' selected' : '' }}>
				{{ $label }}
			</option>
		@endforeach
	</select>
@endunless
