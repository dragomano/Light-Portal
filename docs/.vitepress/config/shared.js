import { defineConfig } from 'vitepress';
import { fileURLToPath, URL } from 'node:url';
import { withSidebar } from 'vitepress-sidebar';

const commonSidebarConfigs = {
  documentRootPath: 'src',
  useTitleFromFileHeading: true,
  useTitleFromFrontmatter: true,
  useFolderTitleFromIndexFile: true,
  sortMenusByFrontmatterOrder: true,
  excludePattern: ['changelog.md', 'examples.md'],
  manualSortFileNameByPriority: [
    'intro.md',
    'getting-started',
    'blocks',
    'pages',
    'plugins',
    'how-to',
  ],
};

const vitePressConfigs = defineConfig({
  title: 'Light Portal',
  base: '/Light-Portal/',
  srcDir: './src',
  rewrites: {
    'en/:rest*': ':rest*'
  },
  head: [
    ['link', { rel: 'icon', href: '/Light-Portal/favicon.ico' }],
    [
      'script',
      {},
      `(function (c, l, a, r, i, t, y) {
        c[a] =
          c[a] ||
          function () {
            (c[a].q = c[a].q || []).push(arguments);
          };
        t = l.createElement(r);
        t.async = 1;
        t.src = 'https://www.clarity.ms/tag/' + i;
        y = l.getElementsByTagName(r)[0];
        y.parentNode.insertBefore(t, y);
      })(window, document, 'clarity', 'script', 'ke5jb39203')`,
    ],
  ],
  lastUpdated: true,
  cleanUrls: true,
  markdown: {
    image: {
      lazyLoading: true,
    },
  },
  themeConfig: {
    logo: '/logo.png',
    siteTitle: 'Light Portal',
    externalLinkIcon: true,
    search: {
      provider: 'local',
    },
    socialLinks: [{ icon: 'github', link: 'https://github.com/dragomano/Light-Portal' }],
  },
  sitemap: {
    hostname: 'https://dragomano.github.io/Light-Portal/',
  },
  vite: {
    resolve: {
      alias: [
        {
          find: /^.*\/ExampleArea\.vue$/,
          replacement: fileURLToPath(new URL('./../components/ExampleArea.vue', import.meta.url)),
        },
      ],
    },
  },
})

const rootLocale = 'en'
const supportedLocales = [rootLocale, 'ru', 'el', 'it', 'ar', 'es', 'de', 'nl', 'pl', 'uk', 'fr', 'tr', 'sl'];

const vitePressSidebarConfigs = [
  ...supportedLocales.map((lang) => {
    return {
      ...commonSidebarConfigs,
      ...(rootLocale === lang ? {} : { basePath: `/${lang}/` }),
      documentRootPath: `/src/${lang}`,
      resolvePath: rootLocale === lang ? '/' : `/${lang}/`,
    };
  })
]

export const shared = defineConfig(withSidebar(vitePressConfigs, vitePressSidebarConfigs))
