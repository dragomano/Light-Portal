import { createApp } from 'vue';
import { createPinia } from 'pinia';
import { createI18n } from 'vue-i18n';
import PluginList from '../../LightPortal/components/PluginList.vue';

const app = createApp(PluginList);

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

app.directive('focus', {
  mounted(el) {
    el.focus();
  },
});

app.mount('#vue_plugins');
