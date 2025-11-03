<div class="cat_bar">
	<h3 class="catbg">@icon('users'){{ $txt['lp_contributors'] }}</h3>
</div>

<div class="windowbg noup">
	<div class="title_bar">
		<h4 class="titlebg">{{ $txt['lp_translators'] }}</h4>
	</div>
	<div class="row">
		@foreach ($context['portal_translations'] as $lang => $translators)
			<div class="col-xs-12 col-sm-6">
				<div class="sub_bar">
					<h5 class="subbg">{{ $lang }}</h5>
				</div>
				<fieldset class="windowbg noup">{{ sentence_list($translators) }}</fieldset>
			</div>
		@endforeach
	</div>

	<div class="title_bar">
		<h4 class="titlebg">{{ $txt['lp_consultants'] }}</h4>
	</div>
	<div class="roundframe noup">
		@foreach ($context['consultants'] as $tester)
			<a class="button" href="{{ $tester['link'] }}" target="_blank" rel="nofollow noopener">
				{{ $tester['name'] }}
			</a>
		@endforeach
	</div>

	<div class="title_bar">
		<h4 class="titlebg">{{ $txt['lp_sponsors'] }}</h4>
	</div>
	<div class="roundframe noup">
		@foreach ($context['sponsors'] as $sponsor)
			<a class="button" href="{{ $sponsor['link'] }}" target="_blank" rel="nofollow noopener">
				{!! $sponsor['name'] !!}
			</a>
		@endforeach
	</div>

	<div class="title_bar">
		<h4 class="titlebg">{{ $txt['lp_tools'] }}</h4>
	</div>
	<div class="row">
		@foreach ($context['tools'] as $tool)
			<div class="col-xs-12 col-sm-6">
				<div class="windowbg centertext" style="padding: 1px">
					<a
						class="button"
						style="width: 100%"
						href="{{ $tool['link'] }}"
						target="_blank" rel="nofollow noopener"
					>
						{{ $tool['name'] }}
					</a>
				</div>
			</div>
		@endforeach
	</div>
</div>

@unless (empty($context['lp_components']))
	<div class="cat_bar">
		<h3 class="catbg">@icon('copyright'){{ $txt['lp_used_components'] }}</h3>
	</div>
	<div class="roundframe noup">
		@foreach ($context['lp_components'] as $item)
			<div class="windowbg row center-xs between-md">
				<div>
					@empty($item['link']))
						<span>{{ $item['title'] }}</span>
					@else
						<a class="bbc_link" href="{{ $item['link'] }}" target="_blank" rel="noopener">
							{{ $item['title'] }}
						</a>
					@endempty
				</div>
				<div class="hidden-xs hidden-sm">
					{{ empty($item['author']) ? '' : '&copy; ' . $item['author'] }}

					@unless (empty($item['license']))
						@isset ($item['author']))
							|
						@endisset
						<a href="{{ $item['license']['link'] }}" target="_blank" rel="noopener">
							{{ $item['license']['name'] }}
						</a>
					@endunless
				</div>
			</div>
		@endforeach
	</div>
@endunless
