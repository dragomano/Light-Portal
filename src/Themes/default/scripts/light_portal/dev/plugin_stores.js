import { defineStore } from 'pinia';

export const useAppStore = defineStore('app', {
  state: () => ({
    baseUrl: smf_scripturl,
    sessionId: smf_session_id,
    sessionVar: smf_session_var,
  }),
});

export const usePluginStore = defineStore('plugins', {
  state: () => portalJson.plugins,
});
