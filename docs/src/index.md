---
# https://vitepress.dev/reference/default-theme-home-page
layout: home

hero:
  name: 'Light Portal'
  tagline: Fast, nice, modern
  image:
    src: logo.png
    alt: Logotype
  actions:
    - theme: brand
      text: Installation
      link: ./getting-started/installation
    - theme: alt
      text: Configuration
      link: ./getting-started/configuration
    - theme: brand
      text: Portal Hooks
      link: ./plugins/all-hooks
    - theme: alt
      text: How to create own frontpage layout
      link: ./how-to/create-layout

features:
  - icon: 🧊
    title: Blocks
    details: A piece of the portal that displays arbitrary content within the aside element. Placed on one of the six panels.
    link: ./blocks/manage
  - icon: 📰
    title: Pages
    details: A piece of the portal containing arbitrary content. Displayed as a separate part of the forum, with an individual URL.
    link: ./pages/manage
  - icon: 🧩
    title: Plugins
    details: It is an independent portal element that adds or modifies some functionality.
    link: ./plugins/manage
---
