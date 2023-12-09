---
title: Команда
description: Информация о команде проекта Light Portal
layout: page
subtitle: Наша команда
role_creator: Автор
role_developer: Разработчик
role_translator: Переводчик
role_tester: Тестировщик
role_designer: Дизайнер
---

<script setup>
import {
  VPTeamPage,
  VPTeamPageTitle,
  VPTeamMembers
} from 'vitepress/theme'
import { useData } from "vitepress";

const { frontmatter } = useData();

const members = [
  {
    avatar: 'https://avatars.githubusercontent.com/u/229402?v=4',
    name: 'Bugo',
    title: `${frontmatter.value.role_creator} / ${frontmatter.value.role_developer} / ${frontmatter.value.role_translator}`,
  },
  {
    avatar: 'https://crowdin-static.downloads.crowdin.com/avatar/15819579/large/a8e3e03afa126e92b35748a56e806e8d_default.png',
    name: 'Panoulis64',
    title: `${frontmatter.value.role_translator} / ${frontmatter.value.role_tester}`,
  },
  {
    avatar: 'https://crowdin-static.downloads.crowdin.com/avatar/14671246/large/5de8c37d614a577459d5f577c78b7812.png',
    name: 'Darknico',
    title: `${frontmatter.value.role_translator}`,
  },
]
</script>

<VPTeamPage>
  <VPTeamPageTitle>
    <template #title></template>
  </VPTeamPageTitle>
  <VPTeamMembers
    :members="members"
  /></VPTeamPage>
