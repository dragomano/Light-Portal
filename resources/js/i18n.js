import { register, init, locale } from 'svelte-i18n';

const { context, txt } = portalJson;

register(context.locale, () => Promise.resolve(txt));

init({
  fallbackLocale: context.locale,
  initialLocale: context.locale,
});

locale.set(context.locale)
