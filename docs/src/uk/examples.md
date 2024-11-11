---
title: Приклади сайту
description: Інформація про сайти, що використовують Light Portal
layout: page
subtitle: Сайти, що використовують Light Portal
lead: Якщо ви хочете додати свій сайт до цього списку, просто надішліть мені повідомлення через розділ <em>Адміністратор -> Портал -> Налаштування -> Зворотній зв'язок</em> на вашому форумі.
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
