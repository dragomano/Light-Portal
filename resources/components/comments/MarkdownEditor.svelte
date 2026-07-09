<script lang="ts">
  import { _ } from 'svelte-i18n';
  import { onMount } from 'svelte';
  import { applyMarkdown } from './markdownActions.js';
  import { MarkdownPreview } from './index.js';
  import Button from '../BaseButton.svelte';

  let { message = $bindable(''), ...rest } = $props();
  let textarea: HTMLTextAreaElement | undefined = $state();

  const actions = [
    { action: 'bold',   label: 'bold' },
    { action: 'italic', label: 'italic' },
    { action: 'quote',  label: 'quote' },
    { action: 'code',   label: 'code' },
    { action: 'link',   label: 'link' },
    { action: 'image',  label: 'image' },
    { action: 'list',   label: 'list' },
    { action: 'task',   label: 'task_list' },
  ] as const;

  onMount(() => textarea?.focus());
</script>

{#if message}
  <MarkdownPreview class="bg odd" content={message} />
{/if}

<div class="toolbar">
  {#each actions as { action, label }}
    <Button
      icon={action}
      aria-label={$_(label)}
      onclick={() => textarea && applyMarkdown(textarea, action)}
    />
  {/each}
</div>

<textarea {...rest} bind:this={textarea} bind:value={message}></textarea>

<style>
  .toolbar {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    align-items: center;
    margin-bottom: 4px;
  }
</style>
