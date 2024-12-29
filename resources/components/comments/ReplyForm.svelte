<script>
  import { get } from 'svelte/store';
  import { _ } from 'svelte-i18n';
  import { useUserStore } from '../../js/stores.js';
  import { MarkdownEditor } from './index.js';
  import Button from '../BaseButton.svelte';

  let { parent, submit, children } = $props();
  let message = $state('');

  const userStore = get(useUserStore);

  const handleSubmit = () => {
    submit({ parent, content: message });
    message = '';
  };
</script>

{#if userStore.id}
  <div class="reply_form roundframe descbox">
    <MarkdownEditor bind:message placeholder={$_('add_comment')} />

    {@render children?.()}

    <Button icon="submit" name="comment" disabled={message.length === 0} onclick={handleSubmit}>
      {$_('post')}
    </Button>
  </div>
{/if}
