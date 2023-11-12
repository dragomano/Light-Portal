<template>
  <div class="cat_bar">
    <h3 class="catbg">
      {{ $t('plugins') }} <span v-if="count" v-text="`(${count})`"></span>
      <span class="floatright">
        <label for="filter">{{ $t('apply_filter') }}</label>
        <select id="filter" v-model="filter">
          <option value="all" :selected="filter === 'all'">{{ $t('all') }}</option>
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
    <PluginItem v-for="(plugin, index) in plugins" :key="index" :item="plugin" />
  </ListTransition>
</template>

<script>
export default {
  name: 'PluginList',
};
</script>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { usePluginStore } from 'stores';
import Button from './BaseButton.vue';
import ListTransition from './ListTransition.vue';
import PluginItem from './PluginItem.vue';

const pluginStore = usePluginStore();

const plugins = ref(pluginStore.list);
const types = ref(pluginStore.types);
const filter = ref('all');
const layout = ref('list');
const showChart = ref(false);

const count = computed(() => plugins.value.length);
const isCardView = computed(() => layout.value === 'card');

const changeType = () => {
  plugins.value = Object.values(pluginStore.list).filter(
    (item) =>
      !Object.keys(types.value).includes(filter.value) ||
      Object.keys(item.types).includes(types.value[filter.value])
  );

  localStorage.setItem('lpAddonListFilter', filter.value);
};

const changeView = () => localStorage.setItem('lpAddonListLayout', layout.value);

onMounted(() => {
  filter.value = localStorage.getItem('lpAddonListFilter') || 'all';
  layout.value = localStorage.getItem('lpAddonListLayout') || 'list';
});

watch(filter, changeType);
watch(layout, changeView);
</script>

<style scoped>
#filter {
  margin-left: 4px;
  cursor: pointer;
}
#addon_list {
  .windowbg {
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);

    &:only-child {
      grid-column: 1 / -1;
    }
  }
}
</style>