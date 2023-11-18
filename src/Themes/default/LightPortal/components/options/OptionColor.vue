<template>
  <div>
    <input type="color" :id="id" :name="name" :value="value" />
  </div>
</template>

<script>
export default {
  name: 'OptionColor',
};
</script>

<script setup>
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
});
</script>
