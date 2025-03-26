import { mount } from 'svelte'
import './../i18n.js'
import MemoryGame from '../../components/apps/Memory/App.svelte'

mount(MemoryGame, {
  target: document.querySelector('.memory_game'),
})
