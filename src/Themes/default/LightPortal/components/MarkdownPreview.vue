<template>
  <fieldset>
    <VueShowdown :markdown="content" :extensions="['bindings']" />
  </fieldset>
</template>

<script setup>
defineProps({
  content: {
    type: String,
    default: '',
  },
});
</script>
