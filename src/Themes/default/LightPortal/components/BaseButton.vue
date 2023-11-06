<template>
  <component :is="button.view" :class="button.class" :role="button.role">
    <span v-html="preparedIcon"></span>
    <slot></slot>
  </component>
</template>

<script>
export default {
  name: 'BaseButton',
};
</script>

<script setup>
import { computed } from 'vue';
import { useIconStore } from 'stores';

const iconStore = useIconStore();

const props = defineProps({
  view: {
    type: String,
    default: 'button',
  },
  icon: {
    type: String,
    default: '',
  },
});

const button = computed(() =>
  props.view === 'button'
    ? {
        view: 'button',
        class: 'button',
        role: undefined,
      }
    : {
        view: 'span',
        class: undefined,
        role: 'button',
      }
);

const preparedIcon = computed(() => iconStore[props.icon] ?? '');
</script>

<style scoped>
[role='button'] {
  cursor: pointer;
}
</style>
