<script>
  import { _ } from 'svelte-i18n';
  import { fade } from 'svelte/transition';
  import { useContextStore, useAppStore } from '../../js/stores.js';
  import { PluginOptionItem } from './index.js';
  import Button from '../BaseButton.svelte';

  /** @type {{ item: { snake_name: string, saveable: boolean, settings: array } }} */
  let { item, hide } = $props();
  let success = $state(false);
  let form = $state();

  const { sessionId, sessionVar } = $useAppStore;
  const { postUrl } = $useContextStore;

  const blockId = $derived(`${item.snake_name}_${sessionId}_settings`);
  const formId = $derived(`${item.snake_name}_form_${sessionId}`);

  const saveSettings = async (e) => {
    e.preventDefault();

    const formData = new FormData(form);
    const { data } = await axios.post(String(postUrl), formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    });

    if (data.success) {
      success = true;
      setTimeout(() => success = false, 2000);
    }
  };
</script>

<div class="roundframe" id={blockId} transition:fade>
  <div class="title_bar">
    <h5 class="titlebg">{$_('settings')}</h5>
  </div>

  <div class="noticebox">
    <form bind:this={form} class="form_settings" id={formId} onsubmit={saveSettings}>
      <input type="hidden" name="plugin_name" value={item.snake_name} />
      <input type="hidden" name={sessionVar} value={sessionId} />

      {#each item.settings as option (option[1])}
        <PluginOptionItem {option} plugin={item.snake_name} />
      {/each}
    </form>
  </div>

  <div class="footer">
    <span class="infobox floatleft {success ? 'show' : ''}">
      {$_('settings_saved')}
    </span>

    <Button icon="close" onclick={hide}>{$_('find_close')}</Button>

    {#if item.saveable}
      <Button icon="save" form={formId} type="submit">
        {$_('save')}
      </Button>
    {/if}
  </div>
</div>
