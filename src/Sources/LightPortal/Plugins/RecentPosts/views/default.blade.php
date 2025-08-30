<div class="{{ $isInSidebar ? 'recent_posts_list' : 'recent_posts noup' }}">

	@if (empty($parameters['use_simple_style']))
		@foreach ($posts as $post)
			@php
				$post['preview'] = '<a href="' . $post['href'] . '">' . $post['preview'] . '</a>';
			@endphp
			<div class="word_break">
				@if ($parameters['show_avatars'] && isset($post['poster']['avatar']))
					<div class="poster_avatar" title="{{ $post['poster']['name'] }}">
						{!! $post['poster']['avatar'] !!}
					</div>
				@endif

				@if ($post['is_new'])
					<a class="new_posts" href="{{ $scripturl }}?topic={{ $post['topic'] }}.msg{{ $post['new_from'] }};topicseen#new">
						{{ $txt['new'] }}
					</a>
				@endif

				<span>
                    {!! $post[$parameters['link_type']] !!}

					@if (empty($parameters['show_avatars']))
						<br>
						<span class="smalltext">
                            {{ $txt['by'] }} {!! $post['poster']['link'] !!}
                        </span>
					@endif

                    <br>
                    <span class="smalltext">{{ $post['timestamp'] }}</span>
                </span>

				@if (! empty($parameters['show_body']))
					<div>{!! $post['body'] !!}</div>
				@endif
			</div>
		@endforeach
	@else
		@foreach ($posts as $post)
			@php
				$post['preview'] = '<a href="' . $post['href'] . '">' . $post['preview'] . '</a>';
			@endphp
			<div class="windowbg">
				<div class="smalltext">{{ $post['time'] }}</div>
				{!! $post[$parameters['link_type']] !!}

				@if (! empty($parameters['show_body']))
					<div>{!! $post['body'] !!}</div>
				@endif
			</div>
		@endforeach
	@endif

</div>
