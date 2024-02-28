<?php

use Bugo\Compat\{Config, Lang, Utils};

function show_chat_block(int $id, bool $show_avatars, bool $full_width): void
{
	echo /** @lang text */ '
	<script>
		const chat', $id, ' = new SimpleChat("', LP_ACTION, '", ', (Utils::$context['lp_chats'][$id] ?: '[]'), ');
	</script>

	<div x-data="chat', $id, '.handleComments()">
		<ul class="moderation_notes">
			<template x-for="(comment, index) in comments" :key="index">
				<li class="smalltext">
					', $show_avatars === true ? '<span x-html="comment.author.avatar ?? null"></span>' : '', '
					<strong x-text="comment.author.name"></strong>: <span x-text="comment.message"></span>
					', Utils::$context['user']['is_admin'] ? ' <span class="main_icons delete floatright" @click="removeComment($refs, index, comment.id)"></span> ' : '', '
					<span class="floatright" x-html="comment.created_at"></span>
				</li>
			</template>
		</ul>';

	if (Utils::$context['user']['is_logged']) {
		echo '
		<form @submit.prevent="addComment($refs)">
			<div class="', $full_width ? 'full_width' : 'floatleft', ' post_note">
				<input type="text" required x-ref="message" autofocus @keyup="$refs.submit.disabled = !$event.target.value">
			</div>
			<button class="button ', $full_width ? 'full_width' : 'floatright', '" disabled x-ref="submit" data-block="', $id, '">', Lang::$txt['post'], '</button>
		</form>';
	} else {
		echo '
		<a href="', Config::$scripturl, '?action=login" onclick="return reqOverlayDiv(this.href, ', Utils::escapeJavaScript(Lang::$txt['login']), ');">', Lang::$txt['lp_simple_chat']['login'], '</a>';
	}

	echo '
	</div>';
}
