<template>
  <div class="windowbg">
    <div class="features" :data-id="index">
      <div class="floatleft">
        <h4>
          {{ item.name }}
          <strong
            v-for="(label, type) in item.types"
            class="new_posts"
            :class="label"
            :key="type"
            v-text="type"
          ></strong>
        </h4>
        <div>
          <p v-if="item.special" v-html="specialDesc"></p>
          <p v-else v-html="item.desc"></p>
          <p v-if="item.author">{{ item.author }}<span v-html="link"></span></p>
        </div>
      </div>

      <div class="floatright">
        <Button
          v-if="item.settings.length"
          tag="span"
          icon="gear"
          :class="show ? 'fa-spin' : undefined"
          :data-id="settingsId"
          @click="show = !show"
        />
        <template v-if="item.special">
          <a
            v-if="item.special === 'can_donate'"
            :href="pluginStore.donate[item.name].link"
            rel="noopener"
            target="_blank"
            v-html="donateIcon"
          ></a>
          <a
            v-else
            :href="pluginStore.download[item.name].link"
            rel="noopener"
            target="_blank"
            v-html="downloadIcon"
          ></a>
        </template>
        <Toggle
          v-if="!item.special && Object.keys(item.types)[0] !== $t('not_applicable')"
          v-model="item.status"
          true-value="on"
          false-value="off"
          @click="toggle"
        />
      </div>

      <PluginOptionList
        v-if="item.settings.length"
        :item="item"
        :show="show"
        @hide="show = false"
      />
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import Toggle from '@vueform/toggle';
import { useContextStore, useIconStore } from '@scripts/base_stores.js';
import { useAppStore, usePluginStore } from '@scripts/plugin_stores.js';
import Button from './BaseButton.vue';
import PluginOptionList from './PluginOptionList.vue';

const appStore = useAppStore();
const pluginStore = usePluginStore();
const contextStore = useContextStore();
const { donate: donateIcon, download: downloadIcon } = useIconStore();

const props = defineProps({
  item: {
    type: {
      name: String,
      settings: Array,
      snake_name: String,
      special: String,
      status: String,
      types: Object,
    },
    required: true,
  },
});

const show = ref(false);
const status = ref(props.item.status);

const specialDesc = computed(
  () =>
    pluginStore[props.item.special === 'can_donate' ? 'donate' : 'download'][props.item.name]
      ?.languages[contextStore.lang ?? 'english']
);

const settingsId = computed(() => props.item.snake_name + '_' + appStore.sessionId);

const index = computed(() =>
  Object.values(pluginStore.list).findIndex((plugin) => plugin.snake_name === props.item.snake_name)
);

const link = computed(() =>
  props.item.link
    ? ` | <a class="bbc_link" href="${props.item.link}" target="_blank" rel="noopener">${props.item.link}</a>`
    : ''
);

const toggle = async () => {
  const data = await axios.post(appStore.baseUrl + '?action=admin;area=lp_plugins;toggle', {
    plugin: index.value,
    status: status.value,
  });

  if (data.success) props.item.status = status.value === 'on' ? 'off' : 'on';
};
</script>
