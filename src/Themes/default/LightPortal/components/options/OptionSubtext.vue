<template>
  <div class="roundframe" v-html="subtext"></div>
</template>

<script>
export default {
  name: 'OptionSubtext',
};
</script>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  option: {
    type: Object,
    required: true,
  },
});

const subtext = computed(() => props.option.subtext);
</script>
