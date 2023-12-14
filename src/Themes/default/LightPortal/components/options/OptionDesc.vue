<template>
  <div class="roundframe" v-html="$t(`lp_${id}`)"></div>
</template>

<script>
export default {
  name: 'OptionDesc',
};
</script>

<script setup>
defineProps({
  id: {
    type: String,
    required: true,
  },
});
</script>
