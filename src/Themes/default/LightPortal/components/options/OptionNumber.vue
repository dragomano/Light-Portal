<template>
  <div>
    <input type="number" :id="id" :name="name" :value="value" :min="min" :max="max" :step="step" />
  </div>
</template>

<script>
export default {
  name: 'OptionNumber',
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
    required: true,
  },
  option: {
    type: Object,
    required: true,
  },
});

const min = computed(() => props.option.min ?? 0);
const max = computed(() => props.option.max);
const step = computed(() => props.option.step ?? (props.option[0] === 'int' ? 1 : 0.01));
</script>
