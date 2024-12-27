import { defineStore } from 'pinia';

export const useContextStore = defineStore('context', {
  state: () => portalJson.context,
});

export const useIconStore = defineStore('icons', {
  state: () => portalJson.icons,
});
