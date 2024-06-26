import { CommentManager, ObjectHelper } from './comment_helpers.js';

const { defineStore } = window.Pinia;

const useAppStore = defineStore('app', {
  state: () => ({
    baseUrl: smf_scripturl,
    loading: ajax_notification_text,
  }),
});

const useUserStore = defineStore('user', {
  state: () => vueGlobals.user,
});

const useContextStore = defineStore('context', {
  state: () => vueGlobals.context,
});

const useSettingStore = defineStore('settings', {
  state: () => vueGlobals.settings,
});

const useIconStore = defineStore('icons', {
  state: () => vueGlobals.icons,
});

const modules = {
  '@scripts/base_stores.js': {
    useContextStore,
    useIconStore,
  },
  '@scripts/comment_stores.js': {
    useAppStore,
    useUserStore,
    useSettingStore,
  },
  '@scripts/comment_helpers.js': {
    CommentManager,
    ObjectHelper,
  },
};

const app = new VueAdapter();

app.mount('CommentList', '#vue_comments', modules);
