<script lang="ts">
  import '@github/markdown-toolbar-element';
  import { _ } from 'svelte-i18n';
  import { onMount } from 'svelte';
  import { MarkdownPreview } from './index.js';
  import Button from '../BaseButton.svelte';

  let { message = $bindable(''), ...rest } = $props();
  let textarea: HTMLTextAreaElement = $state();

  const generateUUID = () => {
    if (typeof crypto !== 'undefined' && crypto.randomUUID) {
      return crypto.randomUUID();
    }

    return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (c) {
      const r = (Math.random() * 16) | 0;
      const v = c === 'x' ? r : (r & 0x3) | 0x8;
      return v.toString(16);
    });
  };

  const uuid = 'id-' + generateUUID();

  const oninput = (e: Event) => {
    const target = e.target as HTMLTextAreaElement;

    message = target.value;
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

<textarea {...rest} bind:this={textarea} bind:value={message} id={uuid} {oninput}></textarea>

<style>
  markdown-toolbar {
    display: flex;
    gap: 10px;
    align-items: center;
    margin-bottom: 4px;
  }
</style>
