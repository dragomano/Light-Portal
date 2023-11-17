import { createApp } from 'vue';
import { createPinia } from 'pinia';
import { createI18n } from 'vue-i18n';
import { VueShowdownPlugin, showdown } from 'vue-showdown';
import '@github/markdown-toolbar-element';
import CommentList from '../../LightPortal/components/CommentList.vue';

const app = createApp(CommentList);

app.use(createPinia());

class Pluralization {
  slavianRule(choice, choicesLength) {
    if (choice === 0) {
      return 0;
    }

    const teen = choice > 10 && choice < 20;
    const endsWithOne = choice % 10 === 1;

    if (!teen && endsWithOne) {
      return 1;
    }

    if (!teen && choice % 10 >= 2 && choice % 10 <= 4) {
      return 2;
    }

    return choicesLength < 4 ? 2 : 3;
  }

  rules() {
    return {
      ru: this.slavianRule,
      uk: this.slavianRule,
    };
  }
}

const plurals = new Pluralization();

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
