---
title: Exemples de site
description: Informations sur les sites utilisant le plugin Light Portal
layout: page
subtitle: Sites utilisant le Portail Lumière
lead: Si vous souhaitez ajouter votre site à cette liste, il suffit de m'envoyer un message via <em>Admin -> Portail -> Paramètres -> Réaction</em> dans votre forum.
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
