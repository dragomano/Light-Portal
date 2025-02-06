<script>
  import { _ } from 'svelte-i18n';
  import { fade } from 'svelte/transition';
  import { appState, contextState, iconState, pluginState } from '../../js/states.svelte.js';
  import { PluginOptionList } from './index.js';
  import Toggle from 'svelte-toggle';
  import Button from '../BaseButton.svelte';

  /** @type {{ item: { snake_name: string, settings: array, special: string } }} */
  let { item } = $props();

  const { donate: donateIcon, download: downloadIcon } = iconState;

  let show = $state(false);
  let toggled = $state(item.status === 'on');

  /** @type {{ pluginState: { donate: { link: string }, donwload: { link: string } } }} */
  const donateLink = $derived(pluginState.donate[item.name].link);
  const downloadLink = $derived(pluginState.download[item.name].link);

  const key = $derived(item.special === 'can_donate' ? 'donate' : 'download');
  const specialDesc = $derived(
    pluginState[key][item.name]?.languages[contextState.lang ?? 'english']
  );

  const showToggle = $derived(!item.special && Object.keys(item.types)[0] !== $_('not_applicable'));
  const settingsId = $derived(item.snake_name + '_' + appState.sessionId);
  const index = $derived(
    Object.values(pluginState.list).findIndex((plugin) => plugin.snake_name === item.snake_name)
  );

  const toggle = async () => {
    const response = await axios.post(appState.baseUrl + '?action=admin;area=lp_plugins;toggle', {
      plugin: index,
      status: item.status
    });

    if (response.data.success) {
      item.status = toggled ? 'off' : 'on';
    }
  };
</script>

<div class="windowbg" transition:fade>
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
          onclick={() => show = !show}
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
