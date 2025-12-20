<script lang="ts">
  import { _ } from 'svelte-i18n';
  import { fade } from 'svelte/transition';
  import { appState, contextState, iconState, pluginState, axios } from '../../js/states.svelte';
  import { PluginOptionList } from './index.js';
  import Toggle from 'svelte-toggle';
  import Button from '../BaseButton.svelte';
  import type { Plugin } from '../types';

  interface Props {
    item: Plugin;
  }

  let { item }: Props = $props();
  let show = $state(false);
  // svelte-ignore state_referenced_locally
  let toggled = $state(item.status === 'on');
  // svelte-ignore state_referenced_locally
  let outdated = $state(item.outdated);
  // svelte-ignore state_referenced_locally
  let settingsId = $state(item.snakeName + '_' + appState.sessionId);
  let index = $state(
    Object.values(pluginState.list).findIndex(
      (plugin: Plugin) => plugin.snakeName === item.snakeName
    )
  );

  const { donate: donateIcon, download: downloadIcon } = iconState;
  const donateLink = $derived(pluginState.donate[item.name].link);
  const downloadLink = $derived(pluginState.download[item.name].link);
  const specialLink = $derived(item.special === 'can_donate' ? donateLink : downloadLink);
  const specialIcon = $derived(item.special === 'can_donate' ? donateIcon : downloadIcon);
  const key = $derived(item.special === 'can_donate' ? 'donate' : 'download');
  const specialDesc = $derived(
    pluginState[key][item.name]?.languages[contextState.lang ?? 'english']
  );
  const showToggle = $derived(!item.special && Object.keys(item.types)[0] !== $_('not_applicable'));

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

<div class="windowbg" class:outdated transition:fade>
  <div class="features" data-id={index}>
    <div class="floatleft">
      <h4>
        {item.name}
        {#each Object.entries(item.types) as [type, label]}
          <strong class="new_posts {label}" data-key={type}>{type}</strong>
        {/each}
      </h4>
      <p>{@html item.special && !outdated ? specialDesc : item.desc}</p>
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

      {#if item.special}
        <a href={specialLink} rel="noopener" target="_blank">
          {@html specialIcon}
        </a>
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

<style>
  .outdated {
    background: darkgray;
  }
</style>
