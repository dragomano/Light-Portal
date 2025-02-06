<script>
  import { _ } from 'svelte-i18n';
  import { userState } from '../../js/states.svelte.js';
  import { MarkdownEditor } from './index.js';
  import Button from '../BaseButton.svelte';

  let { parent, submit, children } = $props();
  let message = $state('');

  const onclick = () => {
    submit({ parent, content: message });
    message = '';
  };
</script>

{#if userState.id}
  <div class="reply_form roundframe descbox">
    <MarkdownEditor bind:message placeholder={$_('add_comment')} />

    {@render children?.()}

    <Button icon="submit" name="comment" disabled={message.length === 0} {onclick}>
      {$_('post')}
    </Button>
  </div>
{/if}
