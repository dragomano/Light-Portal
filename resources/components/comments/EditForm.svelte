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

  const t = $_;

  let { comment, submit, cancel }: Props = $props();

  // svelte-ignore state_referenced_locally
  let message = $state(comment.message);
</script>

<div class="edit_form">
  <MarkdownEditor bind:message required />

  <div class="comment_edit_buttons">
    {#if message.length}
      <Button onclick={() => submit({ id: comment.id, content: message })} tag="span" icon="save">
        {t('save')}
      </Button>
    {/if}

    <Button onclick={() => cancel()} tag="span" icon="undo">
      {t('modify_cancel')}
    </Button>
  </div>
</div>
