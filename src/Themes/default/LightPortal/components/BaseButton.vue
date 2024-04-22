<template>
  <component :is="button.tag" :class="button.class" :role="button.role">
    <span v-html="preparedIcon"></span>
    <slot></slot>
  </component>
</template>

<script setup>
import { computed } from 'vue';
import { useIconStore } from '@scripts/base_stores.js';

const iconStore = useIconStore();

const props = defineProps({
  tag: {
    type: String,
    default: 'button',
  },
  icon: {
    type: String,
    default: '',
  },
});

const button = computed(() =>
  props.tag === 'button'
    ? {
        tag: 'button',
        class: 'button',
        role: undefined,
      }
    : {
        tag: 'span',
        class: undefined,
        role: 'button',
      }
);

const preparedIcon = computed(() => iconStore[props.icon] ?? '');
</script>
