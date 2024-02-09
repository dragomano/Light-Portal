<template>
  <div>
    <textarea :id="id" :name="name">{{ value }}</textarea>
  </div>
</template>

<script setup>
defineProps({
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
