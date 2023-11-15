<template>
  <fieldset>
    <VueShowdown :markdown="content" :extensions="['bindings']" />
  </fieldset>
</template>

<script>
export default {
  name: 'MarkdownPreview',
};
</script>

<script setup>
defineProps({
  content: {
    type: String,
    default: '',
  },
});
</script>
