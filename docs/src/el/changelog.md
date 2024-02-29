---
title: Καταγραφή αλλαγών
description: Πληροφορίες για τις εκδόσεις του Light Portal
layout: page
releases: Εκδόσεις
---

<script setup>
import { ReleaseTimeline, DefaultOptions as options } from "release-timeline";
import "release-timeline/dist/style.css";
import "release-timeline/dist/vitepress.css";
//import "release-timeline/dist/animated-background.css";
import { useData } from "vitepress";

const { frontmatter } = useData();

options.title = `${frontmatter.value.releases}`
options.github.owner = "dragomano";
options.github.repo = "Light-Portal";
options.display.release.name = false
options.display.release.defaultOpenTab = 'desc'
</script>

<ReleaseTimeline :options="options" />
