@php
	use Bugo\Compat\Utils;

	$chatData = $context['lp_chats'][$id] ?? '[]';
	$messages = json_decode($chatData, true) ?: [];
@endphp

<div
	class="column{{ $parameters['form_position'] === 'top' ? ' reverse' : '' }}"
	id="chat-container-{{ $id }}"
	hx-get="{{ $baseUrl }};chat=get_messages;id={{ $id }}"
	hx-trigger="{{ isset($_REQUEST['preview']) ? '' : 'every ' . ($parameters['refresh_interval'] ?? 2) . 's, ' }}updateChat{{ $id }} from:body"
	hx-swap-oob="innerHTML"
	hx-target="#chat-messages-{{ $id }}"
>
	<ul
		id="chat-messages-{{ $id }}"
		class="moderation_notes column{{ $parameters['form_position'] === 'top' ? '' : ' reverse' }}"
		style="max-height: {{ $parameters['window_height'] }}px"
		onscroll="handleChatScroll(this)"
	>
		@foreach ($messages as $message)
			@include('message', [
				'baseUrl'     => $baseUrl,
				'message'     => $message,
				'id'          => $id,
				'isInSidebar' => $isInSidebar,
				'parameters'  => $parameters,
			])
		@endforeach
	</ul>

	@if (! $context['user']['is_logged'])
		<a
			href="{{ $scripturl }}?action=login"
			onclick="return reqOverlayDiv(this.href, '{{ Utils::escapeJavaScript($txt['login']) }}')"
		>{{ $txt['lp_simple_chat']['login'] }}</a>
	@else
		<form
			id="chat-form-{{ $id }}"
			hx-post="{{ $baseUrl }};chat=add_message"
			hx-target="#chat-messages-{{ $id }}"
			hx-swap="beforeend"
			hx-trigger="submit"
			hx-on::after-request="this.reset()"
		>
			<div class="{{ $isInSidebar ? 'full_width' : 'floatleft' }} post_note">
				<input type="hidden" name="block_id" value="{{ $id }}">
				<input
					id="message-input-{{ $id }}"
					type="text"
					name="message"
					required
					autofocus
					oninput="document.getElementById('submit-btn-{{ $id }}').disabled = !this.value"
				>
			</div>
			<button
				id="submit-btn-{{ $id }}"
				class="button {{ $isInSidebar ? 'full_width' : 'floatright' }}"
				disabled
				data-block="{{ $id }}"
			>{{ $txt['post'] }}</button>
		</form>
	@endif
</div>

<script defer>
	let isUserScrolling = false;
	function handleChatScroll(_) {
		isUserScrolling = true;
	}

	document.addEventListener("DOMContentLoaded", () => {
		document.body.addEventListener("htmx:afterRequest", function (e) {
			if (e.detail.elt.id === "chat-form-{{ $id }}") {
				setTimeout(() => {
					const input = document.getElementById("message-input-{{ $id }}");
					input && input.focus();
					isUserScrolling = false;
					htmx.trigger("#chat-container-{{ $id }}", "updateChat{{ $id }}");
				}, 100);
			}
		});

		document.body.addEventListener("htmx:afterSwap", function (e) {
			if (isUserScrolling) return;
			if (e.detail.target.id === "chat-messages-{{ $id }}") {
				const ul = e.detail.target;
				ul.scrollTop = {{ $parameters['form_position'] === 'top' ? '0' : 'ul.scrollHeight' }};
			}
		});
	});
</script>
