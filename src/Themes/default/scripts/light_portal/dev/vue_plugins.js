const { defineStore } = window.Pinia;

const useAppStore = defineStore('app', {
  state: () => ({
    baseUrl: smf_scripturl,
    sessionId: smf_session_id,
    sessionVar: smf_session_var,
  }),
});

const usePluginStore = defineStore('plugins', {
  state: () => vueGlobals.plugins,
});

const useContextStore = defineStore('context', {
  state: () => vueGlobals.context,
});

const useIconStore = defineStore('icons', {
  state: () => vueGlobals.icons,
});

const modules = {
  '@vueform/multiselect': window.VueformMultiselect,
  '@vueform/toggle': window.VueformToggle,
  '@scripts/base_stores.js': {
    useContextStore,
    useIconStore,
  },
  '@scripts/plugin_stores.js': {
    useAppStore,
    usePluginStore,
  },
};

const app = new VueAdapter();

app.mount('PluginList', '#vue_plugins', modules);
