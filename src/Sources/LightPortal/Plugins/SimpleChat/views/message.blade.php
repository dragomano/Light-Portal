<li class="smalltext{{ $isInSidebar ? ' floatleft' : '' }}">
	@if ($parameters['show_avatars'] && isset($message['author']['avatar']))
		{!! $message['author']['avatar'] !!}
	@endif
	<strong>{{ $message['author']['name'] }}</strong>:
	<span>{{ $message['message'] }}</span>

	@if ($context['user']['is_admin'])
		<span
			class="main_icons delete floatright"
			hx-post="{{ $baseUrl }};chat=remove_message"
			hx-target="#chat-messages-{{ $id }}"
			hx-swap="innerHTML"
			hx-vals='{"id": "{{ $message['id'] }}", "block_id": "{{ $id }}"}'
		></span>
	@endif

	<span class="floatright">{!! $message['created_at'] !!}</span>
</li>
