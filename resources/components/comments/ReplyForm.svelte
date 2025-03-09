<script lang="ts">
  import { _ } from 'svelte-i18n';
  import { userState } from '../../js/states.svelte';
  import { MarkdownEditor } from './index.js';
  import Button from '../BaseButton.svelte';
  import type { Snippet } from 'svelte';
  import type { Parent } from '../types';

  interface Props {
    parent?: Parent;
    submit: Function;
    children?: Snippet;
  }

  let { parent, submit, children }: Props = $props();
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
