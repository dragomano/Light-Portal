@if (empty($comment['can_react']))
	@foreach ($context['reaction_buttons'] as $button)
		@isset ($comment['prepared_buttons'][$button['name']])
			<span class="reaction_button" title="{{ $button['title'] }}">
				{{ $button['emoji'] }} {{ $comment['prepared_buttons'][$button['name']] }}
			</span>
		@endisset
	@endforeach
@else
	<span x-data='{
		showButtons: false,
		buttons: [],
		reactions: [],
		init() {
			this.buttons = @json($context['reaction_buttons']);
			this.reactions = @json($comment['prepared_reactions']);
			window.commentReactions = window.commentReactions || [];
			window.commentReactions[{{ $comment['id'] }}] = this;
		}
	}'>
        <template x-for="reaction in buttons">
            <span
				class="reaction_button"
				x-show="reactions[reaction.name] > 0"
				:title="reaction.title"
				x-text="reaction.emoji + ' ' + reactions[reaction.name]"
			></span>
        </template>
        <span
			class="add_comment_reaction"
			x-show="!showButtons"
			@click="showButtons = true"
			:style="Object.keys(reactions).length === 0 && { marginLeft: '0' }"
		>➕</span>
        <span class="reactions" x-show="showButtons" @click.outside="showButtons = false">
            <template x-for="reaction in buttons">
                <span
					class="reaction_button"
					@click="$dispatch('addReaction', { reaction: reaction.name, comment: {{ $comment['id'] }} })"
					:title="reaction.title"
					x-text="reaction.emoji"
				></span>
            </template>
        </span>
    </span>
@endif
