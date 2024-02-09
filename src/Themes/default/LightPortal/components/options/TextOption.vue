<template>
  <div>
    <input
      type="text"
      :id="id"
      :name="name"
      :value="value"
      :pattern="pattern"
      :placeholder="placeholder"
      :required="required"
    />
  </div>
</template>

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

const pattern = computed(() => props.option.pattern);
const placeholder = computed(() => props.option.placeholder);
const required = computed(() => props.option.required);
</script>
