<script>
  import { _ } from 'svelte-i18n';
  import { PluginItem } from './index.js';
  import { pluginState } from '../../js/states.svelte.js';
  import { localStore } from '../../js/stores.js';
  import Button from '../BaseButton.svelte';

  const FILTER_ALL = 'all';
  const FILTER_ACTIVE = 'active';
  const LAYOUT_LIST = 'list';
  const LAYOUT_CARD = 'card';

  const types = $state(pluginState.types);

  const filter = localStore('lpPluginsFilter', FILTER_ALL);
  const layout = localStore('lpPluginsLayout', LAYOUT_LIST);

  const filteredPlugins = $derived.by(() => {
    const plugins = Object.values(pluginState.list);
    const isFilterInTypes = types[$filter];

    if ($filter === FILTER_ALL) return plugins;
    if ($filter === FILTER_ACTIVE) return plugins.filter((item) => item.status === 'on');

    return plugins.filter((item) => !isFilterInTypes || item.types?.[isFilterInTypes]);
  });

  const isCardView = $derived($layout === LAYOUT_CARD);
  const count = $derived(filteredPlugins.length);
</script>

<div class="cat_bar">
  <h3 class="catbg">
    {$_('plugins')}
    {#if count}<span>{`(${count})`}</span>{/if}
    <span class="floatright">
      <label for="filter">{$_('apply_filter')}</label>
      <select id="filter" bind:value={$filter}>
        <option value={FILTER_ALL}>{$_('all')}</option>
        <option value={FILTER_ACTIVE}>{$_('lp_active_only')}</option>
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
      aria-label={$_('list_view')}
      onclick={() => $layout = LAYOUT_LIST}
    />
    <Button
      tag="span"
      icon="tile"
      style="opacity: {isCardView ? '1' : '.5'}"
      aria-label={$_('card_view')}
      onclick={() => $layout = LAYOUT_CARD}
    />
  </div>
</div>

<div id="addon_list" class:addon_list={isCardView}>
  {#each filteredPlugins as plugin (plugin.snake_name)}
    <PluginItem item={plugin} />
  {/each}
</div>
