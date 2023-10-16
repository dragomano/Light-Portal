<?php

function show_page_reactions(): void
{
	global $context;

	// Use valid markup for guests and simple viewers
	if (empty($context['can_react']) || empty($context['user']['is_logged'])) {
		echo '
	<hr>
	<div class="reactions">';

		foreach ($context['prepared_buttons'] as $button) {
			if (isset($context['prepared_reactions'][$button['name']])) {
				echo '
		<button title="', $button['title'], '">', $button['emoji'], ' ', $context['prepared_reactions'][$button['name']], '</button>';
			}
		}

		echo '
	</div>';

		return;
	}

	// Use Alpine.js for users with proper permissions
	echo '
	<hr>
	<div x-data=\'{ showButtons: false, buttons: ', $context['reaction_buttons'], ', reactions: ', json_encode($context['prepared_reactions']), ', init() { window.pageReactions = this } }\'>
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
	</div>

	<script>
		document.addEventListener("alpine:init", () => {
			document.addEventListener("addReaction", (event) => {
				const isComment = typeof event.detail.comment !== "undefined"

				axios.post("', $context['reaction_url'], ';add_reaction", event.detail)
					.then(() => {
						isComment
						? axios
							.post("', $context['reaction_url'], ';get_reactions", {
								comment: event.detail.comment
							})
							.then(response => {
								window["commentReactions" + event.detail.comment].showButtons = false
								window["commentReactions" + event.detail.comment].reactions = response.data
							})
						: axios
							.get("', $context['reaction_url'], ';get_reactions")
							.then(response => {
								window.pageReactions.showButtons = false
								window.pageReactions.reactions = response.data
							})
					})
			})
		})
	</script>';
}

function show_comment_reactions(array $comment): void
{
	global $context;

	if (empty($comment['can_react'])) {
		foreach ($context['prepared_buttons'] as $button) {
			if (isset($comment['prepared_buttons'][$button['name']])) {
				echo '
	<span class="reaction_button" title="', $button['title'], '">', $button['emoji'], ' ', $comment['prepared_buttons'][$button['name']], '</span>';
			}
		}

		return;
	}

	echo '
	<span x-data=\'{ showButtons: false, buttons: ', $context['reaction_buttons'], ', reactions: ', $comment['prepared_reactions'], ', init() { window.commentReactions', $comment['id'], ' = this } }\'>
		<template x-for="reaction in buttons">
			<span class="reaction_button" x-show="reactions[reaction.name] > 0" :title="reaction.title" x-text="reaction.emoji + \' \' + reactions[reaction.name]"></span>
		</template>
		<span class="add_comment_reaction" x-show="!showButtons" @click="showButtons = true" :style="reactions == \'\' && { marginLeft: \'0\' }">➕</span>
		<span class="reactions" x-show="showButtons" @click.outside="showButtons = false">
			<template x-for="reaction in buttons">
				<span class="reaction_button" @click="$dispatch(\'addReaction\', { reaction: reaction.name, comment: ', $comment['id'], ' })" :title="reaction.title" x-text="reaction.emoji"></span>
			</template>
		</span>
	</span>';
}
