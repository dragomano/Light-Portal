---
title: Примеры сайтов
description: Информация о сайтах, использующих плагин Light Portal
layout: page
subtitle: Сайты, использующие Light Portal
lead: Если вы хотите добавить свой сайт в этот список, просто отправьте мне сообщение через раздел <em>Админка -> Портал -> Настройки -> Обратная связь</em> на вашем форуме.
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
