<script lang="ts">
  import { _ } from 'svelte-i18n';
  import { MarkdownEditor } from './index.js';
  import Button from '../BaseButton.svelte';
  import type { Comment } from '../types';

  interface Props {
    comment: Comment;
    submit: Function;
    cancel: Function;
  }

  let { comment, submit, cancel }: Props = $props();
  let message = $state(comment.message);
</script>

<div class="edit_form">
  <MarkdownEditor bind:message required />

  <div class="comment_edit_buttons">
    {#if message.length}
      <Button onclick={() => submit({ id: comment.id, content: message })} tag="span" icon="save">
        {$_('save')}
      </Button>
    {/if}

    <Button onclick={() => cancel()} tag="span" icon="undo">
      {$_('modify_cancel')}
    </Button>
  </div>
</div>
