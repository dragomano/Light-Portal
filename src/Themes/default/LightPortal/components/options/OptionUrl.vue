<template>
  <div>
    <input
      type="url"
      :id="id"
      :name="name"
      :value="value"
      :placeholder="placeholder"
      :required="required"
    />
  </div>
</template>

<script>
export default {
  name: 'OptionUrl',
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

const placeholder = computed(() => props.option.placeholder);
const required = computed(() => props.option.required);
</script>
