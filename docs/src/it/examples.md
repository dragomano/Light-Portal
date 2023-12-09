---
title: Esempi di siti
description: Informazioni sui siti che utilizzano Light Portal
layout: page
subtitle: Siti che utilizzano Light Portal
lead: Se vuoi aggiungere il tuo sito a questo elenco, inviami un messaggio tramite l'area <em>Amministra -> Portale -> Impostazioni -> Suggerimenti</em> dal tuo forum.
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
    <template #title></template>
    <template #lead></template>
  </VPTeamPageTitle>
  <ExampleSites :sites="sites" /></VPTeamPage>
