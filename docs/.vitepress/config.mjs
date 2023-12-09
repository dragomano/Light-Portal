import { defineConfig } from 'vitepress';
import { fileURLToPath, URL } from 'node:url';
import { generateSidebar } from 'vitepress-sidebar';
import locales from './locales';

const sidebar = {
  documentRootPath: 'src',
  useTitleFromFileHeading: true,
  useTitleFromFrontmatter: true,
  useFolderTitleFromIndexFile: true,
  sortMenusByFrontmatterOrder: true,
  excludeFiles: ['examples.md', 'team.md'],
  manualSortFileNameByPriority: [
    'intro.md',
    'getting-started',
    'blocks',
    'pages',
    'plugins',
    'how-to',
  ],
};

const languages = Object.keys(locales).filter((locale) => locale !== 'root');

languages.forEach((lang) => {
  locales[lang].themeConfig = {
    ...locales[lang].themeConfig,
    sidebar: generateSidebar([
      {
        ...sidebar,
        documentRootPath: `src/${lang}`,
        resolvePath: `/${lang}/`,
      },
    ]),
  };
});

export default defineConfig({
  head: [
    ['link', { rel: 'icon', href: '/Light-Portal/favicon.ico' }],
    [
      'script',
      {},
      `(function (m, e, t, r, i, k, a) {
        m[i] =
          m[i] ||
          function () {
            (m[i].a = m[i].a || []).push(arguments);
          };
        m[i].l = 1 * new Date();
        for (var j = 0; j < document.scripts.length; j++) {
          if (document.scripts[j].src === r) {
            return;
          }
        }
        (k = e.createElement(t)),
          (a = e.getElementsByTagName(t)[0]),
          (k.async = 1),
          (k.src = r),
          a.parentNode.insertBefore(k, a);
      })(window, document, 'script', 'https://cdn.jsdelivr.net/npm/yandex-metrica-watch@1/tag.js', 'ym');
      ym(89104842, 'init', {
        clickmap: true,
        trackLinks: true,
        accurateTrackBounce: true,
        webvisor: true,
        trackHash: true,
      });`,
    ],
  ],
  base: '/Light-Portal/',
  srcDir: './src',
  cleanUrls: true,
  themeConfig: {
    externalLinkIcon: true,
    search: {
      provider: 'local',
    },
    sidebar: generateSidebar({
      ...sidebar,
      excludeFolders: languages,
    }),
    socialLinks: [{ icon: 'github', link: 'https://github.com/dragomano/Light-Portal' }],
  },
  locales,
  sitemap: {
    hostname: 'https://dragomano.github.io/Light-Portal/',
  },
  vite: {
    resolve: {
      alias: [
        {
          find: /^.*\/ExampleSites\.vue$/,
          replacement: fileURLToPath(new URL('./components/ExampleSites.vue', import.meta.url)),
        },
      ],
    },
  },
});
