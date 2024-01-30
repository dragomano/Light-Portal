<template>
  <div>
    <label v-if="showLabel" :for="id">
      {{ $t(`lp_${id}`) }}
      <span v-if="postfix && isType !== 'number'" class="postfix">({{ postfix }})</span>
    </label>
    <keep-alive>
      <component :is="dynamicComponentProps.is" v-bind="dynamicComponentProps"></component>
    </keep-alive>
    <div v-if="subtext" class="roundframe" v-html="subtext"></div>
  </div>
</template>

<script>
import CallbackOption from './options/CallbackOption.vue';
import CheckOption from './options/CheckOption.vue';
import ColorOption from './options/ColorOption.vue';
import DescOption from './options/DescOption.vue';
import LargeTextOption from './options/LargeTextOption.vue';
import MultiSelectOption from './options/MultiSelectOption.vue';
import NumberOption from './options/NumberOption.vue';
import RangeOption from './options/RangeOption.vue';
import SelectOption from './options/SelectOption.vue';
import TextOption from './options/TextOption.vue';
import TitleOption from './options/TitleOption.vue';
import UrlOption from './options/UrlOption.vue';

export default {
  name: 'PluginOptionItem',
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
const subtext = computed(() => props.option.subtext);

const isType = computed(() => (['float', 'int'].includes(type.value) ? 'number' : type.value));

const dynamicComponentProps = computed(() => {
  const typeMap = {
    callback: { is: CallbackOption, option: props.option },
    check: {
      is: CheckOption,
      id: id.value,
      name: name.value,
      value: value.value,
    },
    color: {
      is: ColorOption,
      id: id.value,
      name: name.value,
      value: value.value,
    },
    desc: { is: DescOption, id: id.value },
    large_text: {
      is: LargeTextOption,
      id: id.value,
      name: name.value,
      value: value.value,
    },
    multiselect: {
      is: MultiSelectOption,
      id: id.value,
      name: name.value,
      value: value.value,
      option: props.option,
    },
    number: {
      is: NumberOption,
      id: id.value,
      name: name.value,
      value: value.value,
      option: props.option,
    },
    range: {
      is: RangeOption,
      id: id.value,
      name: name.value,
      value: value.value,
      option: props.option,
    },
    select: {
      is: SelectOption,
      id: id.value,
      name: name.value,
      value: value.value,
      option: props.option,
    },
    text: {
      is: TextOption,
      id: id.value,
      name: name.value,
      value: value.value,
      option: props.option,
    },
    title: { is: TitleOption, id: id.value },
    url: {
      is: UrlOption,
      id: id.value,
      name: name.value,
      value: value.value,
      option: props.option,
    },
  };

  return typeMap[isType.value] || false;
});
</script>
