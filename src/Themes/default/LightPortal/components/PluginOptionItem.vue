<template>
  <div>
    <label v-if="showLabel" :for="id">
      {{ $t(`lp_${id}`) }}
      <span v-if="postfix" class="postfix">({{ postfix }})</span>
    </label>
    <keep-alive>
      <component :is="dynamicComponentProps.is" v-bind="dynamicComponentProps"></component>
    </keep-alive>
  </div>
</template>

<script>
import OptionCallback from './options/OptionCallback.vue';
import OptionCheck from './options/OptionCheck.vue';
import OptionColor from './options/OptionColor.vue';
import OptionDesc from './options/OptionDesc.vue';
import OptionLargeText from './options/OptionLargeText.vue';
import OptionMultiSelect from './options/OptionMultiSelect.vue';
import OptionNumber from './options/OptionNumber.vue';
import OptionSelect from './options/OptionSelect.vue';
import OptionSubtext from './options/OptionSubtext.vue';
import OptionText from './options/OptionText.vue';
import OptionTitle from './options/OptionTitle.vue';
import OptionUrl from './options/OptionUrl.vue';

export default {
  name: 'PluginOptionItem',
  components: {
    OptionCallback,
    OptionCheck,
    OptionColor,
    OptionDesc,
    OptionLargeText,
    OptionMultiSelect,
    OptionNumber,
    OptionSelect,
    OptionSubtext,
    OptionText,
    OptionTitle,
    OptionUrl,
  },
};
</script>

<script setup>
import { computed } from 'vue';
import { useContextStore } from '../../scripts/light_portal/dev/base_stores.js';

const contextStore = useContextStore();

const props = defineProps({
  option: {
    type: Object,
    required: true,
  },
  plugin: {
    type: String,
    required: true,
  },
});

const type = computed(() => props.option[0]);
const name = computed(() => props.option[1]);
const id = computed(() => `${props.plugin}.${name.value}`);
const value = computed(() => contextStore[`lp_${props.plugin}`]?.[name.value]);

const showLabel = computed(() => !['callback', 'title', 'desc', 'check'].includes(type.value));
const postfix = computed(() => props.option.postfix);

const isType = computed(() => (['float', 'int'].includes(type.value) ? 'number' : type.value));

const dynamicComponentProps = computed(() => {
  const typeMap = {
    callback: { is: 'OptionCallback', option: props.option },
    check: { is: 'OptionCheck', id: id.value, name: name.value, value: value.value },
    color: { is: 'OptionColor', id: id.value, name: name.value, value: value.value },
    desc: { is: 'OptionDesc', id: id.value },
    large_text: { is: 'OptionLargeText', id: id.value, name: name.value, value: value.value },
    multiselect: {
      is: 'OptionMultiSelect',
      id: id.value,
      name: name.value,
      value: value.value,
      option: props.option,
    },
    number: {
      is: 'OptionNumber',
      id: id.value,
      name: name.value,
      value: value.value,
      option: props.option,
    },
    select: {
      is: 'OptionSelect',
      id: id.value,
      name: name.value,
      value: value.value,
      option: props.option,
    },
    subtext: { is: 'OptionSubtext', option: props.option },
    text: {
      is: 'OptionText',
      id: id.value,
      name: name.value,
      value: value.value,
      option: props.option,
    },
    title: { is: 'OptionTitle', id: id.value },
    url: {
      is: 'OptionUrl',
      id: id.value,
      name: name.value,
      value: value.value,
      option: props.option,
    },
  };

  return typeMap[isType.value] || false;
});
</script>
