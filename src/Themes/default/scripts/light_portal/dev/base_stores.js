import { defineStore } from 'pinia';

export const useContextStore = defineStore('context', {
  state: () => vueGlobals.context,
});

export const useIconStore = defineStore('icons', {
  state: () => vueGlobals.icons,
});
