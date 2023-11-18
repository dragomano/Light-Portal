<template>
  <div>
    <input
      type="text"
      :id="id"
      :name="name"
      :value="value"
      :pattern="pattern"
      :required="required"
    />
  </div>
</template>

<script>
export default {
  name: 'OptionText',
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

const pattern = computed(() => props.option.pattern);
const required = computed(() => props.option.required);
</script>
