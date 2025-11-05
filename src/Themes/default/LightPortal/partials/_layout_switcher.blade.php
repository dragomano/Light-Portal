@unless (empty($modSettings['lp_show_layout_switcher']))
	<label for="layout">
		<i class="fa-solid fa-object-group"></i>
		<span class="sr-only">{{ $txt['lp_template'] }}</span>
	</label>
	<select id="layout" name="layout" onchange="this.form.submit()">
		@foreach ($context['lp_frontpage_layouts'] as $layout => $title)
			<option value="{{ $layout }}"{{ $context['lp_current_layout'] === $layout ? ' selected' : '' }}>
				{{ $title }}
			</option>
		@endforeach
	</select>
@endunless
