<template>
  <div class="roundframe" v-html="$t(`lp_${id}`)"></div>
</template>

<script setup>
defineProps({
  id: {
    type: String,
    required: true,
  },
});
</script>
