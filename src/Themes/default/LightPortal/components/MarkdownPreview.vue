<template>
  <fieldset class="comment_content bg odd">
    <VueShowdown :markdown="content" />
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
