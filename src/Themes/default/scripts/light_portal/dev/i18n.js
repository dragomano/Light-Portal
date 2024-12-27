import { createI18n } from 'vue-i18n';
import Plurals from './plurals';

const plurals = new Plurals();

const { context, txt } = portalJson;

const i18n = createI18n({
  locale: context.locale,
  pluralizationRules: plurals.rules(),
  messages: {
    [context.locale]: txt,
  },
});

export default i18n;
