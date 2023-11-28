---
title: Site examples
description: Information about sites using Light Portal plugin
layout: page
subtitle: Sites using the Light Portal
lead: If you want to add your site into this list, just send me a message via <em>Admin -> Portal -> Settings -> Feedback</em> area on your forum.
---

<script setup>
import {
  VPTeamPage,
  VPTeamPageTitle
} from 'vitepress/theme'
import ExampleSites from './ExampleSites.vue'

const sites = [
  {
    image: '/example_1.png',
    title: 'Light Portal Sandbox',
    link: 'https://demo.dragomano.ru',
  },
  {
    image: '/example_2.png',
    title: 'Απανταχού Τριγλιανοί Απόγονοι',
    link: 'https://www.triglianoi.gr'
  },
  {
    image: '/example_3.png',
    title: 'Italian SMF',
    link: 'https://www.italiansmf.net/forum/'
  },
]
</script>

<VPTeamPage>
  <VPTeamPageTitle>
    <template #title>
      {{ $frontmatter.subtitle }}
    </template>
    <template #lead>
      <span v-html="$frontmatter.lead"></span>
    </template>
  </VPTeamPageTitle>
  <ExampleSites :sites="sites" />
</VPTeamPage>
