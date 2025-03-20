import DefaultTheme from 'vitepress/theme';
import './custom.css';
import 'markdown-it-steps/style.css';
import giscusTalk from 'vitepress-plugin-comment-with-giscus';
import { useData, useRoute } from 'vitepress';
import { toRefs } from 'vue';

export default {
  ...DefaultTheme,
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
          ar: 'ar',
          de: 'de',
          es: 'es',
          fr: 'fr',
          nl: 'nl',
          pl: 'pl',
          tr: 'tr',
          uk: 'uk',
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
