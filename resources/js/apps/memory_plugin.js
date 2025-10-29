import { mount } from 'svelte'
import './../i18n.js'
import MemoryGame from '../../components/apps/Memory/App.svelte'

/** @type {NodeListOf<HTMLElement>} */
const memoryBlocks = document.querySelectorAll('.memory_game');

memoryBlocks.forEach((element) => {
  if (!element.dataset.mounted) {
    mount(MemoryGame, {
      target: element
    });

    element.dataset.mounted = 'true';
  }
});
