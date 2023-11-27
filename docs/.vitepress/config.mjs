import { defineConfig } from 'vitepress';
import { generateSidebar } from 'vitepress-sidebar';
import locales from './locales';

const sidebar = {
  documentRootPath: 'src',
  useTitleFromFileHeading: true,
  useTitleFromFrontmatter: true,
  useFolderTitleFromIndexFile: true,
  sortMenusByFrontmatterOrder: true,
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
  base: '/Light-Portal/',
  srcDir: './src',
  cleanUrls: true,
  themeConfig: {
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
});
