<script>
  import { _ } from 'svelte-i18n';
  import { useUserStore } from '../../js/stores.js';
  import { MarkdownEditor } from './index.js';
  import Button from '../BaseButton.svelte';

  let { parent, submit, children } = $props();
  let message = $state('');

  const onclick = () => {
    submit({ parent, content: message });
    message = '';
  };
</script>

{#if $useUserStore.id}
  <div class="reply_form roundframe descbox">
    <MarkdownEditor bind:message placeholder={$_('add_comment')} />

    {@render children?.()}

    <Button icon="submit" name="comment" disabled={message.length === 0} {onclick}>
      {$_('post')}
    </Button>
  </div>
{/if}
