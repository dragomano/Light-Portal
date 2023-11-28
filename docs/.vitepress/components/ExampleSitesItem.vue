<script setup>
import { withBase } from 'vitepress';

defineProps({
  site: {},
});
</script>

<template>
  <article class="VPTeamMembersItem medium">
    <a class="profile" :href="site.link">
      <figure class="avatar">
        <img class="avatar-img" :src="withBase(site.image)" :alt="site.title" />
      </figure>
      <div class="data">
        <p v-if="site.title" class="affiliation">
          <span class="title">
            {{ site.title }}
          </span>
        </p>
      </div>
    </a>
  </article>
</template>

<style scoped>
.VPTeamMembersItem {
  display: flex;
  flex-direction: column;
  gap: 2px;
  border-radius: 12px;
  width: 100%;
  height: 100%;
  overflow: hidden;
}

.VPTeamMembersItem.medium .profile {
  padding: 48px 32px;
}

.VPTeamMembersItem.medium .data {
  padding-top: 24px;
  text-align: center;
}

.VPTeamMembersItem.medium .affiliation {
  padding-top: 4px;
  font-size: 16px;
}

.profile {
  flex-grow: 1;
  background-color: var(--vp-c-bg-soft);
}

.data {
  text-align: center;
}

.avatar {
  position: relative;
  flex-shrink: 0;
  margin: 0 auto;
  box-shadow: var(--vp-shadow-3);
}

.avatar-img {
  object-fit: cover;
}

.affiliation {
  margin: 0;
  font-weight: 500;
  color: var(--vp-c-text-2);
}

.title {
  color: var(--vp-c-text-2);
  transition: color 0.25s;
}

.title:hover {
  color: var(--vp-c-brand-1);
}
</style>
