<script>
  import { get } from 'svelte/store';
  import { _ } from 'svelte-i18n';
  import { PluginItem } from './index.js';
  import { usePluginStore, useLocalStorage } from '../../js/stores.js';
  import Button from '../BaseButton.svelte';

  const pluginStore = get(usePluginStore);
  const types = $state(pluginStore.types);
  const filter = useLocalStorage('lpPluginsFilter', 'all');
  const layout = useLocalStorage('lpPluginsLayout', 'list');
  const isCardView = $derived($layout === 'card');

  const filteredPlugins = $derived.by(() => {
    if ($filter === 'active') {
      return Object.values(pluginStore.list).filter((item) => item.status === 'on');
    } else if ($filter === 'all') {
      return Object.values(pluginStore.list);
    } else {
      return Object.values(pluginStore.list).filter(
        (item) =>
          !Object.keys(types).includes($filter) || Object.keys(item.types).includes(types[$filter])
      );
    }
  });

  const count = $derived(filteredPlugins.length);
</script>

<div class="cat_bar">
  <h3 class="catbg">
    {$_('plugins')}
    {#if count}<span>{`(${count})`}</span>{/if}
    <span class="floatright">
      <label for="filter">{$_('apply_filter')}</label>
      <select id="filter" bind:value={$filter}>
        <option value="all" selected={$filter === 'all'}>{$_('all')}</option>
        <option value="active" selected={$filter === 'active'}>{$_('lp_active_only')}</option>
        {#each Object.entries(types) as [type, name]}
          <option value={type} selected={$filter === type}>{name}</option>
        {/each}
      </select>
    </span>
  </h3>
</div>

<div class="information">
  {$_('lp_plugins_desc')}
  <div class="hidden-xs floatright">
    <Button
      tag="span"
      icon="simple"
      style="opacity: {isCardView ? '.5' : '1'}"
      onclick={() => ($layout = 'list')}
    />
    <Button
      tag="span"
      icon="tile"
      style="opacity: {isCardView ? '1' : '.5'}"
      onclick={() => ($layout = 'card')}
    />
  </div>
</div>

<div id="addon_list" class={isCardView && 'addon_list'}>
  {#each filteredPlugins as plugin}
    <PluginItem item={plugin} />
  {/each}
</div>
