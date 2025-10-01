<div class="{{ $isInSidebar ? 'recent_topics_list' : 'recent_topics noup' }}">

	@if (empty($parameters['use_simple_style']))
		@foreach ($topics as $topic)
			@php
				$topic['preview'] = '<a href="' . $topic['href'] . '">' . $topic['preview'] . '</a>';
			@endphp
			<div class="windowbg word_break">
				@if (! empty($parameters['show_avatars']) && isset($topic['poster']['avatar']))
					<div class="poster_avatar" title="{{ $topic['poster']['name'] }}">
						{!! $topic['poster']['avatar'] !!}
					</div>
				@endif

				@if ($topic['is_new'])
					<a class="new_posts" href="{{ $scripturl }}?topic={{ $topic['topic'] }}.msg{{ $topic['new_from'] }};topicseen#new">
						{{ $txt['new'] }}
					</a>
				@endif

				<span>
                    @if (! empty($parameters['show_icons']))
						{!! $topic['icon'] !!}
					@endif

					{!! $topic[$parameters['link_type']] !!}

					@if (empty($parameters['show_avatars']))
						<br>
						<span class="smalltext">
                            {{ $txt['by'] }} {!! $topic['poster']['link'] !!}
                        </span>
					@endif

                    <br>
                    <span class="smalltext">{{ $topic['timestamp'] }}</span>
                </span>
			</div>
		@endforeach
	@else
		@foreach ($topics as $topic)
			@php
				$topic['preview'] = '<a href="' . $topic['href'] . '">' . $topic['preview'] . '</a>';
			@endphp
			<div class="windowbg">
				<div class="smalltext">{{ $topic['time'] }}</div>
				{!! $topic[$parameters['link_type']] !!}

				<div class="smalltext{{ $context['right_to_left'] ? ' floatright' : '' }}">
					<i class="fas fa-eye"></i> {{ $topic['views'] }}&nbsp;
					<i class="fas fa-comment"></i> {{ $topic['replies'] }}
				</div>
			</div>
		@endforeach
	@endif

</div>
