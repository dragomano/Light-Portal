<template>
  <span v-html="extra"></span>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  option: {
    type: Object,
    required: true,
  },
});

const extra = computed(() => props.option[2]);
</script>
