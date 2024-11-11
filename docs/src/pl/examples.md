---
title: Przykłady witryny
description: Informacje o witrynach korzystających z wtyczki Light Portal
layout: page
subtitle: Strony korzystające z Light Portal
lead: Jeśli chcesz dodać swoją witrynę do tej listy, po prostu wyślij mi wiadomość za pośrednictwem obszaru <em>Administrator -> Portal -> Ustawienia -> Opinie</em> na swoim forum.
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
]
</script>

<VPTeamPage>
  <VPTeamPageTitle>
    <template #title></template>
    <template #lead></template>
  </VPTeamPageTitle>
  <ExampleSites :sites="sites" /></VPTeamPage>
