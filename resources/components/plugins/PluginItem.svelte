<script>
  import { _ } from 'svelte-i18n';
  import { slide } from 'svelte/transition';
  import { useContextStore, useIconStore, useAppStore, usePluginStore } from '../../js/stores.js';
  import { PluginOptionList } from './index.js';
  import Toggle from 'svelte-toggle';
  import Button from '../BaseButton.svelte';

  /** @type {{ item: { snake_name: string, settings: array, special: string } }} */
  let { item } = $props();

  const appStore = $useAppStore;
  const pluginStore = $usePluginStore;
  const contextStore = $useContextStore;
  const { donate: donateIcon, download: downloadIcon } = $useIconStore;

  let show = $state(false);
  let toggled = $state(item.status === 'on');

  /** @type {{ pluginStore: { donate: { link: string }, donwload: { link: string } } }} */
  const donateLink = $derived(pluginStore.donate[item.name].link);
  const downloadLink = $derived(pluginStore.download[item.name].link);

  const key = $derived(item.special === 'can_donate' ? 'donate' : 'download');
  const specialDesc = $derived(
    pluginStore[key][item.name]?.languages[contextStore.lang ?? 'english']
  );

  const showToggle = $derived(!item.special && Object.keys(item.types)[0] !== $_('not_applicable'));
  const settingsId = $derived(item.snake_name + '_' + appStore.sessionId);
  const index = $derived(
    Object.values(pluginStore.list).findIndex((plugin) => plugin.snake_name === item.snake_name)
  );

  const toggle = async () => {
    const response = await axios.post(appStore.baseUrl + '?action=admin;area=lp_plugins;toggle', {
      plugin: index,
      status: item.status
    });

    if (response.data.success) {
      item.status = toggled ? 'off' : 'on';
    }
  };
</script>

<div class="windowbg" transition:slide>
  <div class="features" data-id={index}>
    <div class="floatleft">
      <h4>
        {item.name}
        {#each Object.entries(item.types) as [type, label]}
          <strong class="new_posts {label}" data-key={type}>{type}</strong>
        {/each}
      </h4>
      <div>
        {#if item.special}
          <p>{@html specialDesc}</p>
        {:else}
          <p>{@html item.desc}</p>
        {/if}
      </div>
    </div>

    <div class="floatright">
      {#if item.settings.length}
        <Button
          tag="span"
          icon="gear"
          class={show && 'fa-spin'}
          data-id={settingsId}
          onclick={() => (show = !show)}
        />
      {/if}

      {#snippet specialLink(link, icon)}
        <a href={link} rel="noopener" target="_blank">
          {@html icon}
        </a>
      {/snippet}

      {#if item.special}
        {#if item.special === 'can_donate'}
          {@render specialLink(donateLink, donateIcon)}
        {:else}
          {@render specialLink(downloadLink, downloadIcon)}
        {/if}
      {/if}

      {#if showToggle}
        <Toggle
          switchColor="#eee"
          toggledColor="#24a148"
          untoggledColor="#fa4d56"
          bind:toggled
          onclick={toggle}
          hideLabel
          label="Toggle"
        />
      {/if}
    </div>

    {#if show && item.settings.length}
      <br class="clear" />
      <PluginOptionList {item} />
    {/if}
  </div>
</div>
