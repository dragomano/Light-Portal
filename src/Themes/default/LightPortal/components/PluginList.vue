<template>
  <div class="cat_bar">
    <h3 class="catbg">
      {{ $t('plugins') }} <span v-if="count" v-text="`(${count})`"></span>
      <span class="floatright">
        <label for="filter">{{ $t('apply_filter') }}</label>
        <select id="filter" v-model="filter">
          <option value="all" :selected="filter === 'all'">{{ $t('all') }}</option>
          <option value="active" :selected="filter === 'active'">{{ $t('lp_active_only') }}</option>
          <option
            v-for="(value, name) in types"
            :key="name"
            :value="name"
            :selected="filter === value"
          >
            {{ value }}
          </option>
        </select>
      </span>
    </h3>
  </div>

  <div class="information">
    {{ $t('lp_plugins_desc') }}
    <div class="hidden-xs floatright">
      <Button
        view="span"
        icon="simple"
        :style="{ opacity: isCardView ? '.5' : '1' }"
        @click="layout = 'list'"
      />
      <Button
        view="span"
        icon="tile"
        :style="{ opacity: isCardView ? '1' : '.5' }"
        @click="layout = 'card'"
      />
    </div>
  </div>

  <ListTransition tag="div" id="addon_list" :class="isCardView ? 'addon_list' : undefined">
    <PluginItem v-for="(plugin, index) in filteredPlugins" :key="index" :item="plugin" />
  </ListTransition>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useStorage } from '@vueuse/core';
import { usePluginStore } from '../../scripts/light_portal/dev/plugin_stores.js';
import Button from './BaseButton.vue';
import ListTransition from './ListTransition.vue';
import PluginItem from './PluginItem.vue';

const pluginStore = usePluginStore();

const types = ref(pluginStore.types);

const filter = useStorage('lpPluginsFilter', 'all', localStorage);
const layout = useStorage('lpPluginsLayout', 'list', localStorage);

const isCardView = computed(() => layout.value === 'card');

const filteredPlugins = computed(() => {
  if (filter.value === 'active') {
    return Object.values(pluginStore.list).filter((item) => item.status === 'on');
  } else if (filter.value === 'all') {
    return Object.values(pluginStore.list);
  } else {
    return Object.values(pluginStore.list).filter(
      (item) =>
        !Object.keys(types.value).includes(filter.value) ||
        Object.keys(item.types).includes(types.value[filter.value])
    );
  }
});

const count = computed(() => filteredPlugins.value.length);
</script>
