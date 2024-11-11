---
title: أمثلة الموقع
description: معلومات عن المواقع التي تستخدم الإضاءة المدخلة
layout: page
subtitle: المواقع التي تستخدم Light Portal
lead: إذا كنت ترغب في إضافة موقعك إلى هذه القائمة، فقط أرسل لي رسالة عبر <em>Admin -> Portal -> إعدادات-> ردود الفعل</em> في المنتدى الخاص بك.
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
