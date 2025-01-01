<script>
  import '@github/markdown-toolbar-element';
  import { _ } from 'svelte-i18n';
  import { onMount } from 'svelte';
  import { MarkdownPreview } from './index.js';
  import Button from '../BaseButton.svelte';

  let { message = $bindable(''), ...rest } = $props();
  let textarea = $state();

  const uuid = 'id-' + crypto.randomUUID();

  const oninput = (e) => {
    message = e.target.value;
  };

  onMount(() => textarea.focus());
</script>

{#if message}
  <MarkdownPreview class="bg odd" content={message} />
{/if}

<markdown-toolbar for={uuid}>
  <md-bold><Button icon="bold" aria-label={$_('bold')} /></md-bold>
  <md-italic><Button icon="italic" aria-label={$_('italic')} /></md-italic>
  <md-quote><Button icon="quote" aria-label={$_('quote')} /></md-quote>
  <md-code><Button icon="code" aria-label={$_('code')} /></md-code>
  <md-link><Button icon="link" aria-label={$_('link')} /></md-link>
  <md-image><Button icon="image" aria-label={$_('image')} /></md-image>
  <md-unordered-list><Button icon="list" aria-label={$_('list')} /></md-unordered-list>
  <md-task-list><Button icon="task" aria-label={$_('task_list')} /></md-task-list>
</markdown-toolbar>

<textarea {...rest} bind:this={textarea} bind:value={message} id={uuid} {oninput}
></textarea>

<style>
  markdown-toolbar {
    display: flex;
    gap: 10px;
    align-items: center;
    margin-bottom: 4px;
  }
</style>
