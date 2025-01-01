import { mount } from 'svelte'
import './i18n.js'
import PluginList from '../components/plugins/PluginList.svelte'

mount(PluginList, {
  target: document.getElementById('svelte_plugins'),
})
