import { createApp } from 'vue';
import { createPinia } from 'pinia';
import { createI18n } from 'vue-i18n';
import PluginList from '../../LightPortal/components/PluginList.vue';
import Plurals from './dev/plurals';

const app = createApp(PluginList);

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

app.directive('focus', {
  mounted(el) {
    el.focus();
  },
});

app.mount('#vue_plugins');
