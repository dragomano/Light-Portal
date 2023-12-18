---
title: Παραδείγματα ιστοτόπων
description: Πληροφορίες σχετικά με τοποθεσίες που χρησιμοποιούν το πρόσθετο Light Portal
layout: page
subtitle: Ιστότοποι που χρησιμοποιούν την Light Portal
lead: Εάν θέλετε να προσθέσετε τον ιστότοπό σας σε αυτήν τη λίστα, απλώς στείλτε μου ένα μήνυμα μέσω της περιοχής <em>Διαχειριστής -> Πύλη -> Ρυθμίσεις -> Σχόλια</em> στο φόρουμ σας.
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
