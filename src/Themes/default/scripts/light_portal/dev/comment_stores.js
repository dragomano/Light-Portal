import { defineStore } from 'pinia';

export const useAppStore = defineStore('app', {
  state: () => ({
    baseUrl: smf_scripturl,
    loading: ajax_notification_text,
  }),
});

export const useUserStore = defineStore('user', {
  state: () => portalJson.user,
});

export const useSettingStore = defineStore('settings', {
  state: () => portalJson.settings,
});
