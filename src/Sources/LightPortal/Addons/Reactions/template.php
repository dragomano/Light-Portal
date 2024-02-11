<?php

use Bugo\Compat\Utils;

function show_page_reactions(): void
{
	// Use valid markup for guests and simple viewers
	if (empty(Utils::$context['can_react']) || empty(Utils::$context['user']['is_logged'])) {
		echo /** @lang text */ '
	<hr>
	<div class="reactions">';

		foreach (Utils::$context['prepared_buttons'] as $button) {
			if (isset(Utils::$context['prepared_reactions'][$button['name']])) {
				echo '
		<button title="', $button['title'], '">', $button['emoji'], ' ', Utils::$context['prepared_reactions'][$button['name']], '</button>';
			}
		}

		echo /** @lang text */ '
	</div>';

		return;
	}

	// Use Alpine.js for users with proper permissions
	echo /** @lang text */ '
	<hr>
	<div x-data=\'{ showButtons: false, buttons: ', Utils::$context['reaction_buttons'], ', reactions: ', json_encode(Utils::$context['prepared_reactions']), /** @lang text */ ', init() { window.pageReactions = this } }\'>
		<div class="reactions">
			<template x-for="reaction in buttons">
				<button x-show="reactions[reaction.name] > 0" :title="reaction.title" x-text="reaction.emoji + \' \' + reactions[reaction.name]"></button>
			</template>
		</div>
		<button class="add_reaction" x-show="!showButtons" @click="showButtons = true" :style="reactions == \'\' && { marginLeft: \'0\' }">➕</button>
		<div class="reactions" x-show="showButtons" @click.outside="showButtons = false">
			<template x-for="reaction in buttons">
				<button class="button" @click="$dispatch(\'addReaction\', { reaction: reaction.name })" :title="reaction.title" x-text="reaction.emoji"></button>
			</template>
		</div>
	</div>';
}

function show_comment_reactions(array $comment): void
{
	if (empty($comment['can_react'])) {
		foreach (Utils::$context['prepared_buttons'] as $button) {
			if (isset($comment['prepared_buttons'][$button['name']])) {
				echo '
	<span class="reaction_button" title="', $button['title'], '">', $button['emoji'], ' ', $comment['prepared_buttons'][$button['name']], '</span>';
			}
		}

		return;
	}

	echo '
	<span x-data=\'{ showButtons: false, buttons: ', Utils::$context['reaction_buttons'], ', reactions: ', $comment['prepared_reactions'], ', init() { window.commentReactions', $comment['id'], /** @lang text */ ' = this } }\'>
		<template x-for="reaction in buttons">
			<span class="reaction_button" x-show="reactions[reaction.name] > 0" :title="reaction.title" x-text="reaction.emoji + \' \' + reactions[reaction.name]"></span>
		</template>
		<span class="add_comment_reaction" x-show="!showButtons" @click="showButtons = true" :style="reactions == \'\' && { marginLeft: \'0\' }">➕</span>
		<span class="reactions" x-show="showButtons" @click.outside="showButtons = false">
			<template x-for="reaction in buttons">
				<span class="reaction_button" @click="$dispatch(\'addReaction\', { reaction: reaction.name, comment: ', $comment['id'], /** @lang text */ ' })" :title="reaction.title" x-text="reaction.emoji"></span>
			</template>
		</span>
	</span>';
}
