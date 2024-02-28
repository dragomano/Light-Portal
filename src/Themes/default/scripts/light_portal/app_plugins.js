import { createApp } from 'vue';
import { createPinia } from 'pinia';
import PluginList from '../../LightPortal/components/PluginList.vue';
import i18n from './dev/i18n';

const app = createApp(PluginList);

app.use(createPinia());

app.use(i18n);

app.directive('focus', {
  mounted(el) {
    el.focus();
  },
});

app.mount('#vue_plugins');
