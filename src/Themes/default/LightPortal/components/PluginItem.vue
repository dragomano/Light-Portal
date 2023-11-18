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
          <p v-if="item.author">
            {{ item.author }}{{ item.link && ` |
            <a class="bbc_link" href="${item.link}" target="_blank" rel="noopener">${item.link}</a>`
            }}
          </p>
        </div>
      </div>

      <div class="floatright">
        <Button
          v-if="item.settings.length"
          view="span"
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
            v-html="iconStore.donate"
          ></a>
          <a
            v-else
            :href="pluginStore.download[item.name].link"
            rel="noopener"
            target="_blank"
            v-html="iconStore.download"
          ></a>
        </template>
        <Button
          v-if="!item.special && Object.keys(item.types)[0] !== $t('not_applicable')"
          view="span"
          :icon="`toggle-${item.status}`"
          :data-toggle="item.status"
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

<script>
export default {
  name: 'PluginItem',
};
</script>

<script setup>
import { ref, computed } from 'vue';
import { useContextStore, useIconStore } from '../../scripts/light_portal/dev/base_stores.js';
import { useAppStore, usePluginStore } from '../../scripts/light_portal/dev/plugin_stores.js';
import Button from './BaseButton.vue';
import PluginOptionList from './PluginOptionList.vue';

const appStore = useAppStore();
const pluginStore = usePluginStore();
const contextStore = useContextStore();
const iconStore = useIconStore();

const props = defineProps({
  item: {
    type: Object,
    required: true,
  },
});

const show = ref(false);

const specialDesc = computed(
  () =>
    pluginStore[props.item.special === 'can_donate' ? 'donate' : 'download'][props.item.name]
      ?.languages[contextStore.user.language ?? 'english']
);

const settingsId = computed(() => props.item.snake_name + '_' + appStore.sessionId);

const index = computed(() =>
  Object.values(pluginStore.list).findIndex((plugin) => plugin.snake_name === props.item.snake_name)
);

const toggle = async () => {
  const { data } = await axios.post(appStore.baseUrl + '?action=admin;area=lp_plugins;toggle', {
    plugin: index.value,
    status: props.item.status,
  });

  if (data.success) props.item.status = props.item.status === 'on' ? 'off' : 'on';
};
</script>
