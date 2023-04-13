// @ts-check
// Note: type annotations allow type checking and IDEs autocompletion

const lightCodeTheme = require('prism-react-renderer/themes/oceanicNext');
const darkCodeTheme = require('prism-react-renderer/themes/palenight');

/** @type {import('@docusaurus/types').Config} */
const config = {
  title: 'Light Portal Docs',
  tagline: 'Be simplier',
  url: 'https://dragomano.github.io',
  baseUrl: '/Light-Portal/',
  onBrokenLinks: 'throw',
  onBrokenMarkdownLinks: 'warn',
  favicon: 'favicon.ico',
  organizationName: 'dragomano', // Usually your GitHub org/user name.
  projectName: 'Light-Portal', // Usually your repo name.
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
          // Please change this to your repo.
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
            position: 'left'
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
        theme: lightCodeTheme,
        darkTheme: darkCodeTheme,
        additionalLanguages: ['php'],
      },
    }),

    i18n: {
      defaultLocale: 'en',
      locales: ['en', 'ru', 'el', 'cs', 'da', 'nl', 'no', 'sv', 'es'],
    },

    plugins: [
      [
        'docusaurus-plugin-yandex-metrica', {
          counterID: '89104842',
          webvisor: true,
          trackHash: true
        },
      ],
      [
        require.resolve("@cmfcmf/docusaurus-search-local"),
        {
          language: ['en', 'ru', 'da', 'nl', 'no', 'sv', 'es'],
          indexBlog: false,
        },
      ],
    ],
};

module.exports = config;
