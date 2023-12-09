<script setup>
import { computed } from 'vue';
import ExampleSitesItem from './ExampleSitesItem.vue';

const props = defineProps({
  sites: {
    type: Array,
    required: true,
  },
});

const classes = computed(() => ['medium', `count-${props.sites.length}`]);
</script>

<template>
  <div class="VPTeamMembers" :class="classes">
    <div class="container">
      <div v-for="site in sites" :key="site.link" class="item">
        <ExampleSitesItem :site="site" />
      </div>
    </div>
  </div>
</template>

<style scoped>
.VPTeamMembers.medium .container {
  grid-template-columns: repeat(auto-fit, minmax(256px, 1fr));
}

@media (min-width: 375px) {
  .VPTeamMembers.medium .container {
    grid-template-columns: repeat(auto-fit, minmax(288px, 1fr));
  }
}

.VPTeamMembers.medium.count-1 .container {
  max-width: 368px;
}
.VPTeamMembers.medium.count-2 .container {
  max-width: calc(368px * 2 + 24px);
}

.container {
  display: grid;
  gap: 24px;
  margin: 0 auto;
  max-width: 1152px;
}
</style>
