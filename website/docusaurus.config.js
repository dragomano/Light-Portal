// @ts-check
const { themes } = require('prism-react-renderer');
const lightTheme = themes.oceanicNext;
const darkTheme = themes.dracula;

/** @type {import('@docusaurus/types').Config} */
const config = {
  title: 'Light Portal Docs',
  tagline: 'Be simplier',
  url: 'https://dragomano.github.io',
  baseUrl: '/Light-Portal/',
  onBrokenLinks: 'throw',
  onBrokenMarkdownLinks: 'warn',
  favicon: 'favicon.ico',
  organizationName: 'dragomano',
  projectName: 'Light-Portal',
  trailingSlash: false,

  presets: [
    [
      'classic',
      /** @type {import('@docusaurus/preset-classic').Options} */
      ({
        docs: {
          routeBasePath: '/',
          sidebarCollapsed: false,
          sidebarPath: require.resolve('./sidebars.js'),
          editUrl: 'https://github.com/dragomano/Light-Portal/edit/master/website/',
          editLocalizedFiles: true,
        },
        blog: false,
        theme: {
          customCss: require.resolve('./src/css/custom.css'),
        },
      }),
    ],
  ],

  themeConfig:
    /** @type {import('@docusaurus/preset-classic').ThemeConfig} */
    ({
      navbar: {
        title: 'Light Portal Docs',
        logo: {
          alt: 'Logo',
          src: 'https://user-images.githubusercontent.com/229402/143980485-16ba84b8-9d8d-4c06-abeb-af949d594f66.png',
        },
        items: [
          {
            href: 'https://demo.dragomano.ru',
            label: 'Demo',
            position: 'left',
          },
          {
            type: 'localeDropdown',
            position: 'right',
          },
          {
            href: 'https://github.com/dragomano/Light-Portal',
            label: 'GitHub',
            position: 'right',
          },
        ],
      },
      footer: {
        style: 'dark',
        copyright: `<a href="https://crowdin.com/project/light-portal">Help us improve the documentation</a>`,
      },
      prism: {
        theme: lightTheme,
        darkTheme: darkTheme,
        additionalLanguages: ['php', 'latte'],
      },
    }),

  i18n: {
    defaultLocale: 'en',
    locales: ['en', 'ru', 'el', 'de'],
  },

  plugins: [
    [
      'docusaurus-plugin-yandex-metrica',
      {
        counterID: '89104842',
        webvisor: true,
        trackHash: true,
      },
    ],
    [
      require.resolve('@cmfcmf/docusaurus-search-local'),
      {
        language: ['en', 'ru', 'es', 'de'],
        indexBlog: false,
      },
    ],
  ],
};

module.exports = config;
