import { CommentManager, ObjectHelper } from './comment_helpers.js';

const { defineStore } = window.Pinia;

const { user, context, settings, icons } = portalJson;

const useAppStore = defineStore('app', {
  state: () => ({
    baseUrl: smf_scripturl,
    loading: ajax_notification_text,
  }),
});

const useUserStore = defineStore('user', {
  state: () => user,
});

const useContextStore = defineStore('context', {
  state: () => context,
});

const useSettingStore = defineStore('settings', {
  state: () => settings,
});

const useIconStore = defineStore('icons', {
  state: () => icons,
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
