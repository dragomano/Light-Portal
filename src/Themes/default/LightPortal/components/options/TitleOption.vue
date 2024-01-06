<template>
  <div class="sub_bar">
    <h6 class="subbg">{{ $t(`lp_${id}`) }}</h6>
  </div>
</template>

<script>
export default {
  name: 'TitleOption',
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
