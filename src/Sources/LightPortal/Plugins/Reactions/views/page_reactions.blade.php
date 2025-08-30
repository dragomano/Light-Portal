@if (empty($context['can_react']) || empty($context['user']['is_logged']))
	<hr @if (! $context['can_react']) class="hidden" @endif>
	<div class="reactions">
		@foreach ($context['prepared_buttons'] as $button)
			@isset ($context['prepared_reactions'][$button['name']])
				<button title="{{ $button['title'] }}">{{ $button['emoji'] }} {{ $context['prepared_reactions'][$button['name']] }}</button>
			@endisset
		@endforeach
	</div>
@else
	<hr>
	<div x-data='{
		showButtons: false,
		buttons: {{ $context['reaction_buttons'] }},
		reactions: {{ json_encode($context['prepared_reactions']) }},
		init() {
			window.pageReactions = this
		}
	}'>
		<div class="reactions">
			<template x-for="reaction in buttons">
				<button x-show="reactions[reaction.name] > 0" :title="reaction.title" x-text="reaction.emoji + ' ' + reactions[reaction.name]"></button>
			</template>
		</div>
		<button class="add_reaction" x-show="!showButtons" @click="showButtons = true" :style="reactions == '' && { marginLeft: '0' }">➕</button>
		<div class="reactions" x-show="showButtons" @click.outside="showButtons = false">
			<template x-for="reaction in buttons">
				<button class="button" @click="$dispatch('addReaction', { reaction: reaction.name })" :title="reaction.title" x-text="reaction.emoji"></button>
			</template>
		</div>
	</div>
@endif
