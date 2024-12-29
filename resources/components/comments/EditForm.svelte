<script>
  import { _ } from 'svelte-i18n';
  import { MarkdownEditor } from './index.js';
  import Button from '../BaseButton.svelte';

  let { comment, submit, cancel } = $props();
  let message = $state(comment.message);

  const handleSubmit = () => {
    submit({ id: comment.id, content: message });
  };

  const handleCancel = () => {
    cancel();
  };
</script>

<div class="edit_form">
  <MarkdownEditor bind:message required />

  <div class="comment_edit_buttons">
    {#if message.length}
      <Button onclick={handleSubmit} tag="span" icon="save">
        {$_('save')}
      </Button>
    {/if}
    <Button onclick={handleCancel} tag="span" icon="undo">
      {$_('modify_cancel')}
    </Button>
  </div>
</div>
