<script>
  import { _ } from 'svelte-i18n';
  import { MarkdownEditor } from './index.js';
  import Button from '../BaseButton.svelte';

  let { comment, submit, cancel } = $props();
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
