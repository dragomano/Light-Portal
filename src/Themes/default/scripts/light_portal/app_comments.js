import { createApp } from 'vue';
import { createPinia } from 'pinia';
import { createI18n } from 'vue-i18n';
import { VueShowdownPlugin, showdown } from 'vue-showdown';
import '@github/markdown-toolbar-element';
import CommentList from '../../LightPortal/components/CommentList.vue';
import Plurals from './dev/plurals';

const app = createApp(CommentList);

app.use(createPinia());

const plurals = new Plurals();

const i18n = createI18n({
  locale: vueGlobals.context.locale,
  pluralizationRules: plurals.rules(),
  messages: {
    [vueGlobals.context.locale]: vueGlobals.txt,
  },
});

app.use(i18n);

const classMap = {
  blockquote: 'bbc_standard_quote',
  code: 'bbc_code',
  h1: 'titlebg',
  h2: 'titlebg',
  h3: 'titlebg',
  image: 'bbc_img',
  a: 'bbc_link',
  ul: 'bbc_list',
  table: 'table_grid',
  tr: 'windowbg',
};

const bindings = Object.keys(classMap).map((key) => ({
  type: 'output',
  regex: new RegExp(`<${key}(.*)>`, 'g'),
  replace: `<${key} class="${classMap[key]}" $1>`,
}));

showdown.extension('bindings', bindings);

app.use(VueShowdownPlugin, {
  flavor: 'github',
  options: {
    emoji: true,
    encodeEmails: true,
    openLinksInNewWindow: true,
  },
});

app.mount('#vue_comments');
