<template>
  <div>
    <input type="range" :id="id" :name="name" v-model="value" :min="min" :max="max" :step="step" />
    <span class="range_field" v-text="value"></span>
  </div>
</template>

<script>
export default {
  name: 'OptionRange',
};
</script>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  id: {
    type: String,
    required: true,
  },
  name: {
    type: String,
    required: true,
  },
  value: {
    type: String,
    default: '',
  },
  option: {
    type: Object,
    required: true,
  },
});

const min = computed(() => props.option.min ?? 0);
const max = computed(() => props.option.max ?? 100);
const step = computed(() => props.option.step ?? 1);
</script>
