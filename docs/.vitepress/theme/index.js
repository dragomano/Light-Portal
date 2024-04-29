import DefaultTheme from 'vitepress/theme';
import './custom.css';
import giscusTalk from 'vitepress-plugin-comment-with-giscus';
import { useData, useRoute } from 'vitepress';
import { h, toRefs } from 'vue';
import {
  NolebaseEnhancedReadabilitiesMenu,
  NolebaseEnhancedReadabilitiesScreenMenu,
} from '@nolebase/vitepress-plugin-enhanced-readabilities/client';
import '@nolebase/vitepress-plugin-enhanced-readabilities/client/style.css';
import { InjectionKey } from '@nolebase/vitepress-plugin-enhanced-readabilities/client';
import { locales } from './locales';

export default {
  ...DefaultTheme,
  Layout: () => {
    return h(DefaultTheme.Layout, null, {
      // A enhanced readabilities menu for wider screens
      'nav-bar-content-after': () => h(NolebaseEnhancedReadabilitiesMenu),
      // A enhanced readabilities menu for narrower screens (usually smaller than iPad Mini)
      'nav-screen-content-after': () => h(NolebaseEnhancedReadabilitiesScreenMenu),
    });
  },
  enhanceApp(ctx) {
    ctx.app.provide(InjectionKey, {
      locales,
    });

    DefaultTheme.enhanceApp(ctx);
  },
  setup() {
    const { frontmatter } = toRefs(useData());
    const route = useRoute();

    // Obtain configuration from: https://giscus.app/
    giscusTalk(
      {
        repo: 'dragomano/Light-Portal',
        repoId: 'MDEwOlJlcG9zaXRvcnkyMzA1OTgxOTE=',
        category: 'Q&A',
        categoryId: 'DIC_kwDODb6mL84CN-iX',
        mapping: 'pathname',
        inputPosition: 'bottom',
        lang: 'en',
        locales: {
          en: 'en',
          ru: 'ru',
          it: 'it',
          el: 'gr',
        },
        homePageShowComment: false,
        lightTheme: 'light_tritanopia',
        darkTheme: 'transparent_dark',
      },
      {
        frontmatter,
        route,
      },
      true
    );
  },
};
