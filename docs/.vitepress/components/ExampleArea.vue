<script setup>
import {
  VPTeamPage,
  VPTeamPageTitle
} from 'vitepress/theme'
import ExampleSites from './ExampleSites.vue'

const sites = [
  {
    image: '/example_1.png',
    title: 'Light Portal Showcase',
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
  {
    image: '/example_4.png',
    title: 'Alliance Multi Gaming',
    link: 'https://alliancemultigaming.com/forum/',
  }
]
</script>

<template>
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
</template>