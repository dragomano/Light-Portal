<?php

use Bugo\Compat\Config;
use Bugo\Compat\Lang;
use Bugo\Compat\Utils;

function show_chat_block(int $id, array $parameters, bool $isInSidebar): void
{
	echo /** @lang text */ '
	<script>
		const chat', $id, ' = new SimpleChat("', LP_ACTION, '", ', (Utils::$context['lp_chats'][$id] ?: '[]'), ');
	</script>

	<div
		class="column', $parameters['form_position'] === 'top' ? ' reverse' : '', '"
		x-data="chat', $id, '.handleComments()"
	>
		<ul class="moderation_notes column', $parameters['form_position'] === 'top' ? '' : ' reverse', '">
			<template x-for="(comment, index) in comments" :key="index">
				<li class="', $isInSidebar ? 'floatleft' : '', ' smalltext">
					', $parameters['show_avatars'] ? '<span x-html="comment.author.avatar ?? null"></span>' : '', '
					<strong x-text="comment.author.name"></strong>: <span x-html="comment.message"></span>
					', Utils::$context['user']['is_admin'] ? ' <span class="main_icons delete floatright" @click="removeComment($refs, index, comment.id)"></span> ' : '', '
					<span class="floatright" x-html="comment.created_at"></span>
				</li>
			</template>
		</ul>';

	if (Utils::$context['user']['is_logged']) {
		echo '
		<form @submit.prevent="addComment($refs)">
			<div class="', $isInSidebar ? 'full_width' : 'floatleft', ' post_note">
				<input
					type="text"
					required
					x-ref="message"
					autofocus
					@keyup="$refs.submit.disabled = !$event.target.value"
				>
			</div>
			<button
				class="button ', $isInSidebar ? 'full_width' : 'floatright', '"
				disabled
				x-ref="submit"
				data-block="', $id, '"
			>', Lang::$txt['post'], '</button>
		</form>';
	} else {
		echo '
		<a
			href="', Config::$scripturl, '?action=login"
			onclick="return reqOverlayDiv(this.href, ', Utils::escapeJavaScript(Lang::$txt['login']), ');"
		>', Lang::$txt['lp_simple_chat']['login'], '</a>';
	}

	echo '
	</div>';
}
