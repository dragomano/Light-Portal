import { createI18n } from 'vue-i18n';
import Plurals from './plurals';

const plurals = new Plurals();

const i18n = createI18n({
  locale: vueGlobals.context.locale,
  pluralizationRules: plurals.rules(),
  messages: {
    [vueGlobals.context.locale]: vueGlobals.txt,
  },
});

export default i18n;
