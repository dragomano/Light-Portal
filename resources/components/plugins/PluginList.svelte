<script>
  import { _ } from 'svelte-i18n';
  import { PluginItem } from './index.js';
  import { usePluginStore, useLocalStorage } from '../../js/stores.js';
  import Button from '../BaseButton.svelte';

  const pluginStore = $usePluginStore;
  const types = $state(pluginStore.types);
  const filter = useLocalStorage('lpPluginsFilter', 'all');
  const layout = useLocalStorage('lpPluginsLayout', 'list');
  const isCardView = $derived($layout === 'card');

  const filteredPlugins = $derived.by(() => {
    const plugins = Object.values(pluginStore.list);

    if ($filter === 'all') return plugins;
    if ($filter === 'active') return plugins.filter((item) => item.status === 'on');

    return plugins.filter((item) => {
      const isFilterInTypes = types[$filter];

      return !isFilterInTypes || item.types?.[isFilterInTypes];
    });
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
        <option value="all">{$_('all')}</option>
        <option value="active">{$_('lp_active_only')}</option>
        {#each Object.entries(types) as [type, name]}
          <option value={type}>{name}</option>
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
