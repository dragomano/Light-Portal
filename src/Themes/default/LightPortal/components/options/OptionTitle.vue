<template>
  <div class="sub_bar">
    <h6 class="subbg">{{ $t(`lp_${id}`) }}</h6>
  </div>
</template>

<script>
export default {
  name: 'OptionTitle',
};
</script>

<script setup>
const props = defineProps({
  id: {
    type: String,
    required: true,
  },
});
</script>
