import { createApp } from 'vue';
import { createPinia } from 'pinia';
import { VueShowdownPlugin, showdown } from 'vue-showdown';
import '@github/markdown-toolbar-element';
import CommentList from '../../LightPortal/components/CommentList.vue';
import i18n from './dev/i18n';

const app = createApp(CommentList);

app.use(createPinia());

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
